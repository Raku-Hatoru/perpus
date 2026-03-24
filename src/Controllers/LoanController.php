<?php

class LoanController
{
    private PDO $db;
    private LoanModel $loanModel;
    private WaitlistModel $waitlistModel;
    private int $activeLoanLimit = 3;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loanModel = new LoanModel();
        $this->waitlistModel = new WaitlistModel();
    }

    public function getActiveLoanLimit(): int
    {
        return $this->activeLoanLimit;
    }

    public function setActiveLoanLimit(int $activeLoanLimit): void
    {
        $this->activeLoanLimit = max(1, $activeLoanLimit);
    }

    public function borrowBook(int $userId, int $bookId): bool
    {
        if ($userId <= 0 || $bookId <= 0) {
            Session::setFlash('error', 'Permintaan peminjaman tidak valid.');
            return false;
        }

        if ($this->loanModel->countActiveLoans($userId) >= $this->getActiveLoanLimit()) {
            Session::setFlash(
                'warning',
                'Batas peminjaman tercapai. Member hanya boleh meminjam maksimal ' . $this->getActiveLoanLimit() . ' buku aktif.'
            );
            return false;
        }

        if ($this->loanModel->hasActiveLoan($userId, $bookId)) {
            Session::setFlash('warning', 'Buku ini masih sedang kamu pinjam.');
            return false;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare('SELECT * FROM books WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $bookId]);
            $book = $stmt->fetch();

            if (!$book) {
                $this->db->rollBack();
                Session::setFlash('error', 'Buku tidak ditemukan.');
                return false;
            }

            if ((int) $book['stock'] <= 0) {
                $this->db->rollBack();
                $message = 'Stok buku sedang habis.';

                if ($this->waitlistModel->isReady()) {
                    if ($this->waitlistModel->hasActiveWaitlist($userId, $bookId)) {
                        $message .= ' Kamu sudah ada di antrian tunggu.';
                    } else {
                        $message .= ' Kamu bisa masuk antrian tunggu dari halaman buku.';
                    }
                }

                Session::setFlash('warning', $message);
                return false;
            }

            $this->db->prepare('UPDATE books SET stock = stock - 1 WHERE id = :id')->execute(['id' => $bookId]);

            $this->loanModel->createLoan([
                'user_id' => $userId,
                'book_id' => $bookId,
                'loan_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime('+7 days')),
            ]);

            if ($this->waitlistModel->isReady()) {
                $this->waitlistModel->markAsFulfilled($userId, $bookId);
            }

            $this->db->commit();
            Session::setFlash('success', 'Buku berhasil dipinjam. Batas pengembalian 7 hari.');
            return true;
        } catch (Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            Session::setFlash('error', 'Peminjaman gagal diproses.');
            return false;
        }
    }

    public function joinWaitlist(int $userId, int $bookId): bool
    {
        if (!$this->waitlistModel->isReady()) {
            Session::setFlash('warning', 'Fitur antrian tunggu belum aktif karena tabel `waitlists` belum tersedia.');
            return false;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare('SELECT stock, title FROM books WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $bookId]);
            $book = $stmt->fetch();

            if (!$book) {
                $this->db->rollBack();
                Session::setFlash('error', 'Buku tidak ditemukan.');
                return false;
            }

            if ((int) $book['stock'] > 0) {
                $this->db->rollBack();
                Session::setFlash('info', 'Stok buku sudah tersedia. Kamu bisa langsung meminjam tanpa antri.');
                return false;
            }

            if ($this->waitlistModel->hasActiveWaitlist($userId, $bookId)) {
                $this->db->rollBack();
                Session::setFlash('warning', 'Kamu sudah terdaftar dalam antrian buku ini.');
                return false;
            }

            $created = $this->waitlistModel->joinQueue($userId, $bookId);
            if (!$created) {
                $this->db->rollBack();
                Session::setFlash('error', 'Gagal menambahkan ke antrian tunggu.');
                return false;
            }

            $this->db->commit();
            Session::setFlash('success', 'Kamu berhasil masuk antrian tunggu untuk buku "' . e($book['title']) . '".');
            return true;
        } catch (Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            Session::setFlash('error', 'Antrian tunggu gagal diproses.');
            return false;
        }
    }

    public function returnBook(int $loanId, int $requestUserId, string $role): bool
    {
        try {
            $this->db->beginTransaction();

            $loan = $this->loanModel->findBorrowedLoanById($loanId);
            if (!$loan) {
                $this->db->rollBack();
                Session::setFlash('error', 'Data pinjaman tidak ditemukan atau sudah dikembalikan.');
                return false;
            }

            if ($role !== 'admin' && (int) $loan['user_id'] !== $requestUserId) {
                $this->db->rollBack();
                Session::setFlash('error', 'Kamu tidak bisa mengembalikan pinjaman ini.');
                return false;
            }

            $fineAmount = $this->calculateFine($loan['due_date']);
            $this->loanModel->markAsReturned($loanId, $fineAmount);
            $this->db->prepare('UPDATE books SET stock = stock + 1 WHERE id = :id')->execute([
                'id' => $loan['book_id'],
            ]);

            $this->db->commit();
            $message = 'Buku berhasil dikembalikan.';

            if ($fineAmount > 0) {
                $message .= ' Denda keterlambatan: Rp ' . number_format($fineAmount, 0, ',', '.');
            }

            if ($this->waitlistModel->isReady()) {
                $message .= ' Member yang sedang mengantri akan melihat badge notifikasi saat buku tersedia.';
            }

            Session::setFlash('success', $message);
            return true;
        } catch (Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            Session::setFlash('error', 'Pengembalian gagal diproses.');
            return false;
        }
    }

    public function generateMonthlyReport(int $month, int $year): array
    {
        return $this->loanModel->getMonthlyStats($month, $year);
    }

    private function calculateFine(string $dueDate): float
    {
        $due = new DateTime($dueDate);
        $today = new DateTime(date('Y-m-d'));

        if ($today <= $due) {
            return 0;
        }

        return (float) ($today->diff($due)->days * 1000);
    }
}

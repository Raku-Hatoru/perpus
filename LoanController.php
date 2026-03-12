<?php
require_once 'WaitlistModel.php';
require_once 'LoanModel.php';
class LoanController {
    private $db;
    private $loanModel;
    private $waitlistModel;

    public function __construct() {
        $this->db = Database::getInstance(); // Untuk Transaction
        $this->loanModel = new LoanModel();
    }

    // FITUR 5 & TANTANGAN BONUS (PDO Transaction)
    public function borrowBook($userId, $bookId) {
        try {
            $this->db->beginTransaction();

            // Kunci baris data buku ini agar tidak bisa dipinjam orang lain di detik yang sama (Race Condition)
            $stmt = $this->db->prepare("SELECT stock FROM books WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $bookId]);
            $book = $stmt->fetch();

            if ($book['stock'] <= 0) {
                // Sengaja lempar Exception khusus agar ditangkap di block CATCH
                throw new Exception("STOK_HABIS"); 
            }

            // Jika aman, kurangi stok dan catat peminjaman
            $this->db->prepare("UPDATE books SET stock = stock - 1 WHERE id = :id")->execute(['id' => $bookId]);
            
            $this->loanModel->createLoan([
                'user_id' => $userId,
                'book_id' => $bookId,
                'loan_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime('+7 days'))
            ]);

            $this->db->commit();
            Session::setFlash('success', 'Buku berhasil dipinjam!');
            
        } catch (Exception $e) {
            $this->db->rollBack(); // Batalkan semua operasi database jika gagal
            
            if ($e->getMessage() === "STOK_HABIS") {
                // Tampilkan tombol untuk masuk ke antrian di View
                Session::setFlash('warning', 'Stok habis! <a href="join_waitlist.php?book_id='.$bookId.'">Klik di sini untuk masuk daftar antrian</a>');
            } else {
                Session::setFlash('error', 'Terjadi kesalahan sistem.');
            }
        }
    }
    // 2. LOGIKA ANTRIAN: Masuk Daftar Tunggu
    public function processWaitlist($userId, $bookId) {
        if ($this->waitlistModel->joinQueue($userId, $bookId)) {
            Session::setFlash('success', 'Anda berhasil masuk antrian. Kami akan memberi notifikasi jika buku tersedia.');
        }
    }
    // 3. PDO TRANSACTION: Proses Mengembalikan Buku
    public function returnBook($loanId, $bookId) {
        try {
            $this->db->beginTransaction();
            
            // Ubah status jadi 'returned'
            $this->db->prepare("UPDATE loans SET status = 'returned', return_date = CURRENT_DATE WHERE id = :id")
                    ->execute(['id' => $loanId]);
                    
            // Kembalikan stok buku (+1) -> Ini akan memicu notifikasi otomatis bagi yang mengantri
            $this->db->prepare("UPDATE books SET stock = stock + 1 WHERE id = :id")
                    ->execute(['id' => $bookId]);
                    
            $this->db->commit();
            Session::setFlash('success', 'Buku dikembalikan. Terima kasih!');
        } catch (Exception $e) {
            $this->db->rollBack();
            Session::setFlash('error', 'Gagal memproses pengembalian.');
        }
    }

    // FITUR 4: Laporan Rekap Bulanan
    public function generateMonthlyReport($month, $year) {
        return $this->loanModel->getMonthlyStats($month, $year);
    }
}
<?php
require_once __DIR__ . '/../includes/layout.php';

requireRole('member');

$bookModel = new BookModel();
$loanModel = new LoanModel();
$loanController = new LoanController();
$waitlistModel = new WaitlistModel();

$activeLoans = $loanModel->getUserActiveLoans((int) $_SESSION['user_id']);
$loanHistory = $loanModel->getUserLoanHistory((int) $_SESSION['user_id'], 6);
$nearestLoan = $loanModel->getNearestDueLoan((int) $_SESSION['user_id']);
$recentBooks = $bookModel->getRecentBooks(4);
$readyNotifications = $waitlistModel->isReady() ? $waitlistModel->getActiveNotifications((int) $_SESSION['user_id']) : [];
$waitingEntries = $waitlistModel->isReady() ? $waitlistModel->getWaitingEntries((int) $_SESSION['user_id']) : [];
$activeLoanLimit = $loanController->getActiveLoanLimit();

renderPageStart('Dashboard Member', [
    'active' => 'dashboard',
]);
?>
<div class="container fade-up">
    <div class="hero-card mb-4">
        <span class="eyebrow">Dashboard Member</span>
        <h1 class="hero-title">Pantau buku yang sedang kamu pinjam dan temukan judul baru dengan lebih mudah.</h1>
        <p class="hero-text mb-0">
            Selamat datang, <?= e(currentUserName()); ?>. Halaman ini menampilkan pinjaman aktif,
            tenggat terdekat, dan riwayat bacaan berdasarkan tabel `loans`.
        </p>
        <?php if (!empty($readyNotifications)): ?>
            <div class="mt-3 d-inline-flex align-items-center gap-2">
                <span class="notification-badge"><?= count($readyNotifications); ?></span>
                <span class="fw-semibold">Buku dari antrian tunggu sudah tersedia.</span>
            </div>
        <?php endif; ?>
    </div>

    <?= Session::display(); ?>

    <?php if (!empty($readyNotifications)): ?>
        <div class="alert alert-success border-0 shadow-sm d-flex justify-content-between align-items-center mb-4">
            <div>
                <strong>Notifikasi antrian aktif</strong>
                <div>Ada <?= count($readyNotifications); ?> buku dari antrian yang sekarang sudah bisa kamu pinjam.</div>
            </div>
            <a href="#antrian" class="btn btn-brand btn-sm">Buka Antrian</a>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Pinjaman Aktif</div>
                <div class="stat-value"><?= $loanModel->countActiveLoans((int) $_SESSION['user_id']); ?></div>
                <div class="muted-text mt-2">Buku yang sedang kamu pinjam saat ini.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Riwayat Selesai</div>
                <div class="stat-value"><?= $loanModel->countReturnedLoans((int) $_SESSION['user_id']); ?></div>
                <div class="muted-text mt-2">Buku yang sudah pernah dikembalikan.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Judul Tersedia</div>
                <div class="stat-value"><?= $bookModel->countAvailableTitles(); ?></div>
                <div class="muted-text mt-2">Jumlah judul yang bisa dipinjam sekarang.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label"><?= $waitlistModel->isReady() ? 'Antrian Siap' : 'Tenggat Terdekat'; ?></div>
                <div class="stat-value stat-value-tight">
                    <?= $waitlistModel->isReady() ? count($readyNotifications) . ' notifikasi' : ($nearestLoan ? e($nearestLoan['due_date']) : 'Belum ada'); ?>
                </div>
                <div class="muted-text mt-2">
                    <?= $waitlistModel->isReady()
                        ? 'Slot pinjam aktif dibatasi maksimal ' . $activeLoanLimit . ' buku.'
                        : ($nearestLoan ? e($nearestLoan['title']) : 'Belum ada buku yang sedang dipinjam.'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="surface-card section-block h-100" id="pinjaman">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="section-title">Pinjaman Aktif Saya</div>
                        <div class="table-subtext">Kembalikan buku langsung dari sini jika sudah selesai dibaca. Maksimal <?= $activeLoanLimit; ?> buku aktif sekaligus.</div>
                    </div>
                    <a href="books.php" class="btn btn-outline-brand">Cari Buku Lagi</a>
                </div>
                <?php if (empty($activeLoans)): ?>
                    <div class="empty-state">
                        <h3 class="h5 fw-bold mb-2">Belum ada pinjaman aktif</h3>
                        <p class="mb-0">Silakan buka katalog buku untuk memulai peminjaman pertama.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Buku</th>
                                    <th>Kategori</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeLoans as $loan): ?>
                                    <?php
                                    $finePreview = max(0, (strtotime(date('Y-m-d')) - strtotime($loan['due_date'])) / 86400) * 1000;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= e($loan['title']); ?></div>
                                            <div class="table-subtext"><?= e($loan['author']); ?> · <?= e($loan['isbn']); ?></div>
                                        </td>
                                        <td><?= e($loan['category_name'] ?? 'Tanpa kategori'); ?></td>
                                        <td>
                                            <div>Pinjam: <?= e($loan['loan_date']); ?></div>
                                            <div class="table-subtext">Jatuh tempo: <?= e($loan['due_date']); ?></div>
                                            <div class="table-subtext">denda: Rp <?= number_format((float) $finePreview, 0, ',', '.'); ?></div>
                                        </td>
                                        <td>
                                            <form action="return_book.php" method="POST" class="d-inline">
                                                <input type="hidden" name="loan_id" value="<?= (int) $loan['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-brand">Kembalikan</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="surface-card section-block h-100">
                <div class="section-title mb-3">Buku Baru di Katalog</div>
                <div class="d-grid gap-3">
                    <?php foreach ($recentBooks as $book): ?>
                        <div class="mini-card">
                            <div class="fw-semibold"><?= e($book['title']); ?></div>
                            <div class="table-subtext"><?= e($book['author']); ?></div>
                            <div class="table-subtext mb-2"><?= e($book['category_name'] ?? 'Tanpa kategori'); ?></div>
                            <span class="cover-chip">Stok <?= (int) $book['stock']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($waitlistModel->isReady()): ?>
        <div class="surface-card section-block mb-4" id="antrian">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="section-title">Antrian Tunggu</div>
                    <div class="table-subtext">Saat stok buku habis, kamu bisa ikut antrian dan akan mendapat badge otomatis ketika stok kembali tersedia.</div>
                </div>
                <span class="pill"><?= count($waitingEntries); ?> antrean</span>
            </div>

            <?php if (empty($waitingEntries)): ?>
                <div class="empty-state">
                    <h3 class="h5 fw-bold mb-2">Belum ada antrian aktif</h3>
                    <p class="mb-0">Jika stok buku habis, gunakan tombol antrian dari halaman katalog buku.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Posisi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($waitingEntries as $entry): ?>
                                <?php
                                $isReady = false;
                                foreach ($readyNotifications as $notification) {
                                    if ((int) $notification['book_id'] === (int) $entry['book_id']) {
                                        $isReady = true;
                                        break;
                                    }
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= e($entry['title']); ?></div>
                                        <div class="table-subtext"><?= e($entry['author']); ?></div>
                                    </td>
                                    <td>#<?= (int) $entry['queue_position']; ?></td>
                                    <td>
                                        <span class="loan-status <?= $isReady ? 'borrowed' : 'returned'; ?>">
                                            <?= $isReady ? 'Siap Dipinjam' : 'Menunggu'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($isReady): ?>
                                            <?php if (count($activeLoans) >= $activeLoanLimit): ?>
                                                <button type="button" class="btn btn-sm btn-outline-brand" disabled>Batas 3 Buku</button>
                                            <?php else: ?>
                                                <form action="borrow_book.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="book_id" value="<?= (int) $entry['book_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-brand">Pinjam Sekarang</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="table-subtext">Stok saat ini <?= (int) $entry['stock']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="surface-card section-block">
        <div class="section-title mb-3">Riwayat Pinjaman Terakhir</div>
        <?php if (empty($loanHistory)): ?>
            <div class="empty-state">
                <h3 class="h5 fw-bold mb-2">Riwayat masih kosong</h3>
                <p class="mb-0">Setelah ada transaksi pinjaman, histori akan muncul di sini.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th>Status</th>
                            <th>Jatuh Tempo</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loanHistory as $loan): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($loan['title']); ?></div>
                                    <div class="table-subtext"><?= e($loan['author']); ?> · <?= e($loan['isbn']); ?></div>
                                </td>
                                <td>
                                    <span class="loan-status <?= e($loan['status']); ?>"><?= e(ucfirst($loan['status'])); ?></span>
                                </td>
                                <td><?= e($loan['due_date']); ?></td>
                                <td>Rp <?= number_format((float) $loan['fine_amount'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php renderPageEnd(); ?>

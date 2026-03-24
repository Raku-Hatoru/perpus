<?php
require_once __DIR__ . '/../includes/layout.php';

requireRole('admin');

$bookModel = new BookModel();
$categoryModel = new CategoryModel();
$loanModel = new LoanModel();
$userModel = new UserModel();

$recentBooks = $bookModel->getRecentBooks(5);
$lowStockBooks = $bookModel->getLowStockBooks(5);
$recentMembers = $userModel->getRecentUsers('member', 5);
$recentLoans = $loanModel->getRecentLoans(6);
$monthlyReport = $loanModel->getMonthlyStats((int) date('n'), (int) date('Y'));

renderPageStart('Dashboard Admin', [
    'active' => 'dashboard',
]);
?>
<div class="container fade-up">
    <div class="hero-card mb-4">
        <span class="eyebrow">Dashboard Admin</span>
        <h1 class="hero-title">Kelola perpustakaan dengan data yang sesuai role, stok, kategori, dan pinjaman.</h1>
        <p class="hero-text mb-0">
            Selamat datang, <?= e(currentUserName()); ?>. Panel ini menampilkan ringkasan operasional utama
            berdasarkan tabel `users`, `books`, `categories`, dan `loans`.
        </p>
        <div class="mt-3">
            <a href="reports.php" class="btn btn-brand">Buka Laporan Bulanan</a>
        </div>
    </div>

    <?= Session::display(); ?>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Total Judul Tersedia</div>
                <div class="stat-value"><?= $bookModel->countAvailableTitles(); ?></div>
                <div class="muted-text mt-2">Jumlah judul buku dengan stok di atas nol.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Total Kategori</div>
                <div class="stat-value"><?= $categoryModel->countCategories(); ?></div>
                <div class="muted-text mt-2">Kategori aktif untuk pengelompokan buku.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Member Terdaftar</div>
                <div class="stat-value"><?= $userModel->countByRole('member'); ?></div>
                <div class="muted-text mt-2">Jumlah pengguna dengan role member.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Pinjaman Aktif</div>
                <div class="stat-value"><?= $loanModel->countActiveLoans(); ?></div>
                <div class="muted-text mt-2">Transaksi dengan status `borrowed`.</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="surface-card section-block h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="section-title">Pinjaman Terbaru</div>
                        <div class="table-subtext">Pantau siapa meminjam buku dan statusnya saat ini.</div>
                    </div>
                    <span class="pill">Denda terkumpul Rp <?= number_format($loanModel->totalCollectedFines(), 0, ',', '.'); ?></span>
                </div>
                <?php if (empty($recentLoans)): ?>
                    <div class="empty-state">
                        <h3 class="h5 fw-bold mb-2">Belum ada data pinjaman</h3>
                        <p class="mb-0">Transaksi pinjaman akan tampil di sini setelah member mulai meminjam buku.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Peminjam</th>
                                    <th>Buku</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLoans as $loan): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= e($loan['username']); ?></div>
                                            <div class="table-subtext"><?= e(roleLabel($loan['role'])); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= e($loan['title']); ?></div>
                                            <div class="table-subtext"><?= e($loan['isbn']); ?></div>
                                        </td>
                                        <td>
                                            <div><?= e($loan['loan_date']); ?></div>
                                            <div class="table-subtext">Jatuh tempo <?= e($loan['due_date']); ?></div>
                                        </td>
                                        <td>
                                            <span class="loan-status <?= e($loan['status']); ?>"><?= e(ucfirst($loan['status'])); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($loan['status'] === 'borrowed'): ?>
                                                <form action="return_book.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="loan_id" value="<?= (int) $loan['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-brand">Proses Kembali</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="table-subtext">Selesai</span>
                                            <?php endif; ?>
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
            <div class="surface-card section-block mb-4">
                <div class="section-title mb-3">Stok Perlu Perhatian</div>
                <?php if (empty($lowStockBooks)): ?>
                    <div class="empty-state">
                        <h3 class="h5 fw-bold mb-2">Stok aman</h3>
                        <p class="mb-0">Belum ada buku dengan stok 3 atau kurang.</p>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-3">
                        <?php foreach ($lowStockBooks as $book): ?>
                            <div class="mini-card">
                                <div class="fw-semibold"><?= e($book['title']); ?></div>
                                <div class="table-subtext mb-2"><?= e($book['category_name'] ?? 'Tanpa kategori'); ?></div>
                                <span class="cover-chip">Sisa stok <?= (int) $book['stock']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="surface-card section-block">
                <div class="section-title mb-3">Buku Terbaru</div>
                <div class="d-grid gap-3">
                    <?php foreach ($recentBooks as $book): ?>
                        <div class="mini-card">
                            <div class="fw-semibold"><?= e($book['title']); ?></div>
                            <div class="table-subtext"><?= e($book['author']); ?></div>
                            <div class="table-subtext mb-2"><?= e($book['category_name'] ?? 'Tanpa kategori'); ?></div>
                            <span class="cover-chip">ISBN <?= e($book['isbn']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="surface-card section-block h-100">
                <div class="section-title mb-3">Member Baru</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentMembers as $member): ?>
                                <tr>
                                    <td><?= e($member['username']); ?></td>
                                    <td><?= e($member['email']); ?></td>
                                    <td><?= e($member['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="surface-card section-block h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title">Rekap Bulan Ini</div>
                    <a href="reports.php" class="btn btn-sm btn-outline-brand">Lihat Tabel Lengkap</a>
                </div>
                <?php if (empty($monthlyReport)): ?>
                    <div class="empty-state">
                        <h3 class="h5 fw-bold mb-2">Belum ada rekap</h3>
                        <p class="mb-0">Data peminjaman bulan ini akan muncul otomatis setelah ada transaksi.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Buku</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($monthlyReport, 0, 5) as $row): ?>
                                    <tr>
                                        <td><?= e($row['title']); ?></td>
                                        <td><?= (int) $row['total_borrowed']; ?> kali</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php renderPageEnd(); ?>

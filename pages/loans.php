<?php
require_once __DIR__ . '/../includes/layout.php';

requireRole('admin');

$loanModel = new LoanModel();
$keyword = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 10;
$totalRows = $loanModel->countFiltered($keyword, $statusFilter);
$totalPages = max(1, (int) ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
}

$loans = $loanModel->getPaginated($limit, ($page - 1) * $limit, $keyword, $statusFilter);

renderPageStart('Daftar Pinjaman', [
    'active' => 'loans',
]);
?>
<div class="container fade-up">
    <div class="hero-card mb-4">
        <span class="eyebrow">Manajemen Pinjaman</span>
        <h1 class="hero-title">Pantau semua transaksi peminjaman.</h1>
        <p class="hero-text mb-0">
            mencari peminjam atau buku, memfilter status pinjaman.
        </p>
    </div>

    <?= Session::display(); ?>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Total Pinjaman</div>
                <div class="stat-value"><?= $loanModel->countAll(); ?></div>
                <div class="muted-text mt-2">Seluruh transaksi pada tabel `loans`.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Masih Dipinjam</div>
                <div class="stat-value"><?= $loanModel->countActiveLoans(); ?></div>
                <div class="muted-text mt-2">Transaksi dengan status `borrowed`.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Total Denda</div>
                <div class="stat-value stat-value-compact">Rp <?= number_format($loanModel->totalCollectedFines(), 0, ',', '.'); ?></div>
                <div class="muted-text mt-2">Akumulasi `fine_amount` dari semua pinjaman.</div>
            </div>
        </div>
    </div>

    <div class="surface-card section-block">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <div class="section-title">Daftar Loans</div>
                <div class="table-subtext">Pencarian mendukung username, email, judul buku, dan ISBN.</div>
            </div>
            <a href="reports.php" class="btn btn-outline-brand">Buka Rekap Bulanan</a>
        </div>

        <form method="GET" action="loans.php" class="row g-3 align-items-end mb-4">
            <div class="col-md-6">
                <label class="form-label">Cari Pinjaman</label>
                <input type="text" name="search" class="form-control" placeholder="Username, email, judul, atau ISBN..." value="<?= e($keyword); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="borrowed" <?= $statusFilter === 'borrowed' ? 'selected' : ''; ?>>Borrowed</option>
                    <option value="returned" <?= $statusFilter === 'returned' ? 'selected' : ''; ?>>Returned</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-brand flex-fill">Terapkan</button>
                <a href="loans.php" class="btn btn-outline-brand flex-fill">Reset</a>
            </div>
        </form>

        <?php if (empty($loans)): ?>
            <div class="empty-state">
                <h3 class="h5 fw-bold mb-2">Pinjaman tidak ditemukan</h3>
                <p class="mb-0">Belum ada transaksi yang cocok dengan filter saat ini.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $index => $loan): ?>
                            <tr>
                                <td><span class="number-badge"><?= (($page - 1) * $limit) + $index + 1; ?></span></td>
                                <td>
                                    <div class="fw-semibold"><?= e($loan['username']); ?></div>
                                    <div class="table-subtext"><?= e($loan['email']); ?> · <?= e(roleLabel($loan['role'])); ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= e($loan['title']); ?></div>
                                    <div class="table-subtext"><?= e($loan['isbn']); ?> · <?= e($loan['category_name'] ?? 'Tanpa kategori'); ?></div>
                                </td>
                                <td>
                                    <div>Pinjam: <?= e($loan['loan_date']); ?></div>
                                    <div class="table-subtext">Jatuh tempo: <?= e($loan['due_date']); ?></div>
                                    <div class="table-subtext">Kembali: <?= e($loan['return_date'] ?? '-'); ?></div>
                                </td>
                                <td>
                                    <span class="loan-status <?= e($loan['status']); ?>"><?= e(ucfirst($loan['status'])); ?></span>
                                </td>
                                <td>Rp <?= number_format((float) $loan['fine_amount'], 0, ',', '.'); ?></td>
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

            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?><?= $keyword !== '' ? '&search=' . urlencode($keyword) : ''; ?><?= $statusFilter !== '' ? '&status=' . urlencode($statusFilter) : ''; ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php renderPageEnd(); ?>

<?php
require_once __DIR__ . '/../includes/layout.php';

requireRole('admin');

$loanModel = new LoanModel();
$selectedMonth = max(1, min(12, (int) ($_GET['month'] ?? date('n'))));
$selectedYear = max(2020, min(2100, (int) ($_GET['year'] ?? date('Y'))));

$reportRows = $loanModel->getMonthlyDetailedReport($selectedMonth, $selectedYear);
$totalLoans = $loanModel->countMonthlyLoans($selectedMonth, $selectedYear);
$totalReturned = $loanModel->countMonthlyReturned($selectedMonth, $selectedYear);
$totalFines = $loanModel->totalMonthlyFines($selectedMonth, $selectedYear);

renderPageStart('Laporan Bulanan', [
    'active' => 'reports',
]);
?>
<div class="container fade-up">
    <div class="hero-card mb-4">
        <span class="eyebrow">Laporan Bulanan</span>
        <h1 class="hero-title">Rekap peminjaman per bulan dalam format tabel.</h1>
        <p class="hero-text mb-0">Gunakan filter bulan dan tahun untuk melihat buku terpopuler, jumlah pengembalian, dan total denda.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value"><?= $totalLoans; ?></div>
                <div class="muted-text mt-2">Jumlah pinjaman pada bulan yang dipilih.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Sudah Dikembalikan</div>
                <div class="stat-value"><?= $totalReturned; ?></div>
                <div class="muted-text mt-2">Transaksi dengan status returned.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Total Denda</div>
                <div class="stat-value stat-value-compact">Rp <?= number_format($totalFines, 0, ',', '.'); ?></div>
                <div class="muted-text mt-2">Akumulasi fine_amount pada periode terpilih.</div>
            </div>
        </div>
    </div>

    <div class="surface-card section-block">
        <form method="GET" action="reports.php" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label">Bulan</label>
                <select name="month" class="form-select">
                    <?php for ($month = 1; $month <= 12; $month++): ?>
                        <option value="<?= $month; ?>" <?= $month === $selectedMonth ? 'selected' : ''; ?>>
                            <?= date('F', mktime(0, 0, 0, $month, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tahun</label>
                <input type="number" name="year" class="form-control" min="2020" max="2100" value="<?= $selectedYear; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-brand w-100">Tampilkan Rekap</button>
            </div>
        </form>

        <?php if (empty($reportRows)): ?>
            <div class="empty-state">
                <h3 class="h5 fw-bold mb-2">Belum ada data laporan</h3>
                <p class="mb-0">Belum ada transaksi peminjaman untuk periode <?= date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)); ?>.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th>Kategori</th>
                            <th>Total Pinjam</th>
                            <th>Masih Dipinjam</th>
                            <th>Dikembalikan</th>
                            <th>Total Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportRows as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($row['title']); ?></div>
                                    <div class="table-subtext"><?= e($row['isbn']); ?></div>
                                </td>
                                <td><?= e($row['category_name'] ?? 'Tanpa kategori'); ?></td>
                                <td><?= (int) $row['total_loans']; ?></td>
                                <td><?= (int) $row['active_count']; ?></td>
                                <td><?= (int) $row['returned_count']; ?></td>
                                <td>Rp <?= number_format((float) $row['total_fines'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php renderPageEnd(); ?>

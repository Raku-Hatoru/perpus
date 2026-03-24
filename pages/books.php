<?php
require_once __DIR__ . '/../includes/layout.php';

requireLogin();

$bookController = new BookController();
$bookModel = new BookModel();
$loanModel = new LoanModel();
$loanController = new LoanController();
$waitlistModel = new WaitlistModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book_id']) && currentUserRole() === 'admin') {
    $bookController->delete((int) $_POST['delete_book_id']);
    header('Location: books.php');
    exit;
}

$keyword = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $bookController->index($page, $keyword);
$books = $result['data'];
$activeBookIds = [];
$activeLoanCount = 0;
$activeLoanLimit = $loanController->getActiveLoanLimit();
$waitlistedBookIds = [];
$readyWaitlistBookIds = [];
$readyNotificationCount = 0;

if (currentUserRole() === 'member') {
    $activeLoans = $loanModel->getUserActiveLoans((int) $_SESSION['user_id']);
    $activeLoanCount = count($activeLoans);
    $activeBookIds = array_map(
        static fn(array $loan): int => (int) $loan['book_id'],
        $activeLoans
    );

    if ($waitlistModel->isReady()) {
        $waitlistedBookIds = $waitlistModel->getWaitlistedBookIds((int) $_SESSION['user_id']);
        $readyNotifications = $waitlistModel->getActiveNotifications((int) $_SESSION['user_id']);
        $readyWaitlistBookIds = array_map(
            static fn(array $item): int => (int) $item['book_id'],
            $readyNotifications
        );
        $readyNotificationCount = count($readyNotifications);
    }
}

renderPageStart('Daftar Buku', [
    'active' => 'books',
]);
?>
<div class="container fade-up">
    <div class="hero-card mb-4">
        <span class="eyebrow"><?= currentUserRole() === 'admin' ? 'Katalog Admin' : 'Katalog Member'; ?></span>
        <h1 class="hero-title">Daftar buku perpustakaan dengan akses dan aksi yang menyesuaikan role login.</h1>
        <p class="hero-text mb-0">
            <?= currentUserRole() === 'admin'
                ? 'Admin dapat menambah, mengedit, dan menghapus buku, sementara member hanya melihat ketersediaan dan meminjam buku.'
                : 'Member dapat mencari buku, melihat stok, dan meminjam judul yang masih tersedia.'; ?>
        </p>
    </div>

    <?= Session::display(); ?>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Buku Ditampilkan</div>
                <div class="stat-value"><?= (int) $result['total_rows']; ?></div>
                <div class="muted-text mt-2">Total hasil berdasarkan pencarian aktif.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Judul Siap Pinjam</div>
                <div class="stat-value"><?= $bookModel->countAvailableTitles(); ?></div>
                <div class="muted-text mt-2">Jumlah judul dengan stok di atas nol.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label">Total Eksemplar</div>
                <div class="stat-value"><?= $bookModel->totalStock(); ?></div>
                <div class="muted-text mt-2">Akumulasi seluruh stok yang tercatat.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-label"><?= currentUserRole() === 'member' ? 'Pinjaman Aktif' : 'Mode Halaman'; ?></div>
                <div class="stat-value stat-value-compact">
                    <?= currentUserRole() === 'member' ? $activeLoanCount . ' / ' . $activeLoanLimit : e(roleLabel(currentUserRole())); ?>
                </div>
                <div class="muted-text mt-2">
                    <?= currentUserRole() === 'member'
                        ? 'membatasi maksimal ' . $activeLoanLimit . ' buku aktif.'
                        : 'Tampilan dan tombol mengikuti role yang aktif.'; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (currentUserRole() === 'member' && $readyNotificationCount > 0): ?>
        <div class="alert alert-success border-0 shadow-sm d-flex justify-content-between align-items-center mb-4">
            <div>
                <strong><?= $readyNotificationCount; ?> notifikasi antrian siap</strong>
                <div>Kamu punya buku dari antrian tunggu yang sekarang sudah tersedia.</div>
            </div>
            <a href="member_dashboard.php#antrian" class="btn btn-brand btn-sm">Lihat Dashboard</a>
        </div>
    <?php endif; ?>

    <div class="surface-card section-block">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <div class="section-title">Katalog Buku</div>
                <div class="table-subtext">Cari berdasarkan ISBN, judul, penulis, penerbit, atau kategori dengan highlight keyword pada hasil.</div>
            </div>
            <?php if (currentUserRole() === 'admin'): ?>
                <a href="add_book.php" class="btn btn-brand">Tambah Buku Baru</a>
            <?php endif; ?>
        </div>

        <form method="GET" action="books.php" class="mb-4">
            <div class="search-box input-group">
                <span class="input-group-text">Cari</span>
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Masukkan kata kunci buku..."
                    value="<?= e($keyword); ?>"
                >
                <button type="submit" class="btn btn-brand px-4">Telusuri</button>
                <?php if ($keyword !== ''): ?>
                    <a href="books.php" class="btn btn-outline-brand px-4">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($books)): ?>
            <div class="empty-state">
                <h3 class="h5 fw-bold mb-2">Tidak ada buku ditemukan</h3>
                <p class="mb-0">
                    <?= $keyword !== '' ? 'Coba gunakan kata kunci lain untuk pencarianmu.' : 'Belum ada data buku yang tersimpan di database.'; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>ISBN</th>
                            <th>Buku</th>
                            <th>Detail</th>
                            <th>Stok</th>
                            <th class="action-col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <?php $alreadyBorrowed = in_array((int) $book['id'], $activeBookIds, true); ?>
                            <?php $alreadyWaitlisted = in_array((int) $book['id'], $waitlistedBookIds, true); ?>
                            <?php $waitlistReady = in_array((int) $book['id'], $readyWaitlistBookIds, true); ?>
                            <tr>
                                <td><?= e($book['isbn']); ?></td>
                                <td>
                                    <div class="fw-semibold"><?= $keyword !== '' ? highlightText($book['title'], $keyword) : e($book['title']); ?></div>
                                    <div class="table-subtext"><?= $keyword !== '' ? highlightText($book['author'], $keyword) : e($book['author']); ?></div>
                                </td>
                                <td>
                                    <div><?= $keyword !== '' ? highlightText($book['category_name'] ?? 'Tanpa kategori', $keyword) : e($book['category_name'] ?? 'Tanpa kategori'); ?></div>
                                    <div class="table-subtext">
                                        <?= $keyword !== '' ? highlightText($book['publisher'] ?: 'Penerbit belum diisi', $keyword) : e($book['publisher'] ?: 'Penerbit belum diisi'); ?>
                                        · <?= e($book['year']); ?>
                                    </div>
                                    <?php if ($waitlistReady): ?>
                                        <div class="table-subtext text-success fw-semibold mt-1">Notifikasi antrian aktif untuk buku ini.</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int) $book['stock'] > 0): ?>
                                        <span class="cover-chip">Tersedia <?= (int) $book['stock']; ?></span>
                                    <?php else: ?>
                                        <span class="cover-chip stock-chip-empty">Stok Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if (currentUserRole() === 'member'): ?>
                                            <?php if ($alreadyBorrowed): ?>
                                                <button type="button" class="btn btn-sm btn-outline-brand" disabled>Sudah Dipinjam</button>
                                            <?php elseif ($activeLoanCount >= $activeLoanLimit): ?>
                                                <button type="button" class="btn btn-sm btn-outline-brand" disabled>Batas 3 Buku</button>
                                            <?php elseif ((int) $book['stock'] > 0): ?>
                                                <form action="borrow_book.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="book_id" value="<?= (int) $book['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-brand">Pinjam</button>
                                                </form>
                                            <?php elseif ($alreadyWaitlisted): ?>
                                                <button type="button" class="btn btn-sm btn-outline-brand" disabled>Dalam Antrian</button>
                                            <?php elseif ($waitlistModel->isReady()): ?>
                                                <form action="join_waitlist.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="book_id" value="<?= (int) $book['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-brand">Masuk Antrian</button>
                                                </form>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-brand" disabled>Tidak Tersedia</button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="edit_book.php?id=<?= (int) $book['id']; ?>" class="btn btn-sm btn-outline-brand">Edit</a>
                                            <form action="books.php" method="POST" class="d-inline" onsubmit="return confirm('Hapus buku ini dari katalog?')">
                                                <input type="hidden" name="delete_book_id" value="<?= (int) $book['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="qr.php?book_id=<?= (int) $book['id']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank">QR</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($result['total_pages'] > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
                            <li class="page-item <?= $i === $result['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?><?= $keyword !== '' ? '&search=' . urlencode($keyword) : ''; ?>">
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

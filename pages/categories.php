<?php
require_once __DIR__ . '/../includes/layout.php';

requireRole('admin');

$controller = new CategoryController();
$model = new CategoryModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_category'])) {
        $controller->store([
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
        ]);
    }

    if (isset($_POST['update_category_id'])) {
        $controller->update(
            (int) $_POST['update_category_id'],
            [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
            ]
        );
    }

    if (isset($_POST['delete_category_id'])) {
        $controller->destroy((int) $_POST['delete_category_id']);
    }

    header('Location: categories.php');
    exit;
}

$keyword = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $controller->paginate($page, $keyword);
$categories = $result['data'];
$editCategoryId = (int) ($_GET['edit'] ?? 0);
$editingCategory = null;

if ($editCategoryId > 0) {
    $editingCategory = $controller->find($editCategoryId);

    if ($editingCategory === false) {
        Session::setFlash('error', 'Kategori yang ingin diedit tidak ditemukan.');
        header('Location: categories.php');
        exit;
    }
}

renderPageStart('Kelola Kategori', [
    'active' => 'categories',
]);
?>
<div class="container fade-up">
    <div class="hero-card mb-4">
        <span class="eyebrow">Kategori Buku</span>
        <h1 class="hero-title">Atur kategori dan deskripsi buku agar katalog lebih rapi dan mudah dicari.</h1>
        <p class="hero-text mb-0">
            Halaman admin ini sekarang mendukung tambah, edit, hapus, pencarian, dan pagination 10 data per halaman untuk tabel `categories`.
        </p>
    </div>

    <?= Session::display(); ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="surface-card section-block h-100">
                <div class="section-title mb-3"><?= $editingCategory ? 'Edit Kategori' : 'Tambah Kategori'; ?></div>
                <form action="categories.php" method="POST">
                    <?php if ($editingCategory): ?>
                        <input type="hidden" name="update_category_id" value="<?= (int) $editingCategory['id']; ?>">
                    <?php else: ?>
                        <input type="hidden" name="create_category" value="1">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" value="<?= e($editingCategory['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Contoh: Koleksi novel, literasi umum, buku ilmiah, dan lainnya."><?= e($editingCategory['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-brand w-100"><?= $editingCategory ? 'Simpan Perubahan' : 'Simpan Kategori'; ?></button>
                        <?php if ($editingCategory): ?>
                            <a href="categories.php" class="btn btn-outline-brand w-100">Batal Edit</a>
                        <?php endif; ?>
                    </div>
                </form>

                <hr class="my-4">

                <div class="stat-card p-0 border-0 shadow-none bg-transparent">
                    <div class="stat-label">Total Kategori</div>
                    <div class="stat-value"><?= $model->countCategories(); ?></div>
                    <div class="muted-text mt-2">Jumlah kategori pada tabel `categories`.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="surface-card section-block h-100">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <div class="section-title">Daftar Kategori</div>
                        <div class="table-subtext">Setiap kategori menampilkan jumlah buku yang terkait dan dibatasi 10 data per halaman.</div>
                    </div>
                    <form method="GET" action="categories.php" class="w-100 search-form-compact">
                        <div class="search-box input-group">
                            <span class="input-group-text">Cari</span>
                            <input type="text" name="search" class="form-control" placeholder="Nama atau deskripsi kategori..." value="<?= e($keyword); ?>">
                            <button type="submit" class="btn btn-brand px-3">Telusuri</button>
                            <?php if ($keyword !== ''): ?>
                                <a href="categories.php" class="btn btn-outline-brand px-3">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="mini-card">
                            <div class="stat-label">Hasil Ditampilkan</div>
                            <div class="stat-value"><?= (int) $result['total_rows']; ?></div>
                            <div class="muted-text mt-2">Total hasil untuk filter aktif.</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="mini-card">
                            <div class="stat-label">Halaman Aktif</div>
                            <div class="stat-value"><?= (int) $result['current_page']; ?></div>
                            <div class="muted-text mt-2">Maksimal <?= (int) $result['limit']; ?> data per halaman.</div>
                        </div>
                    </div>
                </div>

                <?php if (empty($categories)): ?>
                    <div class="empty-state">
                        <h3 class="h5 fw-bold mb-2">Kategori belum tersedia</h3>
                        <p class="mb-0">Tambahkan kategori baru atau ubah kata kunci pencarian.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Buku Terkait</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $index => $category): ?>
                                    <tr>
                                        <td><span class="number-badge"><?= (($result['current_page'] - 1) * $result['limit']) + $index + 1; ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?= e($category['name']); ?></div>
                                            <div class="table-subtext"><?= e($category['description'] ?: 'Belum ada deskripsi kategori.'); ?></div>
                                        </td>
                                        <td><span class="cover-chip"><?= (int) $category['book_count']; ?> buku</span></td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="categories.php?edit=<?= (int) $category['id']; ?><?= $keyword !== '' ? '&search=' . urlencode($keyword) : ''; ?><?= $result['current_page'] > 1 ? '&page=' . $result['current_page'] : ''; ?>" class="btn btn-sm btn-outline-brand">Edit</a>
                                                <form action="categories.php" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini? Relasi buku akan menjadi kosong.')">
                                                    <input type="hidden" name="delete_category_id" value="<?= (int) $category['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
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
    </div>
</div>
<?php renderPageEnd(); ?>

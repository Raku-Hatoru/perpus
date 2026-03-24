<?php
require_once __DIR__ . '/../includes/layout.php';

requireRole('admin');

$userModel = new UserModel();
$keyword = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 10;
$totalRows = $userModel->countFiltered($keyword, $roleFilter);
$totalPages = max(1, (int) ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
}

$users = $userModel->getPaginated($limit, ($page - 1) * $limit, $keyword, $roleFilter);

renderPageStart('Daftar Pengguna', [
    'active' => 'users',
]);
?>
<div class="container fade-up">
    <div class="hero-card mb-4">
        <span class="eyebrow">Manajemen Pengguna</span>
        <h1 class="hero-title">Lihat seluruh akun admin dan member.</h1>
        <p class="hero-text mb-0">
            admin memantau pertumbuhan akun sistem.
        </p>
    </div>

    <?= Session::display(); ?>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Total Pengguna</div>
                <div class="stat-value"><?= $userModel->countAll(); ?></div>
                <div class="muted-text mt-2">Akumulasi admin dan member terdaftar.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Total Admin</div>
                <div class="stat-value"><?= $userModel->countByRole('admin'); ?></div>
                <div class="muted-text mt-2">Akun dengan hak kelola sistem.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Total Member</div>
                <div class="stat-value"><?= $userModel->countByRole('member'); ?></div>
                <div class="muted-text mt-2">Akun yang dapat meminjam buku.</div>
            </div>
        </div>
    </div>

    <div class="surface-card section-block">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <div class="section-title">Daftar Users</div>
                <div class="table-subtext">pencarian, filter role, dan pagination.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="register_member.php" class="btn btn-outline-brand">Tambah Member</a>
                <a href="register_admin.php" class="btn btn-brand">Tambah Admin</a>
            </div>
        </div>

        <form method="GET" action="users.php" class="row g-3 align-items-end mb-4">
            <div class="col-md-6">
                <label class="form-label">Cari Pengguna</label>
                <input type="text" name="search" class="form-control" placeholder="Username atau email..." value="<?= e($keyword); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">Semua Role</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="member" <?= $roleFilter === 'member' ? 'selected' : ''; ?>>Member</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-brand flex-fill">Terapkan</button>
                <a href="users.php" class="btn btn-outline-brand flex-fill">Reset</a>
            </div>
        </form>

        <?php if (empty($users)): ?>
            <div class="empty-state">
                <h3 class="h5 fw-bold mb-2">Pengguna tidak ditemukan</h3>
                <p class="mb-0">Coba ubah kata kunci atau filter role yang digunakan.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><span class="number-badge"><?= (($page - 1) * $limit) + $index + 1; ?></span></td>
                                <td>
                                    <div class="fw-semibold"><?= e($user['username']); ?></div>
                                </td>
                                <td><?= e($user['email']); ?></td>
                                <td><span class="pill"><?= e(roleLabel($user['role'])); ?></span></td>
                                <td><?= e($user['created_at']); ?></td>
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
                                <a class="page-link" href="?page=<?= $i; ?><?= $keyword !== '' ? '&search=' . urlencode($keyword) : ''; ?><?= $roleFilter !== '' ? '&role=' . urlencode($roleFilter) : ''; ?>">
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

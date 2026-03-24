<?php
require_once __DIR__ . '/../includes/layout.php';

if (isLoggedIn() && currentUserRole() !== 'admin') {
    redirectByRole();
}

$userModel = new UserModel();
$adminCount = $userModel->countByRole('admin');

if ($adminCount > 0 && (!isLoggedIn() || currentUserRole() !== 'admin')) {
    Session::setFlash('warning', 'Registrasi admin hanya dapat dilakukan oleh admin yang sudah login.');
    header('Location: login_admin.php');
    exit;
}

renderPageStart('Register Admin', [
    'show_nav' => isLoggedIn(),
    'active' => isLoggedIn() ? 'register_admin' : '',
    'container_class' => isLoggedIn() ? 'app-shell' : 'auth-shell',
]);
?>
<div class="container <?= isLoggedIn() ? 'fade-up' : 'auth-grid fade-up'; ?>">
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-5">
            <div class="auth-side h-100">
                <span class="eyebrow bg-white text-dark">Register Admin</span>
                <h1 class="hero-title text-white">Buat akun admin untuk operasional perpustakaan.</h1>
                <p class="mb-0">
                    Pendaftaran Admin.
                </p>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="auth-card">
                <?= Session::display(); ?>
                <div class="section-title mb-3">Form Registrasi Admin</div>
                <form action="process_register.php" method="POST">
                    <input type="hidden" name="role" value="admin">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= Session::old('username'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= Session::old('email'); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-brand w-100 py-2">Simpan Admin</button>
                </form>
                <?php if (!isLoggedIn()): ?>
                    <div class="auth-links">
                        <a href="register.php">Pilih halaman register lain</a>
                        <a href="login_admin.php">Sudah punya akun admin?</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php renderPageEnd(); ?>

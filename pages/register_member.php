<?php
require_once __DIR__ . '/../includes/layout.php';

if (isLoggedIn()) {
    redirectByRole();
}

renderPageStart('Register Member', [
    'show_nav' => false,
    'container_class' => 'auth-shell',
]);
?>
<div class="container auth-grid fade-up">
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-5">
            <div class="auth-side h-100">
                <span class="eyebrow bg-white text-dark">Register Member</span>
                <h1 class="hero-title text-white">Buat akun member untuk menjelajah dan meminjam buku.</h1>
                <p class="mb-0">Akun member disiapkan khusus untuk akses katalog, peminjaman, dan riwayat pinjam pribadi.</p>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="auth-card">
                <?= Session::display(); ?>
                <div class="section-title mb-3">Form Registrasi Member</div>
                <form action="process_register.php" method="POST">
                    <input type="hidden" name="role" value="member">
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
                    <button type="submit" class="btn btn-brand w-100 py-2">Daftar Member</button>
                </form>
                <div class="auth-links">
                    <a href="register.php">Pilih halaman register lain</a>
                    <a href="login_member.php">Sudah punya akun member?</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php renderPageEnd(); ?>

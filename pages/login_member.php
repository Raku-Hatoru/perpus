<?php
require_once __DIR__ . '/../includes/layout.php';

redirectIfLoggedIn();

renderPageStart('Login Member', [
    'show_nav' => false,
    'container_class' => 'auth-shell',
]);
?>
<div class="container auth-grid fade-up">
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-5">
            <div class="auth-side h-100">
                <span class="eyebrow bg-white text-dark">Login Member</span>
                <h1 class="hero-title text-white">Masuk untuk melihat katalog dan meminjam buku.</h1>
                <p class="mb-0">Akun member akan diarahkan ke dashboard pinjaman pribadi, buku aktif, dan histori pengembalian.</p>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="auth-card">
                <?= Session::display(); ?>
                <div class="section-title mb-3">Form Login Member</div>
                <form action="process_login.php" method="POST">
                    <input type="hidden" name="role" value="member">
                    <div class="mb-3">
                        <label class="form-label">Email Member</label>
                        <input type="email" name="email" class="form-control" value="<?= Session::old('email'); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-brand w-100 py-2">Login Member</button>
                </form>
                <div class="auth-links">
                    <a href="login.php">Pilih halaman login lain</a>
                    <a href="register_member.php">Belum punya akun member?</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php renderPageEnd(); ?>

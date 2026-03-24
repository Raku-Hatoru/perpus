<?php
require_once __DIR__ . '/../includes/layout.php';

redirectIfLoggedIn();

renderPageStart('Pilih Login', [
    'show_nav' => false,
    'container_class' => 'auth-shell',
]);
?>
<div class="container auth-grid fade-up">
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-5">
            <div class="auth-side h-100">
                <span class="eyebrow bg-white text-dark">Akses Sistem</span>
                <h1 class="hero-title text-white">Masuk sesuai peran akun yang kamu gunakan.</h1>
                <p class="mb-4">
                    Admin dan member sekarang punya halaman login terpisah supaya alur akses, dashboard,
                    dan menu yang muncul lebih sesuai dengan role di database.
                </p>
                <ul class="helper-list">
                    <li>Admin dapat mengelola buku, kategori, dan pengguna admin lain.</li>
                    <li>Member fokus ke pencarian buku dan pengelolaan pinjaman pribadi.</li>
                    <li>Jika belum punya akun, lanjut ke halaman register yang sesuai.</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-7">
            <?= Session::display(); ?>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="auth-choice-card">
                        <span class="pill mb-3">Role Admin</span>
                        <h3 class="h4 fw-bold">Login Admin</h3>
                        <p class="muted-text mb-4">Masuk untuk mengelola data master, stok, kategori, dan rekap pinjaman.</p>
                        <a href="login_admin.php" class="btn btn-brand w-100 mb-2">Masuk sebagai Admin</a>
                        <a href="register_admin.php" class="btn btn-outline-brand w-100">Daftar Admin</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="auth-choice-card">
                        <span class="pill mb-3">Role Member</span>
                        <h3 class="h4 fw-bold">Login Member</h3>
                        <p class="muted-text mb-4">Masuk untuk melihat buku, meminjam, dan memantau status pinjaman aktif.</p>
                        <a href="login_member.php" class="btn btn-brand w-100 mb-2">Masuk sebagai Member</a>
                        <a href="register_member.php" class="btn btn-outline-brand w-100">Daftar Member</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php renderPageEnd(); ?>

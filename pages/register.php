<?php
require_once __DIR__ . '/../includes/layout.php';

redirectIfLoggedIn();

renderPageStart('Pilih Register', [
    'show_nav' => false,
    'container_class' => 'auth-shell',
]);
?>
<div class="container auth-grid fade-up">
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-5">
            <div class="auth-side h-100">
                <span class="eyebrow bg-white text-dark">Buat Akun</span>
                <h1 class="hero-title text-white">Pilih jenis akun sebelum melanjutkan registrasi.</h1>
                <p class="mb-4">
                    Form registrasi sekarang dipisah antara admin dan member agar role akun langsung
                    konsisten dengan tabel `users` pada database.
                </p>
                <ul class="helper-list">
                    <li>Member cocok untuk peminjaman dan pelacakan riwayat baca.</li>
                    <li>Admin cocok untuk pengelolaan katalog dan operasional perpustakaan.</li>
                    <li>Setelah registrasi berhasil, login dilakukan dari halaman role yang sama.</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-7">
            <?= Session::display(); ?>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="auth-choice-card">
                        <span class="pill mb-3">Operasional</span>
                        <h3 class="h4 fw-bold">Register Admin</h3>
                        <p class="muted-text mb-4">Digunakan untuk petugas yang perlu akses pengelolaan buku, kategori, dan laporan.</p>
                        <a href="register_admin.php" class="btn btn-brand w-100 mb-2">Daftar Admin</a>
                        <a href="login_admin.php" class="btn btn-outline-brand w-100">Sudah Punya Akun Admin</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="auth-choice-card">
                        <span class="pill mb-3">Peminjam</span>
                        <h3 class="h4 fw-bold">Register Member</h3>
                        <p class="muted-text mb-4">Digunakan untuk anggota perpustakaan yang ingin menjelajah dan meminjam buku.</p>
                        <a href="register_member.php" class="btn btn-brand w-100 mb-2">Daftar Member</a>
                        <a href="login_member.php" class="btn btn-outline-brand w-100">Sudah Punya Akun Member</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php renderPageEnd(); ?>

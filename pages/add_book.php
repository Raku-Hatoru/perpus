<?php
require_once __DIR__ . '/../includes/layout.php';

requireRole('admin');

$categoryController = new CategoryController();
$categories = $categoryController->index();

$formAction = 'process_add_book.php';
$formSubmitLabel = 'Simpan Buku';
$formCancelLabel = 'Kembali ke Katalog';
$formCancelUrl = 'books.php';
$formCategories = $categories;
$formValues = [
    'year' => date('Y'),
    'stock' => '0',
];
$formKey = 'book-create';
$formMode = 'create';

renderPageStart('Tambah Buku', [
    'active' => 'books',
]);
?>
<div class="container fade-up">
    <div class="hero-card mb-4">
        <span class="eyebrow">Tambah Buku</span>
        <h1 class="hero-title">Masukkan data buku baru .</h1>
        <p class="hero-text mb-0">
            Admin perlu melengkapi ISBN, judul, penulis, penerbit, tahun, stok, kategori, dan boleh menambahkan URL cover bila tersedia.
        </p>
    </div>

    <?= Session::display(); ?>

    <?php require __DIR__ . '/../includes/partials/book_form.php'; ?>
</div>
<?php renderPageEnd(); ?>

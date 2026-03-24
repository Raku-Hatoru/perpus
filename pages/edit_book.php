<?php
require_once __DIR__ . '/../includes/layout.php';

requireRole('admin');

$bookId = (int) ($_GET['id'] ?? 0);
$bookController = new BookController();
$book = $bookController->find($bookId);

if ($book === false) {
    Session::setFlash('error', 'Buku yang ingin diedit tidak ditemukan.');
    header('Location: books.php');
    exit;
}

$categoryController = new CategoryController();
$categories = $categoryController->index();

$formAction = 'process_edit_book.php';
$formSubmitLabel = 'Simpan Perubahan';
$formCancelLabel = 'Batal';
$formCancelUrl = 'books.php';
$formCategories = $categories;
$formValues = $book;
$formKey = 'book-edit-' . $bookId;
$formMode = 'edit';
$formBookId = $bookId;

renderPageStart('Edit Buku', [
    'active' => 'books',
]);
?>
<div class="container fade-up">
    <div class="hero-card mb-4">
        <span class="eyebrow">Edit Buku</span>
        <h1 class="hero-title"><?= e($book['title']); ?></h1>
        <p class="hero-text mb-0">
            Perbarui metadata buku, stok, kategori, dan cover sesuai data terbaru pada katalog perpustakaan.
        </p>
    </div>

    <?= Session::display(); ?>

    <?php require __DIR__ . '/../includes/partials/book_form.php'; ?>
</div>
<?php renderPageEnd(); ?>

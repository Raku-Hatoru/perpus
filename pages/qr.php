<?php
require_once __DIR__ . '/../includes/bootstrap.php';

requireLogin();

$bookId = (int) ($_GET['book_id'] ?? 0);
$renderImage = isset($_GET['render']) && $_GET['render'] === '1';

$bookModel = new BookModel();
$book = $bookModel->findById($bookId);

if (!$book) {
    if ($renderImage) {
        http_response_code(404);
        exit('Buku tidak ditemukan.');
    }

    Session::setFlash('error', 'Buku tidak ditemukan.');
    header('Location: books.php');
    exit;
}

if ($renderImage) {
    $service = new QrCodeService();
    $service->renderBookIdentity($book);
    exit;
}

require_once __DIR__ . '/../includes/layout.php';

renderPageStart('QR Buku', [
    'active' => 'books',
]);
?>
<div class="container fade-up">
    <div class="hero-card mb-4">
        <span class="eyebrow">QR Code Buku</span>
        <h1 class="hero-title">Identitas buku dalam bentuk QR yang dihasilkan server-side dengan GD.</h1>
        <p class="hero-text mb-0">QR ini bisa dipakai sebagai identitas cepat untuk ISBN dan metadata buku.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="surface-card section-block">
                <div class="row g-4 align-items-center">
                    <div class="col-md-6 text-center">
                        <img
                            src="qr.php?book_id=<?= (int) $book['id']; ?>&render=1"
                            alt="QR <?= e($book['title']); ?>"
                            class="img-fluid border rounded-4 p-3 bg-white shadow-sm qr-preview-image"
                        >
                    </div>
                    <div class="col-md-6">
                        <div class="section-title mb-3"><?= e($book['title']); ?></div>
                        <div class="mb-2"><strong>ISBN:</strong> <?= e($book['isbn']); ?></div>
                        <div class="mb-2"><strong>Penulis:</strong> <?= e($book['author']); ?></div>
                        <div class="mb-2"><strong>Penerbit:</strong> <?= e($book['publisher'] ?: 'Belum diisi'); ?></div>
                        <div class="mb-2"><strong>Kategori:</strong> <?= e($book['category_name'] ?? 'Tanpa kategori'); ?></div>
                        <div class="mb-4"><strong>Stok:</strong> <?= (int) $book['stock']; ?></div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="qr.php?book_id=<?= (int) $book['id']; ?>&render=1" target="_blank" class="btn btn-brand">Buka PNG</a>
                            <a href="books.php" class="btn btn-outline-brand">Kembali ke Buku</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php renderPageEnd(); ?>

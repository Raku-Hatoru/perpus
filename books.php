<?php
session_start();
require_once 'controllers/BookController.php';

$bookController = new BookController();

// Logic Pencarian atau Tampil Semua
$keyword = $_GET['search'] ?? '';
if ($keyword) {
    $books = $bookController->search($keyword); // Menghasilkan array dengan tag <mark>
} else {
    // Pagination logic sederhana
    $page = $_GET['page'] ?? 1;
    $result = $bookController->index($page);
    $books = $result['data'];
    $totalPages = $result['total_pages'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Buku</h2>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="add_book.php" class="btn btn-success">+ Tambah Buku</a>
        <?php endif; ?>
    </div>

    <form method="GET" action="books.php" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari judul, penulis, atau ISBN..." value="<?= htmlspecialchars($keyword) ?>">
            <button class="btn btn-primary" type="submit">Cari</button>
            <?php if($keyword): ?>
                <a href="books.php" class="btn btn-outline-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ISBN</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                        <td><?= $book['title'] ?></td> 
                        <td><?= $book['author'] ?></td>
                        <td>
                            <?php if ($book['stock'] > 0): ?>
                                <span class="badge bg-success"><?= $book['stock'] ?> Tersedia</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Habis</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($book['stock'] > 0): ?>
                                <a href="borrow.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary">Pinjam</a>
                            <?php else: ?>
                                <a href="join_waitlist.php?book_id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-warning">Antri</a>
                            <?php endif; ?>
                            
                            <a href="qr.php?isbn=<?= $book['isbn'] ?>" target="_blank" class="btn btn-sm btn-outline-dark">QR</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (!$keyword && isset($totalPages)): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
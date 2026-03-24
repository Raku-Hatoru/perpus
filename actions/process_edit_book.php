<?php
require_once __DIR__ . '/../includes/bootstrap.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: books.php');
    exit;
}

$bookId = (int) ($_POST['book_id'] ?? 0);

if ($bookId <= 0) {
    Session::setFlash('error', 'ID buku tidak valid.');
    header('Location: books.php');
    exit;
}

$payload = [
    'isbn' => trim($_POST['isbn'] ?? ''),
    'title' => trim($_POST['title'] ?? ''),
    'author' => trim($_POST['author'] ?? ''),
    'publisher' => trim($_POST['publisher'] ?? ''),
    'year' => $_POST['year'] ?? '',
    'stock' => $_POST['stock'] ?? '',
    'cover_image' => trim($_POST['cover_image'] ?? ''),
    'category_id' => $_POST['category_id'] ?? '',
    '_form_key' => 'book-edit-' . $bookId,
];

Session::setOldInput($payload);

$controller = new BookController();
if ($controller->update($bookId, $payload)) {
    Session::clearOldInput();
    header('Location: books.php');
    exit;
}

header('Location: edit_book.php?id=' . $bookId);
exit;

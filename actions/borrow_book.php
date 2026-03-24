<?php
require_once __DIR__ . '/../includes/bootstrap.php';

requireRole('member');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: books.php');
    exit;
}

$controller = new LoanController();
$controller->borrowBook((int) $_SESSION['user_id'], (int) ($_POST['book_id'] ?? 0));

header('Location: books.php');
exit;

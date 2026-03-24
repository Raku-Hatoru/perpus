<?php
require_once __DIR__ . '/../includes/bootstrap.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . dashboardUrl());
    exit;
}

$controller = new LoanController();
$controller->returnBook((int) ($_POST['loan_id'] ?? 0), (int) $_SESSION['user_id'], (string) $_SESSION['role']);

header('Location: ' . dashboardUrl());
exit;

<?php require_once __DIR__ . '/../includes/bootstrap.php'; if (isLoggedIn()) { redirectByRole(); } header('Location: login.php'); exit;

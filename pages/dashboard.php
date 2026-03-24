<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

redirectByRole();

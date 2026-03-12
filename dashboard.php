<?php

require_once 'Auth.php';
require_once 'WaitlistModel.php';
require_once 'header.php';

// Pastikan user sudah login
Auth::checkRole($_SESSION['role'] ?? 'member'); // Atau sesuaikan dengan logic middleware-mu

// Ambil notifikasi antrian
$waitlistModel = new WaitlistModel();
$notifications = $waitlistModel->getActiveNotifications($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard - Perpustakaan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">PerpusDigital</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="books.php">Buku</a>
            <a class="nav-link" href="categories.php">Kategori</a>
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Selamat Datang, <?= htmlspecialchars($_SESSION['username'] ?? 'Member') ?>! 👋</h2>

    <?php if (count($notifications) > 0): ?>
        <div class="alert alert-success shadow-sm">
            <h5 class="alert-heading"><i class="bi bi-bell"></i> Kabar Gembira!</h5>
            <p class="mb-0">Buku yang kamu antri sudah tersedia:</p>
            <hr>
            <ul class="mb-0">
                <?php foreach ($notifications as $notif): ?>
                    <li>
                        <strong><?= htmlspecialchars($notif['title']) ?></strong> 
                        <a href="borrow.php?id=<?= $notif['book_id'] ?>" class="btn btn-sm btn-primary ms-2">Pinjam Sekarang</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Buku Pinjaman</h5>
                    <p class="card-text fs-2">2 </p>
                </div>
            </div>
        </div>
        </div>
</div>

</body>
</html>
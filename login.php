<?php
require_once 'header.php'; // Pastikan file helper Session.php sudah ada
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Perpustakaan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { margin-top: 100px; max-width: 400px; }
    </style>
</head>
<body>

<div class="container login-container">
    <div class="card shadow border-0">
        <div class="card-body p-5">
            <h3 class="text-center mb-4">PerpusDig</h3>
            
            <?= Session::flash('error'); ?>
            <?= Session::flash('success'); ?>

            <form action="process_login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="nama@email.com">
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="********">
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
            </form>
            
            <div class="text-center mt-4">
                <p class="small text-muted">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
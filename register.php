<?php

require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - Perpustakaan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .register-container { margin-top: 60px; max-width: 450px; }
    </style>
</head>
<body>

<div class="container register-container">
    <div class="card shadow border-0">
        <div class="card-body p-5">
            <h3 class="text-center mb-4">Daftar Akun</h3>
            
            <?= Session::flash('error'); ?>

            <form action="process_register.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="Contoh: budi_perpus">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="nama@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                </div>
                <div class="mb-4">
                    <label class="form-label">Daftar Sebagai</label>
                    <select name="role" class="form-select">
                        <option value="member">Member (Hanya Pinjam)</option>
                        <option value="admin">Admin (Kelola Data)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100 py-2">Daftar Sekarang</button>
            </form>
            
            <div class="text-center mt-4">
                <p class="small text-muted">Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
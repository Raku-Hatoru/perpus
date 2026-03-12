<?php
require_once 'auth.php';
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menangkap data dari form
    $data = [
        'username' => $_POST['username'],
        'email'    => $_POST['email'],
        'password' => $_POST['password'],
        'role'     => $_POST['role']
    ];

    $auth = new Auth();

    // Memanggil method register di class Auth
    if ($auth->register($data)) {
        // Jika sukses, beri pesan dan arahkan ke Login
        Session::setFlash('success', 'Registrasi berhasil! Silakan login.');
        header("Location: login.php");
        exit;
    } else {
        // Jika gagal (validasi gagal), pesan error sudah diset di class Auth
        header("Location: register.php");
        exit;
    }
} else {
    header("Location: register.php");
    exit;
}
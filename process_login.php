<?php
require_once 'auth.php'; // Memanggil Class Auth yang sudah kita buat
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $auth = new Auth();

    if ($auth->login($email, $password)) {
        // Jika login sukses, arahkan ke dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        // Jika gagal, set pesan error dan kembalikan ke login.php
        Session::setFlash('error', 'Email atau Password salah!');
        header("Location: login.php");
        exit;
    }
} else {
    // Jika mencoba akses langsung file ini tanpa POST
    header("Location: login.php");
    exit;
}
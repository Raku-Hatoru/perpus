<?php
require_once 'UserModel.php';

class Auth {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function register($data) {
        // Validasi Server-side sederhana (Poin C.2)
        if (empty($data['username']) || empty($data['email']) || strlen($data['password']) < 6) {
            Session::setFlash('error', 'Data tidak valid atau password kurang dari 6 karakter.');
            return false;
        }
        return $this->userModel->create($data);
    }

    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    // Middleware: Cek Akses (Poin C.6)
    public static function checkRole($requiredRole) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
            Session::setFlash('error', 'Akses ditolak! Anda bukan ' . $requiredRole);
            header("Location: login.php");
            exit;
        }
    }
}
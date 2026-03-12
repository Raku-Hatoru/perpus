<?php

class Database {
    private static $instance = null;
    private $conn;

    // Kredensial Database
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $name = "perpustakaan_digital";

    // Constructor dibuat private agar tidak bisa di-instansiasi dari luar
    private function __construct() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->name;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            // Di sini $this baru boleh digunakan karena berada dalam Class
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die("Koneksi Gagal: " . $e->getMessage());
        }
    }

    // Method statis untuk mengambil koneksi (Syarat Singleton)
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}
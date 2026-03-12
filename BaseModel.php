<?php
require_once 'Database.php';

class BaseModel {
    protected $db;
    protected $table;

    public function __construct() {
        // Mengambil koneksi dari Singleton Database
        $this->db = Database::getInstance();
    }

    // Contoh method generic: Menghapus data berdasarkan ID
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Contoh method generic: Menghitung total data (untuk pagination)
    public function countAll() {
        return $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }
}
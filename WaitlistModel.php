<?php
require_once 'BaseModel.php';

class WaitlistModel extends BaseModel {
    protected $table = 'waitlists';

    // Memasukkan member ke dalam antrian
    public function joinQueue($userId, $bookId) {
        $sql = "INSERT INTO waitlists (user_id, book_id) VALUES (:user_id, :book_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['user_id' => $userId, 'book_id' => $bookId]);
    }

    // Mengambil notifikasi jika buku yang diantri stoknya sudah > 0
    public function getActiveNotifications($userId) {
        $sql = "SELECT w.id AS waitlist_id, b.title, b.id AS book_id 
                FROM waitlists w
                JOIN books b ON w.book_id = b.id
                WHERE w.user_id = :uid AND w.status = 'waiting' AND b.stock > 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}
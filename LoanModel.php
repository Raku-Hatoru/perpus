<?php
require_once 'BaseModel.php';

class LoanModel extends BaseModel {
    protected $table = 'loans';

    public function createLoan($data) {
        $sql = "INSERT INTO loans (user_id, book_id, loan_date, due_date) VALUES (:user_id, :book_id, :loan_date, :due_date)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // Method khusus untuk fitur denda otomatis (D. Fitur 1)
    public function calculateFine($loan_id) {
        $stmt = $this->db->prepare("SELECT due_date, return_date FROM loans WHERE id = :id");
        $stmt->execute(['id' => $loan_id]);
        $loan = $stmt->fetch();

        $due = new DateTime($loan['due_date']);
        $today = new DateTime(); // Anggap dikembalikan hari ini
        
        if ($today > $due) {
            $diff = $today->diff($due)->days;
            return $diff * 1000; // Rp 1.000 per hari
        }
        return 0;
    }
    public function countActiveLoans($userId) {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM loans WHERE user_id = :uid AND status = 'borrowed'");
    $stmt->execute(['uid' => $userId]);
    return $stmt->fetchColumn();
    }

    public function getMonthlyStats($month, $year) {
        $sql = "SELECT b.title, COUNT(l.id) as total_borrowed 
            FROM loans l 
            JOIN books b ON l.book_id = b.id 
            WHERE MONTH(l.loan_date) = :m AND YEAR(l.loan_date) = :y 
            GROUP BY l.book_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['m' => $month, 'y' => $year]);
        return $stmt->fetchAll();
    }
}
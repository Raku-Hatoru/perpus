<?php

class LoanModel extends BaseModel
{
    protected string $table = 'loans';

    public function createLoan(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO loans (user_id, book_id, loan_date, due_date, status, fine_amount)
             VALUES (:user_id, :book_id, :loan_date, :due_date, :status, :fine_amount)'
        );

        return $stmt->execute([
            'user_id' => $data['user_id'],
            'book_id' => $data['book_id'],
            'loan_date' => $data['loan_date'],
            'due_date' => $data['due_date'],
            'status' => $data['status'] ?? 'borrowed',
            'fine_amount' => $data['fine_amount'] ?? 0,
        ]);
    }

    public function hasActiveLoan(int $userId, int $bookId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM loans WHERE user_id = :user_id AND book_id = :book_id AND status = 'borrowed'"
        );
        $stmt->execute([
            'user_id' => $userId,
            'book_id' => $bookId,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function countActiveLoans(?int $userId = null): int
    {
        if ($userId === null) {
            return (int) $this->fetchScalar("SELECT COUNT(*) FROM loans WHERE status = 'borrowed'");
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM loans WHERE user_id = :user_id AND status = 'borrowed'"
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function countReturnedLoans(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM loans WHERE user_id = :user_id AND status = 'returned'"
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function countFiltered(string $keyword = '', string $status = ''): int
    {
        $sql = 'SELECT COUNT(*)
                FROM loans l
                INNER JOIN users u ON u.id = l.user_id
                INNER JOIN books b ON b.id = l.book_id';
        $search = $this->buildSearchClause($keyword, $status);
        $sql .= $search['sql'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($search['params']);

        return (int) $stmt->fetchColumn();
    }

    public function getPaginated(int $limit, int $offset, string $keyword = '', string $status = ''): array
    {
        $sql = 'SELECT l.*, u.username, u.email, u.role, b.title, b.author, b.isbn, c.name AS category_name
                FROM loans l
                INNER JOIN users u ON u.id = l.user_id
                INNER JOIN books b ON b.id = l.book_id
                LEFT JOIN categories c ON c.id = b.category_id';
        $search = $this->buildSearchClause($keyword, $status);
        $sql .= $search['sql'];
        $sql .= ' ORDER BY l.id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);

        foreach ($search['params'] as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getUserActiveLoans(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT l.*, b.title, b.author, b.isbn, c.name AS category_name
             FROM loans l
             INNER JOIN books b ON b.id = l.book_id
             LEFT JOIN categories c ON c.id = b.category_id
             WHERE l.user_id = :user_id AND l.status = 'borrowed'
             ORDER BY l.due_date ASC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function getUserLoanHistory(int $userId, int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, b.title, b.author, b.isbn
             FROM loans l
             INNER JOIN books b ON b.id = l.book_id
             WHERE l.user_id = :user_id
             ORDER BY l.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getRecentLoans(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.username, u.role, b.title, b.isbn
             FROM loans l
             INNER JOIN users u ON u.id = l.user_id
             INNER JOIN books b ON b.id = l.book_id
             ORDER BY l.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getMonthlyStats(int $month, int $year): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.title, COUNT(l.id) AS total_borrowed
             FROM loans l
             INNER JOIN books b ON b.id = l.book_id
             WHERE MONTH(l.loan_date) = :month AND YEAR(l.loan_date) = :year
             GROUP BY b.id, b.title
             ORDER BY total_borrowed DESC, b.title ASC'
        );
        $stmt->execute([
            'month' => $month,
            'year' => $year,
        ]);

        return $stmt->fetchAll();
    }

    public function findBorrowedLoanById(int $loanId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM loans WHERE id = :id AND status = 'borrowed' LIMIT 1"
        );
        $stmt->execute(['id' => $loanId]);

        return $stmt->fetch();
    }

    public function markAsReturned(int $loanId, float $fineAmount): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE loans
             SET return_date = CURRENT_DATE, status = 'returned', fine_amount = :fine_amount
             WHERE id = :id AND status = 'borrowed'"
        );

        return $stmt->execute([
            'fine_amount' => $fineAmount,
            'id' => $loanId,
        ]);
    }

    public function getNearestDueLoan(int $userId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT l.*, b.title
             FROM loans l
             INNER JOIN books b ON b.id = l.book_id
             WHERE l.user_id = :user_id AND l.status = 'borrowed'
             ORDER BY l.due_date ASC
             LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch();
    }

    public function totalCollectedFines(): float
    {
        return (float) $this->fetchScalar('SELECT COALESCE(SUM(fine_amount), 0) FROM loans');
    }

    public function getMonthlyDetailedReport(int $month, int $year): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                b.title,
                b.isbn,
                c.name AS category_name,
                COUNT(l.id) AS total_loans,
                SUM(CASE WHEN l.status = "returned" THEN 1 ELSE 0 END) AS returned_count,
                SUM(CASE WHEN l.status = "borrowed" THEN 1 ELSE 0 END) AS active_count,
                COALESCE(SUM(l.fine_amount), 0) AS total_fines
             FROM loans l
             INNER JOIN books b ON b.id = l.book_id
             LEFT JOIN categories c ON c.id = b.category_id
             WHERE MONTH(l.loan_date) = :month AND YEAR(l.loan_date) = :year
             GROUP BY b.id, b.title, b.isbn, c.name
             ORDER BY total_loans DESC, b.title ASC'
        );
        $stmt->execute([
            'month' => $month,
            'year' => $year,
        ]);

        return $stmt->fetchAll();
    }

    public function countMonthlyLoans(int $month, int $year): int
    {
        return (int) $this->fetchScalar(
            'SELECT COUNT(*) FROM loans WHERE MONTH(loan_date) = :month AND YEAR(loan_date) = :year',
            [
                'month' => $month,
                'year' => $year,
            ]
        );
    }

    public function countMonthlyReturned(int $month, int $year): int
    {
        return (int) $this->fetchScalar(
            "SELECT COUNT(*) FROM loans WHERE MONTH(loan_date) = :month AND YEAR(loan_date) = :year AND status = 'returned'",
            [
                'month' => $month,
                'year' => $year,
            ]
        );
    }

    public function totalMonthlyFines(int $month, int $year): float
    {
        return (float) $this->fetchScalar(
            'SELECT COALESCE(SUM(fine_amount), 0) FROM loans WHERE MONTH(loan_date) = :month AND YEAR(loan_date) = :year',
            [
                'month' => $month,
                'year' => $year,
            ]
        );
    }

    private function buildSearchClause(string $keyword = '', string $status = ''): array
    {
        $keyword = trim($keyword);
        $status = trim($status);
        $clauses = [];
        $params = [];

        if ($keyword !== '') {
            $clauses[] = '(u.username LIKE :keyword OR u.email LIKE :keyword OR b.title LIKE :keyword OR b.isbn LIKE :keyword)';
            $params['keyword'] = '%' . $keyword . '%';
        }

        if ($status !== '') {
            $clauses[] = 'l.status = :status';
            $params['status'] = $status;
        }

        if (empty($clauses)) {
            return ['sql' => '', 'params' => []];
        }

        return [
            'sql' => ' WHERE ' . implode(' AND ', $clauses),
            'params' => $params,
        ];
    }
}

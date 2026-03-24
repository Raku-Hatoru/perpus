<?php

class WaitlistModel extends BaseModel
{
    protected string $table = 'waitlists';
    private ?bool $tableReady = null;

    public function isReady(): bool
    {
        if ($this->tableReady !== null) {
            return $this->tableReady;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name'
        );
        $stmt->execute(['table_name' => $this->table]);
        $this->tableReady = (int) $stmt->fetchColumn() > 0;

        return $this->tableReady;
    }

    public function hasActiveWaitlist(int $userId, int $bookId): bool
    {
        if (!$this->isReady()) {
            return false;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM waitlists WHERE user_id = :user_id AND book_id = :book_id AND status = 'waiting'"
        );
        $stmt->execute([
            'user_id' => $userId,
            'book_id' => $bookId,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function joinQueue(int $userId, int $bookId): bool
    {
        if (!$this->isReady() || $this->hasActiveWaitlist($userId, $bookId)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO waitlists (user_id, book_id, status) VALUES (:user_id, :book_id, 'waiting')"
        );

        return $stmt->execute([
            'user_id' => $userId,
            'book_id' => $bookId,
        ]);
    }

    public function markAsFulfilled(int $userId, int $bookId): bool
    {
        if (!$this->isReady()) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE waitlists
             SET status = 'fulfilled'
             WHERE user_id = :user_id AND book_id = :book_id AND status = 'waiting'"
        );

        return $stmt->execute([
            'user_id' => $userId,
            'book_id' => $bookId,
        ]);
    }

    public function countReadyNotifications(int $userId): int
    {
        return count($this->getActiveNotifications($userId));
    }

    public function getActiveNotifications(int $userId): array
    {
        if (!$this->isReady()) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT ready.*
             FROM (
                SELECT
                    w.id AS waitlist_id,
                    w.book_id,
                    b.title,
                    b.author,
                    b.stock,
                    (
                        SELECT COUNT(*)
                        FROM waitlists w2
                        WHERE w2.book_id = w.book_id
                          AND w2.status = 'waiting'
                          AND w2.id < w.id
                    ) AS queue_before
                FROM waitlists w
                INNER JOIN books b ON b.id = w.book_id
                WHERE w.user_id = :user_id AND w.status = 'waiting'
             ) ready
             WHERE ready.stock > ready.queue_before
             ORDER BY ready.waitlist_id ASC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function getWaitingEntries(int $userId): array
    {
        if (!$this->isReady()) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT
                w.id AS waitlist_id,
                w.book_id,
                b.title,
                b.author,
                b.stock,
                (
                    SELECT COUNT(*)
                    FROM waitlists w2
                    WHERE w2.book_id = w.book_id
                      AND w2.status = 'waiting'
                      AND w2.id < w.id
                ) + 1 AS queue_position
             FROM waitlists w
             INNER JOIN books b ON b.id = w.book_id
             WHERE w.user_id = :user_id AND w.status = 'waiting'
             ORDER BY w.id ASC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function getWaitlistedBookIds(int $userId): array
    {
        if (!$this->isReady()) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT book_id FROM waitlists WHERE user_id = :user_id AND status = 'waiting'"
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map(
            static fn(array $row): int => (int) $row['book_id'],
            $stmt->fetchAll()
        );
    }
}

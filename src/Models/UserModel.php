<?php

class UserModel extends BaseModel
{
    protected string $table = 'users';

    public function create(array $data): bool
    {
        $sql = 'INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        return $stmt->fetch();
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $stmt->execute(['role' => $role]);

        return (int) $stmt->fetchColumn();
    }

    public function countFiltered(string $keyword = '', string $role = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM users';
        $search = $this->buildSearchClause($keyword, $role);
        $sql .= $search['sql'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($search['params']);

        return (int) $stmt->fetchColumn();
    }

    public function getPaginated(int $limit, int $offset, string $keyword = '', string $role = ''): array
    {
        $sql = 'SELECT id, username, email, role, created_at
                FROM users';
        $search = $this->buildSearchClause($keyword, $role);
        $sql .= $search['sql'];
        $sql .= ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);

        foreach ($search['params'] as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getRecentUsers(string $role = 'member', int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, role, created_at
             FROM users
             WHERE role = :role
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function updateRole(int $id, string $role): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET role = :role WHERE id = :id');
        return $stmt->execute([
            'role' => $role,
            'id' => $id,
        ]);
    }

    private function buildSearchClause(string $keyword = '', string $role = ''): array
    {
        $keyword = trim($keyword);
        $role = trim($role);
        $clauses = [];
        $params = [];

        if ($keyword !== '') {
            $clauses[] = '(username LIKE :keyword OR email LIKE :keyword)';
            $params['keyword'] = '%' . $keyword . '%';
        }

        if ($role !== '') {
            $clauses[] = 'role = :role';
            $params['role'] = $role;
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

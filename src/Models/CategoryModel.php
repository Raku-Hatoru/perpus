<?php

class CategoryModel extends BaseModel
{
    protected string $table = 'categories';

    public function getAll(string $keyword = ''): array
    {
        $sql = 'SELECT c.*, COUNT(b.id) AS book_count
                FROM categories c
                LEFT JOIN books b ON b.category_id = c.id';
        $search = $this->buildSearchClause($keyword);
        $sql .= $search['sql'];
        $sql .= ' GROUP BY c.id, c.name, c.description ORDER BY c.name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($search['params']);

        return $stmt->fetchAll();
    }

    public function getPaginated(int $limit, int $offset, string $keyword = ''): array
    {
        $sql = 'SELECT c.*, COUNT(b.id) AS book_count
                FROM categories c
                LEFT JOIN books b ON b.category_id = c.id';
        $search = $this->buildSearchClause($keyword);
        $sql .= $search['sql'];
        $sql .= ' GROUP BY c.id, c.name, c.description
                ORDER BY c.name ASC
                LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);

        foreach ($search['params'] as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countFiltered(string $keyword = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM categories c';
        $search = $this->buildSearchClause($keyword);
        $sql .= $search['sql'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($search['params']);

        return (int) $stmt->fetchColumn();
    }

    public function create(string $name, string $description): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (name, description) VALUES (:name, :description)'
        );

        return $stmt->execute([
            'name' => $name,
            'description' => $description,
        ]);
    }

    public function countCategories(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) FROM categories');
    }

    public function updateCategory(int $id, string $name, string $description): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categories SET name = :name, description = :description WHERE id = :id'
        );

        return $stmt->execute([
            'name' => $name,
            'description' => $description,
            'id' => $id,
        ]);
    }

    private function buildSearchClause(string $keyword): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return ['sql' => '', 'params' => []];
        }

        return [
            'sql' => ' WHERE c.name LIKE :keyword OR c.description LIKE :keyword',
            'params' => [
                'keyword' => '%' . $keyword . '%',
            ],
        ];
    }
}

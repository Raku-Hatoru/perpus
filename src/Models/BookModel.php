<?php

class BookModel extends BaseModel
{
    protected string $table = 'books';
    private string $isbn = '';
    private string $title = '';
    private string $author = '';
    private string $publisher = '';
    private int $year = 0;
    private int $stock = 0;
    private ?string $coverImage = null;
    private int $categoryId = 0;
    private ?bool $fullTextIndexAvailable = null;

    public function setIsbn(string $isbn): void
    {
        $this->isbn = trim($isbn);
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function setTitle(string $title): void
    {
        $this->title = trim($title);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setAuthor(string $author): void
    {
        $this->author = trim($author);
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setPublisher(string $publisher): void
    {
        $this->publisher = trim($publisher);
    }

    public function getPublisher(): string
    {
        return $this->publisher;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setCoverImage(?string $coverImage): void
    {
        $this->coverImage = $coverImage !== null ? trim($coverImage) : null;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function create(array $data): bool
    {
        $this->hydrate($data);

        $sql = 'INSERT INTO books (isbn, title, author, publisher, year, stock, cover_image, category_id)
                VALUES (:isbn, :title, :author, :publisher, :year, :stock, :cover_image, :category_id)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'isbn' => $this->getIsbn(),
            'title' => $this->getTitle(),
            'author' => $this->getAuthor(),
            'publisher' => $this->getPublisher(),
            'year' => $this->getYear(),
            'stock' => $this->getStock(),
            'cover_image' => $this->getCoverImage(),
            'category_id' => $this->getCategoryId(),
        ]);
    }

    public function updateBook(int $id, array $data): bool
    {
        $this->hydrate($data);

        $sql = 'UPDATE books
                SET isbn = :isbn,
                    title = :title,
                    author = :author,
                    publisher = :publisher,
                    year = :year,
                    stock = :stock,
                    cover_image = :cover_image,
                    category_id = :category_id
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'isbn' => $this->getIsbn(),
            'title' => $this->getTitle(),
            'author' => $this->getAuthor(),
            'publisher' => $this->getPublisher(),
            'year' => $this->getYear(),
            'stock' => $this->getStock(),
            'cover_image' => $this->getCoverImage(),
            'category_id' => $this->getCategoryId(),
        ]);
    }

    public function getPaginated(int $limit, int $offset, string $keyword = ''): array
    {
        $sql = 'SELECT b.*, c.name AS category_name
                FROM books b
                LEFT JOIN categories c ON c.id = b.category_id';
        $search = $this->buildSearchClause($keyword);
        $sql .= $search['sql'];

        $sql .= ' ORDER BY b.id DESC LIMIT :limit OFFSET :offset';
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
        $sql = 'SELECT COUNT(*) FROM books b LEFT JOIN categories c ON c.id = b.category_id';
        $search = $this->buildSearchClause($keyword);
        $sql .= $search['sql'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($search['params']);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, c.name AS category_name
            FROM books b
            LEFT JOIN categories c ON c.id = b.category_id
            WHERE b.id = :id
            LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function updateStock(int $id, int $stock): bool
    {
        $stmt = $this->db->prepare('UPDATE books SET stock = :stock WHERE id = :id');

        return $stmt->execute([
            'stock' => $stock,
            'id' => $id,
        ]);
    }

    public function countAvailableTitles(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) FROM books WHERE stock > 0');
    }

    public function totalStock(): int
    {
        return (int) $this->fetchScalar('SELECT COALESCE(SUM(stock), 0) FROM books');
    }

    public function getRecentBooks(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, c.name AS category_name
             FROM books b
             LEFT JOIN categories c ON c.id = b.category_id
             ORDER BY b.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getLowStockBooks(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, c.name AS category_name
             FROM books b
             LEFT JOIN categories c ON c.id = b.category_id
             WHERE b.stock <= 3
             ORDER BY b.stock ASC, b.title ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function hydrate(array $data): void
    {
        $this->setIsbn((string) ($data['isbn'] ?? ''));
        $this->setTitle((string) ($data['title'] ?? ''));
        $this->setAuthor((string) ($data['author'] ?? ''));
        $this->setPublisher((string) ($data['publisher'] ?? ''));
        $this->setYear((int) ($data['year'] ?? 0));
        $this->setStock((int) ($data['stock'] ?? 0));
        $this->setCoverImage($data['cover_image'] ?? null);
        $this->setCategoryId((int) ($data['category_id'] ?? 0));
    }

    private function buildSearchClause(string $keyword): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return ['sql' => '', 'params' => []];
        }

        if ($this->hasFullTextIndex()) {
            return [
                'sql' => ' WHERE (
                            MATCH(b.isbn, b.title, b.author, b.publisher) AGAINST (:fulltext IN BOOLEAN MODE)
                            OR b.title LIKE :keyword
                            OR b.author LIKE :keyword
                            OR b.isbn LIKE :keyword
                            OR b.publisher LIKE :keyword
                            OR c.name LIKE :keyword
                          )',
                'params' => [
                    'fulltext' => $this->toBooleanQuery($keyword),
                    'keyword' => '%' . $keyword . '%',
                ],
            ];
        }

        $terms = $this->splitTerms($keyword);
        $params = [];
        $clauses = [];

        foreach ($terms as $index => $term) {
            $param = 'term_' . $index;
            $clauses[] = "(b.title LIKE :{$param} OR b.author LIKE :{$param} OR b.isbn LIKE :{$param} OR b.publisher LIKE :{$param} OR c.name LIKE :{$param})";
            $params[$param] = '%' . $term . '%';
        }

        return [
            'sql' => ' WHERE ' . implode(' AND ', $clauses),
            'params' => $params,
        ];
    }

    private function hasFullTextIndex(): bool
    {
        if ($this->fullTextIndexAvailable !== null) {
            return $this->fullTextIndexAvailable;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND INDEX_TYPE = 'FULLTEXT'"
        );
        $stmt->execute(['table_name' => $this->table]);
        $this->fullTextIndexAvailable = (int) $stmt->fetchColumn() > 0;

        return $this->fullTextIndexAvailable;
    }

    private function toBooleanQuery(string $keyword): string
    {
        $terms = $this->splitTerms($keyword);
        if (empty($terms)) {
            return $keyword;
        }

        return implode(' ', array_map(static fn(string $term): string => $term . '*', $terms));
    }

    private function splitTerms(string $keyword): array
    {
        $parts = preg_split('/\s+/u', trim($keyword)) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), static fn(string $part): bool => $part !== ''));

        return empty($parts) ? [$keyword] : $parts;
    }
}

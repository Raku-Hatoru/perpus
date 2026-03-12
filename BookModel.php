<?php
require_once 'BaseModel.php';

class BookModel extends BaseModel {
    // Encapsulation: Properti Private
    private $id;
    private $title;
    private $author;
    private $stock;

    protected $table = 'books';

    // Setter & Getter
    public function setTitle($title) { $this->title = $title; }
    public function getTitle() { return $this->title; }
    
    public function setStock($stock) { $this->stock = $stock; }
    public function getStock() { return $this->stock; }

    // --- CRUD METHODS ---

    // CREATE
    public function create($data) {
        $sql = "INSERT INTO books (isbn, title, author, publisher, year, stock, category_id) 
                VALUES (:isbn, :title, :author, :publisher, :year, :stock, :category_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // READ (Get All with Pagination)
    public function getAll($limit, $offset) {
        $stmt = $this->db->prepare("SELECT * FROM books LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // UPDATE
    public function update($id, $data) {
        $sql = "UPDATE books SET title = :title, stock = :stock WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }
    public function searchBooks($keyword) {
    $sql = "SELECT * FROM books WHERE title LIKE :key OR author LIKE :key OR isbn LIKE :key";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['key' => "%$keyword%"]);
    return $stmt->fetchAll();
}
}
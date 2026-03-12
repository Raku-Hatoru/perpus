<?php
require_once 'BookModel.php';

class BookController {
    private $bookModel;
    

    public function __construct() {
        $this->bookModel = new BookModel();
    }

    // LIST / INDEX dengan Pagination (Poin C.1)
    public function index($page = 1) {
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $books = $this->bookModel->getAll($limit, $offset);
        $totalData = $this->bookModel->countAll();
        $totalPages = ceil($totalData / $limit);

        return [
            'data' => $books,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
    }

    // TAMBAH DATA dengan Validasi (Poin C.2)
    public function store($postData) {
        // Validasi: angka positif untuk stok (Poin C.2)
        if ($postData['stock'] < 0) {
            Session::setFlash('error', 'Stok tidak boleh negatif!');
            return false;
        }

        if ($this->bookModel->create($postData)) {
            Session::setFlash('success', 'Buku berhasil ditambah!');
            header("Location: books.php");
        }
    }

    // HAPUS DATA dengan POST (Poin C.3)
    public function delete($id) {
        // Pastikan ini dipanggil lewat method POST di view
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->bookModel->delete($id)) {
                Session::setFlash('success', 'Buku dihapus.');
            }
        }
        header("Location: books.php");
    }

    public function search($keyword) {
        $results = $this->bookModel->searchBooks($keyword);
        
        foreach ($results as &$book) {
            // Gunakan str_ireplace agar case-insensitive
            $replace = "<mark style='background-color: yellow;'>$keyword</mark>";
            $book['title'] = str_ireplace($keyword, $replace, $book['title']);
            $book['author'] = str_ireplace($keyword, $replace, $book['author']);
        }
        return $results;
    }

    // FITUR 2: QR Code Generator (Logic Header)
    // Catatan: Gunakan library simpel seperti 'phpqrcode' yang berbasis GD
    public function generateQR($isbn) {
        require_once 'libs/phpqrcode/qrlib.php'; // Pastikan library ada di folder libs
        
        // Output langsung ke browser sebagai gambar
        header("Content-Type: image/png");
        QRcode::png($isbn, false, QR_ECLEVEL_L, 4);
    }
}
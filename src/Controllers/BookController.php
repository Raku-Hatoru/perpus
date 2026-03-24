<?php

class BookController
{
    private BookModel $bookModel;

    public function __construct()
    {
        $this->bookModel = new BookModel();
    }

    public function index(int $page = 1, string $keyword = ''): array
    {
        $limit = 10;
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;
        $totalRows = $this->bookModel->countFiltered($keyword);
        $totalPages = max(1, (int) ceil($totalRows / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        return [
            'data' => $this->bookModel->getPaginated($limit, $offset, $keyword),
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_rows' => $totalRows,
        ];
    }

    public function find(int $id): array|false
    {
        if ($id <= 0) {
            return false;
        }

        return $this->bookModel->findById($id);
    }

    public function store(array $data): bool
    {
        $payload = $this->normalizePayload($data);

        if (!$this->validatePayload($payload)) {
            return false;
        }

        try {
            $created = $this->bookModel->create($payload);
            if ($created) {
                Session::setFlash('success', 'Buku baru berhasil ditambahkan.');
            }

            return $created;
        } catch (PDOException $exception) {
            $this->handleWriteException($exception, 'menyimpan');
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            Session::setFlash('error', 'ID buku tidak valid.');
            return false;
        }

        if ($this->bookModel->findById($id) === false) {
            Session::setFlash('error', 'Data buku yang ingin diubah tidak ditemukan.');
            return false;
        }

        $payload = $this->normalizePayload($data);

        if (!$this->validatePayload($payload)) {
            return false;
        }

        try {
            $updated = $this->bookModel->updateBook($id, $payload);
            if ($updated) {
                Session::setFlash('success', 'Data buku berhasil diperbarui.');
            }

            return $updated;
        } catch (PDOException $exception) {
            $this->handleWriteException($exception, 'memperbarui');
            return false;
        }
    }

    public function delete(int $id): bool
    {
        if ($id <= 0) {
            Session::setFlash('error', 'ID buku tidak valid.');
            return false;
        }

        if ($this->bookModel->delete($id)) {
            Session::setFlash('success', 'Buku berhasil dihapus.');
            return true;
        }

        Session::setFlash('error', 'Buku gagal dihapus.');
        return false;
    }

    private function normalizePayload(array $data): array
    {
        return [
            'isbn' => trim($data['isbn'] ?? ''),
            'title' => trim($data['title'] ?? ''),
            'author' => trim($data['author'] ?? ''),
            'publisher' => trim($data['publisher'] ?? ''),
            'year' => (int) ($data['year'] ?? 0),
            'stock' => (int) ($data['stock'] ?? 0),
            'cover_image' => trim($data['cover_image'] ?? ''),
            'category_id' => (int) ($data['category_id'] ?? 0),
        ];
    }

    private function validatePayload(array $payload): bool
    {
        if ($payload['isbn'] === '' || $payload['title'] === '' || $payload['author'] === '') {
            Session::setFlash('error', 'ISBN, judul, dan penulis wajib diisi.');
            return false;
        }

        if ($payload['category_id'] <= 0) {
            Session::setFlash('error', 'Pilih kategori buku terlebih dahulu.');
            return false;
        }

        if ($payload['stock'] < 0) {
            Session::setFlash('error', 'Stok tidak boleh negatif.');
            return false;
        }

        if ($payload['year'] < 1900 || $payload['year'] > (int) date('Y')) {
            Session::setFlash('error', 'Tahun terbit tidak valid.');
            return false;
        }

        return true;
    }

    private function handleWriteException(PDOException $exception, string $action): void
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'duplicate') && str_contains($message, 'isbn')) {
            Session::setFlash('error', 'ISBN sudah terdaftar pada buku lain.');
            return;
        }

        Session::setFlash('error', 'Gagal ' . $action . ' buku ke database.');
    }
}

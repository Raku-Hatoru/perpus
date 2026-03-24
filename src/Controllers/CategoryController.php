<?php

class CategoryController
{
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    public function index(string $keyword = ''): array
    {
        return $this->categoryModel->getAll($keyword);
    }

    public function paginate(int $page = 1, string $keyword = ''): array
    {
        $limit = 10;
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;
        $totalRows = $this->categoryModel->countFiltered($keyword);
        $totalPages = max(1, (int) ceil($totalRows / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        return [
            'data' => $this->categoryModel->getPaginated($limit, $offset, $keyword),
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_rows' => $totalRows,
            'limit' => $limit,
        ];
    }

    public function find(int $id): array|false
    {
        if ($id <= 0) {
            return false;
        }

        return $this->categoryModel->findById($id);
    }

    public function store(array $data): bool
    {
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($name === '') {
            Session::setFlash('error', 'Nama kategori wajib diisi.');
            return false;
        }

        try {
            $created = $this->categoryModel->create($name, $description);
            if ($created) {
                Session::setFlash('success', 'Kategori baru berhasil ditambahkan.');
            }

            return $created;
        } catch (PDOException) {
            Session::setFlash('error', 'Gagal menambahkan kategori.');
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            Session::setFlash('error', 'Kategori tidak valid.');
            return false;
        }

        if ($this->categoryModel->findById($id) === false) {
            Session::setFlash('error', 'Kategori yang ingin diedit tidak ditemukan.');
            return false;
        }

        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($name === '') {
            Session::setFlash('error', 'Nama kategori wajib diisi.');
            return false;
        }

        try {
            $updated = $this->categoryModel->updateCategory($id, $name, $description);
            if ($updated) {
                Session::setFlash('success', 'Kategori berhasil diperbarui.');
            }

            return $updated;
        } catch (PDOException) {
            Session::setFlash('error', 'Gagal memperbarui kategori.');
            return false;
        }
    }

    public function destroy(int $id): bool
    {
        if ($id <= 0) {
            Session::setFlash('error', 'Kategori tidak valid.');
            return false;
        }

        if ($this->categoryModel->delete($id)) {
            Session::setFlash('success', 'Kategori berhasil dihapus.');
            return true;
        }

        Session::setFlash('error', 'Kategori gagal dihapus.');
        return false;
    }
}

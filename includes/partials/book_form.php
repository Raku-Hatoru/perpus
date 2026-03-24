<?php
$formAction = $formAction ?? 'process_add_book.php';
$formSubmitLabel = $formSubmitLabel ?? 'Simpan Buku';
$formCancelLabel = $formCancelLabel ?? 'Kembali ke Katalog';
$formCancelUrl = $formCancelUrl ?? 'books.php';
$formCategories = $formCategories ?? [];
$formValues = $formValues ?? [];
$formKey = $formKey ?? 'book-form';
$formMode = $formMode ?? 'create';
$formBookId = (int) ($formBookId ?? 0);

$oldInput = $_SESSION['old_input'] ?? [];
$useOldInput = ($oldInput['_form_key'] ?? '') === $formKey;

$value = static function (string $key, string $default = '') use ($formValues, $oldInput, $useOldInput): string {
    if ($useOldInput && array_key_exists($key, $oldInput)) {
        return e((string) $oldInput[$key]);
    }

    return e((string) ($formValues[$key] ?? $default));
};

$selectedCategoryId = (string) ($useOldInput ? ($oldInput['category_id'] ?? '') : ($formValues['category_id'] ?? ''));
?>
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="surface-card section-block">
            <form action="<?= e($formAction); ?>" method="POST">
                <?php if ($formMode === 'edit'): ?>
                    <input type="hidden" name="book_id" value="<?= $formBookId; ?>">
                <?php endif; ?>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">ISBN</label>
                        <input type="text" name="isbn" class="form-control" value="<?= $value('isbn'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Pilih kategori</option>
                            <?php foreach ($formCategories as $category): ?>
                                <option value="<?= (int) $category['id']; ?>" <?= $selectedCategoryId === (string) $category['id'] ? 'selected' : ''; ?>>
                                    <?= e($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Judul Buku</label>
                        <input type="text" name="title" class="form-control" value="<?= $value('title'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Penulis</label>
                        <input type="text" name="author" class="form-control" value="<?= $value('author'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Penerbit</label>
                        <input type="text" name="publisher" class="form-control" value="<?= $value('publisher'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="number" name="year" class="form-control" min="1900" max="<?= date('Y'); ?>" value="<?= $value('year', (string) date('Y')); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stok</label>
                        <input type="number" name="stock" class="form-control" min="0" value="<?= $value('stock', '0'); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">URL Cover</label>
                        <input type="url" name="cover_image" class="form-control" value="<?= $value('cover_image'); ?>" placeholder="https://contoh.com/cover-buku.jpg">
                        <div class="muted-text mt-2">Opsional. Biarkan kosong jika sampul buku belum tersedia.</div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <a href="<?= e($formCancelUrl); ?>" class="btn btn-outline-brand"><?= e($formCancelLabel); ?></a>
                            <button type="submit" class="btn btn-brand px-5"><?= e($formSubmitLabel); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/bootstrap.php';

function renderPageStart(string $title, array $options = []): void
{
    $showNav = $options['show_nav'] ?? isLoggedIn();
    $active = $options['active'] ?? '';
    $containerClass = $options['container_class'] ?? 'app-shell';

    echo '<!DOCTYPE html>';
    echo '<html lang="id">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . e($title) . '</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '<link rel="stylesheet" href="assets/css/app.css">';
    echo '</head>';
    echo '<body>';

    if ($showNav) {
        renderNavbar($active);
    }

    echo '<main class="' . e($containerClass) . '">';
}

function renderNavbar(string $active = ''): void
{
    $items = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => dashboardUrl()],
        ['key' => 'books', 'label' => 'Buku', 'href' => 'books.php'],
    ];

    if (currentUserRole() === 'admin') {
        $items[] = ['key' => 'categories', 'label' => 'Kategori', 'href' => 'categories.php'];
        $items[] = ['key' => 'users', 'label' => 'Users', 'href' => 'users.php'];
        $items[] = ['key' => 'loans', 'label' => 'Loans', 'href' => 'loans.php'];
        $items[] = ['key' => 'reports', 'label' => 'Laporan', 'href' => 'reports.php'];
        $items[] = ['key' => 'register_admin', 'label' => 'Tambah Admin', 'href' => 'register_admin.php'];
    }

    echo '<nav class="navbar navbar-expand-lg app-navbar">';
    echo '<div class="container">';
    echo '<a class="navbar-brand fw-bold" href="' . e(dashboardUrl()) . '">PerpusDigital</a>';
    echo '<div class="navbar-nav ms-auto align-items-center gap-lg-1 flex-wrap">';

    foreach ($items as $item) {
        $isActive = $active === $item['key'] ? ' active' : '';
        echo '<a class="nav-link' . $isActive . '" href="' . e($item['href']) . '">' . e($item['label']) . '</a>';
    }

    echo '<span class="pill ms-lg-3 me-lg-2">' . e(roleLabel(currentUserRole())) . '</span>';
    echo '<a class="nav-link text-warning" href="logout.php">Logout</a>';
    echo '</div>';
    echo '</div>';
    echo '</nav>';
}

function renderFlashMessages(): void
{
    echo Session::display();
    Session::clearOldInput();
}

function renderPageEnd(): void
{
    echo '</main>';
    echo '</body>';
    echo '</html>';
}

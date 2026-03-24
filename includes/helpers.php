<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Session
{
    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }

    public static function flash(string $type): string
    {
        if (empty($_SESSION['flash'][$type])) {
            return '';
        }

        $messages = (array) $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);

        $html = '';
        foreach ($messages as $message) {
            $html .= self::renderAlert($type, $message);
        }

        return $html;
    }

    public static function display(): string
    {
        if (empty($_SESSION['flash'])) {
            return '';
        }

        $html = '';
        foreach ($_SESSION['flash'] as $type => $messages) {
            foreach ((array) $messages as $message) {
                $html .= self::renderAlert((string) $type, (string) $message);
            }
        }

        unset($_SESSION['flash']);

        return $html;
    }

    public static function setOldInput(array $data): void
    {
        $_SESSION['old_input'] = $data;
    }

    public static function old(string $key, string $default = ''): string
    {
        return e($_SESSION['old_input'][$key] ?? $default);
    }

    public static function clearOldInput(): void
    {
        unset($_SESSION['old_input']);
    }

    private static function renderAlert(string $type, string $message): string
    {
        $map = [
            'error' => 'danger',
            'success' => 'success',
            'warning' => 'warning',
            'info' => 'info',
        ];

        $class = $map[$type] ?? 'secondary';

        return sprintf(
            "<div class='alert alert-%s border-0 shadow-sm mb-3' role='alert'>%s</div>",
            $class,
            $message
        );
    }
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

function currentUserRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

function currentUserName(): string
{
    return $_SESSION['username'] ?? 'Pengguna';
}

function roleLabel(?string $role): string
{
    return $role === 'admin' ? 'Admin' : 'Member';
}

function keywordTokens(string $keyword): array
{
    $parts = preg_split('/\s+/u', trim($keyword)) ?: [];
    $tokens = array_filter(array_map('trim', $parts), static fn(string $part): bool => $part !== '');
    return array_values(array_unique($tokens));
}

function highlightText(string $text, string $keyword): string
{
    $escaped = e($text);
    $tokens = keywordTokens($keyword);

    if ($escaped === '' || empty($tokens)) {
        return $escaped;
    }

    foreach ($tokens as $token) {
        $escapedToken = preg_quote(e($token), '/');
        $escaped = preg_replace(
            '/(' . $escapedToken . ')/iu',
            '<mark class="search-highlight">$1</mark>',
            $escaped
        ) ?? $escaped;
    }

    return $escaped;
}

function dashboardUrl(?string $role = null): string
{
    return ($role ?? currentUserRole()) === 'admin' ? 'admin_dashboard.php' : 'member_dashboard.php';
}

function redirectByRole(): never
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    header('Location: ' . dashboardUrl());
    exit;
}

function redirectIfLoggedIn(): void
{
    if (isLoggedIn()) {
        redirectByRole();
    }
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        Session::setFlash('error', 'Silakan login terlebih dahulu.');
        header('Location: login.php');
        exit;
    }
}

function requireRole(string $role): void
{
    requireLogin();

    if (currentUserRole() !== $role) {
        Session::setFlash('error', 'Kamu tidak punya akses ke halaman tersebut.');
        header('Location: ' . dashboardUrl());
        exit;
    }
}

function assetUrl(string $path): string
{
    return 'assets/' . ltrim(str_replace('\\', '/', $path), '/');
}

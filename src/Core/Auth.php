<?php

class Auth
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function register(array $data): bool
    {
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = (string) ($data['password'] ?? '');
        $role = $data['role'] ?? 'member';

        if ($username === '' || $email === '' || $password === '') {
            Session::setFlash('error', 'Semua field wajib diisi.');
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Format email tidak valid.');
            return false;
        }

        if (strlen($password) < 6) {
            Session::setFlash('error', 'Password minimal 6 karakter.');
            return false;
        }

        if (!in_array($role, ['admin', 'member'], true)) {
            Session::setFlash('error', 'Role akun tidak valid.');
            return false;
        }

        try {
            return $this->userModel->create([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
            ]);
        } catch (PDOException $exception) {
            $message = $exception->getMessage();

            if (str_contains($message, 'users.username')) {
                Session::setFlash('error', 'Username sudah digunakan.');
            } elseif (str_contains($message, 'users.email')) {
                Session::setFlash('error', 'Email sudah terdaftar.');
            } else {
                Session::setFlash('error', 'Registrasi gagal. Periksa data yang dimasukkan.');
            }

            return false;
        }
    }

    public function login(string $email, string $password, ?string $expectedRole = null): bool
    {
        $user = $this->userModel->findByEmail(trim($email));

        if (!$user || !password_verify($password, $user['password'])) {
            Session::setFlash('error', 'Email atau password salah.');
            return false;
        }

        if ($expectedRole !== null && $user['role'] !== $expectedRole) {
            Session::setFlash(
                'error',
                'Akun ini terdaftar sebagai ' . strtolower(roleLabel($user['role'])) . '. Gunakan halaman login yang sesuai.'
            );
            return false;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        session_start();
        Session::setFlash('success', 'Kamu berhasil logout dari sistem.');
    }

    public static function checkRole(string $role): void
    {
        requireRole($role);
    }
}

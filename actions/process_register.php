<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$role = $_POST['role'] ?? 'member';
$role = in_array($role, ['admin', 'member'], true) ? $role : 'member';
$registerPage = $role === 'admin' ? 'register_admin.php' : 'register_member.php';
$loginPage = $role === 'admin' ? 'login_admin.php' : 'login_member.php';

$payload = [
    'username' => trim($_POST['username'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'password' => (string) ($_POST['password'] ?? ''),
    'role' => $role,
];

Session::setOldInput([
    'username' => $payload['username'],
    'email' => $payload['email'],
]);

if ($role === 'admin') {
    $userModel = new UserModel();
    $adminCount = $userModel->countByRole('admin');

    if ($adminCount > 0 && (!isLoggedIn() || currentUserRole() !== 'admin')) {
        Session::setFlash('error', 'Registrasi admin hanya dapat dilakukan oleh admin aktif.');
        header('Location: login_admin.php');
        exit;
    }
}

$auth = new Auth();
if ($auth->register($payload)) {
    Session::clearOldInput();
    Session::setFlash('success', 'Registrasi berhasil. Silakan login sebagai ' . strtolower(roleLabel($role)) . '.');
    header('Location: ' . $loginPage);
    exit;
}

header('Location: ' . $registerPage);
exit;

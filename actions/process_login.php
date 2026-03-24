<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$role = $_POST['role'] ?? 'member';
$role = in_array($role, ['admin', 'member'], true) ? $role : 'member';
$email = trim($_POST['email'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$loginPage = $role === 'admin' ? 'login_admin.php' : 'login_member.php';

Session::setOldInput([
    'email' => $email,
]);

$auth = new Auth();
if ($auth->login($email, $password, $role)) {
    Session::clearOldInput();
    redirectByRole();
}

header('Location: ' . $loginPage);
exit;

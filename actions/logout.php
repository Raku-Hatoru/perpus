<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = new Auth();
$auth->logout();
header('Location: login.php');
exit;

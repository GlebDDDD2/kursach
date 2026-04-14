<?php
session_start();
require 'db.php';
require 'check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_panel.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    die('CSRF Attack blocked');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['error_message'] = 'Некорректный ID объекта.';
    header('Location: admin_panel.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM properties WHERE id = ?');
$stmt->execute([$id]);

$_SESSION['success_message'] = 'Объект удален.';
header('Location: admin_panel.php');
exit;

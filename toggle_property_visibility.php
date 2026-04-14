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

$visibility = (int)($_POST['visibility'] ?? 0) === 1 ? 1 : 0;
$update = $pdo->prepare('UPDATE properties SET is_published = ? WHERE id = ?');
$update->execute([$visibility, $id]);

$_SESSION['success_message'] = $visibility ? 'Объект опубликован.' : 'Объект снят с публикации.';
header('Location: admin_panel.php');
exit;

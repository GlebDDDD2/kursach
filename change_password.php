<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    die('Ошибка безопасности: неверный CSRF-токен.');
}

$userId = (int)$_SESSION['user_id'];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    $_SESSION['error_message'] = 'Заполните все поля формы смены пароля.';
    header('Location: profile.php');
    exit;
}

if (strlen($newPassword) < 8) {
    $_SESSION['error_message'] = 'Новый пароль должен содержать минимум 8 символов.';
    header('Location: profile.php');
    exit;
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['error_message'] = 'Новые пароли не совпадают.';
    header('Location: profile.php');
    exit;
}

$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
    $_SESSION['error_message'] = 'Старый пароль введен неверно.';
    header('Location: profile.php');
    exit;
}

$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$update = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
$update->execute([$newHash, $userId]);

$_SESSION['success_message'] = 'Пароль успешно изменен.';
header('Location: profile.php');
exit;

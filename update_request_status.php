<?php
session_start();
require 'db.php';
require 'check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_requests.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    die('CSRF Attack blocked');
}

$requestId = (int)($_POST['request_id'] ?? 0);
$status = $_POST['status'] ?? 'new';
$allowedStatuses = ['new', 'processed'];

if ($requestId <= 0 || !in_array($status, $allowedStatuses, true)) {
    $_SESSION['error_message'] = 'Некорректные данные для смены статуса.';
    header('Location: admin_requests.php');
    exit;
}

$update = $pdo->prepare('UPDATE viewing_requests SET status = ? WHERE id = ?');
$update->execute([$status, $requestId]);

$_SESSION['success_message'] = 'Статус заявки обновлен.';
header('Location: admin_requests.php');
exit;

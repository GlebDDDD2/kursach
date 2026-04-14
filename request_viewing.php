<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalog.php');
    exit;
}

$property_id = (int)($_POST['property_id'] ?? 0);
$client_name = trim($_POST['client_name'] ?? '');
$client_phone = trim($_POST['client_phone'] ?? '');
$client_email = trim($_POST['client_email'] ?? '');
$preferred_date = trim($_POST['preferred_date'] ?? '');
$comment = trim($_POST['comment'] ?? '');

if ($property_id <= 0 || $client_name === '' || $client_phone === '') {
    die('Заполните обязательные поля формы.');
}

$stmt = $pdo->prepare('INSERT INTO viewing_requests (property_id, client_name, client_phone, client_email, preferred_date, comment)
                       VALUES (:property_id, :client_name, :client_phone, :client_email, :preferred_date, :comment)');

$stmt->execute([
    ':property_id' => $property_id,
    ':client_name' => $client_name,
    ':client_phone' => $client_phone,
    ':client_email' => $client_email !== '' ? $client_email : null,
    ':preferred_date' => $preferred_date !== '' ? $preferred_date : null,
    ':comment' => $comment !== '' ? $comment : null,
]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заявка отправлена</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h1 class="h3 mb-3">Заявка успешно отправлена</h1>
                    <p class="mb-4">Менеджер свяжется с вами для подтверждения просмотра.</p>
                    <a href="catalog.php" class="btn btn-primary">Вернуться в каталог</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

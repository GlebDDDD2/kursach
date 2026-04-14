<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalog.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    die('Сначала войдите в систему! <a href="login.php">Вход</a>');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    die('Ошибка безопасности: неверный CSRF-токен. Запрос отклонен.');
}

$property_id = (int)($_POST['property_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];
$client_name = trim($_POST['client_name'] ?? '');
$client_phone = trim($_POST['client_phone'] ?? '');
$client_email = trim($_POST['client_email'] ?? '');
$preferred_date = trim($_POST['preferred_date'] ?? '');
$comment = trim($_POST['comment'] ?? '');

if ($property_id <= 0 || $client_name === '' || $client_phone === '') {
    die('Ошибка: заполните обязательные поля формы.');
}

$check = $pdo->prepare('SELECT id, title FROM properties WHERE id = ?');
$check->execute([$property_id]);
$property = $check->fetch();

if (!$property) {
    die('Ошибка: попытка оставить заявку на несуществующий объект.');
}

$recentStmt = $pdo->prepare(
    'SELECT id, created_at
     FROM viewing_requests
     WHERE user_id = ? AND property_id = ? AND created_at >= (NOW() - INTERVAL 5 MINUTE)
     ORDER BY id DESC
     LIMIT 1'
);
$recentStmt->execute([$user_id, $property_id]);
$recentRequest = $recentStmt->fetch();

if ($recentRequest) {
    die('Вы уже отправляли заявку на этот объект в последние 5 минут. Подождите немного и попробуйте снова. <a href="property.php?id=' . (int)$property_id . '">Вернуться</a>');
}

$stmt = $pdo->prepare(
    'INSERT INTO viewing_requests (user_id, property_id, client_name, client_phone, client_email, preferred_date, comment, status)
     VALUES (:user_id, :property_id, :client_name, :client_phone, :client_email, :preferred_date, :comment, :status)'
);

$stmt->execute([
    ':user_id' => $user_id,
    ':property_id' => $property_id,
    ':client_name' => $client_name,
    ':client_phone' => $client_phone,
    ':client_email' => $client_email !== '' ? $client_email : null,
    ':preferred_date' => $preferred_date !== '' ? $preferred_date : null,
    ':comment' => $comment !== '' ? $comment : null,
    ':status' => 'new',
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
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-4">
                    <h1 class="h3 mb-3">Заявка успешно отправлена</h1>
                    <p class="mb-2">Объект: <strong><?= h($property['title']) ?></strong></p>
                    <p class="mb-4">Менеджер свяжется с вами для подтверждения просмотра.</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="property.php?id=<?= (int)$property_id ?>" class="btn btn-outline-primary">Вернуться к объекту</a>
                        <a href="profile.php" class="btn btn-primary">Мои заявки</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

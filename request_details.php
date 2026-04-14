<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$requestId = (int)($_GET['id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

if ($requestId <= 0) {
    die('Заявка не найдена или у вас нет прав на ее просмотр.');
}

$sql = '
    SELECT
        vr.id,
        vr.client_name,
        vr.client_phone,
        vr.client_email,
        vr.preferred_date,
        vr.comment,
        vr.status,
        vr.created_at,
        p.id AS property_id,
        p.title,
        p.address,
        p.price,
        p.main_photo,
        d.name AS district_name,
        r.full_name AS realtor_name,
        r.phone AS realtor_phone
    FROM viewing_requests vr
    JOIN properties p ON vr.property_id = p.id
    JOIN districts d ON p.district_id = d.id
    JOIN realtors r ON p.realtor_id = r.id
    WHERE vr.id = ? AND vr.user_id = ?
    LIMIT 1
';
$stmt = $pdo->prepare($sql);
$stmt->execute([$requestId, $userId]);
$request = $stmt->fetch();

if (!$request) {
    die('Заявка не найдена или у вас нет прав на ее просмотр.');
}

$statusMap = [
    'new' => ['label' => 'Новая', 'class' => 'primary'],
    'processed' => ['label' => 'Обработана', 'class' => 'success'],
];
$statusInfo = $statusMap[$request['status']] ?? ['label' => $request['status'], 'class' => 'secondary'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали заявки</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Главная</a>
            <a class="nav-link" href="profile.php">Профиль</a>
            <a class="nav-link" href="logout.php">Выход</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <a href="profile.php" class="btn btn-outline-secondary mb-4">← Назад в профиль</a>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <?php if ($request['main_photo']): ?>
                    <img src="<?= h($request['main_photo']) ?>" class="card-img-top" alt="Фото объекта" style="height:280px; object-fit:cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="h4"><?= h($request['title']) ?></h1>
                    <p class="mb-2"><strong>Район:</strong> <?= h($request['district_name']) ?></p>
                    <p class="mb-2"><strong>Адрес:</strong> <?= h($request['address']) ?></p>
                    <p class="mb-2"><strong>Цена:</strong> <?= number_format((float)$request['price'], 0, ',', ' ') ?> ₽</p>
                    <p class="mb-2"><strong>Риелтор:</strong> <?= h($request['realtor_name']) ?></p>
                    <p class="mb-0"><strong>Телефон риелтора:</strong> <?= h($request['realtor_phone']) ?></p>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Детали моей заявки</h2>
                    <p class="mb-2"><strong>Номер заявки:</strong> #<?= (int)$request['id'] ?></p>
                    <p class="mb-2"><strong>Создана:</strong> <?= h(date('d.m.Y H:i', strtotime($request['created_at']))) ?></p>
                    <p class="mb-2"><strong>Статус:</strong> <span class="badge bg-<?= h($statusInfo['class']) ?>"><?= h($statusInfo['label']) ?></span></p>
                    <p class="mb-2"><strong>Клиент:</strong> <?= h($request['client_name']) ?></p>
                    <p class="mb-2"><strong>Телефон:</strong> <?= h($request['client_phone']) ?></p>
                    <p class="mb-2"><strong>Email:</strong> <?= h($request['client_email'] ?: '-') ?></p>
                    <p class="mb-2"><strong>Желаемая дата просмотра:</strong> <?= h($request['preferred_date'] ? date('d.m.Y', strtotime($request['preferred_date'])) : '-') ?></p>
                    <p class="mb-0"><strong>Комментарий:</strong><br><?= nl2br(h($request['comment'] ?: '-')) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

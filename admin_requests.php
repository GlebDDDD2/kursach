<?php
require 'check_admin.php';
require 'db.php';

$sql = '
    SELECT
        vr.id AS request_id,
        vr.created_at,
        vr.preferred_date,
        vr.client_name,
        vr.client_phone,
        vr.client_email,
        vr.comment,
        vr.status,
        u.email AS user_email,
        p.title AS property_title,
        p.price AS property_price,
        d.name AS district_name
    FROM viewing_requests vr
    JOIN users u ON vr.user_id = u.id
    JOIN properties p ON vr.property_id = p.id
    JOIN districts d ON p.district_id = d.id
    ORDER BY vr.id DESC
';

$stmt = $pdo->query($sql);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заявками</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Главная</a>
            <a class="nav-link" href="admin_panel.php">Админка</a>
            <a class="nav-link active" href="admin_requests.php">Заявки</a>
            <a class="nav-link" href="add_property.php">Добавить объект</a>
            <a class="nav-link" href="logout.php">Выход</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Все заявки на просмотр</h1>
        <a href="admin_panel.php" class="btn btn-outline-secondary">← Назад в админку</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($requests): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID заявки</th>
                                <th>Дата</th>
                                <th>Пользователь</th>
                                <th>Клиент</th>
                                <th>Объект</th>
                                <th>Цена</th>
                                <th>Желаемая дата</th>
                                <th>Статус</th>
                                <th>Комментарий</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?= (int)$request['request_id'] ?></td>
                                    <td><?= h($request['created_at']) ?></td>
                                    <td><?= h($request['user_email']) ?></td>
                                    <td>
                                        <div><strong><?= h($request['client_name']) ?></strong></div>
                                        <div><?= h($request['client_phone']) ?></div>
                                        <div class="text-muted small"><?= h($request['client_email'] ?: '-') ?></div>
                                    </td>
                                    <td>
                                        <div><?= h($request['property_title']) ?></div>
                                        <div class="text-muted small"><?= h($request['district_name']) ?></div>
                                    </td>
                                    <td><?= number_format((float)$request['property_price'], 0, ',', ' ') ?> ₽</td>
                                    <td><?= h($request['preferred_date'] ?: '-') ?></td>
                                    <td>
                                        <?php if (($request['status'] ?? 'new') === 'processed'): ?>
                                            <span class="badge bg-success">Обработана</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Новая</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= nl2br(h($request['comment'] ?: '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">Заявок пока нет.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

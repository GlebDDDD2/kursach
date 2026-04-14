<?php
require 'db.php';
require 'check_admin.php';

$propertyCount = (int)$pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn();
$requestCount = (int)$pdo->query('SELECT COUNT(*) FROM viewing_requests')->fetchColumn();
$userCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

$latestRequests = $pdo->query(
    'SELECT vr.*, p.title AS property_title
     FROM viewing_requests vr
     JOIN properties p ON p.id = vr.property_id
     ORDER BY vr.id DESC
     LIMIT 5'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Главная</a>
            <a class="nav-link active" href="admin_panel.php">Админка</a>
            <a class="nav-link" href="add_property.php">Добавить объект</a>
            <a class="nav-link" href="logout.php">Выход</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="alert alert-success">
        <h1 class="h3">Панель администратора</h1>
        <p class="mb-0">Добро пожаловать, <?= h($_SESSION['username'] ?? 'Администратор') ?>. Здесь вы управляете объектами недвижимости и заявками на просмотр.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100"><div class="card-body"><h2 class="h6 text-muted">Объекты</h2><div class="display-6"><?= $propertyCount ?></div></div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100"><div class="card-body"><h2 class="h6 text-muted">Заявки</h2><div class="display-6"><?= $requestCount ?></div></div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100"><div class="card-body"><h2 class="h6 text-muted">Пользователи</h2><div class="display-6"><?= $userCount ?></div></div></div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <a href="add_property.php" class="btn btn-success">+ Добавить объект</a>
        <a href="catalog.php" class="btn btn-outline-primary">Открыть каталог</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h5 mb-3">Последние заявки на просмотр</h2>
            <?php if ($latestRequests): ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Объект</th>
                                <th>Клиент</th>
                                <th>Телефон</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latestRequests as $request): ?>
                                <tr>
                                    <td><?= (int)$request['id'] ?></td>
                                    <td><?= h($request['property_title']) ?></td>
                                    <td><?= h($request['client_name']) ?></td>
                                    <td><?= h($request['client_phone']) ?></td>
                                    <td><?= h($request['created_at']) ?></td>
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

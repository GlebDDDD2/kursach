<?php
require 'db.php';
require 'check_admin.php';

$successMsg = $_SESSION['success_message'] ?? '';
$errorMsg = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

$propertyCount = (int)$pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn();
$publishedCount = (int)$pdo->query('SELECT COUNT(*) FROM properties WHERE is_published = 1')->fetchColumn();
$requestCount = (int)$pdo->query('SELECT COUNT(*) FROM viewing_requests')->fetchColumn();
$userCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

$latestRequests = $pdo->query(
    'SELECT vr.*, p.title AS property_title
     FROM viewing_requests vr
     JOIN properties p ON p.id = vr.property_id
     ORDER BY vr.id DESC
     LIMIT 5'
)->fetchAll();

$properties = $pdo->query(
    'SELECT p.id, p.title, p.price, p.address, p.is_published, p.created_at, d.name AS district_name
     FROM properties p
     JOIN districts d ON d.id = p.district_id
     ORDER BY p.id DESC
     LIMIT 20'
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
    <?php if ($successMsg): ?>
        <div class="alert alert-success"><?= h($successMsg) ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="alert alert-danger"><?= h($errorMsg) ?></div>
    <?php endif; ?>

    <div class="alert alert-success">
        <h1 class="h3">Панель администратора</h1>
        <p class="mb-0">Добро пожаловать, <?= h($_SESSION['username'] ?? 'Администратор') ?>. Здесь вы управляете объектами недвижимости и заявками на просмотр.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><h2 class="h6 text-muted">Все объекты</h2><div class="display-6"><?= $propertyCount ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><h2 class="h6 text-muted">Опубликовано</h2><div class="display-6"><?= $publishedCount ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><h2 class="h6 text-muted">Заявки</h2><div class="display-6"><?= $requestCount ?></div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><h2 class="h6 text-muted">Пользователи</h2><div class="display-6"><?= $userCount ?></div></div></div></div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="add_property.php" class="btn btn-success">+ Добавить объект</a>
        <a href="admin_requests.php" class="btn btn-outline-dark">Все заявки</a>
        <a href="admin_seeder.php" class="btn btn-outline-primary">Генератор контента</a>
        <a href="catalog.php" class="btn btn-outline-secondary">Открыть каталог</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Управление объектами</h2></div>
        <div class="card-body">
            <?php if ($properties): ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Район</th>
                                <th>Цена</th>
                                <th>Статус</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $property): ?>
                                <tr>
                                    <td><?= (int)$property['id'] ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= h($property['title']) ?></div>
                                        <div class="text-muted small"><?= h($property['address']) ?></div>
                                    </td>
                                    <td><?= h($property['district_name']) ?></td>
                                    <td><?= number_format((float)$property['price'], 0, ',', ' ') ?> ₽</td>
                                    <td>
                                        <?php if ((int)$property['is_published'] === 1): ?>
                                            <span class="badge bg-success">Опубликован</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Скрыт</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            <a href="property.php?id=<?= (int)$property['id'] ?>" class="btn btn-sm btn-outline-primary">Открыть</a>
                                            <a href="edit_property.php?id=<?= (int)$property['id'] ?>" class="btn btn-sm btn-warning">Редактировать</a>

                                            <form action="toggle_property_visibility.php" method="POST" class="m-0">
                                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                                <input type="hidden" name="id" value="<?= (int)$property['id'] ?>">
                                                <input type="hidden" name="visibility" value="<?= (int)$property['is_published'] === 1 ? 0 : 1 ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-dark">
                                                    <?= (int)$property['is_published'] === 1 ? 'Снять с публикации' : 'Опубликовать' ?>
                                                </button>
                                            </form>

                                            <form action="delete_property.php" method="POST" class="m-0" onsubmit="return confirm('Удалить объект безвозвратно?');">
                                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                                <input type="hidden" name="id" value="<?= (int)$property['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">Объектов пока нет.</p>
            <?php endif; ?>
        </div>
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

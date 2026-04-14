<?php
session_start();
require 'db.php';

$where = [];
$params = [];

$price_from = trim($_GET['price_from'] ?? '');
$price_to = trim($_GET['price_to'] ?? '');
$floor = trim($_GET['floor'] ?? '');
$area_from = trim($_GET['area_from'] ?? '');
$area_to = trim($_GET['area_to'] ?? '');
$property_type = trim($_GET['property_type'] ?? '');

if ($price_from !== '') {
    $where[] = 'p.price >= :price_from';
    $params[':price_from'] = $price_from;
}
if ($price_to !== '') {
    $where[] = 'p.price <= :price_to';
    $params[':price_to'] = $price_to;
}
if ($floor !== '') {
    $where[] = 'p.floor = :floor';
    $params[':floor'] = $floor;
}
if ($area_from !== '') {
    $where[] = 'p.area >= :area_from';
    $params[':area_from'] = $area_from;
}
if ($area_to !== '') {
    $where[] = 'p.area <= :area_to';
    $params[':area_to'] = $area_to;
}
if ($property_type !== '') {
    $where[] = 'p.property_type = :property_type';
    $params[':property_type'] = $property_type;
}

$sql = 'SELECT p.*, d.name AS district_name, r.full_name AS realtor_name
        FROM properties p
        JOIN districts d ON p.district_id = d.id
        JOIN realtors r ON p.realtor_id = r.id';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог недвижимости</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Главная</a>
            <a class="nav-link active" href="catalog.php">Каталог</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="nav-link" href="profile.php">Профиль</a>
                <a class="nav-link" href="logout.php">Выход</a>
            <?php else: ?>
                <a class="nav-link" href="login.php">Вход</a>
                <a class="nav-link" href="register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h1 class="mb-4">Каталог объектов</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="catalog.php" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Тип</label>
                    <select name="property_type" class="form-select">
                        <option value="">Все</option>
                        <option value="apartment" <?= $property_type === 'apartment' ? 'selected' : '' ?>>Квартира</option>
                        <option value="house" <?= $property_type === 'house' ? 'selected' : '' ?>>Дом</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Цена от</label>
                    <input type="number" name="price_from" class="form-control" value="<?= htmlspecialchars($price_from) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Цена до</label>
                    <input type="number" name="price_to" class="form-control" value="<?= htmlspecialchars($price_to) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Этаж</label>
                    <input type="number" name="floor" class="form-control" value="<?= htmlspecialchars($floor) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Площадь от</label>
                    <input type="number" step="0.01" name="area_from" class="form-control" value="<?= htmlspecialchars($area_from) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Площадь до</label>
                    <input type="number" step="0.01" name="area_to" class="form-control" value="<?= htmlspecialchars($area_to) ?>">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">Применить фильтр</button>
                    <a class="btn btn-outline-secondary" href="catalog.php">Сбросить</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php if ($properties): ?>
            <?php foreach ($properties as $property): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($property['main_photo'])): ?>
                            <img src="<?= htmlspecialchars($property['main_photo']) ?>" class="card-img-top property-thumb" alt="Фото объекта">
                        <?php else: ?>
                            <div class="property-thumb bg-secondary text-white d-flex align-items-center justify-content-center">Нет фото</div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5"><?= htmlspecialchars($property['title']) ?></h2>
                            <p class="mb-1"><strong>Тип:</strong> <?= $property['property_type'] === 'apartment' ? 'Квартира' : 'Дом' ?></p>
                            <p class="mb-1"><strong>Район:</strong> <?= htmlspecialchars($property['district_name']) ?></p>
                            <p class="mb-1"><strong>Адрес:</strong> <?= htmlspecialchars($property['address']) ?></p>
                            <p class="mb-1"><strong>Цена:</strong> <?= number_format((float)$property['price'], 0, ',', ' ') ?> ₽</p>
                            <p class="mb-1"><strong>Площадь:</strong> <?= htmlspecialchars($property['area']) ?> м²</p>
                            <p class="mb-1"><strong>Этаж:</strong> <?= htmlspecialchars((string)($property['floor'] ?? '-')) ?></p>
                            <p class="mb-3"><strong>Риелтор:</strong> <?= htmlspecialchars($property['realtor_name']) ?></p>
                            <a href="property.php?id=<?= (int)$property['id'] ?>" class="btn btn-outline-primary mt-auto">Подробнее</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-warning mb-0">По вашему запросу ничего не найдено.</div></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

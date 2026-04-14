<?php
session_start();
require 'db.php';

$where = ['p.is_published = 1'];
$params = [];

$priceFrom = trim($_GET['price_from'] ?? '');
$priceTo = trim($_GET['price_to'] ?? '');
$floor = trim($_GET['floor'] ?? '');
$areaFrom = trim($_GET['area_from'] ?? '');
$areaTo = trim($_GET['area_to'] ?? '');
$propertyType = trim($_GET['property_type'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 6;

if ($priceFrom !== '') {
    $where[] = 'p.price >= :price_from';
    $params[':price_from'] = $priceFrom;
}
if ($priceTo !== '') {
    $where[] = 'p.price <= :price_to';
    $params[':price_to'] = $priceTo;
}
if ($floor !== '') {
    $where[] = 'p.floor = :floor';
    $params[':floor'] = $floor;
}
if ($areaFrom !== '') {
    $where[] = 'p.area >= :area_from';
    $params[':area_from'] = $areaFrom;
}
if ($areaTo !== '') {
    $where[] = 'p.area <= :area_to';
    $params[':area_to'] = $areaTo;
}
if ($propertyType !== '' && in_array($propertyType, ['apartment', 'house'], true)) {
    $where[] = 'p.property_type = :property_type';
    $params[':property_type'] = $propertyType;
}

$whereSql = ' WHERE ' . implode(' AND ', $where);

$countSql = 'SELECT COUNT(*) FROM properties p' . $whereSql;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

$sql = 'SELECT p.*, d.name AS district_name, r.full_name AS realtor_name
        FROM properties p
        JOIN districts d ON p.district_id = d.id
        JOIN realtors r ON p.realtor_id = r.id'
        . $whereSql .
        ' ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset';
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$properties = $stmt->fetchAll();

$queryParams = $_GET;
unset($queryParams['page']);
$queryBase = http_build_query($queryParams);
$queryBase = $queryBase !== '' ? $queryBase . '&' : '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог недвижимости</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Главная</a>
            <a class="nav-link active" href="catalog.php">Каталог</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                    <a class="nav-link" href="admin_panel.php">Админка</a>
                <?php endif; ?>
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
                        <option value="apartment" <?= $propertyType === 'apartment' ? 'selected' : '' ?>>Квартира</option>
                        <option value="house" <?= $propertyType === 'house' ? 'selected' : '' ?>>Дом</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Цена от</label>
                    <input type="number" name="price_from" class="form-control" value="<?= h($priceFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Цена до</label>
                    <input type="number" name="price_to" class="form-control" value="<?= h($priceTo) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Этаж</label>
                    <input type="number" name="floor" class="form-control" value="<?= h($floor) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Площадь от</label>
                    <input type="number" step="0.01" name="area_from" class="form-control" value="<?= h($areaFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Площадь до</label>
                    <input type="number" step="0.01" name="area_to" class="form-control" value="<?= h($areaTo) ?>">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">Применить фильтр</button>
                    <a class="btn btn-outline-secondary" href="catalog.php">Сбросить</a>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">Найдено объектов: <?= $totalRows ?></div>
        <div class="text-muted">Страница <?= $page ?> из <?= $totalPages ?></div>
    </div>

    <div class="row g-4">
        <?php if ($properties): ?>
            <?php foreach ($properties as $property): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($property['main_photo'])): ?>
                            <img src="<?= h($property['main_photo']) ?>" class="card-img-top property-thumb" alt="Фото объекта">
                        <?php else: ?>
                            <div class="property-thumb bg-secondary text-white d-flex align-items-center justify-content-center">Нет фото</div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5"><?= h($property['title']) ?></h2>
                            <p class="mb-1"><strong>Тип:</strong> <?= $property['property_type'] === 'apartment' ? 'Квартира' : 'Дом' ?></p>
                            <p class="mb-1"><strong>Район:</strong> <?= h($property['district_name']) ?></p>
                            <p class="mb-1"><strong>Адрес:</strong> <?= h($property['address']) ?></p>
                            <p class="mb-1"><strong>Цена:</strong> <?= number_format((float)$property['price'], 0, ',', ' ') ?> ₽</p>
                            <p class="mb-1"><strong>Площадь:</strong> <?= h($property['area']) ?> м²</p>
                            <p class="mb-1"><strong>Этаж:</strong> <?= h((string)($property['floor'] ?? '-')) ?></p>
                            <p class="mb-3"><strong>Риелтор:</strong> <?= h($property['realtor_name']) ?></p>
                            <a href="property.php?id=<?= (int)$property['id'] ?>" class="btn btn-outline-primary mt-auto">Подробнее</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-warning mb-0">По вашему запросу ничего не найдено.</div></div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center mb-0">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= h($queryBase) ?>page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
</body>
</html>

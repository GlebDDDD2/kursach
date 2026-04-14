<?php
session_start();
require 'db.php';

$stmt = $pdo->query(
    'SELECT p.*, d.name AS district_name
     FROM properties p
     JOIN districts d ON d.id = p.district_id
     ORDER BY p.id DESC
     LIMIT 6'
);
$properties = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Агентство недвижимости</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="index.php">Главная</a>
                <a class="nav-link" href="catalog.php">Каталог</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <a class="nav-link" href="admin_panel.php">Админка</a>
                        <a class="nav-link" href="add_property.php">Добавить объект</a>
                    <?php endif; ?>
                    <a class="nav-link" href="profile.php">Профиль</a>
                    <a class="nav-link" href="logout.php">Выход</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Вход</a>
                    <a class="nav-link" href="register.php">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<header class="hero-section text-white d-flex align-items-center">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Сайт агентства недвижимости</h1>
        <p class="lead mb-4">Квартиры, дома, удобный фильтр, галерея фото и заявки на просмотр.</p>
        <a href="catalog.php" class="btn btn-primary btn-lg">Открыть каталог</a>
    </div>
</header>

<div class="container py-5">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h5">Каталог объектов</h2>
                    <p class="mb-0">Просмотр квартир и домов с основной информацией об объекте.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h5">Сложный фильтр</h2>
                    <p class="mb-0">Поиск по цене, этажу, площади и типу недвижимости.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h5">Заявки на просмотр</h2>
                    <p class="mb-0">Клиенты могут оставить заявку на интересующий объект.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h3 mb-0">Новые объекты</h2>
        <a href="catalog.php" class="btn btn-outline-primary">Смотреть все</a>
    </div>

    <div class="row">
        <?php foreach ($properties as $property): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($property['main_photo'])): ?>
                        <img src="<?= h($property['main_photo']) ?>" class="card-img-top" alt="Фото объекта" style="height:220px; object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:220px;">Нет фото</div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h3 class="h5 card-title"><?= h($property['title']) ?></h3>
                        <p class="mb-1"><strong>Цена:</strong> <?= number_format((float)$property['price'], 0, ',', ' ') ?> ₽</p>
                        <p class="mb-1"><strong>Район:</strong> <?= h($property['district_name']) ?></p>
                        <p class="mb-0 text-muted text-truncate"><?= h($property['address']) ?></p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="property.php?id=<?= (int)$property['id'] ?>" class="btn btn-primary w-100">Подробнее</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (!$properties): ?>
            <p class="text-muted">Объекты пока не добавлены. Зайдите под админом и добавьте первый объект.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

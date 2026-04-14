<?php
session_start();
require 'db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Некорректный ID объекта.');
}

$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$sql = 'SELECT p.*, d.name AS district_name, r.full_name AS realtor_name, r.phone AS realtor_phone, r.email AS realtor_email
        FROM properties p
        JOIN districts d ON p.district_id = d.id
        JOIN realtors r ON p.realtor_id = r.id
        WHERE p.id = ?';
if (!$isAdmin) {
    $sql .= ' AND p.is_published = 1';
}
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$property = $stmt->fetch();

if (!$property) {
    die('Объект не найден.');
}

$photoStmt = $pdo->prepare('SELECT * FROM property_photos WHERE property_id = ? ORDER BY sort_order ASC, id ASC');
$photoStmt->execute([$id]);
$photos = $photoStmt->fetchAll();

if (!$photos && !empty($property['main_photo'])) {
    $photos = [
        ['photo_path' => $property['main_photo']]
    ];
}

$userData = null;
if (isset($_SESSION['user_id'])) {
    $userStmt = $pdo->prepare('SELECT username, phone, email FROM users WHERE id = ?');
    $userStmt->execute([(int)$_SESSION['user_id']]);
    $userData = $userStmt->fetch();
}

$mapQuery = rawurlencode($property['address']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($property['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Главная</a>
            <a class="nav-link" href="catalog.php">Каталог</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($isAdmin): ?>
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
    <a href="catalog.php" class="btn btn-outline-secondary mb-4">← Назад в каталог</a>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if ($photos): ?>
                        <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded overflow-hidden">
                                <?php foreach ($photos as $index => $photo): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= h($photo['photo_path']) ?>" class="d-block w-100" alt="Фото объекта" style="height:420px; object-fit:cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($photos) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded" style="height:420px;">Нет фото</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <h1 class="h3 mb-0"><?= h($property['title']) ?></h1>
                        <?php if ((int)$property['is_published'] !== 1): ?>
                            <span class="badge bg-secondary">Скрыт</span>
                        <?php endif; ?>
                    </div>
                    <p class="mb-2"><strong>Тип:</strong> <?= $property['property_type'] === 'apartment' ? 'Квартира' : 'Дом' ?></p>
                    <p class="mb-2"><strong>Цена:</strong> <?= number_format((float)$property['price'], 0, ',', ' ') ?> ₽</p>
                    <p class="mb-2"><strong>Район:</strong> <?= h($property['district_name']) ?></p>
                    <p class="mb-2"><strong>Адрес:</strong> <?= h($property['address']) ?></p>
                    <p class="mb-2"><strong>Площадь:</strong> <?= h($property['area']) ?> м²</p>
                    <p class="mb-2"><strong>Этаж:</strong> <?= h($property['floor'] ?? '-') ?></p>
                    <p class="mb-2"><strong>Комнат:</strong> <?= h($property['rooms'] ?? '-') ?></p>
                    <hr>
                    <p class="mb-2"><strong>Риелтор:</strong> <?= h($property['realtor_name']) ?></p>
                    <p class="mb-2"><strong>Телефон:</strong> <?= h($property['realtor_phone']) ?></p>
                    <p class="mb-0"><strong>Email:</strong> <?= h($property['realtor_email']) ?></p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Оставить заявку на просмотр</h2>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="alert alert-warning mb-0">
                            Чтобы отправить заявку, сначала <a href="login.php">войдите</a> или <a href="register.php">зарегистрируйтесь</a>.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="request_viewing.php">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="property_id" value="<?= (int)$property['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Ваше имя</label>
                                <input type="text" name="client_name" class="form-control" value="<?= h($userData['username'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Телефон</label>
                                <input type="text" name="client_phone" class="form-control" value="<?= h($userData['phone'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="client_email" class="form-control" value="<?= h($userData['email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Желаемая дата</label>
                                <input type="date" name="preferred_date" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Комментарий</label>
                                <textarea name="comment" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Отправить заявку</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Описание объекта</h2>
            <p class="mb-0"><?= nl2br(h($property['description'])) ?></p>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Расположение на карте</h2>
                <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer" href="https://yandex.ru/maps/?text=<?= $mapQuery ?>">Открыть в Яндекс.Картах</a>
            </div>
            <div class="ratio ratio-16x9 rounded overflow-hidden border">
                <iframe src="https://yandex.ru/map-widget/v1/?text=<?= $mapQuery ?>&z=15" allowfullscreen loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

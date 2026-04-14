<?php
require 'db.php';
require 'check_admin.php';

$message = '';
$districts = $pdo->query('SELECT id, name FROM districts ORDER BY name')->fetchAll();
$realtors = $pdo->query('SELECT id, full_name FROM realtors ORDER BY full_name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $propertyType = $_POST['property_type'] ?? 'apartment';
    $districtId = (int)($_POST['district_id'] ?? 0);
    $realtorId = (int)($_POST['realtor_id'] ?? 0);
    $address = trim($_POST['address'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $floor = trim($_POST['floor'] ?? '');
    $totalFloors = trim($_POST['total_floors'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $rooms = trim($_POST['rooms'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $mainPhoto = trim($_POST['main_photo'] ?? '');
    $galleryRaw = trim($_POST['gallery_urls'] ?? '');
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($title === '' || $districtId <= 0 || $realtorId <= 0 || $address === '' || $price === '' || $area === '') {
        $message = '<div class="alert alert-danger">Заполните обязательные поля: название, район, риелтор, адрес, цена и площадь.</div>';
    } elseif (!in_array($propertyType, ['apartment', 'house'], true)) {
        $message = '<div class="alert alert-danger">Некорректный тип объекта.</div>';
    } elseif ($userId <= 0) {
        $message = '<div class="alert alert-danger">Ошибка сессии. Войдите заново.</div>';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO properties (title, property_type, district_id, realtor_id, user_id, address, price, floor, total_floors, area, rooms, description, main_photo)
             VALUES (:title, :property_type, :district_id, :realtor_id, :user_id, :address, :price, :floor, :total_floors, :area, :rooms, :description, :main_photo)'
        );

        try {
            $stmt->execute([
                ':title' => $title,
                ':property_type' => $propertyType,
                ':district_id' => $districtId,
                ':realtor_id' => $realtorId,
                ':user_id' => $userId,
                ':address' => $address,
                ':price' => $price,
                ':floor' => $floor !== '' ? $floor : null,
                ':total_floors' => $totalFloors !== '' ? $totalFloors : null,
                ':area' => $area,
                ':rooms' => $rooms !== '' ? $rooms : null,
                ':description' => $description !== '' ? $description : null,
                ':main_photo' => $mainPhoto !== '' ? $mainPhoto : null,
            ]);

            $propertyId = (int)$pdo->lastInsertId();
            $galleryLines = preg_split('/
||
/', $galleryRaw);
            $insertPhoto = $pdo->prepare('INSERT INTO property_photos (property_id, photo_path, sort_order) VALUES (?, ?, ?)');
            $sort = 1;

            if ($mainPhoto !== '') {
                $insertPhoto->execute([$propertyId, $mainPhoto, $sort++]);
            }

            foreach ($galleryLines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $insertPhoto->execute([$propertyId, $line, $sort++]);
            }

            $message = '<div class="alert alert-success">Объект недвижимости успешно добавлен.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Ошибка БД: ' . h($e->getMessage()) . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить объект</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="admin_panel.php">Админка</a>
            <a class="nav-link active" href="add_property.php">Добавить объект</a>
            <a class="nav-link" href="logout.php">Выход</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h1 class="h3 mb-3">Добавление нового объекта</h1>
    <a href="admin_panel.php" class="btn btn-outline-secondary mb-3">← Назад в админку</a>

    <?= $message ?>

    <form method="POST" class="card shadow-sm p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Название объекта</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Тип</label>
                <select name="property_type" class="form-select">
                    <option value="apartment">Квартира</option>
                    <option value="house">Дом</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Цена</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Район</label>
                <select name="district_id" class="form-select" required>
                    <option value="">Выберите район</option>
                    <?php foreach ($districts as $district): ?>
                        <option value="<?= (int)$district['id'] ?>"><?= h($district['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Риелтор</label>
                <select name="realtor_id" class="form-select" required>
                    <option value="">Выберите риелтора</option>
                    <?php foreach ($realtors as $realtor): ?>
                        <option value="<?= (int)$realtor['id'] ?>"><?= h($realtor['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Адрес</label>
                <input type="text" name="address" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Этаж</label>
                <input type="number" name="floor" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Всего этажей</label>
                <input type="number" name="total_floors" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Площадь, м²</label>
                <input type="number" step="0.01" name="area" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Комнат</label>
                <input type="number" name="rooms" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Главное фото (URL)</label>
                <input type="text" name="main_photo" class="form-control" placeholder="https://example.com/photo.jpg">
            </div>
            <div class="col-12">
                <label class="form-label">Галерея фото (по одной ссылке на строку)</label>
                <textarea name="gallery_urls" class="form-control" rows="4" placeholder="https://example.com/photo2.jpg&#10;https://example.com/photo3.jpg"></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Описание</label>
                <textarea name="description" class="form-control" rows="5"></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-success mt-4">Сохранить объект</button>
    </form>
</div>
</body>
</html>

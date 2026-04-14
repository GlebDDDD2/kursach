<?php
require 'db.php';
require 'check_admin.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Объект не найден.');
}

$districts = $pdo->query('SELECT id, name FROM districts ORDER BY name')->fetchAll();
$realtors = $pdo->query('SELECT id, full_name FROM realtors ORDER BY full_name')->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM properties WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$property = $stmt->fetch();
if (!$property) {
    die('Объект не найден.');
}

$photosStmt = $pdo->prepare('SELECT photo_path FROM property_photos WHERE property_id = ? ORDER BY sort_order ASC, id ASC');
$photosStmt->execute([$id]);
$photoRows = $photosStmt->fetchAll();
$galleryUrls = [];
foreach ($photoRows as $photoRow) {
    if ($photoRow['photo_path'] !== $property['main_photo']) {
        $galleryUrls[] = $photoRow['photo_path'];
    }
}

$formData = [
    'title' => $property['title'],
    'property_type' => $property['property_type'],
    'district_id' => $property['district_id'],
    'realtor_id' => $property['realtor_id'],
    'address' => $property['address'],
    'price' => $property['price'],
    'floor' => $property['floor'],
    'total_floors' => $property['total_floors'],
    'area' => $property['area'],
    'rooms' => $property['rooms'],
    'description' => $property['description'],
    'main_photo' => $property['main_photo'],
    'gallery_urls' => implode("\n", $galleryUrls),
    'is_published' => $property['is_published'],
];
$message = '';
$submitLabel = 'Обновить объект';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $message = '<div class="alert alert-danger">Ошибка безопасности: неверный CSRF-токен.</div>';
    } else {
        foreach ($formData as $key => $value) {
            if ($key === 'is_published') {
                $formData[$key] = isset($_POST[$key]) ? 1 : 0;
            } else {
                $formData[$key] = trim((string)($_POST[$key] ?? ''));
            }
        }

        $districtId = (int)$formData['district_id'];
        $realtorId = (int)$formData['realtor_id'];
        $propertyType = in_array($formData['property_type'], ['apartment', 'house'], true) ? $formData['property_type'] : '';

        if ($formData['title'] === '' || $districtId <= 0 || $realtorId <= 0 || $formData['address'] === '' || $formData['price'] === '' || $formData['area'] === '') {
            $message = '<div class="alert alert-danger">Заполните обязательные поля.</div>';
        } elseif ($propertyType === '') {
            $message = '<div class="alert alert-danger">Некорректный тип объекта.</div>';
        } else {
            $update = $pdo->prepare(
                'UPDATE properties
                 SET title = :title,
                     property_type = :property_type,
                     district_id = :district_id,
                     realtor_id = :realtor_id,
                     address = :address,
                     price = :price,
                     floor = :floor,
                     total_floors = :total_floors,
                     area = :area,
                     rooms = :rooms,
                     description = :description,
                     main_photo = :main_photo,
                     is_published = :is_published
                 WHERE id = :id'
            );

            try {
                $update->execute([
                    ':title' => $formData['title'],
                    ':property_type' => $propertyType,
                    ':district_id' => $districtId,
                    ':realtor_id' => $realtorId,
                    ':address' => $formData['address'],
                    ':price' => $formData['price'],
                    ':floor' => $formData['floor'] !== '' ? $formData['floor'] : null,
                    ':total_floors' => $formData['total_floors'] !== '' ? $formData['total_floors'] : null,
                    ':area' => $formData['area'],
                    ':rooms' => $formData['rooms'] !== '' ? $formData['rooms'] : null,
                    ':description' => $formData['description'] !== '' ? $formData['description'] : null,
                    ':main_photo' => $formData['main_photo'] !== '' ? $formData['main_photo'] : null,
                    ':is_published' => $formData['is_published'],
                    ':id' => $id,
                ]);

                $pdo->prepare('DELETE FROM property_photos WHERE property_id = ?')->execute([$id]);
                $galleryLines = preg_split('/\r\n|\r|\n/', $formData['gallery_urls']);
                $insertPhoto = $pdo->prepare('INSERT INTO property_photos (property_id, photo_path, sort_order) VALUES (?, ?, ?)');
                $sort = 1;

                if ($formData['main_photo'] !== '') {
                    $insertPhoto->execute([$id, $formData['main_photo'], $sort++]);
                }

                foreach ($galleryLines as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $insertPhoto->execute([$id, $line, $sort++]);
                }

                $_SESSION['success_message'] = 'Объект обновлен.';
                header('Location: admin_panel.php');
                exit;
            } catch (PDOException $e) {
                $message = '<div class="alert alert-danger">Ошибка БД: ' . h($e->getMessage()) . '</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать объект</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="admin_panel.php">Админка</a>
            <a class="nav-link active" href="edit_property.php?id=<?= $id ?>">Редактирование</a>
            <a class="nav-link" href="logout.php">Выход</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h1 class="h3 mb-3">Редактирование объекта</h1>
    <a href="admin_panel.php" class="btn btn-outline-secondary mb-3">← Назад в админку</a>

    <?= $message ?>

    <?php require 'property_form.php'; ?>
</div>
</body>
</html>

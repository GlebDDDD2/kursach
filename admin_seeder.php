<?php
session_start();
require 'db.php';
require 'check_admin.php';

$message = '';
$allowedTables = ['properties'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $message = '<div class="alert alert-danger">Ошибка безопасности: неверный CSRF-токен.</div>';
    } else {
        $tableName = $_POST['table_name'] ?? '';
        $count = max(1, min(100, (int)($_POST['count'] ?? 10)));

        if (!in_array($tableName, $allowedTables, true)) {
            $message = '<div class="alert alert-danger">Ошибка: Таблица не найдена в белом списке.</div>';
        } else {
            $exportDir = __DIR__ . '/exports';
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0775, true);
            }

            $stmt = $pdo->query('SELECT * FROM properties ORDER BY id ASC');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                $message = '<div class="alert alert-warning">Таблица пуста. Сначала создайте хотя бы один объект вручную.</div>';
            } else {
                $filename = $exportDir . '/properties_' . date('Y-m-d_H-i-s') . '.csv';
                $fp = fopen($filename, 'w');
                fputcsv($fp, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($fp, $row);
                }
                fclose($fp);

                $template = $rows[array_rand($rows)];
                $insertProperty = $pdo->prepare(
                    'INSERT INTO properties (title, property_type, district_id, realtor_id, user_id, address, price, floor, total_floors, area, rooms, description, main_photo, is_published)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $insertPhoto = $pdo->prepare('INSERT INTO property_photos (property_id, photo_path, sort_order) VALUES (?, ?, ?)');
                $templatePhotosStmt = $pdo->prepare('SELECT photo_path, sort_order FROM property_photos WHERE property_id = ? ORDER BY sort_order ASC, id ASC');
                $templatePhotosStmt->execute([(int)$template['id']]);
                $templatePhotos = $templatePhotosStmt->fetchAll();

                $inserted = 0;
                for ($i = 0; $i < $count; $i++) {
                    $suffix = mt_rand(1000, 9999);
                    $price = round((float)$template['price'] * (1 + mt_rand(-15, 15) / 100), 2);
                    $area = round((float)$template['area'] * (1 + mt_rand(-10, 10) / 100), 2);
                    $floor = $template['floor'] !== null ? max(1, (int)$template['floor'] + mt_rand(-2, 2)) : null;
                    $rooms = $template['rooms'] !== null ? max(1, (int)$template['rooms'] + mt_rand(-1, 1)) : null;

                    try {
                        $insertProperty->execute([
                            $template['title'] . ' #' . $suffix,
                            $template['property_type'],
                            $template['district_id'],
                            $template['realtor_id'],
                            $template['user_id'],
                            $template['address'] . ', корпус ' . mt_rand(1, 9),
                            $price,
                            $floor,
                            $template['total_floors'],
                            $area,
                            $rooms,
                            (string)$template['description'] . ' Копия #' . $suffix,
                            $template['main_photo'],
                            1,
                        ]);
                        $newPropertyId = (int)$pdo->lastInsertId();

                        foreach ($templatePhotos as $photo) {
                            $insertPhoto->execute([$newPropertyId, $photo['photo_path'], $photo['sort_order']]);
                        }

                        $inserted++;
                    } catch (Throwable $e) {
                        continue;
                    }
                }

                $message = '<div class="alert alert-info">Бэкап сохранен: exports/' . h(basename($filename)) . '<br>Успешно сгенерировано объектов: ' . $inserted . ' из ' . $count . '.</div>';
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
    <title>Генератор контента</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5 bg-light">
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">⚙️ Генератор контента (Seeder)</h3>
            </div>
            <div class="card-body">
                <?= $message ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Выберите таблицу для наполнения</label>
                        <select name="table_name" class="form-select">
                            <?php foreach ($allowedTables as $table): ?>
                                <option value="<?= h($table) ?>"><?= h($table) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Для твоего проекта рекомендуется properties.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Сколько записей добавить?</label>
                        <input type="number" name="count" class="form-control" value="10" min="1" max="100">
                    </div>

                    <div class="alert alert-warning">
                        <small>
                            ⚠️ Скрипт создаст CSV-бэкап в папке /exports, а затем скопирует случайный объект указанное количество раз, меняя цену, площадь и адрес.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success w-100">🚀 Наполнить и сделать бэкап</button>
                </form>

                <a href="admin_panel.php" class="btn btn-secondary mt-3">← Вернуться в админку</a>
            </div>
        </div>
    </div>
</body>
</html>

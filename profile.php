<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$successMsg = $_SESSION['success_message'] ?? '';
$errorMsg = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

$userStmt = $pdo->prepare('SELECT id, username, email, phone, role, created_at FROM users WHERE id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

if (!$user) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$sql = '
    SELECT
        vr.id AS request_id,
        vr.created_at,
        vr.preferred_date,
        vr.status,
        p.id AS property_id,
        p.title,
        p.price,
        p.main_photo
    FROM viewing_requests vr
    JOIN properties p ON vr.property_id = p.id
    WHERE vr.user_id = ?
    ORDER BY vr.created_at DESC
';
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$myRequests = $stmt->fetchAll();

$statusMap = [
    'new' => ['label' => 'Новая', 'class' => 'primary'],
    'processed' => ['label' => 'Обработана', 'class' => 'success'],
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Главная</a>
            <a class="nav-link" href="catalog.php">Каталог</a>
            <?php if (($user['role'] ?? '') === 'admin'): ?>
                <a class="nav-link" href="admin_panel.php">Админка</a>
            <?php endif; ?>
            <a class="nav-link active" href="profile.php">Профиль</a>
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

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="h4 mb-3">Личный кабинет</h1>
                    <p class="mb-2"><strong>Имя:</strong> <?= h($user['username'] ?: 'Пользователь') ?></p>
                    <p class="mb-2"><strong>Email:</strong> <?= h($user['email']) ?></p>
                    <p class="mb-2"><strong>Телефон:</strong> <?= h($user['phone'] ?: '-') ?></p>
                    <p class="mb-2"><strong>Роль:</strong> <?= h($user['role']) ?></p>
                    <p class="mb-0"><strong>Регистрация:</strong> <?= h(date('d.m.Y H:i', strtotime($user['created_at']))) ?></p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Смена пароля</h2>
                    <form method="POST" action="change_password.php">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <div class="mb-3">
                            <label class="form-label">Старый пароль</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Повтор нового пароля</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Сменить пароль</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Мои заявки на просмотр</h2>
                </div>
                <div class="card-body">
                    <?php if ($myRequests): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>№</th>
                                        <th>Дата</th>
                                        <th>Объект</th>
                                        <th>Цена</th>
                                        <th>Желаемая дата</th>
                                        <th>Статус</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myRequests as $request): ?>
                                        <?php $statusInfo = $statusMap[$request['status']] ?? ['label' => $request['status'], 'class' => 'secondary']; ?>
                                        <tr>
                                            <td>#<?= (int)$request['request_id'] ?></td>
                                            <td><?= h(date('d.m.Y H:i', strtotime($request['created_at']))) ?></td>
                                            <td>
                                                <div class="fw-semibold"><?= h($request['title']) ?></div>
                                            </td>
                                            <td><?= number_format((float)$request['price'], 0, ',', ' ') ?> ₽</td>
                                            <td><?= h($request['preferred_date'] ? date('d.m.Y', strtotime($request['preferred_date'])) : '-') ?></td>
                                            <td><span class="badge bg-<?= h($statusInfo['class']) ?>"><?= h($statusInfo['label']) ?></span></td>
                                            <td><a href="request_details.php?id=<?= (int)$request['request_id'] ?>" class="btn btn-sm btn-outline-primary">Подробнее</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <h3 class="h5 text-muted">У вас пока нет заявок.</h3>
                            <a href="catalog.php" class="btn btn-primary mt-3">Перейти в каталог</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Estate Agency</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Главная</a>
            <a class="nav-link" href="catalog.php">Каталог</a>
            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <a class="nav-link" href="admin_panel.php">Админка</a>
            <?php endif; ?>
            <a class="nav-link active" href="profile.php">Профиль</a>
            <a class="nav-link" href="logout.php">Выход</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h3 mb-3">Личный кабинет</h1>
            <p><strong>ID пользователя:</strong> <?= (int)$_SESSION['user_id'] ?></p>
            <p><strong>Имя:</strong> <?= h($_SESSION['username'] ?? 'Пользователь') ?></p>
            <p><strong>Роль:</strong> <?= h($_SESSION['user_role'] ?? 'client') ?></p>
        </div>
    </div>
</div>
</body>
</html>

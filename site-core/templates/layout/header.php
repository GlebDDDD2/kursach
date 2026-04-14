<?php
$config = is_file(dirname(__DIR__, 3) . '/config/config.php') ? require dirname(__DIR__, 3) . '/config/config.php' : ['base_url' => ''];
$baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
$full = static fn(string $path = 'index.php'): string => $baseUrl !== '' ? $baseUrl . '/' . ltrim($path, '/') : $path;
$e = static fn(mixed $value): string => \App\Core\Helpers::e($value);
$csrf = static fn(): string => \App\Core\Helpers::csrfToken();
$isLoggedIn = \App\Core\Auth::check();
$isAdmin = \App\Core\Auth::role() === 'admin';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'Estate Agency') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $e($full('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= $e($full('index.php')) ?>">Estate Agency</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= $e($full('index.php')) ?>">Главная</a>
                <a class="nav-link" href="<?= $e($full('index.php?page=catalog')) ?>">Каталог</a>
                <?php if ($isLoggedIn): ?>
                    <?php if ($isAdmin): ?>
                        <a class="nav-link" href="<?= $e($full('index.php?page=admin')) ?>">Админка</a>
                    <?php endif; ?>
                    <a class="nav-link" href="<?= $e($full('index.php?page=profile')) ?>">Профиль</a>
                    <a class="nav-link" href="<?= $e($full('index.php?page=logout')) ?>">Выход</a>
                <?php else: ?>
                    <a class="nav-link" href="<?= $e($full('index.php?page=login')) ?>">Вход</a>
                    <a class="nav-link" href="<?= $e($full('index.php?page=register')) ?>">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

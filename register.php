<?php
session_start();
require 'db.php';

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $passConfirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $email === '' || $pass === '' || $passConfirm === '') {
        $errorMsg = 'Заполните все обязательные поля.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Некорректный email.';
    } elseif ($pass !== $passConfirm) {
        $errorMsg = 'Пароли не совпадают.';
    } elseif (strlen($pass) < 6) {
        $errorMsg = 'Пароль должен содержать минимум 6 символов.';
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (email, password_hash, username, phone, role)
                VALUES (:email, :password_hash, :username, :phone, 'client')";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':email' => $email,
                ':password_hash' => $hash,
                ':username' => $username,
                ':phone' => $phone !== '' ? $phone : null,
            ]);
            $successMsg = 'Регистрация успешна. Теперь можно войти в систему.';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errorMsg = 'Такой email уже зарегистрирован.';
            } else {
                $errorMsg = 'Ошибка базы данных: ' . $e->getMessage();
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
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h1 class="h4 mb-0">Регистрация</h1>
                </div>
                <div class="card-body">
                    <?php if ($errorMsg): ?>
                        <div class="alert alert-danger"><?= h($errorMsg) ?></div>
                    <?php endif; ?>

                    <?php if ($successMsg): ?>
                        <div class="alert alert-success">
                            <?= h($successMsg) ?>
                            <div class="mt-2"><a href="login.php">Перейти ко входу</a></div>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="register.php">
                            <div class="mb-3">
                                <label class="form-label">Имя</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Телефон</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Пароль</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Подтверждение пароля</label>
                                <input type="password" name="password_confirm" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="login.php">Уже есть аккаунт? Войти</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

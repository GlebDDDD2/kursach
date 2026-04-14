<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Helpers;
use App\Core\View;
use App\Models\User;
use PDOException;

final class AuthController
{
    public function login(): void
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $error = 'Заполните все поля.';
            } else {
                $user = (new User())->findByEmail($email);
                if ($user && password_verify($password, $user['password_hash'])) {
                    Auth::login($user);
                    Helpers::redirect($user['role'] === 'admin' ? 'index.php?page=admin' : 'index.php?page=profile');
                }
                $error = 'Неверный логин или пароль.';
            }
        }

        View::render('auth/login', ['title' => 'Вход', 'error' => $error]);
    }

    public function register(): void
    {
        $error = '';
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $phone = trim((string) ($_POST['phone'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $confirm = (string) ($_POST['password_confirm'] ?? '');

            if ($username === '' || $email === '' || $password === '' || $confirm === '') {
                $error = 'Заполните все обязательные поля.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Некорректный email.';
            } elseif ($password !== $confirm) {
                $error = 'Пароли не совпадают.';
            } elseif (strlen($password) < 6) {
                $error = 'Пароль должен содержать минимум 6 символов.';
            } else {
                try {
                    (new User())->create($email, $password, $username, $phone !== '' ? $phone : null);
                    $success = 'Регистрация успешна. Теперь можно войти в систему.';
                } catch (PDOException $e) {
                    $error = $e->getCode() === '23000' ? 'Такой email уже зарегистрирован.' : 'Ошибка базы данных: ' . $e->getMessage();
                }
            }
        }

        View::render('auth/register', ['title' => 'Регистрация', 'error' => $error, 'success' => $success]);
    }

    public function logout(): void
    {
        Auth::logout();
        Helpers::redirect('index.php');
    }
}

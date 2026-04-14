<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public static function role(): string
    {
        return (string) ($_SESSION['user_role'] ?? 'guest');
    }

    public static function user(): array
    {
        return [
            'id' => self::id(),
            'username' => (string) ($_SESSION['username'] ?? ''),
            'role' => self::role(),
        ];
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            Helpers::redirect('index.php?page=login');
        }
    }

    public static function requireAdmin(): void
    {
        if (!self::check() || self::role() !== 'admin') {
            http_response_code(403);
            exit('ДОСТУП ЗАПРЕЩЕН. У вас нет прав администратора. <a href="index.php?page=login">Войти</a>');
        }
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = (string) ($user['username'] ?? '');
        $_SESSION['user_role'] = (string) $user['role'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
    }
}

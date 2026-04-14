<?php
declare(strict_types=1);

namespace App\Core;

final class Helpers
{
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    public static function csrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Сессия не запущена.');
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(?string $token): bool
    {
        return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals((string) $_SESSION['csrf_token'], $token);
    }

    public static function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    public static function getFlash(string $key): string
    {
        $message = $_SESSION['flash'][$key] ?? '';
        unset($_SESSION['flash'][$key]);
        return is_string($message) ? $message : '';
    }

    public static function old(string $key, string $default = ''): string
    {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : $default;
    }
}

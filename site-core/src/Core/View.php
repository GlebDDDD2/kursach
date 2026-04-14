<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $templateFile = dirname(__DIR__, 2) . '/templates/' . $template . '.php';
        if (!is_file($templateFile)) {
            throw new \RuntimeException('Шаблон не найден: ' . $template);
        }
        require dirname(__DIR__, 2) . '/templates/layout/header.php';
        require $templateFile;
        require dirname(__DIR__, 2) . '/templates/layout/footer.php';
    }
}

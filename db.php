<?php
// db.php
$host = 'localhost';
$db   = 'YOUR_DB_NAME';
$user = 'YOUR_DB_USER';
$pass = 'YOUR_DB_PASSWORD';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}

function h($string): string
{
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

file_put_contents(__DIR__ . '/debug.txt', "STEP 1\n");

require_once __DIR__ . '/../site-core/src/Core/Autoloader.php';
file_put_contents(__DIR__ . '/debug.txt', "STEP 2\n", FILE_APPEND);

use App\Core\Autoloader;

Autoloader::register(__DIR__ . '/../site-core/src');
file_put_contents(__DIR__ . '/debug.txt', "STEP 3\n", FILE_APPEND);

$result = class_exists('App\Controllers\HomeController');
file_put_contents(__DIR__ . '/debug.txt', "STEP 4: class_exists = " . ($result ? 'true' : 'false') . "\n", FILE_APPEND);

$test = new App\Controllers\HomeController();
file_put_contents(__DIR__ . '/debug.txt', "STEP 5\n", FILE_APPEND);

echo 'OK';
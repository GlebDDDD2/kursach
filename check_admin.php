<?php
// check_admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die("ДОСТУП ЗАПРЕЩЕН. У вас нет прав администратора. <a href='login.php'>Войти</a>");
}

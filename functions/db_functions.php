<?php

function getPDO() {
    $host = '127.0.0.1';
    $db   = 'hotel';           // tên DB của bạn là "hotel"
    $user = 'root';
    $pass = '972005';          
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        die("Kết nối database thất bại: " . $e->getMessage());
    }
}

// Hàm kiểm tra đăng nhập + phân quyền user
function require_user() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
        header("Location: login.php");
        exit;
    }
}

// Hàm kiểm tra admin (dùng sau này)
function require_admin() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header("Location: login.php");
        exit;
    }
}
?>
<?php
// functions/logout.php
session_start();

// Xóa toàn bộ dữ liệu session
$_SESSION = [];

// Xóa cookie session nếu có
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session hoàn toàn
session_destroy();

// Chuyển hướng về trang login (cùng thư mục views)
header("Location: ../views/login.php");
exit;
?>
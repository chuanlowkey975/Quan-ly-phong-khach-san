<?php
require '../functions/db_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pdo = getPDO();

    $username = $_POST['username'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];

    // Lưu mật khẩu – nếu chưa dùng password_hash thì để nguyên public 
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert vào database
    $sql = "INSERT INTO users (username, password, fullname, role) VALUES (?, ?, ?, 'user')";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$username, $hashedPassword, $fullname])) {
        // Chuyển hướng về trang đăng nhập
        header("Location: ../login.php?message=register_success");
        exit;
    } else {
        echo "Lỗi khi đăng ký.";
    }
}
?>

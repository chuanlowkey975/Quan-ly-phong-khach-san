<?php
session_start();
require_once "../functions/db_functions.php";
$pdo = getPDO();

$error = "";
$success = isset($_GET['register']) && $_GET['register'] === 'success';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = md5($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user;
        header("Location: " . ($user['role'] === "admin" ? "admin_dashboard.php" : "user_dashboard.php"));
        exit;
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập • Ninh Chuẩn Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css?v=<?= time() ?>">
</head>
<body class="auth-bg">

<div class="container login-wrapper">
    <div class="row align-items-center g-5">
        <!-- Cột trái: Form + Logo -->
        <div class="col-lg-6">
            <div class="brand-section">
                <img src="../images/logo.png" alt="Ninh Chuẩn Hotel Logo" class="logo-img mb-3">
                <h1 class="brand-name">Ninh Chuẩn Hotel</h1>
                <p class="brand-subtitle">RESORT & SPA • LUXURY EXPERIENCE</p>
            </div>

            <div class="login-card">
                <h2 class="text-center mb-5 fw-bold" style="color: var(--primary);">Đăng Nhập Hệ Thống</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-custom text-center">
                        Đăng ký thành công! Chào mừng bạn đến với Ninh Chuẩn Hotel
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-custom text-center">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4 position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                            <input type="text" name="username" class="form-control form-control-auth" placeholder="Tên đăng nhập" required autofocus>
                        </div>
                    </div>

                    <div class="mb-5 position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" name="password" class="form-control form-control-auth" placeholder="Mật khẩu" required>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-login">
                            ĐĂNG NHẬP
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <small class="text-muted">
                        Chưa có tài khoản?
                        <a href="register.php" class="fw-bold" style="color: var(--primary); text-decoration: none;">
                            Đăng ký ngay
                        </a>
                    </small>
                </div>
            </div>
        </div>

        <!-- Cột phải: Gallery ảnh resort -->
        <div class="col-lg-6 text-center">
            <img src="../images/resort1.jpg" class="img-fluid gallery-img mb-4" alt="Ninh Chuẩn Hotel">
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <img src="../images/resort2.jpg" class="gallery-img" style="width:140px;height:140px;" alt="Phòng">
                <img src="../images/resort3.jpg" class="gallery-img" style="width:140px;height:140px;" alt="Bể bơi">
                <img src="../images/resort4.jpg" class="gallery-img" style="width:140px;height:140px;" alt="Spa">
                <img src="../images/resort5.jpg" class="gallery-img" style="width:140px;height:140px;" alt="Phòng ăn">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
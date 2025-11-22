<?php
require_once "../functions/db_functions.php";
$pdo = getPDO();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $password = md5($_POST['password']);

    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);

    if ($check->fetch()) {
        $message = "Tên đăng nhập đã tồn tại! Vui lòng chọn tên khác.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $password, $fullname]);
            header("Location: login.php?register=success");
            exit;
        } catch (Exception $e) {
            $message = "Có lỗi xảy ra. Vui lòng thử lại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký • Ninh Chuẩn Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/register.css?v=<?= time() ?>">
</head>
<body class="register-bg">

<div class="container register-wrapper">
    <div class="row align-items-center g-5">

        <!-- Form Đăng ký -->
        <div class="col-lg-6">
            <div class="brand-section">
                <img src="../images/logo.png" alt="Ninh Chuẩn Hotel" class="logo-img mb-3">
                <h1 class="brand-name">Ninh Chuẩn Hotel</h1>
                <p class="brand-subtitle">RESORT & SPA • LUXURY EXPERIENCE</p>
            </div>

            <div class="register-card">
                <h2 class="text-center mb-5 fw-bold" style="color:var(--primary);">Tạo Tài Khoản Mới</h2>

                <?php if ($message): ?>
                    <div class="alert alert-danger alert-custom text-center mb-4">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Họ và tên -->
                    <div class="mb-4 position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="fullname" class="form-control form-control-register" 
                                   placeholder="Họ và tên" required value="<?= $_POST['fullname'] ?? '' ?>">
                        </div>
                    </div>

                    <!-- Tên đăng nhập -->
                    <div class="mb-4 position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                            <input type="text" name="username" class="form-control form-control-register" 
                                   placeholder="Tên đăng nhập" required value="<?= $_POST['username'] ?? '' ?>">
                        </div>
                    </div>

                    <!-- Mật khẩu -->
                    <div class="mb-5 position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password" class="form-control form-control-register" 
                                   placeholder="Mật khẩu (tối thiểu 6 ký tự)" required minlength="6">
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-register">
                            ĐĂNG KÝ NGAY
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <small class="text-muted">
                        Đã có tài khoản? 
                        <a href="login.php" class="fw-bold" style="color:var(--primary); text-decoration:none;">
                            Đăng nhập tại đây
                        </a>
                    </small>
                </div>
            </div>
        </div>

        <!-- Gallery ảnh bên phải -->
        <div class="col-lg-6 text-center">
            <img src="../images/resort1.jpg" class="img-fluid gallery-img mb-4" alt="Ninh Chuẩn Hotel">
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <img src="../images/resort2.jpg" class="gallery-img" style="width:140px;height:140px;">
                <img src="../images/resort3.jpg" class="gallery-img" style="width:140px;height:140px;">
                <img src="../images/resort4.jpg" class="gallery-img" style="width:140px;height:140px;">
                <img src="../images/resort5.jpg" class="gallery-img" style="width:140px;height:140px;">
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
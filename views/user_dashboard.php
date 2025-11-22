<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trang chủ • Ninh Chuẩn Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/user_dashboard.css?v=<?= time() ?>">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="user_dashboard.php">NINH CHUẨN HOTEL</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="rooms.php">Xem phòng</a></li>
                    <li class="nav-item"><a class="nav-link" href="mybookings.php">Phòng đã đặt</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Xin chào, <?= htmlspecialchars($user['fullname'] ?? $user['full_name'] ?? 'Khách') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../handle/logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-content">
            <h1>Chào mừng quý khách</h1>
            <p>Trải nghiệm kỳ nghỉ dưỡng đẳng cấp 6 sao tại Ninh Chuẩn Hotel & Spa</p>
            <a href="rooms.php" class="btn btn-book-now">Đặt phòng ngay</a>
        </div>
    </section>

    <!-- Features – GỌN GÀNG TRONG 1 TRANG -->
    <section class="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Dịch vụ nổi bật</h2>
                <p class="lead">Chúng tôi mang đến trải nghiệm nghỉ dưỡng hoàn hảo</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-water feature-icon"></i>
                        <h3 class="feature-title">Hồ bơi vô cực</h3>
                        <p class="feature-desc">Tầm nhìn panoramic ra biển & thành phố</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-cup-hot-fill feature-icon"></i>
                        <h3 class="feature-title">Nhà hàng 5 sao</h3>
                        <p class="feature-desc">Ẩm thực Á - Âu từ đầu bếp hàng đầu</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-flower1 feature-icon"></i>
                        <h3 class="feature-title">Spa & Wellness</h3>
                        <p class="feature-desc">Liệu pháp thư giãn cao cấp từ thiên nhiên</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p class="mb-0">© 2025 Ninh Chuẩn Hotel & Spa – All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
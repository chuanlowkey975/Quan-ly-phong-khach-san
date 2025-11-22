<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");  // Đã sửa: chỉ cần login.php vì cùng thư mục views
    exit;
}
$userFullname = $_SESSION['user']['fullname'] ?? $_SESSION['user']['full_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin • Ninh Chuẩn Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=<?= time() ?>">
</head>
<body class="admin-bg">

    <!-- 70% CHỨC NĂNG -->
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <img src="../images/logo.png" alt="Logo" class="admin-logo">
            <h1 class="sidebar-title">NINH CHUẨN HOTEL</h1>
            <p class="welcome-name">Xin chào, <?= htmlspecialchars($userFullname) ?></p>
        </div>

        <div class="menu-grid">

            <a href="admin_rooms.php" class="menu-link">
                <div class="menu-item">
                    <i class="bi bi-building-fill-gear menu-icon"></i>
                    <div class="menu-title">Quản Lý Phòng</div>
                    <div class="menu-desc">Thêm, sửa, xóa & trạng thái</div>
                </div>
            </a>

            <a href="admin_customers.php" class="menu-link">
                <div class="menu-item">
                    <i class="bi bi-people-fill menu-icon"></i>
                    <div class="menu-title">Khách Hàng</div>
                    <div class="menu-desc">Thông tin khách đã đặt</div>
                </div>
            </a>

            <a href="admin_bookings.php" class="menu-link">
                <div class="menu-item">
                    <i class="bi bi-calendar-check-fill menu-icon"></i>
                    <div class="menu-title">Đặt Phòng</div>
                    <div class="menu-desc">Xác nhận & hủy đơn</div>
                </div>
            </a>

            <a href="admin_revenue.php" class="menu-link">
                <div class="menu-item">
                    <i class="bi bi-graph-up-arrow menu-icon"></i>
                    <div class="menu-title">Doanh Thu</div>
                    <div class="menu-desc">Thống kê & biểu đồ</div>
                </div>
            </a>

            <a href="../handle/logout.php" class="menu-link">
                <div class="menu-item logout">
                    <i class="bi bi-box-arrow-right menu-icon"></i>
                    <div class="menu-title">Đăng Xuất</div>
                    <div class="menu-desc">Thoát khỏi hệ thống</div>
                </div>
            </a>

        </div>
    </div>

    <!-- 30% ẢNH RESORT -->
    <div class="resort-photo">
        <img src="../images/resort1.jpg" alt="Ninh Chuẩn Hotel">
        <div class="resort-overlay">
            RESORT & SPA • LUXURY EXPERIENCE
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
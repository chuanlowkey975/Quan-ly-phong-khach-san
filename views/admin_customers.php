<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require '../functions/db_functions.php';
$pdo = getPDO();

// DÙNG CHÍNH XÁC CỘT booking_date CỦA BẠN → KHÔNG LỖI NỮA!
$customers = $pdo->query("
    SELECT 
        c.id,
        c.full_name,
        c.phone,
        MAX(b.booking_date) AS last_booking_date
    FROM customers c
    LEFT JOIN bookings b ON c.id = b.customer_id AND b.status != 'hủy'
    GROUP BY c.id, c.full_name, c.phone
    ORDER BY last_booking_date DESC, c.full_name ASC
")->fetchAll();

// Thống kê chính xác
$total_customers = count($customers);
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status != 'hủy'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Khách Hàng • Hotel Ninh Chuẩn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin_customers.css?v=<?= time() ?>">
</head>
<body>

<div class="header-bar">
    <h1>Quản Lý Khách Hàng</h1>
    <a href="admin_dashboard.php" class="btn-back">Quay lại Dashboard</a>
</div>

<div class="container-custom">

    <div class="page-title-section">
        <h2>Khách hàng thân thiết</h2>
        <div class="stats-box">
            <div class="stat">
                <div class="number"><?= $total_customers ?></div>
                <div class="label">Tổng khách</div>
            </div>
            <div class="stat">
                <div class="number"><?= $total_bookings ?></div>
                <div class="label">Đã đặt phòng</div>
            </div>
        </div>
    </div>

    <?php if (empty($customers)): ?>
        <div class="empty-state">
            <i class="bi bi-person-slash" style="font-size: 90px; color: #a5d6a7;"></i>
            <h3>Chưa có khách hàng nào</h3>
            <p class="text-muted">Khi có khách đặt phòng, thông tin sẽ hiển thị tại đây</p>
        </div>
    <?php else: ?>
        <div class="customers-grid">
            <?php foreach ($customers as $c): 
                $hasBooking = $c['last_booking_date'] !== null;
                $date = $hasBooking ? date('d/m/Y', strtotime($c['last_booking_date'])) : '';
                $time = $hasBooking ? date('H:i', strtotime($c['last_booking_date'])) : '';
            ?>
                <div class="customer-card">
                    <div class="avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>

                    <div class="info">
                        <h3 class="name"><?= htmlspecialchars($c['full_name']) ?></h3>
                        <div class="phone">
                            <i class="bi bi-telephone"></i>
                            <?= htmlspecialchars($c['phone']) ?>
                        </div>
                    </div>

                    <?php if ($hasBooking): ?>
                        <div class="last-booking">
                            <i class="bi bi-calendar3"></i>
                            <span class="date"><?= $date ?></span>
                            <span class="time"><?= $time ?></span>
                        </div>
                    <?php else: ?>
                        <div class="last-booking no-booking">
                            Chưa đặt phòng
                        </div>
                    <?php endif; ?>

                    <div class="footer">
                        #<?= sprintf('%04d', $c['id']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
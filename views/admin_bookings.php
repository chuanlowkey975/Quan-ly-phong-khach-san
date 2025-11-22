<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php"); 
    exit;
}
require '../functions/db_functions.php';
$pdo = getPDO();

// === XỬ LÝ HÀNH ĐỘNG ADMIN ===
if (isset($_GET['confirm'])) {
    $id = (int)$_GET['confirm'];
    $pdo->beginTransaction();
    try {
        $pdo->prepare("
            UPDATE bookings b 
            JOIN rooms r ON b.room_id = r.id 
            SET b.status = 'đã thanh toán', r.status = 'đã đặt' 
            WHERE b.id = ? AND b.status = 'pending'
        ")->execute([$id]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

if (isset($_GET['cancel'])) {
    $id = (int)$_GET['cancel'];
    $pdo->beginTransaction();
    try {
        $booking = $pdo->query("SELECT room_id, status FROM bookings WHERE id = $id")->fetch();
        $pdo->prepare("UPDATE bookings SET status = 'hủy' WHERE id = ?")->execute([$id]);
        if ($booking && $booking['status'] !== 'đã thanh toán') {
            $pdo->prepare("UPDATE rooms SET status = 'trống' WHERE id = ?")->execute([$booking['room_id']]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

// Lấy danh sách đặt phòng
$bookings = $pdo->query("
    SELECT b.*, r.room_number, r.type, c.full_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN customers c ON b.customer_id = c.id
    ORDER BY b.id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý đặt phòng • Ninh Chuẩn Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_bookings.css?v=<?= time() ?>">
</head>
<body class="bookings-bg">

<div class="container-fluid bookings-container">
    <div class="bookings-header">
        <h1 class="bookings-title">Quản Lý Đặt Phòng</h1>
        <a href="admin_dashboard.php" class="btn btn-back">Quay lại Dashboard</a>
    </div>

    <div class="card-bookings">
        <div class="card-header-bookings">
            <h4>Danh sách đơn đặt phòng</h4>
        </div>

        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <h3>Chưa có đơn đặt phòng nào</h3>
                    <p class="text-muted">Khi khách đặt phòng, thông tin sẽ hiển thị tại đây</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bookings table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="90">Mã</th>
                                <th>Khách hàng</th>
                                <th>Phòng</th>
                                <th>Nhận - Trả phòng</th>
                                <th>Tổng tiền</th>
                                <th width="150">Trạng thái</th>
                                <th width="280">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><span class="booking-id">#<?= sprintf('%04d', $b['id']) ?></span></td>
                                <td class="customer-name"><?= htmlspecialchars($b['full_name']) ?></td>
                                <td class="room-info"><?= $b['room_number'] ?> - <?= $b['type'] ?></td>
                                <td class="date-range">
                                    <?= date('d/m/Y', strtotime($b['check_in'])) ?>
                                    <i class="bi bi-arrow-right mx-2"></i>
                                    <?= date('d/m/Y', strtotime($b['check_out'])) ?>
                                </td>
                                <td class="total-price"><?= number_format($b['total_price']) ?>đ</td>
                                <td class="text-center">
                                    <?php if ($b['status'] == 'pending'): ?>
                                        <span class="status-badge status-pending">Chờ thanh toán</span>
                                    <?php elseif ($b['status'] == 'đã thanh toán'): ?>
                                        <span class="status-badge status-paid">Đã thanh toán</span>
                                    <?php elseif ($b['status'] == 'hủy'): ?>
                                        <span class="status-badge status-cancelled">Đã hủy</span>
                                    <?php else: ?>
                                        <span class="status-badge bg-secondary"><?= htmlspecialchars($b['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <?php if ($b['status'] == 'pending'): ?>
                                            <a href="?confirm=<?= $b['id'] ?>" class="btn btn-confirm btn-sm"
                                               onclick="return confirm('Xác nhận khách đã THANH TOÁN và giữ phòng?')">
                                                Xác nhận thanh toán
                                            </a>
                                            <a href="?cancel=<?= $b['id'] ?>" class="btn btn-cancel btn-sm"
                                               onclick="return confirm('HỦY đơn đặt phòng này?')">
                                                Hủy đơn
                                            </a>
                                        <?php elseif ($b['status'] == 'đã thanh toán'): ?>
                                            <span class="status-badge status-paid">Đã xác nhận</span>
                                        <?php elseif ($b['status'] == 'hủy'): ?>
                                            <span class="status-badge status-cancelled">Đã hủy</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
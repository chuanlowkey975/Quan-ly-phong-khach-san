<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php"); 
    exit;
}
require '../functions/db_functions.php';
$pdo = getPDO();
$user_id = $_SESSION['user']['id'];

// === THANH TOÁN ===
if (isset($_GET['pay']) && is_numeric($_GET['pay'])) {
    $booking_id = (int)$_GET['pay'];
    $stmt = $pdo->prepare("
        UPDATE bookings b 
        JOIN rooms r ON b.room_id = r.id 
        SET b.status = 'đã thanh toán', r.status = 'đã đặt' 
        WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'
    ");
    $stmt->execute([$booking_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['msg'] = ['type' => 'success', 'text' => 'Thanh toán thành công! Chúc quý khách có kỳ nghỉ vui vẻ!'];
    } else {
        $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Không thể thanh toán! Đơn đã được xử lý hoặc không tồn tại.'];
    }
    header("Location: mybookings.php"); 
    exit;
}

// === HỦY ĐẶT PHÒNG ===
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $booking_id = (int)$_GET['cancel'];
    $stmt = $pdo->prepare("
        SELECT b.*, r.id AS room_id 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        WHERE b.id = ? AND b.user_id = ? AND b.status IN ('pending', 'đã đặt')
    ");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();

    if ($booking && strtotime($booking['check_in']) > time()) {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE bookings SET status = 'hủy' WHERE id = ?")->execute([$booking_id]);
        if ($booking['status'] != 'đã thanh toán') {
            $pdo->prepare("UPDATE rooms SET status = 'trống' WHERE id = ?")->execute([$booking['room_id']]);
        }
        $pdo->commit();
        $_SESSION['msg'] = ['type' => 'success', 'text' => 'Hủy đặt phòng thành công!'];
    } else {
        $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Không thể hủy! Đã qua ngày nhận phòng hoặc đơn không hợp lệ.'];
    }
    header("Location: mybookings.php"); 
    exit;
}

// Lấy danh sách booking
$order = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'created_at'")->rowCount() > 0 ? "b.created_at DESC" : "b.id DESC";
$stmt = $pdo->prepare("
    SELECT b.*, r.room_number, r.type, r.image, r.price_per_night,
           DATEDIFF(b.check_out, b.check_in) AS nights
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.user_id = ? 
    ORDER BY $order
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phòng đã đặt - Ninh Chuẩn Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/mybookings.css?v=<?= time() ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand" href="user_dashboard.php">NINH CHUẨN</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="rooms.php">Xem phòng</a></li>
                <li class="nav-item"><a class="nav-link active text-warning fw-bold" href="mybookings.php">Phòng đã đặt</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <?= htmlspecialchars($_SESSION['user']['fullname'] ?? 'Khách') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../handle/logout.php">Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <h1 class="text-center my-5 fw-bold display-5">PHÒNG ĐÃ ĐẶT</h1>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-<?= $_SESSION['msg']['type'] ?> text-center mb-4">
            <strong><?= $_SESSION['msg']['text'] ?></strong>
        </div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
        <div class="text-center py-5">
            <h3 class="text-muted">Bạn chưa đặt phòng nào</h3>
            <a href="rooms.php" class="btn btn-warning btn-lg px-5 mt-3">Đặt phòng ngay</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($bookings as $b): ?>
                <div class="col">
                    <div class="booking-card">
                        <img src="../images/<?= htmlspecialchars($b['image'] ?? 'default.jpg') ?>" class="w-100 room-img" 
                             onerror="this.src='../images/default.jpg'" alt="Phòng">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold">
                                    <?= htmlspecialchars($b['room_number']) ?> - <?= htmlspecialchars($b['type']) ?>
                                </h5>
                                <?php if ($b['status'] == 'pending'): ?>
                                    <span class="status-badge" style="background: var(--warning);">Chờ thanh toán</span>
                                <?php elseif ($b['status'] == 'đã thanh toán'): ?>
                                    <span class="status-badge" style="background: var(--success);">Đã thanh toán</span>
                                <?php elseif ($b['status'] == 'hủy'): ?>
                                    <span class="status-badge" style="background: var(--danger);">Đã hủy</span>
                                <?php endif; ?>
                            </div>

                            <div class="text-muted small mb-3">
                                <div>Nhận: <strong><?= date('d/m/Y', strtotime($b['check_in'])) ?></strong></div>
                                <div>Trả: <strong><?= date('d/m/Y', strtotime($b['check_out'])) ?></strong></div>
                                <div>Số đêm: <strong><?= $b['nights'] ?></strong></div>
                            </div>

                            <div class="text-success fw-bold fs-4 mb-3">
                                Tổng: <?= number_format($b['total_price']) ?>đ
                            </div>

                            <div class="text-center text-muted small mb-4">
                                Mã đặt: <strong>#<?= sprintf('%04d', $b['id']) ?></strong>
                            </div>

                            <!-- NÚT CHỨC NĂNG -->
                            <?php if ($b['status'] == 'pending'): ?>
                                <a href="mybookings.php?pay=<?= $b['id'] ?>" 
                                   class="btn btn-pay w-100 mb-3 d-block"
                                   onclick="return confirm('Xác nhận THANH TOÁN phòng này?')">
                                    THANH TOÁN NGAY
                                </a>
                                <?php if (strtotime($b['check_in']) > time()): ?>
                                    <a href="mybookings.php?cancel=<?= $b['id'] ?>" 
                                       class="btn btn-cancel w-100 d-block"
                                       onclick="return confirm('Bạn chắc chắn muốn HỦY đặt phòng này?')">
                                        HỦY ĐẶT PHÒNG
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>Đã qua ngày nhận</button>
                                <?php endif; ?>

                            <?php elseif ($b['status'] == 'đã thanh toán'): ?>
                                <button class="btn btn-pay w-100" disabled>ĐÃ THANH TOÁN</button>
                            <?php elseif ($b['status'] == 'hủy'): ?>
                                <button class="btn btn-cancel w-100" disabled>ĐÃ HỦY</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
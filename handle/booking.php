<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}
require '../functions/db_functions.php';
$pdo = getPDO();
header('Content-Type: application/json');

$room_id     = $_POST['room_id'] ?? 0;
$check_in    = $_POST['check_in'] ?? '';
$check_out   = $_POST['check_out'] ?? '';
$total_price = $_POST['total_price'] ?? 0;
$full_name   = trim($_POST['fullname'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$id_card     = trim($_POST['id_card'] ?? '');
$user_id     = $_SESSION['user']['id'];

if (!$room_id || !$check_in || !$check_out || $full_name === '' || $phone === '' || $total_price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
    exit;
}
if (strtotime($check_in) >= strtotime($check_out)) {
    echo json_encode(['success' => false, 'message' => 'Ngày trả phòng phải sau ngày nhận phòng!']);
    exit;
}

// Kiểm tra trùng ngày
$stmt = $pdo->prepare("SELECT id FROM bookings WHERE room_id = ? AND status != 'hủy' AND (check_in < ? AND check_out > ?)");
$stmt->execute([$room_id, $check_out, $check_in]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Phòng đã được đặt trong khoảng thời gian này!']);
    exit;
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO customers (full_name, phone, id_card, address) VALUES (?, ?, ?, 'Không có thông tin')");
    $stmt->execute([$full_name, $phone, $id_card]);
    $customer_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO bookings (room_id, customer_id, user_id, check_in, check_out, total_price, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$room_id, $customer_id, $user_id, $check_in, $check_out, $total_price]);
    $booking_id = $pdo->lastInsertId();

    $pdo->prepare("UPDATE rooms SET status = 'đang chờ' WHERE id = ? AND status = 'trống'")->execute([$room_id]);
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Đặt phòng thành công! Mã đặt: <strong>#".sprintf('%04d',$booking_id)."</strong><br>Vui lòng thanh toán trong 15 phút!"
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại!']);
}
exit;
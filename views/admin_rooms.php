<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once '../functions/db_functions.php';
$pdo = getPDO();

/* ==================== XỬ LÝ AJAX ==================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    // Lấy danh sách loại phòng từ ENUM trong DB
    $enumResult = $pdo->query("
        SELECT COLUMN_TYPE 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'rooms' AND COLUMN_NAME = 'type'
    ")->fetchColumn();

    $roomTypes = [];
    if ($enumResult && preg_match("/^enum\((.*)\)$/", $enumResult, $matches)) {
        $roomTypes = array_map(function($v) { return trim($v, "'"); }, explode(',', $matches[1]));
    }

    /* ---- THÊM / SỬA PHÒNG ---- */
    if ($action === 'save_room') {
        $id              = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $room_number     = trim($_POST['room_number']);
        $type            = trim($_POST['type']);
        $price_per_night = (int)preg_replace('/\D/', '', $_POST['price_per_night']);
        $capacity        = (int)$_POST['capacity'];
        $area            = trim($_POST['area']);
        $amenities       = trim($_POST['amenities']);
        $description     = trim($_POST['description']);
        $status          = $_POST['status'] ?? 'trống';

        if (!in_array($type, $roomTypes)) {
            echo json_encode(['success' => false, 'message' => 'Loại phòng không hợp lệ!']);
            exit;
        }

        // Xử lý ảnh
        $image = $_POST['old_image'] ?? 'default.jpg';
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5*1024*1024) {
                $image = 'room_'.time().'_'.rand(100,999).'.'.$ext;
                move_uploaded_file($_FILES['image']['tmp_name'], "../images/$image");
                if ($_POST['old_image'] && $_POST['old_image'] !== 'default.jpg' && file_exists("../images/{$_POST['old_image']}")) {
                    @unlink("../images/{$_POST['old_image']}");
                }
            }
        }

        try {
            if (!$id) {
                $sql = "INSERT INTO rooms (room_number, type, price_per_night, image, capacity, area, amenities, description, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql)->execute([$room_number, $type, $price_per_night, $image, $capacity, $area, $amenities, $description, $status]);
                $msg = "Thêm phòng thành công!";
            } else {
                $sql = "UPDATE rooms SET room_number=?, type=?, price_per_night=?, image=?, capacity=?, area=?, amenities=?, description=?, status=? WHERE id=?";
                $pdo->prepare($sql)->execute([$room_number, $type, $price_per_night, $image, $capacity, $area, $amenities, $description, $status, $id]);
                $msg = "Cập nhật thành công!";
            }
            echo json_encode(['success' => true, 'message' => $msg]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: '.$e->getMessage()]);
        }
        exit;
    }

    /* ---- XÓA PHÒNG ---- */
    if ($action === 'delete' && !empty($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $room = $pdo->query("SELECT image FROM rooms WHERE id = $id")->fetch();
        $pdo->prepare("DELETE FROM rooms WHERE id = ?")->execute([$id]);
        if ($room && $room['image'] && $room['image'] !== 'default.jpg' && file_exists("../images/{$room['image']}")) {
            @unlink("../images/{$room['image']}");
        }
        echo json_encode(['success' => true, 'message' => 'Xóa phòng thành công!']);
        exit;
    }
}

/* ==================== LẤY DANH SÁCH PHÒNG ==================== */
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit  = 12;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("SELECT * FROM rooms ORDER BY LPAD(room_number, 10, '0') ASC LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
$rooms = $stmt->fetchAll();

$total       = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$total_pages = ceil($total / $limit);

// Lấy ENUM loại phòng (dùng lại cho phần HTML)
$enumResult = $pdo->query("
    SELECT COLUMN_TYPE 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'rooms' AND COLUMN_NAME = 'type'
")->fetchColumn();

$roomTypes = [];
if ($enumResult && preg_match("/^enum\((.*)\)$/", $enumResult, $matches)) {
    $roomTypes = array_map(function($v) { return trim($v, "'"); }, explode(',', $matches[1]));
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý phòng • Ninh Chuẩn Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_rooms.css?v=<?= time() ?>">
</head>
<body>

<div class="header">
    <h1>Quản Lý Phòng</h1>
    <a href="admin_dashboard.php" class="btn-back">Quay lại Dashboard</a>
</div>

<div class="container-custom">
    <div class="top-bar">
        <h2>Danh sách phòng khách sạn <span class="total">(Tổng: <?= $total ?> phòng)</span></h2>
        <button class="btn-add-room" data-bs-toggle="modal" data-bs-target="#roomModal" onclick="openAddModal()">
            Thêm phòng mới
        </button>
    </div>

    <!-- Thông báo AJAX -->
    <div id="alertContainer"></div>

    <?php if ($total == 0): ?>
        <div class="empty-state text-center py-5">
            <i class="bi bi-building-slash" style="font-size: 90px; color: #a5d6a7;"></i>
            <h3 class="mt-4">Chưa có phòng nào</h3>
            <button class="btn-add-room mt-3" data-bs-toggle="modal" data-bs-target="#roomModal" onclick="openAddModal()">Thêm phòng ngay</button>
        </div>
    <?php else: ?>
        <div class="rooms-grid" id="roomsGrid">
            <?php foreach ($rooms as $room):
                $isAvailable = ($room['status'] ?? 'trống') === 'trống';
                $image = !empty($room['image']) && file_exists("../images/{$room['image']}") ? "../images/{$room['image']}" : "../images/default.jpg";
            ?>
                <div class="room-card" data-id="<?= $room['id'] ?>">
                    <div class="room-image">
                        <img src="<?= $image ?>" alt="Phòng <?= $room['room_number'] ?>">
                        <div class="status-badge <?= $isAvailable ? 'available' : 'booked' ?>">
                            <?= $isAvailable ? 'Trống' : 'Đã đặt' ?>
                        </div>
                        <div class="room-number-overlay">#<?= $room['room_number'] ?></div>
                    </div>
                    <div class="room-info">
                        <h3><?= htmlspecialchars($room['type']) ?></h3>
                        <div class="price"><?= number_format($room['price_per_night']) ?>đ <small>/đêm</small></div>
                        <div class="details">
                            <span>Người <?= $room['capacity'] ?? 2 ?> người</span>
                            <span>Diện tích <?= $room['area'] ?? '28m²' ?></span>
                        </div>
                    </div>
                    <div class="room-actions">
                        <button class="btn-edit" onclick='openEditModal(<?= json_encode($room) ?>)'>
                            Chỉnh sửa
                        </button>
                        <button class="btn-delete" onclick="openDeleteModal(<?= $room['id'] ?>, '<?= htmlspecialchars($room['room_number']) ?>')">
                            Xóa phòng
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination-wrapper">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal Thêm / Sửa -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="roomModalTitle">Thêm phòng mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="roomForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="save_room">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="old_image" id="old_image">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Số phòng</label>
                            <input type="text" name="room_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Loại phòng</label>
                            <select name="type" class="form-select" required>
                                <?php foreach ($roomTypes as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Giá / đêm (VNĐ)</label>
                            <input type="text" name="price_per_night" class="form-control" placeholder="1.500.000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Sức chứa</label>
                            <input type="number" name="capacity" class="form-control" min="1" value="2" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Diện tích</label>
                            <input type="text" name="area" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Tiện nghi</label>
                            <textarea name="amenities" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="trống">Trống</option>
                                <option value="đã đặt">Đã đặt</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Ảnh phòng</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-5">
                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 80px;"></i>
                <h4 class="mt-4">Xóa vĩnh viễn?</h4>
                <p>Phòng <strong><span id="delRoomNum"></span></strong> sẽ bị xóa hoàn toàn!</p>
            </div>
            <form id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="delete_id" id="delId">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa vĩnh viễn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const alertContainer = document.getElementById('alertContainer');

function showAlert(message, type = 'success') {
    const alert = `<div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top:20px;right:20px;z-index:9999;">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
    alertContainer.innerHTML = alert;
    setTimeout(() => alertContainer.innerHTML = '', 4000);
}

function openAddModal() {
    document.getElementById('roomModalTitle').textContent = 'Thêm phòng mới';
    document.getElementById('roomForm').reset();
    document.getElementById('edit_id').value = '';
    document.getElementById('old_image').value = '';
}

function openEditModal(room) {
    document.getElementById('roomModalTitle').textContent = 'Sửa phòng #' + room.room_number;
    document.getElementById('edit_id').value = room.id;
    document.getElementById('old_image').value = room.image || 'default.jpg';

    document.querySelector('[name="room_number"]').value = room.room_number;
    document.querySelector('[name="type"]').value = room.type;
    document.querySelector('[name="price_per_night"]').value = room.price_per_night.toLocaleString('vi-VN');
    document.querySelector('[name="capacity"]').value = room.capacity;
    document.querySelector('[name="area"]').value = room.area;
    document.querySelector('[name="amenities"]').value = room.amenities || '';
    document.querySelector('[name="description"]').value = room.description || '';
    document.querySelector('[name="status"]').value = room.status || 'trống';

    new bootstrap.Modal(document.getElementById('roomModal')).show();
}

function openDeleteModal(id, number) {
    document.getElementById('delId').value = id;
    document.getElementById('delRoomNum').textContent = number;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// AJAX Submit form
document.getElementById('roomForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            showAlert(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('roomModal')).hide();
                setTimeout(() => location.reload(), 1000);
            }
        });
});

// AJAX Delete
document.getElementById('deleteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            showAlert(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                setTimeout(() => location.reload(), 1000);
            }
        });
});
</script>
</body>
</html>
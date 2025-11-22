<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit;
}
require '../functions/db_functions.php';
$pdo = getPDO();
$stmt = $pdo->query("SELECT * FROM rooms WHERE status = 'trống' ORDER BY room_number");
$rooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Danh sách phòng - Luxury Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/rooms.css?v=<?= time() ?>">
    <style>
        .btn-book { background: #28a745 !important; border: none !important; color: white !important; }
        .btn-book:hover { background: #218838 !important; }
        .flatpickr-calendar.inline { width: 100% !important; box-shadow: none !important; }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange { background: #28a745 !important; border-color: #28a745 !important; }
        .room-thumb { width: 90px; height: 70px; object-fit: cover; border-radius: 16px; border: 4px solid white; box-shadow: 0 8px 20px rgba(0,0,0,0.2); }
        .total-price-box { min-height: 90px; display: flex; flex-direction: column; justify-content: center; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="user_dashboard.php">LUXURY HOTEL</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link active fw-bold" href="rooms.php">Xem phòng</a></li>
                <li class="nav-item"><a class="nav-link" href="mybookings.php">Phòng đã đặt</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <?= htmlspecialchars($_SESSION['user']['fullname']) ?>
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
<div class="bg-dark text-white py-5 text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Khám Phá Các Loại Phòng Sang Trọng</h1>
        <p class="lead">Chọn không gian hoàn hảo cho kỳ nghỉ của bạn</p>
    </div>
</div>

<!-- Danh sách phòng -->
<div class="container my-5">
    <?php if (empty($rooms)): ?>
        <div class="alert alert-warning text-center fs-3">
            Hiện tại không còn phòng trống. Vui lòng quay lại sau!
        </div>
    <?php else: ?>
        <div class="row g-5">
            <?php foreach ($rooms as $room): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card room-card h-100 shadow-lg">
                        <div class="position-relative overflow-hidden">
                            <img src="../images/<?= htmlspecialchars($room['image'] ?? 'default.jpg') ?>"
                                 class="room-img w-100" loading="lazy" onerror="this.src='../images/default.jpg'" alt="<?= $room['room_number'] ?>">
                            <div class="price-badge">
                                <?= number_format($room['price_per_night']) ?>đ
                                <small class="d-block">/đêm</small>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title text-center fw-bold text-dark">
                                <?= $room['room_number'] ?> - <?= $room['type'] ?>
                            </h5>
                            <div class="text-center text-muted small mb-3">
                                <i class="bi bi-people-fill text-primary"></i> <?= $room['capacity'] ?? 2 ?> người
                                • <i class="bi bi-rulers"></i> <?= $room['area'] ?? '30m²' ?>
                            </div>
                            <p class="text-muted small flex-grow-1">
                                <?= htmlspecialchars(mb_substr($room['description'] ?? '', 0, 110)) ?>...
                            </p>

                            <!-- 2 NÚT XẾP NGANG -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="button" class="btn btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#roomModal<?= $room['id'] ?>">
                                    Xem chi tiết
                                </button>
                                <button type="button" class="btn btn-book flex-fill" onclick='openBookingModal(<?= json_encode($room) ?>)'>
                                    Đặt ngay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal chi tiết -->
                <div class="modal fade" id="roomModal<?= $room['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-dark text-white">
                                <h4 class="modal-title">Phòng <?= $room['room_number'] ?> - <?= $room['type'] ?></h4>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <img src="../images/<?= htmlspecialchars($room['image'] ?? 'default.jpg') ?>" class="img-fluid rounded shadow" alt="">
                                    </div>
                                    <div class="col-md-6">
                                        <h4 class="text-primary fw-bold mb-3"><?= number_format($room['price_per_night']) ?>đ / đêm</h4>
                                        <ul class="list-unstyled text-muted mb-4">
                                            <li><strong>Sức chứa:</strong> <?= $room['capacity'] ?? 2 ?> người</li>
                                            <li><strong>Diện tích:</strong> <?= $room['area'] ?? '30m²' ?></li>
                                        </ul>
                                        <h6 class="fw-bold mb-2">Tiện nghi</h6>
                                        <div class="bg-light p-3 rounded small"><?= nl2br(htmlspecialchars($room['amenities'] ?? '')) ?></div>
                                        <h6 class="fw-bold mt-4 mb-2">Mô tả</h6>
                                        <p class="small text-muted"><?= nl2br(htmlspecialchars($room['description'] ?? '')) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-book btn-lg px-5" onclick='openBookingModal(<?= json_encode($room) ?>)'>Đặt ngay</button>
                                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Đặt Phòng -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Đặt phòng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bookingForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="room_id" id="roomId">
                    <input type="hidden" name="total_price" id="totalPriceInput">

                    <div class="bg-light border border-success rounded-4 p-4 mb-4">
                        <div class="d-flex align-items-center gap-4">
                            <img id="roomImg" src="" class="room-thumb" alt="">
                            <div>
                                <h5 id="roomTitle" class="mb-1 fw-bold text-success"></h5>
                                <p id="roomDesc" class="small text-muted mb-1"></p>
                                <p id="roomPrice" class="fw-bold text-success mb-0"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Khu vực hiện thông báo -->
                    <div id="alertPlaceholder" class="mb-4"></div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><input type="text" name="fullname" class="form-control" placeholder="Họ và tên *" required></div>
                        <div class="col-md-6"><input type="text" name="phone" class="form-control" placeholder="Số điện thoại *" required></div>
                    </div>
                    <div class="mb-3"><input type="text" name="id_card" class="form-control" placeholder="CMND/CCCD (không bắt buộc)"></div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><input type="text" name="check_in" id="checkin" class="form-control" readonly required placeholder="Ngày nhận phòng"></div>
                        <div class="col-md-6"><input type="text" name="check_out" id="checkout" class="form-control" readonly required placeholder="Ngày trả phòng"></div>
                    </div>

                    <div class="text-center p-4 bg-white rounded-4 shadow-sm border mb-4">
                        <div id="inlineCalendar"></div>
                    </div>

                    <div class="total-price-box bg-success text-white rounded-4 p-4 text-center mb-4">
                        <strong>Chọn ngày để xem tổng tiền</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-lg px-5" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success btn-lg px-5">Xác nhận đặt phòng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    let currentRoom = null;
    let fp = null;

    function openBookingModal(room) {
        currentRoom = room;
        document.getElementById('roomId').value = room.id;
        document.getElementById('roomTitle').textContent = `Phòng ${room.room_number} - ${room.type}`;
        document.getElementById('roomDesc').textContent = (room.description || '').substring(0, 80) + '...';
        document.getElementById('roomPrice').textContent = `${parseInt(room.price_per_night).toLocaleString()}đ / đêm`;
        document.getElementById('roomImg').src = `../images/${room.image || 'default.jpg'}`;

        document.getElementById('bookingForm').reset();
        document.getElementById('alertPlaceholder').innerHTML = '';
        document.querySelector('.total-price-box').innerHTML = '<strong class="text-white">Chọn ngày để xem tổng tiền</strong>';

        const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
        modal.show();

        setTimeout(() => {
            if (fp) fp.destroy();
            fp = flatpickr("#inlineCalendar", {
                inline: true,
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: "today",
                onChange: function(selectedDates) {
                    if (selectedDates.length === 2) {
                        document.getElementById('checkin').value = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                        document.getElementById('checkout').value = flatpickr.formatDate(selectedDates[1], "Y-m-d");
                        calculateTotal();
                    }
                }
            });
        }, 400);
    }

    function calculateTotal() {
        const checkin = document.getElementById('checkin').value;
        const checkout = document.getElementById('checkout').value;
        if (!checkin || !checkout || !currentRoom) return;

        const date1 = new Date(checkin);
        const date2 = new Date(checkout);
        if (date2 <= date1) {
            document.querySelector('.total-price-box').innerHTML = '<strong class="bg-white text-danger rounded px-4 py-2">Ngày trả phải sau ngày nhận!</strong>';
            return;
        }

        const nights = Math.ceil((date2 - date1) / 86400000);
        const price = parseInt(currentRoom.price_per_night);
        const total = price * nights;
        document.getElementById('totalPriceInput').value = total;

        document.querySelector('.total-price-box').innerHTML = `
            <div class="d-flex justify-content-between px-3">
                <span class="fs-5">${nights} đêm × ${price.toLocaleString()}đ</span>
                <strong class="fs-3">${total.toLocaleString()}đ</strong>
            </div>
            <small class="opacity-75">Chưa bao gồm thuế & phí</small>
        `;
    }

    // AJAX - Xử lý đặt phòng + thông báo trùng ngày
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const alertDiv = document.getElementById('alertPlaceholder');

        fetch('../handle/booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alertDiv.innerHTML = `
                    <div class="alert alert-success text-center">
                        <strong>${data.message}</strong>
                    </div>`;
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
                    setTimeout(() => location.href = 'mybookings.php', 600);
                }, 3000);
            } else {
                alertDiv.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <strong>${data.message}</strong>
                    </div>`;
            }
        })
        .catch(() => {
            alertDiv.innerHTML = `<div class="alert alert-danger text-center"><strong>Lỗi kết nối, vui lòng thử lại!</strong></div>`;
        });
    });
</script>
</body>
</html>
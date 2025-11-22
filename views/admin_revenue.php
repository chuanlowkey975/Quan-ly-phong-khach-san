<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php"); 
    exit;
}
require '../functions/db_functions.php';
$pdo = getPDO();

$year = date('Y');
$currentMonth = date('m');

// 1. Doanh thu tháng này
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM bookings WHERE YEAR(check_in)=? AND MONTH(check_in)=? AND status='đã thanh toán'");
$stmt->execute([$year, $currentMonth]);
$monthlyRevenue = (int)$stmt->fetchColumn();

// 2. Tổng phòng, khách hàng, tỷ lệ lấp đầy
$totalRooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$activeCustomers = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM bookings WHERE YEAR(check_in)=$year AND status='đã thanh toán'")->fetchColumn();
$paidBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE YEAR(check_in)=$year AND status='đã thanh toán'")->fetchColumn();
$occupancyRate = $totalRooms > 0 ? round(($paidBookings / $totalRooms) * 100) : 0;

// 3. Doanh thu theo loại phòng (biểu đồ tròn)
$revenueByType = $pdo->query("
    SELECT r.type, COALESCE(SUM(b.total_price),0) as revenue
    FROM rooms r
    LEFT JOIN bookings b ON r.id = b.room_id AND YEAR(b.check_in)=$year AND b.status='đã thanh toán'
    GROUP BY r.type ORDER BY revenue DESC
")->fetchAll();

// 4. Doanh thu từng tháng (biểu đồ đường)
$revenueByMonth = [];
for ($m=1; $m<=12; $m++) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM bookings WHERE YEAR(check_in)=? AND MONTH(check_in)=? AND status='đã thanh toán'");
    $stmt->execute([$year, $m]);
    $revenueByMonth[] = (int)$stmt->fetchColumn();
}
$totalYearRevenue = array_sum($revenueByMonth);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Doanh Thu • Hotel Ninh Chuẩn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/admin_revenue.css?v=<?= time() ?>">
    <style>
        /* CARD MINI SIÊU ĐẸP – DÙNG CHUNG CHO 4 CARD */
        .revenue-mini-card {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            border-radius: 28px;
            padding: 24px 20px;
            text-align: center;
            box-shadow: 
                0 15px 35px rgba(86,171,47,0.25),
                0 5px 15px rgba(0,0,0,0.1),
                inset 0 1px 0 rgba(255,255,255,0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
        }
        .revenue-mini-card::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.15) 50%, transparent 70%);
            transform: translateX(-100%) rotate(30deg); transition: 0.8s;
        }
        .revenue-mini-card:hover::before { transform: translateX(100%) rotate(30deg); }
        .revenue-mini-card:hover { transform: translateY(-8px) scale(1.03); box-shadow: 0 25px 50px rgba(86,171,47,0.35); }

        .revenue-mini-header { font-size: 1.05rem; font-weight: 600; opacity: 0.95; margin-bottom: 8px; letter-spacing: 0.5px; }
        .revenue-mini-amount { font-size: 2.8rem; font-weight: 900; line-height: 1; color: #fff; text-shadow: 0 4px 12px rgba(0,0,0,0.3); margin: 8px 0; }
        .revenue-mini-currency { font-size: 2rem; font-weight: 800; color: #ffeb3b; text-shadow: 0 3px 10px rgba(0,0,0,0.4); letter-spacing: 1px; }

        @media (max-width: 768px) {
            .revenue-mini-amount { font-size: 2.4rem; }
            .revenue-mini-currency { font-size: 1.6rem; }
        }
    </style>
</head>
<body class="revenue-bg">

<div class="revenue-container">

    <!-- Header -->
    <div class="revenue-header">
        <div>
            <h1 class="revenue-title">Dashboard Doanh Thu</h1>
            <p class="mb-0 text-muted">Tổng quan & thống kê doanh thu theo dữ liệu thực tế</p>
        </div>
        <div class="text-end">
            <div class="text-muted small">Năm: <strong><?= $year ?></strong></div>
            <div class="fs-4 fw-bold text-success">Tổng doanh thu: <?= number_format($totalYearRevenue) ?> VNĐ</div>
            <a href="admin_dashboard.php" class="btn btn-back mt-3">Quay lại Dashboard</a>
        </div>
    </div>

    <!-- 4 CARD MINI SIÊU ĐẸP -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-6">
            <div class="revenue-mini-card">
                <div class="revenue-mini-header">Doanh thu tháng này</div>
                <div class="revenue-mini-amount"><?= number_format($monthlyRevenue) ?></div>
                <div class="revenue-mini-currency">VNĐ</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="revenue-mini-card" style="background: linear-gradient(135deg, #2193b0, #6dd5ed);">
                <div class="revenue-mini-header">Tổng số phòng</div>
                <div class="revenue-mini-amount"><?= $totalRooms ?></div>
                <div class="revenue-mini-currency">phòng</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="revenue-mini-card" style="background: linear-gradient(135deg, #ff7e5f, #feb47b);">
                <div class="revenue-mini-header">Khách hàng</div>
                <div class="revenue-mini-amount"><?= $activeCustomers ?></div>
                <div class="revenue-mini-currency">người</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="revenue-mini-card" style="background: linear-gradient(135deg, #8e2de2, #4a00e0);">
                <div class="revenue-mini-header">Tỷ lệ lấp đầy</div>
                <div class="revenue-mini-amount"><?= $occupancyRate ?>%</div>
                <div class="revenue-mini-currency">filled</div>
            </div>
        </div>
    </div>

    <!-- 2 BIỂU ĐỒ CHÍNH -->
    <div class="row g-5">
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="card-header-revenue">Doanh thu theo loại phòng</div>
                <div class="chart-body">
                    <canvas id="roomTypeChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="card-header-revenue">Doanh thu theo tháng (năm <?= $year ?>)</div>
                <div class="chart-body">
                    <canvas id="monthlyChart"></canvas>
                 </div>
            </div>
        </div>
    </div>
</div>

<script>
// Biểu đồ tròn
new Chart(document.getElementById('roomTypeChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($revenueByType, 'type')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($revenueByType, 'revenue')) ?>,
            backgroundColor: ['#2E8B57','#3CB371','#90EE90','#98FB98','#8FBC8F','#228B22','#006400','#32CD32'],
            borderWidth: 4, borderColor: '#fff', hoverOffset: 12
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 20, font: { size: 13 } } },
            tooltip: { callbacks: { label: c => `${c.label}: ${Number(c.raw).toLocaleString('vi-VN')} VNĐ` }}
        }
    }
});

// Biểu đồ đường
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
        datasets: [{
            label: 'Doanh thu',
            data: <?= json_encode($revenueByMonth) ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40,167,69,0.1)',
            borderWidth: 4,
            pointBackgroundColor: '#28a745',
            pointRadius: 6,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: c => `Doanh thu: ${Number(c.parsed.y).toLocaleString('vi-VN')} VNĐ` }}
        },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('vi-VN') + ' VNĐ' }},
            x: { grid: { display: false }}
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
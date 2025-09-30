<?php
session_start();
include 'koneksi.php';

// Cek apakah sudah login & role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Hitung total kamar
$q_total_rooms = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM rooms");
$total_rooms = mysqli_fetch_assoc($q_total_rooms)['total'];

// Hitung kamar tersedia
$q_available = mysqli_query($koneksi, "SELECT COUNT(*) as available FROM rooms WHERE status = 'available'");
$available_rooms = mysqli_fetch_assoc($q_available)['available'];

// Hitung kamar dipesan
$q_booked = mysqli_query($koneksi, "SELECT COUNT(*) as booked FROM rooms WHERE status = 'booked'");
$booked_rooms = mysqli_fetch_assoc($q_booked)['booked'];

// Hitung total booking
$q_bookings = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM bookings");
$total_bookings = mysqli_fetch_assoc($q_bookings)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Luxury Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #1B5E20;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px 0;
            color: #fff;
        }
        .sidebar h4 {
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #fff;
            text-decoration: none;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: #2E7D32;
        }
        .content {
            margin-left: 260px;
            padding: 30px;
        }
        .card-stat {
            border-radius: 12px;
            padding: 20px;
            color: #fff;
        }
        .bg-blue { background: #2196F3; }
        .bg-green { background: #4CAF50; }
        .bg-yellow { background: #FFC107; }
        .bg-teal { background: #009688; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4><i class="fas fa-hotel me-2"></i>Luxury Hotel</h4>
        <a href="admin_dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
        <a href="rooms/index.php"><i class="fas fa-bed me-2"></i>Kelola Kamar</a>
        <a href="admin/payment_confirm.php"><i class="fas fa-credit-card me-2"></i>Konfirmasi Pembayaran</a>
        <a href="bookings/all_bookings.php"><i class="fas fa-calendar me-2"></i>Semua Booking</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard Admin</h2>
            <div>
                <span class="me-3">Halo, <?php echo $_SESSION['full_name']; ?> 👋</span>
                <span class="badge bg-success">Administrator</span>
            </div>
        </div>

        <!-- Statistik -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card-stat bg-blue text-center">
                    <i class="fas fa-door-open fa-2x mb-2"></i>
                    <h3><?php echo $total_rooms; ?></h3>
                    <p>Total Kamar</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stat bg-green text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3><?php echo $available_rooms; ?></h3>
                    <p>Kamar Tersedia</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stat bg-yellow text-center">
                    <i class="fas fa-bed fa-2x mb-2"></i>
                    <h3><?php echo $booked_rooms; ?></h3>
                    <p>Kamar Dipesan</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stat bg-teal text-center">
                    <i class="fas fa-calendar-check fa-2x mb-2"></i>
                    <h3><?php echo $total_bookings; ?></h3>
                    <p>Total Booking</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h5>Kelola Kamar</h5>
                        <p>Kelola ketersediaan dan informasi kamar hotel</p>
                        <a href="rooms/index.php" class="btn btn-primary">Kelola Kamar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h5>Konfirmasi Pembayaran</h5>
                        <p>Verifikasi pembayaran dari customer</p>
                        <a href="admin/payment_confirm.php" class="btn btn-success">Konfirmasi Pembayaran</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

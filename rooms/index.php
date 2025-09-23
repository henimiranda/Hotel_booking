<?php
session_start();
include '../koneksi.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../customer_dashboard.php");
    exit();
}

// Ambil data kamar
$query = "SELECT * FROM rooms ORDER BY room_number";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kamar - Luxury Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .room-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .room-card:hover {
            transform: translateY(-5px);
        }
        
        .room-image {
            height: 200px;
            background-size: cover;
            background-position: center;
        }
        
        .status-available { border-left: 4px solid #28a745; }
        .status-booked { border-left: 4px solid #dc3545; }
        
        .price-tag {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar" style="background: linear-gradient(180deg, #2E7D32 0%, #1B5E20 100%); min-height: 100vh; color: white;">
                <div class="sidebar-brand p-3">
                    <h5><i class="fas fa-hotel me-2"></i>Luxury Hotel</h5>
                    <small>Admin Panel</small>
                </div>
                
                <nav class="nav flex-column mt-3">
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-door-open me-2"></i>Kelola Kamar
                    </a>
                    <a class="nav-link" href="../admin/payment_confirm.php">
                        <i class="fas fa-money-check me-2"></i>Konfirmasi Pembayaran
                    </a>
                    <a class="nav-link" href="../bookings/index.php">
                        <i class="fas fa-calendar-check me-2"></i>Semua Booking
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-door-open me-2"></i>Kelola Kamar</h2>
                        <p class="text-muted">Kelola ketersediaan dan informasi kamar hotel</p>
                    </div>
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Kamar Baru
                    </a>
                </div>

                <!-- Kamar Grid -->
                <div class="row">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($room = mysqli_fetch_assoc($result)) {
                            $status_class = $room['status'] == 'available' ? 'status-available' : 'status-booked';
                            $status_text = $room['status'] == 'available' ? 'Tersedia' : 'Dipesan';
                            $status_badge = $room['status'] == 'available' ? 'bg-success' : 'bg-danger';
                            
                            echo '
                            <div class="col-xl-4 col-lg-6">
                                <div class="card room-card ' . $status_class . '">
                                    <div class="room-image" style="background-image: url(https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400);"></div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title">Kamar ' . $room['room_number'] . '</h5>
                                            <span class="badge ' . $status_badge . '">' . $status_text . '</span>
                                        </div>
                                        
                                        <h6 class="text-primary mb-3">' . $room['room_type'] . '</h6>
                                        
                                        <div class="price-tag d-inline-block mb-3">
                                            Rp ' . number_format($room['price_per_night'], 0, ',', '.') . '/malam
                                        </div>
                                        
                                        <div class="room-facilities mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-wifi me-1"></i>Free WiFi • 
                                                <i class="fas fa-snowflake me-1"></i>AC • 
                                                <i class="fas fa-tv me-1"></i>TV
                                            </small>
                                        </div>
                                        
                                        <div class="btn-group w-100">
                                            <a href="edit.php?id=' . $room['id'] . '" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            <a href="delete.php?id=' . $room['id'] . '" class="btn btn-outline-danger btn-sm" 
                                               onclick="return confirm(\'Yakin hapus kamar ini?\')">
                                                <i class="fas fa-trash me-1"></i>Hapus
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<div class="col-12">
                                <div class="alert alert-info text-center py-5">
                                    <i class="fas fa-door-open fa-3x mb-3"></i>
                                    <h4>Belum ada kamar</h4>
                                    <p>Mulai dengan menambahkan kamar pertama Anda</p>
                                    <a href="create.php" class="btn btn-primary">Tambah Kamar Pertama</a>
                                </div>
                              </div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
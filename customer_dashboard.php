<?php
session_start();
include 'koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek role, jika admin redirect ke admin dashboard
if ($_SESSION['role'] == 'admin') {
    header("Location: index.php");
    exit();
}

// Ambil data booking customer
$user_id = $_SESSION['user_id'];
$query_bookings = "SELECT b.*, r.room_number, r.room_type, r.price_per_night 
                   FROM bookings b 
                   JOIN rooms r ON b.room_id = r.id 
                   WHERE b.customer_email = (SELECT username FROM users WHERE id = $user_id)
                   ORDER BY b.booking_date DESC 
                   LIMIT 5";
$result_bookings = mysqli_query($koneksi, $query_bookings);

// Ambil booking yang sedang aktif (check-in sampai check-out)
$query_active = "SELECT b.*, r.room_number, r.room_type 
                 FROM bookings b 
                 JOIN rooms r ON b.room_id = r.id 
                 WHERE b.customer_email = (SELECT username FROM users WHERE id = $user_id)
                 AND b.payment_status = 'paid' 
                 AND b.status IN ('confirmed', 'checked_in')
                 AND CURDATE() BETWEEN b.check_in AND b.check_out
                 ORDER BY b.check_in DESC 
                 LIMIT 1";
$result_active = mysqli_query($koneksi, $query_active);
$active_booking = mysqli_fetch_assoc($result_active);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pelanggan - Hotel Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e8f5e8 0%, #ffffff 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
        }
        .room-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease;
            margin-bottom: 20px;
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
        .booking-card {
            border-left: 4px solid #4CAF50;
            background: white;
        }
        .active-booking {
            border-left: 4px solid #ffc107;
            background: #fffbf0;
        }
        .status-badge {
            font-size: 0.8em;
            padding: 5px 10px;
            border-radius: 10px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="customer_dashboard.php">
                <i class="fas fa-hotel me-2"></i>
                <strong>Luxury Hotel</strong>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>
                    Halo, <?php echo $_SESSION['full_name']; ?> (Pelanggan)
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="text-center mb-5">
            <h1 class="text-success">🏨 Selamat Datang, <?php echo $_SESSION['full_name']; ?>!</h1>
            <p class="lead text-muted">Nikmati pengalaman menginap terbaik bersama Luxury Hotel</p>
        </div>

        <!-- Booking yang Sedang Aktif -->
        <?php if ($active_booking): ?>
        <div class="card active-booking mb-5">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Booking Aktif Saat Ini</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="text-success">Kamar <?php echo $active_booking['room_number']; ?> - <?php echo $active_booking['room_type']; ?></h4>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>Check-in:</strong><br>
                                <span class="h6"><?php echo date('d M Y', strtotime($active_booking['check_in'])); ?></span></p>
                                <p><strong>Status:</strong><br>
                                <span class="badge bg-success status-badge">
                                    <?php 
                                    $status_text = [
                                        'confirmed' => 'Terkonfirmasi',
                                        'checked_in' => 'Sedang Menginap',
                                        'checked_out' => 'Selesai'
                                    ];
                                    echo $status_text[$active_booking['status']];
                                    ?>
                                </span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Check-out:</strong><br>
                                <span class="h6"><?php echo date('d M Y', strtotime($active_booking['check_out'])); ?></span></p>
                                <p><strong>Durasi:</strong><br>
                                <span class="h6">
                                    <?php 
                                    $checkin = new DateTime($active_booking['check_in']);
                                    $checkout = new DateTime($active_booking['check_out']);
                                    echo $checkin->diff($checkout)->days . ' malam';
                                    ?>
                                </span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="stats-card">
                            <i class="fas fa-key fa-2x text-warning mb-3"></i>
                            <h4>Aktif</h4>
                            <p class="text-muted">Anda sedang menginap</p>
                            <a href="bookings/payment_status.php?booking_id=<?php echo $active_booking['id']; ?>" class="btn btn-warning btn-sm">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistik Cepat -->
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-calendar-check fa-2x text-primary mb-3"></i>
                    <h3>
                        <?php
                        $query_total = "SELECT COUNT(*) as total FROM bookings 
                                      WHERE customer_email = (SELECT username FROM users WHERE id = $user_id)";
                        $result_total = mysqli_query($koneksi, $query_total);
                        $total = mysqli_fetch_assoc($result_total);
                        echo $total['total'];
                        ?>
                    </h3>
                    <p class="text-muted">Total Booking</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-clock fa-2x text-warning mb-3"></i>
                    <h3>
                        <?php
                        $query_pending = "SELECT COUNT(*) as pending FROM bookings 
                                        WHERE customer_email = (SELECT username FROM users WHERE id = $user_id)
                                        AND payment_status = 'pending'";
                        $result_pending = mysqli_query($koneksi, $query_pending);
                        $pending = mysqli_fetch_assoc($result_pending);
                        echo $pending['pending'];
                        ?>
                    </h3>
                    <p class="text-muted">Menunggu Konfirmasi</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                    <h3>
                        <?php
                        $query_confirmed = "SELECT COUNT(*) as confirmed FROM bookings 
                                          WHERE customer_email = (SELECT username FROM users WHERE id = $user_id)
                                          AND payment_status = 'paid'";
                        $result_confirmed = mysqli_query($koneksi, $query_confirmed);
                        $confirmed = mysqli_fetch_assoc($result_confirmed);
                        echo $confirmed['confirmed'];
                        ?>
                    </h3>
                    <p class="text-muted">Terkonfirmasi</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-bed fa-2x text-info mb-3"></i>
                    <h3>
                        <?php
                        $query_completed = "SELECT COUNT(*) as completed FROM bookings 
                                          WHERE customer_email = (SELECT username FROM users WHERE id = $user_id)
                                          AND status = 'checked_out'";
                        $result_completed = mysqli_query($koneksi, $query_completed);
                        $completed = mysqli_fetch_assoc($result_completed);
                        echo $completed['completed'];
                        ?>
                    </h3>
                    <p class="text-muted">Selesai</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Kamar Tersedia -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-bed me-2"></i>Kamar Tersedia</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Pilih kamar yang tersedia untuk melakukan pemesanan</p>
                        
                        <div class="row">
                            <?php
                            $query = "SELECT * FROM rooms WHERE status = 'available' ORDER BY room_number";
                            $result = mysqli_query($koneksi, $query);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($room = mysqli_fetch_assoc($result)) {
                                    $room_details = getRoomDetails($room['room_type'], $room['price_per_night']);
                                    
                                    echo '
                                    <div class="col-md-6">
                                        <div class="card room-card shadow-sm h-100">
                                            <div class="room-image" style="background-image: url(' . $room_details['image'] . ');"></div>
                                            <div class="card-body">
                                                <h5 class="card-title">Kamar ' . $room['room_number'] . '</h5>
                                                <h6 class="text-primary">' . $room['room_type'] . '</h6>
                                                <p class="card-text">
                                                    <i class="fas fa-tag me-2"></i><strong>Rp ' . number_format($room['price_per_night'], 0, ',', '.') . '/malam</strong>
                                                </p>
                                                <p class="text-muted small">' . $room_details['description'] . '</p>
                                                <a href="bookings/create.php?room_id=' . $room['id'] . '" class="btn btn-success w-100">
                                                    <i class="fas fa-calendar-plus me-2"></i>Pesan Sekarang
                                                </a>
                                            </div>
                                        </div>
                                    </div>';
                                }
                            } else {
                                echo '<div class="col-12">
                                        <div class="alert alert-info text-center">
                                            <i class="fas fa-bed fa-2x mb-3"></i><br>
                                            Maaf, tidak ada kamar yang tersedia saat ini.
                                        </div>
                                      </div>';
                            }

                            function getRoomDetails($room_type, $price) {
                                $details = [
                                    'Standard' => [
                                        'image' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400',
                                        'description' => 'Kamar nyaman dengan fasilitas standar untuk menginap yang hemat.',
                                    ],
                                    'Deluxe' => [
                                        'image' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=400',
                                        'description' => 'Kamar lebih luas dengan fasilitas premium untuk kenyamanan extra.',
                                    ],
                                    'Suite' => [
                                        'image' => 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=400',
                                        'description' => 'Kamar mewah dengan ruang living terpisah, pengalaman bintang 5.',
                                    ]
                                ];
                                return $details[$room_type] ?? $details['Standard'];
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Booking Terbaru -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Booking Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result_bookings) > 0): ?>
                            <div class="list-group">
                                <?php while ($booking = mysqli_fetch_assoc($result_bookings)): 
                                    $status_badge = [
                                        'pending' => 'bg-warning',
                                        'paid' => 'bg-success',
                                        'failed' => 'bg-danger'
                                    ];
                                    
                                    $status_text = [
                                        'pending' => 'Menunggu',
                                        'paid' => 'Terkonfirmasi', 
                                        'failed' => 'Ditolak'
                                    ];
                                ?>
                                <a href="bookings/payment_status.php?booking_id=<?php echo $booking['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Kamar <?php echo $booking['room_number']; ?></h6>
                                        <small>
                                            <span class="badge <?php echo $status_badge[$booking['payment_status']]; ?>">
                                                <?php echo $status_text[$booking['payment_status']]; ?>
                                            </span>
                                        </small>
                                    </div>
                                    <p class="mb-1 small">
                                        <?php echo date('d M Y', strtotime($booking['check_in'])); ?> - 
                                        <?php echo date('d M Y', strtotime($booking['check_out'])); ?>
                                    </p>
                                    <small class="text-muted">
                                        Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?>
                                    </small>
                                </a>
                                <?php endwhile; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="bookings/history.php" class="btn btn-outline-info btn-sm">Lihat Semua Riwayat</a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada riwayat booking</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="bookings/create.php" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Booking Baru
                            </a>
                            <a href="bookings/history.php" class="btn btn-outline-primary">
                                <i class="fas fa-history me-2"></i>Lihat Riwayat
                            </a>
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="fas fa-user me-2"></i>Profil Saya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
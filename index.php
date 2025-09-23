<?php
session_start();
include 'koneksi.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: customer_dashboard.php");
    exit();
}

// Ambil statistik untuk dashboard
$query_stats = "SELECT 
    (SELECT COUNT(*) FROM rooms) as total_rooms,
    (SELECT COUNT(*) FROM rooms WHERE status = 'available') as available_rooms,
    (SELECT COUNT(*) FROM rooms WHERE status = 'booked') as booked_rooms,
    (SELECT COUNT(*) FROM bookings) as total_bookings,
    (SELECT COUNT(*) FROM bookings WHERE payment_status = 'pending') as pending_payments,
    (SELECT COUNT(*) FROM bookings WHERE status = 'checked_in') as checked_in_guests";

$result_stats = mysqli_query($koneksi, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Luxury Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2E7D32;
            --primary-dark: #1B5E20;
            --secondary: #4CAF50;
            --accent: #FFC107;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            min-height: 100vh;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .recent-bookings {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .badge-pending { background: #FFC107; color: black; }
        .badge-paid { background: #28a745; }
        .badge-failed { background: #dc3545; }
        
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="sidebar-brand">
                    <h4><i class="fas fa-hotel me-2"></i>Luxury Hotel</h4>
                    <small>Admin Panel</small>
                </div>
                
                <nav class="nav flex-column mt-4">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-tachometer-alt"></i>Dashboard
                    </a>
                    <a class="nav-link" href="rooms/index.php">
                        <i class="fas fa-door-open"></i>Kelola Kamar
                    </a>
                    <a class="nav-link" href="admin/payment_confirm.php">
                        <i class="fas fa-money-check"></i>Konfirmasi Pembayaran
                    </a>
                    <a class="nav-link" href="bookings/index.php">
                        <i class="fas fa-calendar-check"></i>Semua Booking
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ml-sm-auto">
                <!-- Header -->
                <div class="header">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="mb-0">Dashboard Admin</h2>
                            </div>
                            <div class="col-auto">
                                <span class="text-muted me-3">Halo, <?php echo $_SESSION['full_name']; ?></span>
                                <span class="badge bg-success">Administrator</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid">
                    <!-- Statistik -->
                    <div class="row mb-5">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon text-primary">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <div class="stat-number text-primary"><?php echo $stats['total_rooms']; ?></div>
                                <p class="text-muted">Total Kamar</p>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-number text-success"><?php echo $stats['available_rooms']; ?></div>
                                <p class="text-muted">Kamar Tersedia</p>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon text-warning">
                                    <i class="fas fa-bed"></i>
                                </div>
                                <div class="stat-number text-warning"><?php echo $stats['booked_rooms']; ?></div>
                                <p class="text-muted">Kamar Dipesan</p>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon text-info">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-number text-info"><?php echo $stats['total_bookings']; ?></div>
                                <p class="text-muted">Total Booking</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-5">
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-door-open fa-3x text-primary mb-3"></i>
                                    <h4>Kelola Kamar</h4>
                                    <p class="text-muted">Kelola ketersediaan dan informasi kamar hotel</p>
                                    <a href="rooms/index.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-cog me-2"></i>Kelola Kamar
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-money-check fa-3x text-success mb-3"></i>
                                    <h4>Konfirmasi Pembayaran</h4>
                                    <p class="text-muted">Verifikasi pembayaran dari customer</p>
                                    <a href="admin/payment_confirm.php" class="btn btn-success btn-lg">
                                        <i class="fas fa-check-circle me-2"></i>Konfirmasi Pembayaran
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Terbaru -->
                    <div class="row">
                        <div class="col-12">
                            <div class="recent-bookings">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Booking Terbaru</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Customer</th>
                                                    <th>Kamar</th>
                                                    <th>Check-in</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query_recent = "SELECT b.*, r.room_number, r.room_type 
                                                              FROM bookings b 
                                                              JOIN rooms r ON b.room_id = r.id 
                                                              ORDER BY b.booking_date DESC 
                                                              LIMIT 5";
                                                $result_recent = mysqli_query($koneksi, $query_recent);
                                                
                                                if (mysqli_num_rows($result_recent) > 0) {
                                                    while ($row = mysqli_fetch_assoc($result_recent)) {
                                                        $status_class = [
                                                            'pending' => 'badge-pending',
                                                            'paid' => 'badge-paid',
                                                            'failed' => 'badge-failed'
                                                        ];
                                                        
                                                        echo "<tr>";
                                                        echo "<td>#" . str_pad($row['id'], 6, '0', STR_PAD_LEFT) . "</td>";
                                                        echo "<td>{$row['customer_name']}<br><small>{$row['customer_phone']}</small></td>";
                                                        echo "<td>{$row['room_number']}<br><small>{$row['room_type']}</small></td>";
                                                        echo "<td>" . date('d M Y', strtotime($row['check_in'])) . "</td>";
                                                        echo "<td>Rp " . number_format($row['total_price'], 0, ',', '.') . "</td>";
                                                        echo "<td><span class='badge {$status_class[$row['payment_status']]}'>" . ucfirst($row['payment_status']) . "</span></td>";
                                                        echo "<td><a href='bookings/detail.php?id={$row['id']}' class='btn btn-sm btn-outline-primary'>Detail</a></td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='7' class='text-center py-4'>Belum ada booking</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
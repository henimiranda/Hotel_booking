<?php
session_start();
include '../koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Cek parameter booking_id
if (!isset($_GET['booking_id'])) {
    header("Location: create.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);

// Ambil data booking
$query = "SELECT b.*, r.room_number, r.room_type, r.price_per_night
          FROM bookings b 
          JOIN rooms r ON b.room_id = r.id 
          WHERE b.id = $booking_id";
$result = mysqli_query($koneksi, $query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    header("Location: create.php?error=Booking tidak ditemukan");
    exit();
}

// Hitung selisih hari untuk info
$checkin = new DateTime($booking['check_in']);
$checkout = new DateTime($booking['check_out']);
$night_count = $checkin->diff($checkout)->days;

// Tentukan status dan icon
$status_config = [
    'pending' => [
        'icon' => 'fa-clock',
        'color' => 'warning',
        'title' => 'Menunggu Konfirmasi',
        'message' => 'Pembayaran sedang menunggu konfirmasi admin',
        'alert' => 'warning'
    ],
    'paid' => [
        'icon' => 'fa-check-circle',
        'color' => 'success',
        'title' => 'Pembayaran Berhasil',
        'message' => 'Pembayaran telah dikonfirmasi oleh admin',
        'alert' => 'success'
    ],
    'failed' => [
        'icon' => 'fa-times-circle',
        'color' => 'danger',
        'title' => 'Pembayaran Gagal',
        'message' => 'Pembayaran tidak valid atau ditolak',
        'alert' => 'danger'
    ]
];

$status_info = $status_config[$booking['payment_status']];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Status Pembayaran - Luxury Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e8f5e8 0%, #ffffff 100%);
            min-height: 100vh;
        }
        .status-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .status-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 60px;
        }
        .timeline-icon {
            position: absolute;
            left: 20px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e9ecef;
            border: 3px solid white;
        }
        .timeline-item.active .timeline-icon {
            background: #4CAF50;
        }
        .timeline-item.completed .timeline-icon {
            background: #4CAF50;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4CAF50;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);">
        <div class="container">
            <a class="navbar-brand" href="../customer_dashboard.php">
                <i class="fas fa-hotel me-2"></i>
                <strong>Luxury Hotel</strong>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Halo, <?php echo $_SESSION['full_name']; ?></span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="status-card">
                    <div class="card-body p-5">
                        
                        <!-- Status Header -->
                        <div class="text-center mb-4">
                            <div class="status-icon text-<?php echo $status_info['color']; ?>">
                                <i class="fas <?php echo $status_info['icon']; ?>"></i>
                            </div>
                            <h2 class="text-<?php echo $status_info['color']; ?>"><?php echo $status_info['title']; ?></h2>
                            <p class="lead"><?php echo $status_info['message']; ?></p>
                            
                            <?php if ($booking['payment_status'] == 'pending'): ?>
                            <div class="mt-3">
                                <div class="loading"></div>
                                <span class="ms-2">Menunggu konfirmasi admin...</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Alert Status -->
                        <div class="alert alert-<?php echo $status_info['alert']; ?>">
                            <h5><i class="fas fa-info-circle me-2"></i>Status Booking</h5>
                            <p class="mb-0">
                                <strong>Kode Booking: #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></strong><br>
                                Kamar <?php echo $booking['room_number']; ?> - <?php echo $booking['room_type']; ?>
                            </p>
                        </div>

                        <!-- Timeline Proses -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Proses Booking</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item <?php echo $booking['payment_status'] != 'pending' ? 'completed' : 'active'; ?>">
                                        <div class="timeline-icon"></div>
                                        <div>
                                            <h6>Pembayaran Diverifikasi</h6>
                                            <p class="text-muted small">Admin akan memverifikasi pembayaran Anda</p>
                                            <?php if ($booking['payment_status'] == 'paid' && $booking['payment_date']): ?>
                                                <small class="text-success">
                                                    <i class="fas fa-check me-1"></i>
                                                    Dikonfirmasi pada: <?php echo date('d M Y H:i', strtotime($booking['payment_date'])); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item <?php echo $booking['payment_status'] == 'paid' ? 'active' : ''; ?>">
                                        <div class="timeline-icon"></div>
                                        <div>
                                            <h6>Booking Dikonfirmasi</h6>
                                            <p class="text-muted small">Kamar dipastikan tersedia untuk Anda</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <div class="timeline-icon"></div>
                                        <div>
                                            <h6>Siap Check-in</h6>
                                            <p class="text-muted small">Tunjukkan bukti booking saat check-in</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Booking -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Detail Menginap</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Check-in:</strong><br>
                                        <?php echo date('l, d F Y', strtotime($booking['check_in'])); ?><br>
                                        <small class="text-muted">Setelah 14:00 WIB</small></p>
                                        
                                        <p><strong>Check-out:</strong><br>
                                        <?php echo date('l, d F Y', strtotime($booking['check_out'])); ?><br>
                                        <small class="text-muted">Sebelum 12:00 WIB</small></p>
                                        
                                        <p><strong>Durasi:</strong> <?php echo $night_count; ?> malam</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Detail Pembayaran</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Total Pembayaran:</strong><br>
                                        <span class="h5 text-success">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span></p>
                                        
                                        <p><strong>Metode Pembayaran:</strong><br>
                                        <?php 
                                        $payment_methods = [
                                            'bca_va' => 'Transfer BCA Virtual Account',
                                            'bni_va' => 'Transfer BNI Virtual Account', 
                                            'bri_va' => 'Transfer BRI Virtual Account',
                                            'qris' => 'QRIS'
                                        ];
                                        echo $payment_methods[$booking['payment_method']] ?? $booking['payment_method'];
                                        ?></p>
                                        
                                        <?php if ($booking['va_number']): ?>
                                        <p><strong>Virtual Account:</strong><br>
                                        <code><?php echo chunk_split($booking['va_number'], 4, ' '); ?></code></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center mt-4">
                            <a href="../customer_dashboard.php" class="btn btn-success">
                                <i class="fas fa-home me-2"></i>Kembali ke Dashboard
                            </a>
                            
                            <?php if ($booking['payment_status'] == 'paid'): ?>
                            <a href="print_receipt.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-outline-primary ms-2">
                                <i class="fas fa-print me-2"></i>Cetak Invoice
                            </a>
                            <?php endif; ?>
                            
                            <button onclick="location.reload()" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-sync-alt me-2"></i>Refresh Status
                            </button>
                        </div>

                        <!-- Info Kontak Admin -->
                        <?php if ($booking['payment_status'] == 'pending'): ?>
                        <div class="alert alert-info mt-4">
                            <h6><i class="fas fa-headset me-2"></i>Butuh Bantuan?</h6>
                            <p class="mb-1">Jika pembayaran sudah dilakukan lebih dari 2 jam namun status belum update, hubungi:</p>
                            <p class="mb-0">
                                <strong>WhatsApp Admin: 0812-3456-7890</strong><br>
                                <strong>Email: admin@luxuryhotel.com</strong>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto refresh untuk status pending -->
    <?php if ($booking['payment_status'] == 'pending'): ?>
    <script>
    // Auto refresh setiap 30 detik
    setTimeout(function() {
        location.reload();
    }, 30000);
    
    // Manual refresh dengan button
    document.getElementById('refreshBtn').addEventListener('click', function() {
        location.reload();
    });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
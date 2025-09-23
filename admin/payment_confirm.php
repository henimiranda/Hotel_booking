<?php
session_start();
include '../koneksi.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Proses konfirmasi pembayaran
if (isset($_POST['confirm_payment'])) {
    $booking_id = intval($_POST['booking_id']);
    $action = $_POST['action']; // 'confirm' or 'reject'
    
    $payment_status = ($action == 'confirm') ? 'paid' : 'failed';
    $booking_status = ($action == 'confirm') ? 'confirmed' : 'cancelled';
    
    $query = "UPDATE bookings SET 
              payment_status = '$payment_status', 
              status = '$booking_status',
              payment_date = NOW()
              WHERE id = $booking_id";
    
    if (mysqli_query($koneksi, $query)) {
        $message = "Pembayaran berhasil dikonfirmasi!";
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}

// Ambil data booking yang pending
$query_pending = "SELECT b.*, r.room_number, r.room_type 
                  FROM bookings b 
                  JOIN rooms r ON b.room_id = r.id 
                  WHERE b.payment_status = 'pending' 
                  ORDER BY b.booking_date DESC";
$result_pending = mysqli_query($koneksi, $query_pending);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .payment-pending { border-left: 4px solid #ffc107; }
        
        .va-number {
            background: #f8f9fa;
            border: 2px dashed #6c757d;
            border-radius: 10px;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            text-align: center;
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
                    <a class="nav-link" href="../rooms/index.php">
                        <i class="fas fa-door-open me-2"></i>Kelola Kamar
                    </a>
                    <a class="nav-link active" href="payment_confirm.php">
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
                        <h2><i class="fas fa-money-check me-2"></i>Konfirmasi Pembayaran</h2>
                        <p class="text-muted">Verifikasi dan konfirmasi pembayaran dari customer</p>
                    </div>
                </div>

                <?php if (isset($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (mysqli_num_rows($result_pending) > 0): ?>
                    <div class="row">
                        <?php while ($booking = mysqli_fetch_assoc($result_pending)): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card payment-card payment-pending">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        Menunggu Konfirmasi
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Kode Booking:</strong><br>
                                            #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                            
                                            <p><strong>Customer:</strong><br>
                                            <?php echo $booking['customer_name']; ?><br>
                                            <small><?php echo $booking['customer_phone']; ?></small></p>
                                            
                                            <p><strong>Kamar:</strong><br>
                                            <?php echo $booking['room_number']; ?> - <?php echo $booking['room_type']; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Check-in:</strong><br>
                                            <?php echo date('d M Y', strtotime($booking['check_in'])); ?></p>
                                            
                                            <p><strong>Total:</strong><br>
                                            <span class="h5 text-success">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span></p>
                                            
                                            <p><strong>Metode:</strong><br>
                                            <?php echo strtoupper($booking['payment_method']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <?php if ($booking['va_number']): ?>
                                    <div class="va-number mt-3">
                                        <?php echo chunk_split($booking['va_number'], 4, ' '); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-grid gap-2 mt-3">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="action" value="confirm">
                                            <button type="submit" name="confirm_payment" class="btn btn-success w-100">
                                                <i class="fas fa-check me-2"></i>Konfirmasi Pembayaran
                                            </button>
                                        </form>
                                        
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" name="confirm_payment" class="btn btn-outline-danger w-100">
                                                <i class="fas fa-times me-2"></i>Tolak Pembayaran
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h4>Tidak ada pembayaran yang menunggu konfirmasi</h4>
                        <p class="mb-0">Semua pembayaran telah diproses</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
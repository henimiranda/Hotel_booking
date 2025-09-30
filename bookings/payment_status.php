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
$query = "SELECT b.*, r.room_number, r.room_type 
          FROM bookings b 
          JOIN rooms r ON b.room_id = r.id 
          WHERE b.id = $booking_id";
$result = mysqli_query($koneksi, $query);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    header("Location: create.php?error=Booking tidak ditemukan");
    exit();
}

// Simulasi: 70% kemungkinan pembayaran berhasil, 30% pending
$payment_status = (rand(1, 10) <= 7) ? 'paid' : 'pending';
$status_message = ($payment_status == 'paid') ? 
    'Pembayaran Berhasil!' : 'Menunggu Konfirmasi Pembayaran';

// Update status pembayaran (simulasi)
if ($payment_status == 'paid') {
    $update_query = "UPDATE bookings SET 
                    payment_status = 'paid', 
                    payment_date = NOW(),
                    status = 'confirmed'
                    WHERE id = $booking_id";
    mysqli_query($koneksi, $update_query);
}
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
        .success { color: #28a745; }
        .warning { color: #ffc107; }
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
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="status-card">
                    <div class="card-body text-center p-5">
                        
                        <?php if ($payment_status == 'paid'): ?>
                        <!-- Pembayaran Berhasil -->
                        <div class="status-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="text-success">Pembayaran Berhasil!</h2>
                        <p class="lead">Terima kasih telah melakukan pembayaran.</p>
                        
                        <div class="alert alert-success mt-4">
                            <h5><i class="fas fa-receipt me-2"></i>Booking Dikonfirmasi</h5>
                            <p class="mb-0">Kamar <?php echo $booking['room_number']; ?> telah dipesan atas nama <?php echo $booking['customer_name']; ?></p>
                        </div>
                        
                        <?php else: ?>
                        <!-- Menunggu Konfirmasi -->
                        <div class="status-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h2 class="text-warning">Menunggu Konfirmasi</h2>
                        <p class="lead">Pembayaran Anda sedang diproses.</p>
                        
                        <div class="alert alert-warning mt-4">
                            <h5><i class="fas fa-hourglass-half me-2"></i>Sedang Diverifikasi</h5>
                            <p class="mb-0">Tim kami akan memverifikasi pembayaran Anda dalam 1-5 menit</p>
                        </div>
                        
                        <!-- Loading Animation -->
                        <div class="my-4">
                            <div class="loading"></div>
                            <span class="ms-2">Memeriksa status pembayaran...</span>
                        </div>
                        <?php endif; ?>

                        <!-- Informasi Booking -->
                        <div class="card mt-4">
                            <div class="card-body text-start">
                                <h5><i class="fas fa-info-circle me-2"></i>Detail Booking</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Kode Booking</strong></td>
                                        <td>#<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Kamar</strong></td>
                                        <td><?php echo $booking['room_number'] . ' - ' . $booking['room_type']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Periode</strong></td>
                                        <td><?php echo date('d M Y', strtotime($booking['check_in'])); ?> - <?php echo date('d M Y', strtotime($booking['check_out'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td class="text-success"><strong>Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Action Buttons -->
<div class="text-center mt-4">
    <button type="button" class="btn btn-success btn-lg" onclick="showPaymentConfirmation()">
        <i class="fas fa-check-circle me-2"></i>Sudah Bayar
    </button>
    <a href="../customer_dashboard.php" class="btn btn-outline-secondary ms-2">Kembali</a>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Terima kasih telah melakukan pembayaran. Pembayaran Anda sedang menunggu konfirmasi dari admin.</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Proses Konfirmasi:</strong><br>
                    1. Tim admin akan memverifikasi pembayaran Anda<br>
                    2. Konfirmasi biasanya memakan waktu 1-2 jam<br>
                    3. Status booking akan otomatis update setelah dikonfirmasi
                </div>
                <p>Anda dapat memantau status pembayaran di halaman status booking.</p>
            </div>
            <div class="modal-footer">
                <a href="payment_status.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-success">
                    <i class="fas fa-eye me-2"></i>Lihat Status Booking
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function showPaymentConfirmation() {
    // Tampilkan modal konfirmasi
    var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    paymentModal.show();
}
</script>
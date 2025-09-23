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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran - Luxury Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e8f5e8 0%, #ffffff 100%);
            min-height: 100vh;
        }
        .payment-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .va-number {
            background: #f8f9fa;
            border: 2px dashed #4CAF50;
            border-radius: 10px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
        }
        .qrcode-container {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .countdown {
            font-size: 1.5em;
            font-weight: bold;
            color: #dc3545;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            padding: 10px 20px;
            margin: 0 5px;
            background: #e9ecef;
            border-radius: 20px;
            font-weight: 600;
        }
        .step.active {
            background: #4CAF50;
            color: white;
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

    <div class="container mt-4">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step">1. Pilih Kamar</div>
            <div class="step">2. Data Pemesan</div>
            <div class="step active">3. Pembayaran</div>
            <div class="step">4. Konfirmasi</div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="payment-card">
                    <div class="card-body p-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <h2 class="text-success"><i class="fas fa-credit-card me-2"></i>Pembayaran</h2>
                            <p class="text-muted">Selesaikan pembayaran dalam <span class="countdown" id="countdown">24:00:00</span></p>
                        </div>

                        <!-- Informasi Booking -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Detail Pemesanan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Kamar:</strong> <?php echo $booking['room_number'] . ' - ' . $booking['room_type']; ?></p>
                                        <p><strong>Check-in:</strong> <?php echo date('d M Y', strtotime($booking['check_in'])); ?></p>
                                        <p><strong>Check-out:</strong> <?php echo date('d M Y', strtotime($booking['check_out'])); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Pemesan:</strong> <?php echo $booking['customer_name']; ?></p>
                                        <p><strong>No. Telepon:</strong> <?php echo $booking['customer_phone']; ?></p>
                                        <p><strong>Jumlah Tamu:</strong> <?php echo $booking['guest_count']; ?> orang</p>
                                    </div>
                                </div>
                                <hr>
                                <h4 class="text-success text-center">Total: Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></h4>
                            </div>
                        </div>

                        <!-- Metode Pembayaran -->
                        <?php if ($booking['payment_method'] != 'qris'): ?>
                        <!-- Virtual Account -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-university me-2"></i>Virtual Account</h5>
                            </div>
                            <div class="card-body text-center">
                                <p>Silakan transfer tepat sebesar jumlah di atas ke Virtual Account berikut:</p>
                                
                                <div class="va-number my-4">
                                    <?php echo chunk_split($booking['va_number'], 4, ' '); ?>
                                </div>
                                
                                <div class="bank-info mb-3">
                                    <?php
                                    $bank_name = '';
                                    switch ($booking['payment_method']) {
                                        case 'bca_va': $bank_name = 'Bank BCA'; break;
                                        case 'bni_va': $bank_name = 'Bank BNI'; break;
                                        case 'bri_va': $bank_name = 'Bank BRI'; break;
                                    }
                                    ?>
                                    <h5><?php echo $bank_name; ?></h5>
                                    <p class="text-muted">Virtual Account</p>
                                </div>
                                
                                <div class="alert alert-info">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        Pembayaran akan diverifikasi otomatis dalam 1-5 menit setelah transfer
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- QR Code -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>QRIS Payment</h5>
                            </div>
                            <div class="card-body text-center">
                                <p>Scan QR Code berikut untuk melakukan pembayaran:</p>
                                
                                <div class="qrcode-container my-4">
                                    <!-- QR Code Placeholder -->
                                    <div style="width: 200px; height: 200px; background: #f8f9fa; border: 2px dashed #ccc; 
                                                display: inline-flex; align-items: center; justify-content: center; 
                                                font-size: 3em; color: #4CAF50;">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <p class="mt-3"><strong>QR CODE</strong></p>
                                </div>
                                
                                <div class="alert alert-info">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        Scan QR code dengan aplikasi e-wallet atau mobile banking yang mendukung QRIS
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Instruksi Pembayaran -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Instruksi Pembayaran</h5>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li>Lakukan pembayaran sesuai dengan jumlah yang tertera</li>
                                    <li>Pembayaran akan diverifikasi otomatis oleh sistem</li>
                                    <li>Status booking akan berubah menjadi "Dikonfirmasi" setelah pembayaran berhasil</li>
                                    <li>Simpan bukti pembayaran untuk keperluan check-in</li>
                                    <li>Pembayaran akan kadaluarsa dalam <strong>24 jam</strong></li>
                                </ol>
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
<?php
session_start();
include '../koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $room_id = mysqli_real_escape_string($koneksi, $_POST['room_id']);
    $customer_name = mysqli_real_escape_string($koneksi, $_POST['customer_name']);
    $customer_email = mysqli_real_escape_string($koneksi, $_POST['customer_email']);
    $customer_phone = mysqli_real_escape_string($koneksi, $_POST['customer_phone']);
    $check_in = mysqli_real_escape_string($koneksi, $_POST['check_in']);
    $check_out = mysqli_real_escape_string($koneksi, $_POST['check_out']);
    $total_price = mysqli_real_escape_string($koneksi, $_POST['total_price']);
    $guest_count = mysqli_real_escape_string($koneksi, $_POST['guest_count']);
    $special_request = mysqli_real_escape_string($koneksi, $_POST['special_request'] ?? '');
    $payment_method = mysqli_real_escape_string($koneksi, $_POST['payment_method']);
    
    // Validasi data
    if (empty($room_id) || empty($customer_name) || empty($customer_email) || 
        empty($customer_phone) || empty($check_in) || empty($check_out) || 
        empty($total_price) || empty($payment_method)) {
        header("Location: create.php?error=Semua field wajib diisi!");
        exit();
    }
    
    // Validasi tanggal
    if ($check_in >= $check_out) {
        header("Location: create.php?error=Tanggal check-out harus setelah check-in!");
        exit();
    }
    
    // Generate Virtual Account Number atau QR Code
    $va_number = generateVANumber($payment_method);
    $qr_code = ($payment_method == 'qris') ? generateQRCode($total_price, $customer_name) : null;
    
    // Hitung expiry date (24 jam dari sekarang)
    $expiry_date = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Mulai transaction
    mysqli_begin_transaction($koneksi);
    
    try {
        // 1. Insert data booking
        $query_booking = "INSERT INTO bookings 
                         (room_id, customer_name, customer_email, customer_phone, 
                         check_in, check_out, total_price, guest_count, special_request,
                         payment_method, va_number, qr_code, payment_status, expiry_date) 
                         VALUES 
                         ('$room_id', '$customer_name', '$customer_email', '$customer_phone',
                         '$check_in', '$check_out', '$total_price', '$guest_count', '$special_request',
                         '$payment_method', '$va_number', '$qr_code', 'pending', '$expiry_date')";
        
        if (!mysqli_query($koneksi, $query_booking)) {
            throw new Exception("Error insert booking: " . mysqli_error($koneksi));
        }
        
        $booking_id = mysqli_insert_id($koneksi);
        
        // 2. Update status kamar jadi 'booked'
        $query_room = "UPDATE rooms SET status = 'booked' WHERE id = '$room_id'";
        
        if (!mysqli_query($koneksi, $query_room)) {
            throw new Exception("Error update room status: " . mysqli_error($koneksi));
        }
        
        // Commit transaction
        mysqli_commit($koneksi);
        
        // Redirect ke halaman pembayaran
        header("Location: payment.php?booking_id=" . $booking_id);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction jika error
        mysqli_rollback($koneksi);
        header("Location: create.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: create.php");
    exit();
}

// Function untuk generate VA Number
function generateVANumber($payment_method) {
    $prefix = '';
    
    switch ($payment_method) {
        case 'bca_va':
            $prefix = '812345';
            break;
        case 'bni_va':
            $prefix = '881234';
            break;
        case 'bri_va':
            $prefix = '888123';
            break;
        default:
            $prefix = '800000';
    }
    
    // Generate random number untuk bagian belakang
    $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    return $prefix . $random;
}

// Function untuk generate QR Code (simulasi)
function generateQRCode($amount, $customer_name) {
    // Dalam implementasi real, ini akan generate QR code image
    // Untuk simulasi, kita return data JSON
    $qr_data = [
        'amount' => $amount,
        'merchant' => 'Luxury Hotel',
        'customer' => $customer_name,
        'timestamp' => time()
    ];
    
    return json_encode($qr_data);
}
?>
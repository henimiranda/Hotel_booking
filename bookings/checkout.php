<?php
include '../koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: index.php?error=ID booking tidak ditemukan!");
    exit();
}

$booking_id = $_GET['id'];

// Mulai transaction
mysqli_begin_transaction($koneksi);

try {
    // 1. Dapatkan room_id dari booking
    $query_get_room = "SELECT room_id FROM bookings WHERE id = $booking_id";
    $result = mysqli_query($koneksi, $query_get_room);
    $booking = mysqli_fetch_assoc($result);
    $room_id = $booking['room_id'];
    
    // 2. Update status booking jadi 'checked_out'
    $query_booking = "UPDATE bookings SET status = 'checked_out' WHERE id = $booking_id";
    
    if (!mysqli_query($koneksi, $query_booking)) {
        throw new Exception("Error update booking: " . mysqli_error($koneksi));
    }
    
    // 3. Update status kamar kembali jadi 'available'
    $query_room = "UPDATE rooms SET status = 'available' WHERE id = $room_id";
    
    if (!mysqli_query($koneksi, $query_room)) {
        throw new Exception("Error update room: " . mysqli_error($koneksi));
    }
    
    // Commit transaction
    mysqli_commit($koneksi);
    
    header("Location: index.php?message=Check-out berhasil dilakukan! Kamar sekarang tersedia lagi.");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction jika error
    mysqli_rollback($koneksi);
    header("Location: index.php?error=" . $e->getMessage());
    exit();
}
?>
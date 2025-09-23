<?php
include '../koneksi.php';

// Cek apakah form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $room_number = $_POST['room_number'];
    $room_type = $_POST['room_type'];
    $price_per_night = $_POST['price_per_night'];
    $status = $_POST['status'];
    
    // Validasi sederhana
    if (empty($room_number) || empty($room_type) || empty($price_per_night)) {
        die("Error: Semua field harus diisi!");
    }
    
    // Query untuk insert data
    $query = "INSERT INTO rooms (room_number, room_type, price_per_night, status) 
              VALUES ('$room_number', '$room_type', $price_per_night, '$status')";
    
    // Eksekusi query
    if (mysqli_query($koneksi, $query)) {
        // Redirect ke halaman daftar kamar jika sukses
        header("Location: index.php?message=Kamar berhasil ditambahkan");
        exit();
    } else {
        // Tampilkan error jika gagal
        die("Error: " . mysqli_error($koneksi));
    }
} else {
    // Jika akses langsung tanpa submit form, redirect ke halaman create
    header("Location: create.php");
    exit();
}
?>
<?php
$host = "localhost"; // Host database (biasanya localhost)
$username = "root"; // Username database (biasanya root)
$password = ""; // Password database (biasanya kosong di XAMPP)
$database = "hotel_booking_db"; // Nama database yang kamu buat

// Membuat koneksi
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset untuk menghindari error karakter khusus
mysqli_set_charset($koneksi, "utf8");
?>
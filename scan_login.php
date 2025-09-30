<?php
// scan_login.php
session_start();

// Ganti token ini dengan string panjang yang hanya kamu tahu
define('SCAN_TOKEN', 'k0ksiK3rit@Scan2025!'); 

// jika token benar, set session sebagai user customer1
if (isset($_GET['scan_token']) && $_GET['scan_token'] === SCAN_TOKEN) {
    // Sesuaikan dengan struktur user di aplikasi-mu
    // Contoh: user id 2 adalah customer1
    $_SESSION['user_id']   = 2;
    $_SESSION['username']  = 'customer1';
    $_SESSION['role']      = 'customer';
    $_SESSION['full_name'] = 'Customer Demo';

    // Redirect ke halaman yang ingin discan setelah login
    header('Location: dashboard_customers.php'); // ganti sesuai route tujuan
    exit;
}

// Jika token salah atau tidak ada, tampilkan pesan
http_response_code(403);
echo "Forbidden: access only allowed with valid scan_token.";

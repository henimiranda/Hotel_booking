<?php
include '../koneksi.php';

// Cek apakah ID ada di URL
if (!isset($_GET['id'])) {
    header("Location: index.php?error=ID kamar tidak ditemukan!");
    exit();
}

$id = $_GET['id'];

// Query delete
$query = "DELETE FROM rooms WHERE id = $id";

if (mysqli_query($koneksi, $query)) {
    header("Location: index.php?message=Data kamar berhasil dihapus");
    exit();
} else {
    header("Location: index.php?error=Error: " . mysqli_error($koneksi));
    exit();
}
?>
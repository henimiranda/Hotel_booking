<?php
include '../koneksi.php';

// Cek apakah form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id = $_POST['id'];
    $room_number = $_POST['room_number'];
    $room_type = $_POST['room_type'];
    $price_per_night = $_POST['price_per_night'];
    $status = $_POST['status'];
    
    // Validasi
    if (empty($room_number) || empty($room_type) || empty($price_per_night)) {
        header("Location: edit.php?id=$id&error=Semua field harus diisi!");
        exit();
    }
    
    // Query update
    $query = "UPDATE rooms SET 
              room_number = '$room_number', 
              room_type = '$room_type', 
              price_per_night = $price_per_night, 
              status = '$status' 
              WHERE id = $id";
    
    // Eksekusi query
    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php?message=Data kamar berhasil diupdate");
        exit();
    } else {
        header("Location: edit.php?id=$id&error=Error: " . mysqli_error($koneksi));
        exit();
    }
} else {
    // Jika akses langsung tanpa submit form
    header("Location: index.php");
    exit();
}
?>
<?php
include 'koneksi.php';

// Data sample kamar
$sample_rooms = [
    ['101', 'Standard', 500000, 'available'],
    ['102', 'Standard', 500000, 'available'],
    ['201', 'Deluxe', 750000, 'available'],
    ['202', 'Deluxe', 750000, 'booked'],
    ['301', 'Suite', 1200000, 'available']
];

foreach ($sample_rooms as $room) {
    $room_number = $room[0];
    $room_type = $room[1];
    $price = $room[2];
    $status = $room[3];
    
    $query = "INSERT INTO rooms (room_number, room_type, price_per_night, status) 
              VALUES ('$room_number', '$room_type', $price, '$status')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "Kamar $room_number berhasil ditambahkan<br>";
    } else {
        echo "Error: " . mysqli_error($koneksi) . "<br>";
    }
}

echo "<h3>Data sample berhasil dimasukkan!</h3>";
echo "<a href='rooms/index.php'>Lihat Daftar Kamar</a>";
?>
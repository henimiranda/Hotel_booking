<?php
session_start();
include("../koneksi.php");

// Ambil semua booking dengan join ke tabel rooms
$query = "
    SELECT b.id, b.customer_name, b.customer_email, b.customer_phone, 
           b.check_in, b.check_out, b.total_price, b.guest_count,
           r.room_number, r.room_type, r.price_per_night, r.status AS room_status
    FROM bookings b
    LEFT JOIN rooms r ON b.room_id = r.id
    ORDER BY b.id DESC
";

$result = mysqli_query($koneksi, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Semua Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2>Daftar Semua Booking</h2>
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>Kode</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Kamar</th>
                <th>Tipe</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Total</th>
                <th>Tamu</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>#<?= str_pad($row['id'], 5, "0", STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['customer_email']) ?></td>
                <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                <td><?= $row['room_number'] ?></td>
                <td><?= $row['room_type'] ?></td>
                <td><?= $row['check_in'] ?></td>
                <td><?= $row['check_out'] ?></td>
                <td>Rp <?= number_format($row['total_price'], 0, ',', '.') ?></td>
                <td><?= $row['guest_count'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

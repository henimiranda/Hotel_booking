<?php
session_start();
include '../koneksi.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../customer_dashboard.php");
    exit();
}
?>

<!-- Rest of your code -->


<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pemesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Daftar Pemesanan Kamar</h2>
        
        <!-- Tombol Tambah Pemesanan -->
        <a href="create.php" class="btn btn-primary mb-3">Buat Pemesanan Baru</a>
        
        <!-- Pesan Notifikasi -->
        <?php
        if (isset($_GET['message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
            echo $_GET['message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo $_GET['error'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
        }
        ?>
        
        <!-- Tabel Daftar Pemesanan -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kamar</th>
                    <th>Pelanggan</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query dengan JOIN ke tabel rooms
                $query = "SELECT b.*, r.room_number, r.room_type 
                          FROM bookings b 
                          JOIN rooms r ON b.room_id = r.id 
                          ORDER BY b.booking_date DESC";
                $result = mysqli_query($koneksi, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Tentukan badge warna berdasarkan status
                        $status_badge = [
                            'confirmed' => 'bg-primary',
                            'checked_in' => 'bg-success', 
                            'checked_out' => 'bg-secondary',
                            'cancelled' => 'bg-danger'
                        ];
                        
                        $status_text = [
                            'confirmed' => 'Dikonfirmasi',
                            'checked_in' => 'Check-in',
                            'checked_out' => 'Check-out',
                            'cancelled' => 'Dibatalkan'
                        ];
                        
                        $badge_color = $status_badge[$row['status']];
                        $status_display = $status_text[$row['status']];
                        
                        echo "<tr>";
                        echo "<td>" . $no . "</td>";
                        echo "<td>" . $row['room_number'] . " (" . $row['room_type'] . ")</td>";
                        echo "<td>" . $row['customer_name'] . "<br><small>" . $row['customer_phone'] . "</small></td>";
                        echo "<td>" . date('d M Y', strtotime($row['check_in'])) . "</td>";
                        echo "<td>" . date('d M Y', strtotime($row['check_out'])) . "</td>";
                        echo "<td>Rp " . number_format($row['total_price'], 0, ',', '.') . "</td>";
                        echo "<td><span class='badge $badge_color'>$status_display</span></td>";
                        echo "<td>";
                        
                        // Tombol aksi berdasarkan status
                        if ($row['status'] == 'confirmed') {
                            echo "<a href='checkin.php?id=" . $row['id'] . "' class='btn btn-success btn-sm'>Check-in</a> ";
                        } elseif ($row['status'] == 'checked_in') {
                            echo "<a href='checkout.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>Check-out</a> ";
                        }
                        
                        echo "<a href='cancel.php?id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin batalkan pemesanan?\")'>Batal</a>";
                        echo "</td>";
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>Belum ada data pemesanan</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <a href="../index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
</body>
</html>
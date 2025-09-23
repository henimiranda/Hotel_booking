<?php
include '../koneksi.php';

// Ambil ID kamar dari URL
if (!isset($_GET['id'])) {
    die("Error: ID kamar tidak ditemukan!");
}

$id = $_GET['id'];

// Ambil data kamar berdasarkan ID
$query = "SELECT * FROM rooms WHERE id = $id";
$result = mysqli_query($koneksi, $query);
$room = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$room) {
    die("Error: Kamar tidak ditemukan!");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Kamar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Data Kamar</h2>
        
        <form action="process_update.php" method="POST">
            <!-- Input hidden untuk menyimpan ID -->
            <input type="hidden" name="id" value="<?php echo $room['id']; ?>">
            
            <div class="mb-3">
                <label for="room_number" class="form-label">Nomor Kamar</label>
                <input type="text" class="form-control" id="room_number" name="room_number" 
                       value="<?php echo $room['room_number']; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="room_type" class="form-label">Tipe Kamar</label>
                <select class="form-select" id="room_type" name="room_type" required>
                    <option value="">Pilih Tipe Kamar</option>
                    <option value="Standard" <?php echo ($room['room_type'] == 'Standard') ? 'selected' : ''; ?>>Standard</option>
                    <option value="Deluxe" <?php echo ($room['room_type'] == 'Deluxe') ? 'selected' : ''; ?>>Deluxe</option>
                    <option value="Suite" <?php echo ($room['room_type'] == 'Suite') ? 'selected' : ''; ?>>Suite</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="price_per_night" class="form-label">Harga per Malam</label>
                <input type="number" class="form-control" id="price_per_night" name="price_per_night" 
                       value="<?php echo $room['price_per_night']; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="available" <?php echo ($room['status'] == 'available') ? 'selected' : ''; ?>>Tersedia</option>
                    <option value="booked" <?php echo ($room['status'] == 'booked') ? 'selected' : ''; ?>>Dipesan</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Kamar</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>
<?php include '../koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kamar Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Tambah Kamar Baru</h2>
        
        <form action="process_create.php" method="POST">
            <div class="mb-3">
                <label for="room_number" class="form-label">Nomor Kamar</label>
                <input type="text" class="form-control" id="room_number" name="room_number" required>
            </div>
            
            <div class="mb-3">
                <label for="room_type" class="form-label">Tipe Kamar</label>
                <select class="form-select" id="room_type" name="room_type" required>
                    <option value="">Pilih Tipe Kamar</option>
                    <option value="Standard">Standard</option>
                    <option value="Deluxe">Deluxe</option>
                    <option value="Suite">Suite</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="price_per_night" class="form-label">Harga per Malam</label>
                <input type="number" class="form-control" id="price_per_night" name="price_per_night" required>
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="available">Tersedia</option>
                    <option value="booked">Dipesan</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Kamar</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>
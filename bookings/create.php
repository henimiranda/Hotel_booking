<?php
session_start();
include '../koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Jika admin, redirect ke dashboard admin
if ($_SESSION['role'] == 'admin') {
    header("Location: ../index.php");
    exit();
}

// Jika ada parameter room_id, ambil data kamar
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$room = null;

if ($room_id > 0) {
    $query_room = "SELECT * FROM rooms WHERE id = $room_id AND status = 'available'";
    $result_room = mysqli_query($koneksi, $query_room);
    $room = mysqli_fetch_assoc($result_room);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Pemesanan - Luxury Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e8f5e8 0%, #ffffff 100%);
            min-height: 100vh;
        }
        .booking-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .step {
            text-align: center;
            padding: 10px 20px;
            margin: 5px;
            background: #f8f9fa;
            border-radius: 20px;
            font-weight: 600;
            flex: 1;
            max-width: 200px;
        }
        .step.active {
            background: #4CAF50;
            color: white;
        }
        .step-number {
            background: #6c757d;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        .step.active .step-number {
            background: white;
            color: #4CAF50;
        }
        .room-card {
            border: 2px solid #4CAF50;
            border-radius: 15px;
            background: white;
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #4CAF50;
            background: #f8fff8;
        }
        .payment-method.selected {
            border-color: #4CAF50;
            background: #f0fff0;
        }
        .btn-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);">
        <div class="container">
            <a class="navbar-brand" href="../customer_dashboard.php">
                <i class="fas fa-hotel me-2"></i>
                <strong>Luxury Hotel</strong>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Halo, <?php echo $_SESSION['full_name']; ?></span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Booking Steps -->
        <div class="booking-steps">
            <div class="step active">
                <span class="step-number">1</span>
                Pilih Kamar & Tanggal
            </div>
            <div class="step">
                <span class="step-number">2</span>
                Data Pemesan
            </div>
            <div class="step">
                <span class="step-number">3</span>
                Pembayaran
            </div>
            <div class="step">
                <span class="step-number">4</span>
                Konfirmasi
            </div>
        </div>

        <h2 class="text-center mb-4">Buat Pemesanan Kamar</h2>

        <!-- Error Message -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="process_booking.php" method="POST" id="bookingForm" onsubmit="return validateForm()">
            <!-- Step 1: Pilih Kamar & Tanggal -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Step 1: Pilih Kamar & Tanggal Menginap</h5>
                </div>
                <div class="card-body">
                    <?php if ($room): ?>
                        <!-- Tampilkan kamar yang dipilih -->
                        <div class="room-card p-3 mb-3">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5>Kamar <?php echo $room['room_number']; ?> - <?php echo $room['room_type']; ?></h5>
                                    <p class="text-muted">Rp <?php echo number_format($room['price_per_night'], 0, ',', '.'); ?> / malam</p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-success">Tersedia</span>
                                </div>
                            </div>
                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                        </div>
                    <?php else: ?>
                        <!-- Pilih kamar dari dropdown -->
                        <div class="mb-3">
                            <label for="room_id" class="form-label">Pilih Kamar <span class="text-danger">*</span></label>
                            <select class="form-select" id="room_id" name="room_id" required onchange="updateRoomPrice()">
                                <option value="">Pilih Kamar yang Tersedia</option>
                                <?php
                                $query_rooms = "SELECT * FROM rooms WHERE status = 'available' ORDER BY room_number";
                                $result_rooms = mysqli_query($koneksi, $query_rooms);
                                while ($room_opt = mysqli_fetch_assoc($result_rooms)) {
                                    echo "<option value='{$room_opt['id']}' data-price='{$room_opt['price_per_night']}'>";
                                    echo "Kamar {$room_opt['room_number']} - {$room_opt['room_type']} (Rp " . number_format($room_opt['price_per_night'], 0, ',', '.') . ")";
                                    echo "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="check_in" class="form-label">Tanggal Check-in <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="check_in" name="check_in" required 
                                       min="<?php echo date('Y-m-d'); ?>" onchange="calculateTotal()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="check_out" class="form-label">Tanggal Check-out <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="check_out" name="check_out" required 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" onchange="calculateTotal()">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Harga per Malam</label>
                                <input type="text" class="form-control" id="display_price" readonly>
                                <input type="hidden" id="hidden_price" name="price_per_night">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jumlah Malam</label>
                                <input type="text" class="form-control" id="night_count" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Data Pemesan -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Step 2: Data Pemesan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                       value="<?php echo $_SESSION['full_name']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_phone" name="customer_phone" 
                                       placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guest_count" class="form-label">Jumlah Tamu <span class="text-danger">*</span></label>
                                <select class="form-select" id="guest_count" name="guest_count" required>
                                    <option value="1">1 Orang</option>
                                    <option value="2" selected>2 Orang</option>
                                    <option value="3">3 Orang</option>
                                    <option value="4">4 Orang</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="special_request" class="form-label">Permintaan Khusus (Opsional)</label>
                        <textarea class="form-control" id="special_request" name="special_request" rows="3" 
                                  placeholder="Contoh: Tambah bed, makanan khusus, dll."></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 3: Pembayaran -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Step 3: Metode Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="payment-method" onclick="selectPayment('bca_va')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="bca_va" id="bca_va" required>
                                    <label class="form-check-label" for="bca_va">
                                        <i class="fas fa-university me-2"></i>
                                        <strong>Transfer Bank BCA</strong>
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">Virtual Account BCA</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method" onclick="selectPayment('bni_va')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="bni_va" id="bni_va" required>
                                    <label class="form-check-label" for="bni_va">
                                        <i class="fas fa-university me-2"></i>
                                        <strong>Transfer Bank BNI</strong>
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">Virtual Account BNI</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="payment-method" onclick="selectPayment('bri_va')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="bri_va" id="bri_va" required>
                                    <label class="form-check-label" for="bri_va">
                                        <i class="fas fa-university me-2"></i>
                                        <strong>Transfer Bank BRI</strong>
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">Virtual Account BRI</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method" onclick="selectPayment('qris')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="qris" id="qris" required>
                                    <label class="form-check-label" for="qris">
                                        <i class="fas fa-qrcode me-2"></i>
                                        <strong>QRIS</strong>
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">Scan QR Code</p>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Metode Pembayaran -->
                    <div id="paymentPreview" class="mt-4" style="display: none;">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Preview Pembayaran</h6>
                            </div>
                            <div class="card-body">
                                <div id="vaPreview" style="display: none;">
                                    <p>Anda akan melakukan pembayaran melalui <strong id="bankName">Bank</strong></p>
                                    <div class="alert alert-info">
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            Virtual Account number akan ditampilkan setelah Anda mengklik "Lanjutkan ke Pembayaran"
                                        </small>
                                    </div>
                                </div>
                                <div id="qrisPreview" style="display: none;">
                                    <p>Anda akan melakukan pembayaran melalui <strong>QRIS</strong></p>
                                    <div class="alert alert-info">
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            QR Code akan ditampilkan setelah Anda mengklik "Lanjutkan ke Pembayaran"
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Pembayaran -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h4>Total Pembayaran</h4>
                    <h1 class="text-success" id="total_price">Rp 0</h1>
                    <input type="hidden" id="hidden_total" name="total_price">
                    <p class="text-muted">*Termasuk pajak dan service charge</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-success btn-lg px-5" id="submitBtn">
                    <i class="fas fa-credit-card me-2"></i>Lanjutkan ke Pembayaran
                </button>
                <a href="../customer_dashboard.php" class="btn btn-outline-secondary ms-2">Batal</a>
            </div>
        </form>
    </div>

    <script>
    // Fungsi untuk update harga kamar
    function updateRoomPrice() {
        const roomSelect = document.getElementById('room_id');
        const priceField = document.getElementById('display_price');
        const hiddenPrice = document.getElementById('hidden_price');
        
        if (roomSelect.value) {
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            priceField.value = 'Rp ' + parseInt(price).toLocaleString('id-ID');
            hiddenPrice.value = price;
            calculateTotal();
        } else {
            priceField.value = '';
            hiddenPrice.value = '';
        }
    }

    // Fungsi untuk menghitung total
    function calculateTotal() {
        const checkin = new Date(document.getElementById('check_in').value);
        const checkout = new Date(document.getElementById('check_out').value);
        const pricePerNight = parseFloat(document.getElementById('hidden_price').value) || 0;
        
        // Validasi tanggal
        if (checkin && checkout && checkin < checkout) {
            const timeDiff = checkout.getTime() - checkin.getTime();
            const nightCount = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            document.getElementById('night_count').value = nightCount + ' malam';
            
            const totalPrice = nightCount * pricePerNight;
            document.getElementById('total_price').textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
            document.getElementById('hidden_total').value = totalPrice;
            
            // Enable/disable submit button berdasarkan validasi
            validateSubmitButton();
        } else {
            document.getElementById('night_count').value = '';
            document.getElementById('total_price').textContent = 'Rp 0';
            document.getElementById('hidden_total').value = '';
            validateSubmitButton();
        }
    }

    // Fungsi untuk memilih metode pembayaran
    function selectPayment(method) {
        console.log('Metode dipilih:', method);
        
        // Remove selected class dari semua payment method
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
        });
        
        // Add selected class ke method yang dipilih
        const selectedElement = document.querySelector(`[onclick="selectPayment('${method}')"]`);
        if (selectedElement) {
            selectedElement.classList.add('selected');
        }
        
        // Check radio button
        const radioButton = document.getElementById(method);
        if (radioButton) {
            radioButton.checked = true;
        }
        
        // Show payment preview
        showPaymentPreview(method);
        validateSubmitButton();
    }

    // Fungsi untuk menampilkan preview pembayaran
    function showPaymentPreview(method) {
        const paymentPreview = document.getElementById('paymentPreview');
        const vaPreview = document.getElementById('vaPreview');
        const qrisPreview = document.getElementById('qrisPreview');
        const bankName = document.getElementById('bankName');
        
        // Show preview container
        paymentPreview.style.display = 'block';
        
        // Hide semua preview terlebih dahulu
        vaPreview.style.display = 'none';
        qrisPreview.style.display = 'none';
        
        // Tampilkan preview berdasarkan metode
        if (method.includes('_va')) {
            let bank = '';
            switch(method) {
                case 'bca_va': bank = 'BCA'; break;
                case 'bni_va': bank = 'BNI'; break;
                case 'bri_va': bank = 'BRI'; break;
            }
            bankName.textContent = bank;
            vaPreview.style.display = 'block';
        } else if (method === 'qris') {
            qrisPreview.style.display = 'block';
        }
    }

    // Fungsi untuk validasi form sebelum submit
    function validateForm() {
        const roomId = document.getElementById('room_id').value;
        const checkIn = document.getElementById('check_in').value;
        const checkOut = document.getElementById('check_out').value;
        const totalPrice = document.getElementById('hidden_total').value;
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        
        if (!roomId) {
            alert('Silakan pilih kamar terlebih dahulu!');
            return false;
        }
        
        if (!checkIn || !checkOut) {
            alert('Silakan pilih tanggal check-in dan check-out!');
            return false;
        }
        
        if (new Date(checkIn) >= new Date(checkOut)) {
            alert('Tanggal check-out harus setelah tanggal check-in!');
            return false;
        }
        
        if (!totalPrice || totalPrice == 0) {
            alert('Total harga tidak valid!');
            return false;
        }
        
        if (!paymentMethod) {
            alert('Silakan pilih metode pembayaran!');
            return false;
        }
        
        return true;
    }

    // Fungsi untuk enable/disable submit button
    function validateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        const roomId = document.getElementById('room_id').value;
        const checkIn = document.getElementById('check_in').value;
        const checkOut = document.getElementById('check_out').value;
        const totalPrice = document.getElementById('hidden_total').value;
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        
        const isValid = roomId && checkIn && checkOut && totalPrice && totalPrice > 0 && paymentMethod;
        
        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-disabled');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('btn-disabled');
        }
    }

    // Initialize ketika halaman load
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners untuk payment methods
        document.querySelectorAll('.payment-method').forEach(element => {
            element.addEventListener('click', function() {
                const radioInput = this.querySelector('input[type="radio"]');
                if (radioInput) {
                    selectPayment(radioInput.value);
                }
            });
        });
        
        // Add event listeners untuk radio buttons
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    selectPayment(this.value);
                }
            });
        });
        
        // Initialize jika room sudah dipilih dari halaman sebelumnya
        <?php if ($room): ?>
        document.getElementById('display_price').value = 'Rp <?php echo number_format($room['price_per_night'], 0, ',', '.'); ?>';
        document.getElementById('hidden_price').value = '<?php echo $room['price_per_night']; ?>';
        calculateTotal();
        <?php endif; ?>
        
        // Validasi awal
        validateSubmitButton();
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
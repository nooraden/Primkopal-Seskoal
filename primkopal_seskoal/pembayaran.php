<?php
session_start();
require_once 'config/database.php';

// Proteksi halaman: Jika belum login atau keranjang kosong, lempar ke index
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

// 1. HITUNG TOTAL BELANJA
$total_belanja = 0;
foreach ($_SESSION['cart'] as $product_id => $qty) {
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = :id");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        $total_belanja += $product['price'] * $qty;
    }
}

// 2. LOGIKA PEMROSESAN (Hanya jalan jika tombol 'proses_bayar' diklik)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proses_bayar'])) {
    $user_id = $_SESSION['user_id'];
    $method = $_POST['payment_method'];
    $status = ($method == 'cod') ? 'dikemas' : 'pending';
    $bukti_nama = null;

    // Proses Upload Bukti jika Transfer
    if ($method != 'cod') {
        $input_name = ($method == 'transfer_bca') ? 'bukti_bca' : 'bukti_bri';
        
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
            $target_dir = "assets/images/bukti_transfer/"; 
            
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_ext = pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION);
            $bukti_nama = "BUKTI_" . time() . "_" . $user_id . "." . $file_ext;
            
            move_uploaded_file($_FILES[$input_name]['tmp_name'], $target_dir . $bukti_nama);
        }
    }

    try {
        $conn->beginTransaction();

        // Simpan ke tabel orders
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status, created_at, payment_method, payment_proof) 
                               VALUES (?, ?, ?, NOW(), ?, ?)");
        $stmt->execute([$user_id, $total_belanja, $status, $method, $bukti_nama]);
        
        $order_id = $conn->lastInsertId();

        $msg = "Ada pesanan baru dengan ID #" . $order_id;
        $stmt_notif = $conn->prepare("INSERT INTO notifications (order_id, message) VALUES (?, ?)");
        $stmt_notif->execute([$order_id, $msg]);

        // Simpan ke tabel order_items (Detail Barang)
        foreach ($_SESSION['cart'] as $product_id => $qty) {
            $stmt_p = $conn->prepare("SELECT price FROM products WHERE id = ?");
            $stmt_p->execute([$product_id]);
            $p = $stmt_p->fetch();

            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt_item->execute([$order_id, $product_id, $qty, $p['price']]);
        }

        $conn->commit();

        // Bersihkan keranjang
        unset($_SESSION['cart']);
        
        // Redirect ke pesanan_user.php
        echo "<script>alert('Pesanan berhasil dibuat!'); window.location='pesanan_user.php';</script>";
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        die("Gagal menyimpan pesanan: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Primkopal Seskoal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container { max-width: 800px; margin: 40px auto; padding: 20px; font-family: 'Poppins', sans-serif; }
        .payment-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .total-section { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .payment-option { border: 2px solid #eee; border-radius: 8px; padding: 15px; margin-bottom: 15px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; }
        .payment-option:hover, .payment-option.selected { border-color: #2e7d32; background-color: #f1f8e9; }
        .payment-logo { font-size: 2rem; margin-right: 15px; color: #555; width: 50px; text-align: center; }
        .bank-details { display: none; margin-top: 15px; padding: 10px; text-align: center; border-top: 1px solid #eee; width: 100%; }
        .upload-section { margin-top: 15px; padding: 15px; background: #fdfdfd; border: 1px dashed #2e7d32; border-radius: 8px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .btn-confirm { background: #2e7d32; color: white; border: none; padding: 15px; width: 100%; border-radius: 5px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 20px; }
        .btn-confirm:hover { background: #1b5e20; }
    </style>
</head>
<body>

<div class="container">
    <div class="payment-card">
        <div class="total-section">
            <h3>Total Pembayaran</h3>
            <h1 style="color: #2e7d32;">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></h1>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" id="payment-form">
            <h4 style="margin-bottom: 20px;">Pilih Metode Pembayaran:</h4>

            <div class="payment-option" id="opt-cod" onclick="selectPayment('cod')">
                <input type="radio" name="payment_method" id="radio-cod" value="cod" required>
                <div class="payment-logo"><i class="fas fa-hand-holding-usd"></i></div>
                <div>
                    <strong>Bayar Ditempat (COD)</strong>
                    <div style="font-size: 0.9rem; color: #777;">Bayar tunai saat kurir sampai di rumah Anda.</div>
                </div>
            </div>

            <div class="payment-option" id="opt-bca" onclick="selectPayment('bca')">
                <input type="radio" name="payment_method" id="radio-bca" value="transfer_bca">
                <div class="payment-logo"><i class="fas fa-university"></i></div>
                <div>
                    <strong>Transfer Bank BCA</strong>
                    <div class="bank-details" id="detail-bca">
                        <p>A/N: Toko Kelontong<br>No. Rek: 123-456-7890</p>
                        <div class="upload-section">
                            <label style="color:red; font-weight:bold;">Unggah Bukti Transfer (Wajib):</label>
                            <input type="file" name="bukti_bca" id="file-bca" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <div class="payment-option" id="opt-bri" onclick="selectPayment('bri')">
                <input type="radio" name="payment_method" id="radio-bri" value="transfer_bri">
                <div class="payment-logo"><i class="fas fa-university"></i></div>
                <div>
                    <strong>Transfer Bank BRI</strong>
                    <div class="bank-details" id="detail-bri">
                        <p>A/N: Toko Kelontong<br>No. Rek: 0000-1111-2222</p>
                        <div class="upload-section">
                            <label style="color:red; font-weight:bold;">Unggah Bukti Transfer (Wajib):</label>
                            <input type="file" name="bukti_bri" id="file-bri" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" name="proses_bayar" class="btn-confirm">Bayar Sekarang & Selesaikan Pesanan</button>
        </form>
    </div>
</div>

<script>
function selectPayment(id) {
    // Reset semua pilihan
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.bank-details').forEach(el => el.style.display = 'none');
    
    // Reset required file inputs
    document.getElementById('file-bca').required = false;
    document.getElementById('file-bri').required = false;
    
    // Aktifkan pilihan yang diklik
    document.getElementById('opt-'+id).classList.add('selected');
    document.getElementById('radio-'+id).checked = true;
    
    // Jika transfer, tampilkan upload dan set required
    if(id === 'bca' || id === 'bri') {
        document.getElementById('detail-'+id).style.display = 'block';
        document.getElementById('file-'+id).required = true;
    }
}
</script>
</body>
</html>
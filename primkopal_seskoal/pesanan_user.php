<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// QUERY DIPERBAIKI: Menambahkan kolom yang mungkin dibutuhkan nanti
$stmt = $conn->prepare("
    SELECT id, total_price, payment_method, status, created_at, address, phone_number
    FROM orders
    WHERE user_id = :user_id
    ORDER BY created_at DESC
");
$stmt->execute([':user_id'=>$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section style="padding:50px 10%; background:#f4f6f9; min-height:80vh;">
    <div class="section-title" style="text-align:center; margin-bottom:30px;">
        <h2 style="color:#2e7d32;">Pesanan Saya</h2>
        <p>Daftar riwayat pesanan Anda di Primkopal</p>
    </div>

    <div class="card" style="background:white; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); overflow-x:auto;">
        <table width="100%" style="border-collapse: collapse; text-align: left; min-width:600px;">
            <thead>
                <tr style="background:#2e7d32; color:white;">
                    <th style="padding:15px;">ID Pesanan</th>
                    <th style="padding:15px;">Produk</th>
                    <th style="padding:15px;">Total Bayar</th>
                    <th style="padding:15px;">Metode</th>
                    <th style="padding:15px;">Status</th>
                    <th style="padding:15px;">Waktu Transaksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($orders) > 0): ?>
                    <?php foreach($orders as $order): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:15px; font-weight:bold;">#<?php echo $order['id']; ?></td>

                        <td style="padding:15px;">
                            <ul style="margin:0; padding-left:15px; font-size:0.85rem; color:#555;">
                            <?php
                                $stmt_items = $conn->prepare("
                                    SELECT p.name, oi.quantity 
                                    FROM order_items oi
                                    JOIN products p ON oi.product_id = p.id
                                    WHERE oi.order_id = :order_id
                                ");
                                $stmt_items->execute([':order_id' => $order['id']]);
                                $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach($items as $item) {
                                    echo "<li>" . htmlspecialchars($item['name']) . " <strong>(x" . $item['quantity'] . ")</strong></li>";
                                }
                            ?>
                            </ul>
                        </td>

                        <td style="padding:15px; font-weight:bold; color:#2e7d32;">
                            Rp <?php echo number_format($order['total_price'],0,',','.'); ?>
                        </td>

                        <td style="padding:15px; font-size:0.85rem;">
                            <?php echo strtoupper(str_replace('_',' ',$order['payment_method'])); ?>
                        </td>

                        <td style="padding:15px;">
                            <?php 
    $status = $order['status'];
    $bg = '#fff3cd'; $cl = '#856404'; // Default Pending (Kuning)

    if($status == 'dikemas') { 
        $bg = '#d1ecf1'; $cl = '#0c5460'; // Biru Muda
    } elseif($status == 'dikirim') { 
        $bg = '#cfe2ff'; $cl = '#084298'; // Biru Tua
    } elseif($status == 'completed') { 
        $bg = '#d4edda'; $cl = '#155724'; // Hijau
    } elseif($status == 'cancelled' || $status == 'ditolak') { 
        $bg = '#f8d7da'; $cl = '#721c24'; // Merah
    }
?>
<span style="padding:5px 12px; border-radius:20px; font-size:0.75rem; font-weight:bold; background:<?php echo $bg; ?>; color:<?php echo $cl; ?>;">
    <?php echo ucfirst($status); ?>
</span>
                        </td>

                        <td style="padding:15px; font-size:0.8rem; color:#888;">
                            <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; color:#999;">
                            <i class="fas fa-shopping-basket" style="font-size:2rem; display:block; margin-bottom:10px;"></i>
                            Belum ada riwayat pesanan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
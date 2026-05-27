<?php
session_start();
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
    // Update agar status unread (0) menjadi read (1) karena admin sudah membuka detailnya
    $stmt_read = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE order_id = ?");
    $stmt_read->execute([$order_id]);
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Update Status Logic
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $order_id]);
    header("Location: pesanan.php?msg=updated");
    exit;
}

// Fetch Orders
$stmt = $conn->query("
    SELECT o.id, o.total_price, o.status, o.payment_method, o.payment_proof, o.created_at, u.full_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin Primkopal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #001f3f;
            --accent: #3498db;
            --bg-light: #f8f9fa;
            --text-dark: #333;
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body { background-color: var(--bg-light); display: flex; }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary);
            color: white;
            height: 100vh;
            position: fixed;
            padding: 30px 0;
            transition: all 0.3s;
        }

        .sidebar-brand {
            padding: 0 30px 30px;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-menu { list-style: none; }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: 0.3s;
            font-weight: 400;
        }

        .sidebar-menu a i { width: 30px; font-size: 1.1rem; }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left: 4px solid var(--accent);
        }

        /* --- CONTENT AREA --- */
        .content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 40px;
        }

        .header-title {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title h2 { color: var(--primary); font-weight: 700; }

        /* --- TABLE STYLE --- */
        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        table { width: 100%; border-collapse: collapse; }
        
        th {
            text-align: left;
            padding: 15px;
            background: #fdfdfd;
            border-bottom: 2px solid #f1f1f1;
            color: #777;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }

        td { padding: 15px; border-bottom: 1px solid #f1f1f1; font-size: 0.9rem; vertical-align: middle; }

       /* --- UI COMPONENTS STATUS --- */
.status-badge {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 700;
    display: inline-block;
    text-transform: uppercase;
}

/* Warna untuk masing-masing status */
.status-pending   { background: #fff3cd; color: #856404; }
.status-dikemas   { background: #d1ecf1; color: #0c5460; }
.status-dikirim   { background: #cfe2ff; color: #084298; }
.status-completed { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }
.status-ditolak   { background: #f8d7da; color: #721c24; }

        .badge-proof {
            background: #e9ecef;
            color: #495057;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: 0.2s;
        }
        .badge-proof:hover { background: #dee2e6; }

        .update-box {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .status-select {
            padding: 6px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 0.8rem;
        }

        .btn-update {
            background: var(--accent);
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
        }

        .btn-detail {
            background: #f1f1f1;
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .btn-detail:hover { background: #e2e2e2; }

        .alert {
            padding: 15px;
            background: #d4edda;
            color: #155724;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #28a745;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-anchor"></i> PRIMKOPAL
    </div>
    <div class="sidebar-menu">
        <a href="index.php"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="produk.php"><i class="fas fa-boxes"></i> Kelola Produk</a>
        <a href="pesanan.php" class="active"><i class="fas fa-shopping-bag"></i> Pesanan</a>
        <a href="pelanggan.php"><i class="fas fa-user-friends"></i> Pelanggan</a>
        <div style="margin-top: 50px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="../logout.php" style="color: #ff7675;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>
</div>

<div class="content">
    <div class="header-title">
        <h2>Daftar Pesanan</h2>
        <span style="color: #888; font-size: 0.9rem;">Admin / Pesanan</span>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert">
            <i class="fas fa-check-circle"></i> Status pesanan berhasil diperbarui!
        </div>
    <?php endif; ?>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Total Bayar</th>
                    <th>Metode</th>
                    <th>Bukti</th>
                    <th>Status & Perubahan</th>
                    <th>Opsi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $order): ?>
                <tr>
                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                    <td>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                        <small style="color: #999;"><?php echo date('d M Y', strtotime($order['created_at'])); ?></small>
                    </td>
                    <td style="font-weight: 600; color: #2c3e50;">
                        Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?>
                    </td>
                    <td>
                        <span style="font-size: 0.75rem; color: #555;">
                            <i class="fas fa-wallet"></i> <?php echo strtoupper(str_replace('_', ' ', $order['payment_method'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($order['payment_method'] !== 'cod' && !empty($order['payment_proof'])): ?>
                            <a href="../assets/images/bukti_transfer/<?php echo $order['payment_proof']; ?>" target="_blank" class="badge-proof">
                                <i class="fas fa-eye"></i> Cek Bukti
                            </a>
                        <?php elseif($order['payment_method'] === 'cod'): ?>
                            <span style="color: #aaa; font-style: italic;">N/A (COD)</span>
                        <?php else: ?>
                            <span style="color: #e74c3c; font-size: 0.75rem; font-weight: 600;">Belum Bayar</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo strtoupper($order['status']); ?>
                        </span>
                        
                        <form method="POST" action="pesanan.php" class="update-box">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" class="status-select">
                            <option value="pending" <?= $order['status']=='pending'?'selected':'' ?>>Pending</option>
                            <option value="dikemas" <?= $order['status']=='dikemas'?'selected':'' ?>>Dikemas</option>
                            <option value="dikirim" <?= $order['status']=='dikirim'?'selected':'' ?>>Dikirim</option>
                            <option value="completed" <?= $order['status']=='completed'?'selected':'' ?>>Selesai</option>
                            <option value="cancelled" <?= $order['status']=='cancelled'?'selected':'' ?>>Dibatalkan</option>
                            </select>
                            <button type="submit" name="update_status" class="btn-update">Simpan</button>
                        </form>
                    </td>
                    <td>
                        <a href="detail_pesanan.php?id=<?php echo $order['id']; ?>" class="btn-detail">Detail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
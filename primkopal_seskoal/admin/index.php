<?php
session_start();
require_once '../config/database.php';

// Authorization Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Stats Logic
try {
    // Total Products
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();

    // Total Pending Orders
    $stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $pending_orders = $stmt->fetchColumn();

    // Total Customers
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $total_customers = $stmt->fetchColumn();
    
    // Recent Orders
    $stmt = $conn->query("
        SELECT o.id, o.total_price, o.status, o.created_at, u.full_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC LIMIT 5
    ");
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Primkopal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #001f3f;
            --accent: #3498db;
            --bg-light: #f4f7f6;
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
            z-index: 100;
        }

        .sidebar-brand {
            padding: 0 30px 30px;
            font-size: 1.5rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar-menu a i { width: 30px; font-size: 1.1rem; }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left: 4px solid var(--accent);
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 40px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .welcome-text h3 { color: var(--primary); font-weight: 700; }
        .welcome-text p { color: #888; font-size: 0.9rem; }

        /* --- STATS CARDS --- */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
            gap: 25px; 
            margin-bottom: 40px; 
        }

        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
            display: flex; 
            align-items: center; 
            gap: 20px; 
            transition: transform 0.3s;
        }
        
        .stat-card:hover { transform: translateY(-5px); }

        .stat-icon { 
            width: 60px; height: 60px; 
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 1.6rem; 
        }

        .bg-products { background: #e3f2fd; color: #1976d2; }
        .bg-orders { background: #fff3e0; color: #f57c00; }
        .bg-users { background: #f3e5f5; color: #7b1fa2; }

        .stat-info h3 { font-size: 1.8rem; font-weight: 700; color: var(--primary); }
        .stat-info p { color: #888; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }

        /* --- TABLE & CARDS --- */
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #aaa; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f8f9fa; }
        td { padding: 15px; border-bottom: 1px solid #f8f9fa; font-size: 0.9rem; color: #444; }

        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }

        /* --- NOTIFICATIONS --- */
        .top-actions { display: flex; align-items: center; gap: 20px; }
        
        .notif-btn { 
            position: relative; 
            background: white; 
            width: 45px; height: 45px; 
            display: flex; align-items: center; justify-content: center; 
            border-radius: 10px; cursor: pointer;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .notification-badge {
            position: absolute; top: -5px; right: -5px;
            background: #ff4757; color: white;
            font-size: 0.65rem; padding: 3px 6px;
            border-radius: 50%; border: 2px solid white;
        }

        .notification-dropdown {
            position: absolute; right: 0; top: 55px;
            width: 320px; background: white;
            border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            display: none; z-index: 1000; overflow: hidden;
            border: 1px solid #eee;
        }
        .notification-dropdown.active { display: block; }

        .view-site-btn {
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #f8f9fa;
            text-decoration: none;
            display: block;
            color: #444;
            transition: 0.2s;
        }
        .notification-item:hover { background: #f0f7ff; }
        .notification-item.unread { border-left: 4px solid var(--accent); background: #fbfcfe; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-anchor"></i> PRIMKOPAL
    </div>
    <div class="sidebar-menu">
        <a href="index.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="produk.php"><i class="fas fa-boxes"></i> Kelola Produk</a>
        <a href="pesanan.php"><i class="fas fa-shopping-bag"></i> Pesanan</a>
        <a href="pelanggan.php"><i class="fas fa-user-friends"></i> Pelanggan</a>
        <div style="margin-top: 50px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="../logout.php" style="color: #ff7675;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <div class="welcome-text">
            <h3>Halo, <?php echo explode(' ', trim($_SESSION['full_name']))[0]; ?>! 👋</h3>
            <p>Berikut adalah ringkasan performa toko hari ini.</p>
        </div>

        <div class="top-actions">
            <div style="position: relative;">
                <div class="notif-btn" onclick="toggleNotifications()">
                    <i class="fas fa-bell" style="color: #555;"></i>
                    <span class="notification-badge" id="notif-badge">0</span>
                </div>
                <div class="notification-dropdown" id="notif-dropdown">
                    <div style="padding: 15px; background: #f8f9fa; border-bottom: 1px solid #eee; font-weight: 700; font-size: 0.9rem;">
                        Notifikasi Terbaru
                    </div>
                    <div class="notification-list" id="notif-list">
                        </div>
                </div>
            </div>

            <a href="../index.php" target="_blank" class="view-site-btn">
                <i class="fas fa-external-link-alt"></i> Lihat Toko
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-products"><i class="fas fa-box-open"></i></div>
            <div class="stat-info">
                <p>Produk</p>
                <h3><?php echo $total_products; ?></h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-orders"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info">
                <p>Pesanan Baru</p>
                <h3><?php echo $pending_orders; ?></h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-users"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <p>Pelanggan</p>
                <h3><?php echo $total_customers; ?></h3>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 style="color: var(--primary);">Pesanan Terakhir</h4>
            <a href="pesanan.php" style="text-decoration: none; color: var(--accent); font-size: 0.85rem; font-weight: 600;">Lihat Semua <i class="fas fa-arrow-right"></i></a>
        </div>

        <?php if(count($recent_orders) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recent_orders as $order): ?>
                <tr>
                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                    <td style="font-weight: 600;"><?php echo htmlspecialchars($order['full_name']); ?></td>
                    <td style="font-weight: 700; color: #2d3436;">Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo strtoupper($order['status']); ?>
                        </span>
                    </td>
                    <td style="color: #888;"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #aaa;">
                <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 10px;"></i>
                <p>Belum ada data pesanan saat ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Fungsi yang sama dengan sebelumnya
function toggleNotifications() {
    const dropdown = document.getElementById('notif-dropdown');
    dropdown.classList.toggle('active');
}

function fetchNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notif-badge');
            const list = document.getElementById('notif-list');
            
            if (data.unread_count > 0) {
                badge.style.display = 'block';
                badge.innerText = data.unread_count;
            } else {
                badge.style.display = 'none';
            }
            
            list.innerHTML = '';
            if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notif => {
                    const item = document.createElement('a');
                    item.href = 'detail_pesanan.php?id=' + notif.order_id;
                    item.className = 'notification-item' + (notif.is_read == 0 ? ' unread' : '');
                    item.innerHTML = `
                        <div style="font-size: 0.85rem; font-weight: 600;">${notif.message}</div>
                        <small style="color: #999;">${new Date(notif.created_at).toLocaleString()}</small>
                    `;
                    list.appendChild(item);
                });
            } else {
                list.innerHTML = '<div style="padding: 20px; text-align: center; color: #ccc; font-size: 0.85rem;">Tidak ada notifikasi</div>';
            }
        });
}

setInterval(fetchNotifications, 10000);
fetchNotifications();

// Tutup dropdown jika klik di luar
document.addEventListener('click', function(e) {
    if (!e.target.closest('.notif-btn') && !e.target.closest('.notification-dropdown')) {
        document.getElementById('notif-dropdown').classList.remove('active');
    }
});
</script>

</body>
</html>
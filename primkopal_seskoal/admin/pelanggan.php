<?php
session_start();
require_once '../config/database.php';

// Authorization Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch Customers - Pastikan query mengambil semua kolom
try {
    $stmt = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pelanggan - Admin Primkopal</title>
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
        .sidebar-brand { padding: 0 30px 30px; font-size: 1.5rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar-menu a { display: flex; align-items: center; padding: 15px 30px; color: rgba(255,255,255,0.7); text-decoration: none; transition: 0.3s; }
        .sidebar-menu a i { width: 30px; font-size: 1.1rem; }
        .sidebar-menu a:hover, .sidebar-menu a.active { color: white; background: rgba(255,255,255,0.1); border-left: 4px solid var(--accent); }

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 40px; }
        .header-section { margin-bottom: 30px; }
        .header-section h2 { color: var(--primary); font-weight: 700; }
        .header-section p { color: #888; font-size: 0.9rem; }

        /* --- CARD & TABLE --- */
        .card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { text-align: left; padding: 15px; color: #aaa; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f8f9fa; }
        td { padding: 15px; border-bottom: 1px solid #f8f9fa; font-size: 0.9rem; color: #444; vertical-align: middle; }

        .avatar { width: 40px; height: 40px; background: #e7eff6; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-name { font-weight: 600; color: #2d3436; }
        
        .phone-link { color: #2e7d32; text-decoration: none; font-weight: 600; }
        .address-text { font-size: 0.8rem; color: #666; max-width: 200px; line-height: 1.4; }
        .join-date { display: inline-block; padding: 4px 10px; background: #f1f2f6; border-radius: 6px; font-size: 0.8rem; color: #57606f; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand"><i class="fas fa-anchor"></i> PRIMKOPAL</div>
    <div class="sidebar-menu">
        <a href="index.php"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="produk.php"><i class="fas fa-boxes"></i> Kelola Produk</a>
        <a href="pesanan.php"><i class="fas fa-shopping-bag"></i> Pesanan</a>
        <a href="pelanggan.php" class="active"><i class="fas fa-user-friends"></i> Pelanggan</a>
        <div style="margin-top: 50px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="../logout.php" style="color: #ff7675;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="header-section">
        <h2>Daftar Pelanggan</h2>
        <p>Manajemen data akun pelanggan yang terdaftar di koperasi.</p>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>No. Telepon</th>
                    <th>Alamat</th>
                    <th>Username</th>
                    <th>Terdaftar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td style="color: #aaa; font-weight: 600;">#<?php echo $user['id']; ?></td>
                    <td>
                        <div class="user-info">
                            <div class="avatar">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                        </div>
                    </td>
                    <td>
                        <a href="https://wa.me/<?php echo $user['phone_number']; ?>" target="_blank" class="phone-link">
                            <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($user['phone_number'] ?? '-'); ?>
                        </a>
                    </td>
                    <td>
                        <div class="address-text">
                            <?php echo htmlspecialchars($user['address'] ?? 'Belum mengisi alamat'); ?>
                        </div>
                    </td>
                    <td style="font-family: monospace; font-weight: 600;">
                        @<?php echo htmlspecialchars($user['username']); ?>
                    </td>
                    <td>
                        <span class="join-date">
                            <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if(empty($users)): ?>
            <div style="text-align: center; padding: 40px; color: #ccc;">
                <i class="fas fa-users-slash" style="font-size: 3rem; margin-bottom: 10px;"></i>
                <p>Belum ada pelanggan yang terdaftar.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
<?php
session_start();
require_once '../config/database.php';

// Authorization Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch Products
try {
    $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin Primkopal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #001f3f;
            --accent: #3498db;
            --bg-light: #f4f7f6;
            --sidebar-width: 260px;
            --danger: #e74c3c;
            --warning: #f1c40f;
            --success: #2ecc71;
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

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-section h2 { color: var(--primary); font-weight: 700; }

        /* --- UI COMPONENTS --- */
        .btn-add {
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0,31,63,0.2);
        }
        .btn-add:hover { background: #003366; transform: translateY(-2px); }

        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* --- TABLE STYLE --- */
        table { width: 100%; border-collapse: collapse; }
        
        th {
            text-align: left;
            padding: 15px;
            background: #fdfdfd;
            border-bottom: 2px solid #f1f1f1;
            color: #aaa;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }

        td { padding: 15px; border-bottom: 1px solid #f8f9fa; font-size: 0.9rem; vertical-align: middle; color: #444; }

        .product-img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #eee;
        }

        .category-badge {
            background: #e9ecef;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            color: #666;
            font-weight: 600;
        }

        .stock-indicator {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .action-btns { display: flex; gap: 8px; }

        .btn-action {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-edit { background: var(--warning); color: #856404; }
        .btn-delete { background: var(--danger); }
        .btn-action:hover { opacity: 0.8; transform: scale(1.05); }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-anchor"></i> PRIMKOPAL
    </div>
    <div class="sidebar-menu">
        <a href="index.php"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="produk.php" class="active"><i class="fas fa-boxes"></i> Kelola Produk</a>
        <a href="pesanan.php"><i class="fas fa-shopping-bag"></i> Pesanan</a>
        <a href="pelanggan.php"><i class="fas fa-user-friends"></i> Pelanggan</a>
        <div style="margin-top: 50px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="../logout.php" style="color: #ff7675;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="header-section">
        <div>
            <h2>Daftar Produk</h2>
            <p style="color: #888; font-size: 0.9rem;">Kelola inventaris barang koperasi Anda</p>
        </div>
        <a href="tambah_produk.php" class="btn-add">
            <i class="fas fa-plus"></i> Tambah Produk Baru
        </a>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Info Produk</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $product): ?>
                <tr>
                    <td style="color: #aaa;">#<?php echo $product['id']; ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="../assets/images/<?php echo $product['image']; ?>" alt="img" class="product-img">
                            <span style="font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($product['name']); ?></span>
                        </div>
                    </td>
                    <td style="font-weight: 600;">
                        Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                    </td>
                    <td>
                        <?php 
                            $stock_color = $product['stock'] <= 5 ? 'background: #ffe5e5; color: #d63031;' : 'background: #e3fcef; color: #00b894;';
                        ?>
                        <span class="stock-indicator" style="<?php echo $stock_color; ?>">
                            <?php echo $product['stock']; ?> unit
                        </span>
                    </td>
                    <td>
                        <span class="category-badge"><?php echo htmlspecialchars($product['category']); ?></span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="edit_produk.php?id=<?php echo $product['id']; ?>" class="btn-action btn-edit" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="proses_produk.php?action=delete&id=<?php echo $product['id']; ?>" class="btn-action btn-delete" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if(empty($products)): ?>
            <div style="text-align: center; padding: 40px; color: #ccc;">
                <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 10px;"></i>
                <p>Belum ada produk yang ditambahkan.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
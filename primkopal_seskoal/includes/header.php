<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    }

// Tambahkan logika hitung keranjang di sini
$total_item_keranjang = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $total_item_keranjang += $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRIMKOPAL SESKOAL</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <nav class="navbar">
        <div class="logo">
    <a href="index.php">
        <img src="assets/images/logo.png" width="40"> PRIMKOPAL SESKOAL
    </a>
</div>
        
      <ul class="nav-links">
    <li><a href="index.php">Beranda</a></li>
    <li><a href="index.php#tentang">Tentang Koperasi</a></li>
    <li><a href="index.php#kontak">Kontak</a></li>
    <li><a href="produk.php">Produk</a></li>
    <li><a href="pesanan_user.php">Pesanan Saya</a></li>

    <li style="position: relative; margin-right: 15px;">
        <a href="keranjang.php" title="Keranjang Belanja">
            <i class="fas fa-shopping-cart" style="font-size: 1.2rem;"></i>
            <?php if (isset($total_item_keranjang) && $total_item_keranjang > 0): ?>
                <span style="
                    position: absolute;
                    top: -10px;
                    right: -10px;
                    background: #d32f2f;
                    color: white;
                    font-size: 0.7rem;
                    padding: 2px 6px;
                    border-radius: 50%;
                    font-weight: bold;
                    border: 2px solid white;
                ">
                    <?php echo $total_item_keranjang; ?>
                </span>
            <?php endif; ?>
        </a>
    </li>

    <?php if(isset($_SESSION['user_id'])): ?>
        
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <li><a href="admin/index.php" style="color: #ff9800;">Dashboard Admin</a></li>
        <?php endif; ?>
        
        <li><a href="logout.php" class="btn-produk" style="background-color: #d32f2f; color: white;">Logout</a></li>

    <?php else: ?>
        
        <li><a href="login.php" class="btn-produk">Login</a></li>

    <?php endif; ?>
</ul>

        <div class="burger">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>
    </nav>
</header>
<main>
<style>
/* CSS lama kamu */

/* TAMBAHKAN DI SINI */
table{
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:10px;
    overflow:hidden;
}

th, td{
    padding:14px 16px;
    text-align:left;
}

th{
    background:#0d1b2a;
    color:white;
    font-weight:600;
}

td{
    border-bottom:1px solid #eee;
    font-size:14px;
}

tr:last-child td{
    border-bottom:none;
}

tr:hover{
    background:#f5f5f5;
}

td:nth-child(1){ width:80px; }
td:nth-child(2){ width:150px; font-weight:bold; }
td:nth-child(3){ width:150px; }
td:nth-child(4){ width:140px; }
td:nth-child(5){ width:180px; }

.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    overflow-x:auto;
}
</style>
<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $phone_number = trim($_POST['phone_number']); // Ambil data nomor telepon

    if ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                // Update Query: Tambahkan phone_number
                $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, address, phone_number, role) VALUES (:username, :password, :full_name, :address, :phone_number, 'user')");
                
                $stmt->execute([
                    ':username'     => $username,
                    ':password'     => $hashed_password,
                    ':full_name'    => $full_name,
                    ':address'      => $address,
                    ':phone_number' => $phone_number
                ]);
                
                $success = 'Pendaftaran berhasil! Silakan login.';
            } catch (PDOException $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Toko Kelontong</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #2e7d32 0%, #81c784 100%);
            font-family: 'Poppins', sans-serif;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-header { text-align: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.3rem; color: #666; font-size: 0.85rem; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            box-sizing: border-box;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #2e7d32;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-login:hover { background-color: #1b5e20; }
        .error-msg { background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 1rem; text-align: center; font-size: 0.8rem; }
        .success-msg { background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 1rem; text-align: center; font-size: 0.8rem; }
        .back-link { text-align: center; margin-top: 1rem; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h2>Daftar Akun Baru</h2>
        <p>Silakan isi data diri Anda</p>
    </div>

    <?php if($error): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="success-msg">
            <?php echo $success; ?>
            <br><br>
            <a href="login.php" style="background: #2e7d32; color: white; padding: 8px 20px; border-radius: 5px; text-decoration: none; display: inline-block;">Login Sekarang</a>
        </div>
    <?php else: ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="full_name" required>
        </div>

        <div class="form-group">
            <label>Nomor Telepon/WA</label>
            <input type="tel" name="phone_number" placeholder="081234567XXX" required>
        </div>

        <div class="form-group">
            <label>Alamat Lengkap Rumah</label>
            <textarea name="address" rows="2" required></textarea>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Konfirmasi Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn-login">Daftar Akun</button>
    </form>
    <?php endif; ?>

    <div class="back-link">
        Sudah punya akun? <a href="login.php" style="color: #2e7d32; text-decoration: none; font-weight: bold;">Login disini</a>
    </div>
</div>

</body>
</html>
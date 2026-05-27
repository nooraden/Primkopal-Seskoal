<?php
session_start();
require_once '../config/database.php';

if(isset($_POST['order_id']) && isset($_POST['status'])){

    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE orders 
        SET status = :status 
        WHERE id = :id
    ");

    $stmt->execute([
        ':status' => $status,
        ':id' => $order_id
    ]);

}

header("Location: pesanan.php");
exit;
?>
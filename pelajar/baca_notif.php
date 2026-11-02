<?php
session_start();
include "../config/config.php";

$id_user = $_SESSION['id_user'] ?? 0;

if ($id_user > 0) {
    // Perbarui waktu terakhir kali user membuka notifikasi
    mysqli_query($conn, "UPDATE pelajar SET last_notif_read = NOW() WHERE user_id = '$id_user'");
}

echo json_encode(['status' => 'success']);
?>
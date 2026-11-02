<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 2) {
    header('Location: ../login.php');
    exit();
}

$id_user = $_SESSION['id_user'];
$pembimbing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM pembimbing WHERE user_id = '$id_user'"));
$id_pembimbing = $pembimbing['id'] ?? 0;

if (isset($_GET['id']) && isset($_GET['aksi'])) {
    $id_pendaftaran = (int)$_GET['id'];
    $aksi = $_GET['aksi'];

    $status = ($aksi === 'setuju') ? 'diterima' : (($aksi === 'tolak') ? 'ditolak' : 'menunggu');

    // PERBAIKAN: Memastikan update pendaftaran sesuai pelajar_id
    $query = "UPDATE pendaftaran SET status = '$status', updated_at = NOW() 
              WHERE id = '$id_pendaftaran' AND pembimbing_id = '$id_pembimbing'";

    if (mysqli_query($conn, $query)) {
        header('Location: dashboard_pembimbing.php?status=berhasil_approve');
    } else {
        header('Location: dashboard_pembimbing.php?status=gagal_approve');
    }
    exit();
}
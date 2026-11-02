<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 2) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_log = isset($_POST['id_log']) ? (int)$_POST['id_log'] : 0;
    $komentar = mysqli_real_escape_string($conn, $_POST['komentar'] ?? '');

    if ($id_log <= 0) {
        header('Location: logbook.php?pesan=gagal');
        exit();
    }

    $status = 'menunggu';
    if (isset($_POST['aksi_setuju'])) {
        $status = 'disetujui';
    } elseif (isset($_POST['aksi_revisi'])) {
        $status = 'revisi';
    } elseif (isset($_POST['aksi_tolak'])) {
        $status = 'ditolak';
    }

    $query = "UPDATE logbook SET status = '$status', komentar = '$komentar', updated_at = NOW() WHERE id = '$id_log'";
    
    if (mysqli_query($conn, $query)) {
        header('Location: logbook.php?pesan=sukses');
    } else {
        header('Location: logbook.php?pesan=gagal');
    }
    exit();
}
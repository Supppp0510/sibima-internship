<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: logbook.php');
    exit();
}

$id_user = $_SESSION['id_user'];
$pelajar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '$id_user'"));
$id_pelajar = $pelajar['id'] ?? 0;

if (!$id_pelajar) {
    header('Location: logbook.php?pesan=gagal');
    exit();
}

$tanggal  = mysqli_real_escape_string($conn, $_POST['tanggal']);
$judul    = mysqli_real_escape_string($conn, $_POST['judul']);
$kegiatan = mysqli_real_escape_string($conn, $_POST['kegiatan']);

$nama_file = '';
if (!empty($_FILES['dokumentasi']['name'])) {
    $ext = strtolower(pathinfo($_FILES['dokumentasi']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed_ext)) {
        header('Location: logbook.php?pesan=gagal');
        exit();
    }

    $nama_file = 'log_' . time() . '_' . uniqid() . '.' . $ext;
    $target_dir = '../assets/upload/pelajar/dokumentasi/';

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $target_dir . $nama_file)) {
        header('Location: logbook.php?pesan=gagal');
        exit();
    }
}

$query = "INSERT INTO logbook (pelajar_id, tanggal, judul, kegiatan, dokumentasi, status, komentar, created_at, updated_at)
          VALUES ('$id_pelajar', '$tanggal', '$judul', '$kegiatan', '$nama_file', 'menunggu', '', NOW(), NOW())";

if (mysqli_query($conn, $query)) {
    header('Location: logbook.php?pesan=sukses');
} else {
    header('Location: logbook.php?pesan=gagal');
}
exit();
?>
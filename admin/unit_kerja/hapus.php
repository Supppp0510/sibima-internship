<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = (int) $_GET['id'];

// Cek data unit kerja
$q = mysqli_query($conn, "SELECT * FROM unit_kerja WHERE id='$id'");

if (!$q) {
    die("Error SELECT : " . mysqli_error($conn));
}

if (mysqli_num_rows($q) == 0) {
    die("Data unit kerja tidak ditemukan.");
}

$data = mysqli_fetch_assoc($q);
$nama = $data['nama_unit_kerja'];

// Hapus data
$hapus = mysqli_query($conn, "DELETE FROM unit_kerja WHERE id='$id'");

if (!$hapus) {
    die("DELETE ERROR : " . mysqli_error($conn));
}

// Simpan activity log
activityLog($conn, "Menghapus unit kerja : " . $nama);

header("Location: index.php?hapus=1");
exit;
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

// 1. Cek apakah parameter ID ada di URL
if (! isset($_GET['id'])) {
    die("ID ruangan tidak ditemukan.");
}

$id_ruangan = (int) $_GET['id'];

// 2. Ambil data ruangan terlebih dahulu sekaligus untuk kebutuhan activity log
$q = mysqli_query($conn, "SELECT * FROM ruangan WHERE id_ruangan = '$id_ruangan'");

if (! $q) {
    die("Error SELECT : " . mysqli_error($conn));
}

if (mysqli_num_rows($q) == 0) {
    die("Data ruangan tidak ditemukan.");
}

$data         = mysqli_fetch_assoc($q);
$nama_ruangan = $data['nama_ruangan'];

// 3. Jalankan query hapus data dari database
$hapus = mysqli_query($conn, "DELETE FROM ruangan WHERE id_ruangan = '$id_ruangan'");

if (! $hapus) {
    die("DELETE ERROR : " . mysqli_error($conn));
}

// 4. Catat log aktivitas penghapusan
activityLog($conn, "Menghapus data ruangan : " . $nama_ruangan);

// 5. Redirect kembali ke halaman index dengan parameter hapus sukses
header("Location: index.php?hapus=1");
exit;
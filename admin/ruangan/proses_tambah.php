<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

// 1. Ambil data dari form (tambah.php)
$id_unit_kerja = isset($_POST['id_unit_kerja']) ? intval($_POST['id_unit_kerja']) : 0;
$nama_ruangan  = isset($_POST['nama_ruangan']) ? trim($_POST['nama_ruangan']) : '';
$lokasi        = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';
$kuota         = isset($_POST['kuota']) ? intval($_POST['kuota']) : 0;

// 2. Amankan data sebelum masuk ke database (Mencegah SQL Injection)
$id_uk_aman   = mysqli_real_escape_string($conn, $id_unit_kerja);
$ruangan_aman = mysqli_real_escape_string($conn, $nama_ruangan);
$lokasi_aman  = mysqli_real_escape_string($conn, $lokasi);
$kuota_aman   = mysqli_real_escape_string($conn, $kuota);

// 3. Simpan ke database (Sesuai kolom tabel: id_unit_kerja, nama_ruangan, lokasi, kuota)
mysqli_query($conn, "
    INSERT INTO ruangan
    (
        id_unit_kerja,
        nama_ruangan,
        lokasi,
        kuota
    )
    VALUES
    (
        '$id_uk_aman',
        '$ruangan_aman',
        '$lokasi_aman',
        '$kuota_aman'
    )
");

// 4. Catat aktivitas
activityLog(
    $conn,
    "Menambahkan data ruangan : " . $nama_ruangan
);

// 5. Redirect kembali ke halaman index dengan status sukses
header("Location: index.php?success=tambah");
exit;
<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$email          = isset($_POST['email']) ? trim($_POST['email']) : '';
$password       = isset($_POST['password']) ? trim($_POST['password']) : '';
$status         = isset($_POST['status']) ? trim($_POST['status']) : 'Aktif';
$nama           = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$nip            = isset($_POST['nip']) ? trim($_POST['nip']) : '';
$golongan_ruang = isset($_POST['golongan_ruang']) ? trim($_POST['golongan_ruang']) : '';
$pangkat        = isset($_POST['pangkat']) ? trim($_POST['pangkat']) : '';
$jenis_jabatan  = isset($_POST['jenis_jabatan']) ? trim($_POST['jenis_jabatan']) : '';
$nama_jabatan   = isset($_POST['nama_jabatan']) ? trim($_POST['nama_jabatan']) : '';
$unit_kerja_id  = isset($_POST['unit_kerja_id']) ? trim($_POST['unit_kerja_id']) : '';

$email_aman     = mysqli_real_escape_string($conn, $email);
$status_aman    = mysqli_real_escape_string($conn, $status);
$nama_aman      = mysqli_real_escape_string($conn, $nama);
$nip_aman       = mysqli_real_escape_string($conn, $nip);
$gol_ruang_aman = mysqli_real_escape_string($conn, $golongan_ruang);
$pangkat_aman   = mysqli_real_escape_string($conn, $pangkat);
$jenis_jab_aman = mysqli_real_escape_string($conn, $jenis_jabatan);
$nama_jab_aman  = mysqli_real_escape_string($conn, $nama_jabatan);
$uk_id_aman     = mysqli_real_escape_string($conn, $unit_kerja_id);

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role_id_pembimbing = 2; 

mysqli_query($conn, "
    INSERT INTO users (role_id, name, email, password, status)
    VALUES ('$role_id_pembimbing', '$nama_aman', '$email_aman', '$hashed_password', '$status_aman')
");

$user_id = mysqli_insert_id($conn);

if (empty($uk_id_aman)) {
    mysqli_query($conn, "
        INSERT INTO pembimbing (user_id, nip, nama, email, golongan_ruang, pangkat, nama_jabatan, jenis_jabatan, unit_kerja_id)
        VALUES ('$user_id', '$nip_aman', '$nama_aman', '$email_aman', '$gol_ruang_aman', '$pangkat_aman', '$nama_jab_aman', '$jenis_jab_aman', NULL)
    ");
} else {
    mysqli_query($conn, "
        INSERT INTO pembimbing (user_id, nip, nama, email, golongan_ruang, pangkat, nama_jabatan, jenis_jabatan, unit_kerja_id)
        VALUES ('$user_id', '$nip_aman', '$nama_aman', '$email_aman', '$gol_ruang_aman', '$pangkat_aman', '$nama_jab_aman', '$jenis_jab_aman', '$uk_id_aman')
    ");
}

activityLog($conn, "Menambahkan pembimbing baru: " . $nama_aman);

header("Location: index.php?success=tambah");
exit;
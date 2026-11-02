<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id            = isset($_POST['id']) ? trim($_POST['id']) : '';
$user_id       = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';
$email         = isset($_POST['email']) ? trim($_POST['email']) : '';
$password      = isset($_POST['password']) ? trim($_POST['password']) : '';
$status        = isset($_POST['status']) ? trim($_POST['status']) : 'Aktif';
$nama          = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$nip           = isset($_POST['nip']) ? trim($_POST['nip']) : '';
$gol_ruang     = isset($_POST['golongan_ruang']) ? trim($_POST['golongan_ruang']) : '';
$pangkat       = isset($_POST['pangkat']) ? trim($_POST['pangkat']) : '';
$jenis_jab     = isset($_POST['jenis_jabatan']) ? trim($_POST['jenis_jabatan']) : '';
$nama_jab      = isset($_POST['nama_jabatan']) ? trim($_POST['nama_jabatan']) : '';
$unit_kerja_id = isset($_POST['unit_kerja_id']) ? trim($_POST['unit_kerja_id']) : '';

$id_aman       = mysqli_real_escape_string($conn, $id);
$uid_aman      = mysqli_real_escape_string($conn, $user_id);
$email_aman    = mysqli_real_escape_string($conn, $email);
$status_aman   = mysqli_real_escape_string($conn, $status);
$nama_aman     = mysqli_real_escape_string($conn, $nama);
$nip_aman      = mysqli_real_escape_string($conn, $nip);
$gol_aman      = mysqli_real_escape_string($conn, $gol_ruang);
$pangkat_aman  = mysqli_real_escape_string($conn, $pangkat);
$jenis_j_aman  = mysqli_real_escape_string($conn, $jenis_jab);
$nama_j_aman   = mysqli_real_escape_string($conn, $nama_jab);
$uk_id_aman    = mysqli_real_escape_string($conn, $unit_kerja_id);

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query_users = "UPDATE users SET name = '$nama_aman', email = '$email_aman', status = '$status_aman', password = '$hashed_password' WHERE id = '$uid_aman'";
} else {
    $query_users = "UPDATE users SET name = '$nama_aman', email = '$email_aman', status = '$status_aman' WHERE id = '$uid_aman'";
}
mysqli_query($conn, $query_users);

if (empty($uk_id_aman)) {
    $query_pembimbing = "UPDATE pembimbing SET nip = '$nip_aman', nama = '$nama_aman', email = '$email_aman', golongan_ruang = '$gol_aman', pangkat = '$pangkat_aman', nama_jabatan = '$nama_j_aman', jenis_jabatan = '$jenis_j_aman', unit_kerja_id = NULL WHERE id = '$id_aman'";
} else {
    $query_pembimbing = "UPDATE pembimbing SET nip = '$nip_aman', nama = '$nama_aman', email = '$email_aman', golongan_ruang = '$gol_aman', pangkat = '$pangkat_aman', nama_jabatan = '$nama_j_aman', jenis_jabatan = '$jenis_j_aman', unit_kerja_id = '$uk_id_aman' WHERE id = '$id_aman'";
}
mysqli_query($conn, $query_pembimbing);

activityLog($conn, "Mengubah data pembimbing: " . $nama_aman);

header("Location: index.php?success=edit");
exit;
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

// 1. Cek apakah tombol submit ditekan / data POST ada
if (! isset($_POST['id_ruangan'])) {
    die("Akses dilarang!");
}

$id_ruangan   = intval($_POST['id_ruangan']);
$nama_ruangan = trim($_POST['nama_ruangan']);
$lokasi       = trim($_POST['lokasi']);
$kuota        = intval($_POST['kuota']);

// Ambil ID dari dropdown Eselon III dan Eselon IV
$id_eselon_3 = isset($_POST['id_eselon_3']) ? intval($_POST['id_eselon_3']) : 0;
$id_eselon_4 = isset($_POST['id_unit_kerja']) ? intval($_POST['id_unit_kerja']) : 0;

// LOGIKA PENENTUAN ID UNIT KERJA:
// Jika Eselon IV dipilih, maka gunakan ID Eselon IV.
// Jika Eselon IV dikosongkan (0), maka gunakan ID Eselon III.
$id_unit_kerja_final = ($id_eselon_4 > 0) ? $id_eselon_4 : $id_eselon_3;

// 2. Validasi sederhana
if ($id_ruangan <= 0 || empty($nama_ruangan) || $id_unit_kerja_final <= 0) {
    die("Gagal: Data yang dikirim tidak lengkap atau ID Unit Kerja tidak valid! (ID Final: $id_unit_kerja_final)");
}

// 3. Catat log aktivitas
activityLog($conn, "Mengubah data ruangan : " . $nama_ruangan);

// 4. Amankan data
$id_ruangan_aman = mysqli_real_escape_string($conn, $id_ruangan);
$id_uk_aman      = mysqli_real_escape_string($conn, $id_unit_kerja_final);
$ruangan_aman    = mysqli_real_escape_string($conn, $nama_ruangan);
$lokasi_aman     = mysqli_real_escape_string($conn, $lokasi);
$kuota_aman      = mysqli_real_escape_string($conn, $kuota);

// 5. Jalankan query update
$query = "UPDATE ruangan SET
            id_unit_kerja = '$id_uk_aman',
            nama_ruangan = '$ruangan_aman',
            lokasi = '$lokasi_aman',
            kuota = '$kuota_aman'
          WHERE id_ruangan = '$id_ruangan_aman'";

$update = mysqli_query($conn, $query);

if (! $update) {
    die("UPDATE ERROR SQL: " . mysqli_error($conn));
}

// 6. Redirect sukses
header("Location: index.php?success=edit");
exit;

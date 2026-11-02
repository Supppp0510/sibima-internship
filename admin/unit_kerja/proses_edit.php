<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

// Ambil data dari form POST
$id               = $_POST['id'];
$unit_kerja       = $_POST['unit_kerja'];
$unit_kerja_es_ii = $_POST['unit_kerja_es_ii'];
$unit_kerja_es_iii= $_POST['unit_kerja_es_iii'];
$unit_kerja_es_iv = $_POST['unit_kerja_es_iv'];
$kuota            = $_POST['kuota'];

// Catat log aktivitas setelah variabel terdefinisi
activityLog(
    $conn,
    "Mengubah data unit kerja : " . $unit_kerja
);

// Jalankan kueri update ke database
mysqli_query($conn, "
    UPDATE unit_kerja SET
    unit_kerja = '$unit_kerja',
    unit_kerja_es_ii = '$unit_kerja_es_ii',
    unit_kerja_es_iii = '$unit_kerja_es_iii',
    unit_kerja_es_iv = '$unit_kerja_es_iv',
    kuota = '$kuota'
    WHERE id = '$id'
");

header("Location: index.php");
exit;
?>
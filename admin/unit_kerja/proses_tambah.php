<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

activityLog(
    $conn,
    "Menambahkan data unit kerja : " . $nama_unit_kerja
);

$nama = $_POST['nama_unit_kerja'];
$kuota = $_POST['kuota'];
$deskripsi = $_POST['deskripsi'];
$status = $_POST['status'];

mysqli_query($conn, "
INSERT INTO unit_kerja
(
nama_unit_kerja,
kuota,
deskripsi,
status
)
VALUES
(
'$nama',
'$kuota',
'$deskripsi',
'$status'
)
");

header("Location:index.php?success=tambah");
exit;
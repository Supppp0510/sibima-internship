<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

activityLog(
    $conn,
    "Menambahkan data bidang : ".$nama_bidang
);

$nama = $_POST['nama_bidang'];
$kuota = $_POST['kuota'];
$deskripsi = $_POST['deskripsi'];
$status = $_POST['status'];

mysqli_query($conn,"
INSERT INTO bidang
(
nama_bidang,
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
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

activityLog(
    $conn,
    "Mengubah data bidang : ".$nama_bidang
);

$id=$_POST['id'];
$nama=$_POST['nama_bidang'];
$kuota=$_POST['kuota'];
$deskripsi=$_POST['deskripsi'];
$status=$_POST['status'];

mysqli_query($conn,"
UPDATE bidang
SET

nama_bidang='$nama',
kuota='$kuota',
deskripsi='$deskripsi',
status='$status'

WHERE id='$id'
");

header("Location:index.php");
exit;
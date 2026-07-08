<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_POST['id'];

$nama = mysqli_real_escape_string($conn,$_POST['nama_prodi']);
$jenjang = mysqli_real_escape_string($conn,$_POST['jenjang']);

$update = mysqli_query($conn,"
UPDATE program_studi
SET
nama_prodi='$nama',
jenjang='$jenjang'
WHERE id='$id'
");

if($update){

    activityLog(
        $conn,
        "Mengubah Program Studi : ".$nama
    );

    header("Location:index.php");
    exit;

}else{

    die(mysqli_error($conn));

}
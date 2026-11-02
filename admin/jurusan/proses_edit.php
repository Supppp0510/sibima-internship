<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_POST['id'];

$nama = mysqli_real_escape_string($conn,$_POST['nama_jurusan']);
$jenjang = mysqli_real_escape_string($conn,$_POST['jenjang']);

$update = mysqli_query($conn,"
UPDATE jurusan
SET
nama_jurusan='$nama',
jenjang='$jenjang'
WHERE id='$id'
");

if($update){

    activityLog(
        $conn,
        "Mengubah Jurusan : ".$nama
    );

    header("Location:index.php");
    exit;

}else{

    die(mysqli_error($conn));

}
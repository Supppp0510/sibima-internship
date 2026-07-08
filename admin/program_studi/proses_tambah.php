<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$nama = mysqli_real_escape_string($conn,$_POST['nama_prodi']);
$jenjang = mysqli_real_escape_string($conn,$_POST['jenjang']);

$simpan = mysqli_query($conn,"
INSERT INTO program_studi
(
nama_prodi,
jenjang
)
VALUES
(
'$nama',
'$jenjang'
)
");

if($simpan){

    activityLog(
        $conn,
        "Menambahkan Program Studi : ".$nama
    );

    header("Location:index.php");
    exit;

}else{

    die(mysqli_error($conn));

}
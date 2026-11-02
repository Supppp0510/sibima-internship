<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$nama = mysqli_real_escape_string($conn,$_POST['nama_instansi']);
$alamat = mysqli_real_escape_string($conn,$_POST['alamat']);
$kota = mysqli_real_escape_string($conn,$_POST['kota']);

$simpan = mysqli_query($conn,"
INSERT INTO instansi
(
nama_instansi,
alamat,
kota
)
VALUES
(
'$nama',
'$alamat',
'$kota'
)
");

if($simpan){

    activityLog(
        $conn,
        "Menambahkan Instansi : ".$nama
    );

    header("Location:index.php");

}else{

    die(mysqli_error($conn));

}
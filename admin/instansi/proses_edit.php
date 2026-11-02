<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_POST['id'];

$nama = mysqli_real_escape_string($conn,$_POST['nama_instansi']);
$alamat = mysqli_real_escape_string($conn,$_POST['alamat']);
$kota = mysqli_real_escape_string($conn,$_POST['kota']);

$update = mysqli_query($conn,"
UPDATE instansi
SET
nama_instansi='$nama',
alamat='$alamat',
kota='$kota'
WHERE id='$id'
");

if($update){

    activityLog(
        $conn,
        "Mengubah data instansi : ".$nama
    );

    header("Location:index.php");
    exit;

}else{

    die(mysqli_error($conn));

}
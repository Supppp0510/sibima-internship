<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_POST['id'];

$nama = mysqli_real_escape_string($conn,$_POST['nama_periode']);
$mulai = $_POST['tanggal_mulai'];
$selesai = $_POST['tanggal_selesai'];
$status = $_POST['status'];

if($mulai > $selesai){

    echo "<script>
    alert('Tanggal mulai tidak boleh melebihi tanggal selesai.');
    window.history.back();
    </script>";
    exit;

}

$update = mysqli_query($conn,"
UPDATE periode_pkl
SET
nama_periode='$nama',
tanggal_mulai='$mulai',
tanggal_selesai='$selesai',
status='$status'
WHERE id='$id'
");

if($update){

    activityLog(
        $conn,
        "Mengubah Periode PKL : ".$nama
    );

    header("Location:index.php");
    exit;

}else{

    die(mysqli_error($conn));

}
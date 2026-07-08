<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

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

$simpan = mysqli_query($conn,"
INSERT INTO periode_pkl
(
nama_periode,
tanggal_mulai,
tanggal_selesai,
status
)
VALUES
(
'$nama',
'$mulai',
'$selesai',
'$status'
)
");

if($simpan){

    activityLog(
        $conn,
        "Menambahkan Periode PKL : ".$nama
    );

    header("Location:index.php");
    exit;

}else{

    die(mysqli_error($conn));

}
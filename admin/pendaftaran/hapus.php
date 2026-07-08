<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_GET['id'];

$q = mysqli_query($conn,"
SELECT *
FROM pendaftaran
WHERE id='$id'
");

if(mysqli_num_rows($q)==0){

    header("Location:index.php");
    exit;

}

$data = mysqli_fetch_assoc($q);

// ===============================
// Hapus File
// ===============================

$files = [

    "../../assets/upload/pendaftaran/surat_pengantar/".$data['surat_pengantar'],

    "../../assets/upload/pendaftaran/cv/".$data['cv'],

    "../../assets/upload/pendaftaran/transkrip/".$data['transkrip'],

    "../../assets/upload/pendaftaran/proposal/".$data['proposal']

];

foreach($files as $file){

    if(file_exists($file)){

        unlink($file);

    }

}

// ===============================
// Hapus Database
// ===============================

$hapus = mysqli_query($conn,"
DELETE FROM pendaftaran
WHERE id='$id'
");

if($hapus){

    $mhs = mysqli_query($conn,"
    SELECT nama
    FROM mahasiswa
    WHERE id='".$data['mahasiswa_id']."'
    ");

    $nama = mysqli_fetch_assoc($mhs);

    activityLog(

        $conn,

        "Menghapus Pendaftaran PKL : ".$nama['nama']

    );

    header("Location:index.php");

    exit;

}else{

    die(mysqli_error($conn));

}
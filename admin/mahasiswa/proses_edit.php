<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_POST['id'];

$nim = mysqli_real_escape_string($conn,$_POST['nim']);
$nama = mysqli_real_escape_string($conn,$_POST['nama']);
$universitas = $_POST['universitas_id'];
$prodi = $_POST['program_studi_id'];
$alamat = mysqli_real_escape_string($conn,$_POST['alamat']);
$hp = mysqli_real_escape_string($conn,$_POST['no_hp']);
$email = mysqli_real_escape_string($conn,$_POST['email']);

$fotoLama = $_POST['foto_lama'];
$namaFoto = $fotoLama;

//
// ==============================
// VALIDASI NIM
// ==============================
//

$cek = mysqli_query($conn,"
SELECT id
FROM mahasiswa
WHERE nim='$nim'
AND id<>'$id'
");

if(mysqli_num_rows($cek)>0){

    echo "<script>

    alert('NIM sudah digunakan');

    window.history.back();

    </script>";

    exit;

}

//
// ==============================
// VALIDASI EMAIL
// ==============================
//

$cekEmail = mysqli_query($conn,"
SELECT id
FROM mahasiswa
WHERE email='$email'
AND id<>'$id'
");

if(mysqli_num_rows($cekEmail)>0){

    echo "<script>

    alert('Email sudah digunakan');

    window.history.back();

    </script>";

    exit;

}

//
// ==============================
// CEK FOTO BARU
// ==============================
//

if($_FILES['foto']['name']!=""){

    $file=$_FILES['foto']['name'];

    $tmp=$_FILES['foto']['tmp_name'];

    $ext=strtolower(pathinfo($file,PATHINFO_EXTENSION));

    $boleh=array("jpg","jpeg","png");

    if(!in_array($ext,$boleh)){

        echo "<script>

        alert('Format foto harus JPG/JPEG/PNG');

        window.history.back();

        </script>";

        exit;

    }

    $namaFoto=time()."_".$file;

    move_uploaded_file(

        $tmp,

        "../../assets/upload/mahasiswa/foto/".$namaFoto

    );

    // ===========================
    // Hapus Foto Lama
    // ===========================

    if($fotoLama!=""){

        if(file_exists("../../assets/upload/mahasiswa/foto/".$fotoLama)){

            unlink("../../assets/upload/mahasiswa/foto/".$fotoLama);

        }

    }

}

//
// ==============================
// UPDATE DATABASE
// ==============================
//

$update=mysqli_query($conn,"
UPDATE mahasiswa
SET

nim='$nim',

nama='$nama',

universitas_id='$universitas',

program_studi_id='$prodi',

alamat='$alamat',

no_hp='$hp',

email='$email',

foto='$namaFoto'

WHERE id='$id'
");

if($update){

    activityLog(

        $conn,

        "Mengubah data mahasiswa : ".$nama

    );

    header("Location:index.php");

    exit;

}else{

    die(mysqli_error($conn));

}
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

// =======================
// User Login
// =======================

$user_id = $_SESSION['id'];

// =======================
// Ambil Data Form
// =======================

$nim = mysqli_real_escape_string($conn,$_POST['nim']);
$nama = mysqli_real_escape_string($conn,$_POST['nama']);
$universitas = $_POST['universitas_id'];
$prodi = $_POST['program_studi_id'];
$alamat = mysqli_real_escape_string($conn,$_POST['alamat']);
$hp = mysqli_real_escape_string($conn,$_POST['no_hp']);
$email = mysqli_real_escape_string($conn,$_POST['email']);

// =======================
// Cek NIM
// =======================

$cekNim = mysqli_query($conn,"
SELECT id
FROM mahasiswa
WHERE nim='$nim'
");

if(mysqli_num_rows($cekNim)>0){

    echo "<script>

    alert('NIM sudah digunakan!');

    window.history.back();

    </script>";

    exit;

}

// =======================
// Cek Email
// =======================

$cekEmail = mysqli_query($conn,"
SELECT id
FROM mahasiswa
WHERE email='$email'
");

if(mysqli_num_rows($cekEmail)>0){

    echo "<script>

    alert('Email sudah digunakan!');

    window.history.back();

    </script>";

    exit;

}

// =======================
// Upload Foto
// =======================

$namaFoto="";

if($_FILES['foto']['name']!=""){

    $namaFile=$_FILES['foto']['name'];
    $tmp=$_FILES['foto']['tmp_name'];

    $ext=strtolower(pathinfo($namaFile,PATHINFO_EXTENSION));

    $boleh=array(
        "jpg",
        "jpeg",
        "png"
    );

    if(!in_array($ext,$boleh)){

        echo "<script>

        alert('Format foto harus JPG, JPEG atau PNG');

        window.history.back();

        </script>";

        exit;

    }

    $namaFoto=time()."_".$namaFile;

    move_uploaded_file(

        $tmp,

        "../../assets/upload/mahasiswa/foto/".$namaFoto

    );

}

// =======================
// Simpan Database
// =======================

$simpan=mysqli_query($conn,"
INSERT INTO mahasiswa
(

user_id,

nim,

nama,

universitas_id,

program_studi_id,

alamat,

no_hp,

email,

foto

)

VALUES
(

'$user_id',

'$nim',

'$nama',

'$universitas',

'$prodi',

'$alamat',

'$hp',

'$email',

'$namaFoto'

)
");

// =======================
// Activity Log
// =======================

if($simpan){

    activityLog(

        $conn,

        "Menambahkan Mahasiswa : ".$nama

    );

    header("Location:index.php");

    exit;

}else{

    die(mysqli_error($conn));

}
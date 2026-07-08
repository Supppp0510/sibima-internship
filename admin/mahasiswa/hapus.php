<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id=$_GET['id'];

$q=mysqli_query($conn,"
SELECT *
FROM mahasiswa
WHERE id='$id'
");

if(mysqli_num_rows($q)==0){

    header("Location:index.php");

    exit;

}

$data=mysqli_fetch_assoc($q);

//
// Hapus Foto
//

if($data['foto']!=""){

    if(file_exists("../../assets/upload/mahasiswa/".$data['foto'])){

        unlink("../../assets/upload/mahasiswa/".$data['foto']);

    }

}

//
// Hapus Database
//

$hapus=mysqli_query($conn,"
DELETE FROM mahasiswa
WHERE id='$id'
");

if($hapus){

    activityLog(

        $conn,

        "Menghapus data mahasiswa : ".$data['nama']

    );

    header("Location:index.php");

    exit;

}else{

    die(mysqli_error($conn));

}
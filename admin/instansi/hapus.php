<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_GET['id'];

$q = mysqli_query($conn,"
SELECT *
FROM instansi
WHERE id='$id'
");

if(mysqli_num_rows($q)==0){
    die("Data tidak ditemukan.");
}

$data = mysqli_fetch_assoc($q);

$hapus = mysqli_query($conn,"
DELETE FROM instansi
WHERE id='$id'
");

if($hapus){

    activityLog(
        $conn,
        "Menghapus Instansi : ".$data['nama_instansi']
    );

    header("Location:index.php");

}else{

    die(mysqli_error($conn));

}
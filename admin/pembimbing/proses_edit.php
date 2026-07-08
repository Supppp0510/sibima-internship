<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_POST['id'];

$bidang = $_POST['bidang_id'];

$nip = mysqli_real_escape_string($conn,$_POST['nip']);

$nama = mysqli_real_escape_string($conn,$_POST['nama']);

$jabatan = mysqli_real_escape_string($conn,$_POST['jabatan']);

$no_hp = mysqli_real_escape_string($conn,$_POST['no_hp']);


// ==========================
// Validasi NIP
// ==========================

$cek = mysqli_query($conn,"
SELECT id
FROM pembimbing
WHERE nip='$nip'
AND id<>'$id'
");

if(mysqli_num_rows($cek)>0){

    echo "<script>

    alert('NIP sudah digunakan!');

    window.history.back();

    </script>";

    exit;

}


// ==========================
// Update
// ==========================

$update = mysqli_query($conn,"
UPDATE pembimbing
SET

bidang_id='$bidang',

nip='$nip',

nama='$nama',

jabatan='$jabatan',

no_hp='$no_hp'

WHERE id='$id'
");

if($update){

    activityLog(

        $conn,

        "Mengubah data pembimbing : ".$nama

    );

    header("Location:index.php");

    exit;

}else{

    die(mysqli_error($conn));

}

?>
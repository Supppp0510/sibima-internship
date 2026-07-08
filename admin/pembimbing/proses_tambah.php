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

$bidang = $_POST['bidang_id'];

$nip = mysqli_real_escape_string($conn,$_POST['nip']);

$nama = mysqli_real_escape_string($conn,$_POST['nama']);

$jabatan = mysqli_real_escape_string($conn,$_POST['jabatan']);

$no_hp = mysqli_real_escape_string($conn,$_POST['no_hp']);

// =======================
// Validasi NIP
// =======================

$cek = mysqli_query($conn,"
SELECT id
FROM pembimbing
WHERE nip='$nip'
");

if(mysqli_num_rows($cek)>0){

    echo "<script>

    alert('NIP sudah digunakan!');

    window.history.back();

    </script>";

    exit;

}

// =======================
// Simpan Data
// =======================

$simpan = mysqli_query($conn,"
INSERT INTO pembimbing
(

user_id,

bidang_id,

nip,

nama,

jabatan,

no_hp

)

VALUES
(

'$user_id',

'$bidang',

'$nip',

'$nama',

'$jabatan',

'$no_hp'

)
");

// =======================
// Activity Log
// =======================

if($simpan){

    activityLog(

        $conn,

        "Menambahkan Pembimbing : ".$nama

    );

    header("Location:index.php");

    exit;

}else{

    die(mysqli_error($conn));

}

?>
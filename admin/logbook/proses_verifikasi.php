<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_POST['id'];

$status = $_POST['status'];

$komentar = mysqli_real_escape_string(
$conn,
$_POST['komentar']
);

$update = mysqli_query($conn,"
UPDATE logbook
SET

status='$status',

komentar='$komentar'

WHERE id='$id'
");

if($update){

    $q = mysqli_query($conn,"
    SELECT

    l.id,

    m.nama

    FROM logbook l

    LEFT JOIN mahasiswa m
    ON l.mahasiswa_id=m.id

    WHERE l.id='$id'
    ");

    $d=mysqli_fetch_assoc($q);

    activityLog(

        $conn,

        "Verifikasi Logbook : ".$d['nama']." menjadi ".$status

    );

    echo "<script>

    alert('Verifikasi berhasil.');

    window.location='index.php';

    </script>";

}else{

    die(mysqli_error($conn));

}

?>
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_GET['id'];

$q = mysqli_query($conn,"
SELECT *
FROM pembimbing
WHERE id='$id'
");

if(mysqli_num_rows($q)==0){

    header("Location:index.php");
    exit;

}

$data = mysqli_fetch_assoc($q);

$hapus = mysqli_query($conn,"
DELETE FROM pembimbing
WHERE id='$id'
");

if($hapus){

    activityLog(

        $conn,

        "Menghapus data pembimbing : ".$data['nama']

    );

    header("Location:index.php");

    exit;

}else{

    die(mysqli_error($conn));

}

?>
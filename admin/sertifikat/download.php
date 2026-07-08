<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id=$_GET['id'];

$q=mysqli_query($conn,"
SELECT *
FROM sertifikat
WHERE id='$id'
");

if(mysqli_num_rows($q)==0){

die("Data tidak ditemukan.");

}

$data=mysqli_fetch_assoc($q);

$file="../../assets/upload/sertifikat/".$data['file_pdf'];

if(!file_exists($file)){

die("File PDF tidak ditemukan.");

}

header('Content-Type: application/pdf');

header('Content-Disposition: attachment; filename="'.$data['file_pdf'].'"');

readfile($file);

exit;

?>
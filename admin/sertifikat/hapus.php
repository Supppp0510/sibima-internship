<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id=$_GET['id'];

$q=mysqli_query($conn,"
SELECT

s.*,

m.nama

FROM sertifikat s

LEFT JOIN mahasiswa m
ON s.mahasiswa_id=m.id

WHERE s.id='$id'
");

if(mysqli_num_rows($q)==0){

header("Location:index.php");
exit;

}

$data=mysqli_fetch_assoc($q);

$file="../../assets/upload/sertifikat/".$data['file_pdf'];

if(file_exists($file)){

unlink($file);

}

$hapus=mysqli_query($conn,"
DELETE
FROM sertifikat
WHERE id='$id'
");

if($hapus){

activityLog(

$conn,

"Menghapus Sertifikat PKL : ".$data['nama']

);

echo "

<script>

alert('Sertifikat berhasil dihapus.');

window.location='index.php';

</script>

";

}else{

die(mysqli_error($conn));

}

?>
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id=$_POST['id'];

$disiplin=$_POST['disiplin'];

$komunikasi=$_POST['komunikasi'];

$kerjasama=$_POST['kerjasama'];

$tanggung_jawab=$_POST['tanggung_jawab'];

$inisiatif=$_POST['inisiatif'];

$catatan=mysqli_real_escape_string(
$conn,
$_POST['catatan']
);

// =========================
// Hitung Nilai Akhir
// =========================

$nilai_akhir=(

$disiplin+

$komunikasi+

$kerjasama+

$tanggung_jawab+

$inisiatif

)/5;

// =========================
// Update
// =========================

$update=mysqli_query($conn,"

UPDATE penilaian

SET

disiplin='$disiplin',

komunikasi='$komunikasi',

kerjasama='$kerjasama',

tanggung_jawab='$tanggung_jawab',

inisiatif='$inisiatif',

nilai_akhir='$nilai_akhir',

catatan='$catatan'

WHERE id='$id'

");

if($update){

$q=mysqli_query($conn,"
SELECT m.nama
FROM penilaian p
JOIN mahasiswa m
ON p.mahasiswa_id=m.id
WHERE p.id='$id'
");

$m=mysqli_fetch_assoc($q);

activityLog(

$conn,

"Mengubah Penilaian PKL : ".$m['nama']

);

echo "

<script>

alert('Penilaian berhasil diperbarui.');

window.location='index.php';

</script>

";

}else{

die(mysqli_error($conn));

}

?>
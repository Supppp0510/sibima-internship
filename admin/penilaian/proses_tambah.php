<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$mahasiswa_id=$_POST['mahasiswa_id'];

$pembimbing_id=$_POST['pembimbing_id'];

$disiplin=$_POST['disiplin'];

$komunikasi=$_POST['komunikasi'];

$kerjasama=$_POST['kerjasama'];

$tanggung_jawab=$_POST['tanggung_jawab'];

$inisiatif=$_POST['inisiatif'];

$catatan=mysqli_real_escape_string(

$conn,

$_POST['catatan']

);

// ============================
// Hitung Nilai Akhir
// ============================

$nilai_akhir=

(

$disiplin+

$komunikasi+

$kerjasama+

$tanggung_jawab+

$inisiatif

)/5;

// ============================
// Simpan
// ============================

$simpan=mysqli_query($conn,"

INSERT INTO penilaian(

mahasiswa_id,

pembimbing_id,

disiplin,

komunikasi,

kerjasama,

tanggung_jawab,

inisiatif,

nilai_akhir,

catatan

)

VALUES(

'$mahasiswa_id',

'$pembimbing_id',

'$disiplin',

'$komunikasi',

'$kerjasama',

'$tanggung_jawab',

'$inisiatif',

'$nilai_akhir',

'$catatan'

)

");

if($simpan){

$m=mysqli_query($conn,"
SELECT nama
FROM mahasiswa
WHERE id='$mahasiswa_id'
");

$nama=mysqli_fetch_assoc($m);

activityLog(

$conn,

"Menambahkan Penilaian PKL : ".$nama['nama']

);

echo "

<script>

alert('Penilaian berhasil disimpan.');

window.location='index.php';

</script>

";

}else{

die(mysqli_error($conn));

}

?>
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$query = mysqli_query($conn,"
SELECT

a.*,

m.nama,
m.nim,
m.no_hp,
m.email,

u.nama_universitas,

ps.nama_prodi,

b.nama_bidang,

pb.nama AS nama_pembimbing,
pb.nip

FROM absensi a

LEFT JOIN mahasiswa m
ON a.mahasiswa_id=m.id

LEFT JOIN universitas u
ON m.universitas_id=u.id

LEFT JOIN program_studi ps
ON m.program_studi_id=ps.id

LEFT JOIN pendaftaran p
ON p.mahasiswa_id=m.id
AND p.status='diterima'

LEFT JOIN bidang b
ON p.bidang_id=b.id

LEFT JOIN pembimbing pb
ON p.pembimbing_id=pb.id

WHERE a.id='$id'
");

if(mysqli_num_rows($query)==0){

    echo "<script>

    alert('Data tidak ditemukan.');

    window.location='index.php';

    </script>";

    exit;

}

$row = mysqli_fetch_assoc($query);

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

Detail Absensi Mahasiswa

</h4>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<div class="mb-3">

<label class="form-label">

Nama Mahasiswa

</label>

<input
type="text"
class="form-control"
value="<?= $row['nama']; ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

NIM

</label>

<input
type="text"
class="form-control"
value="<?= $row['nim']; ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Universitas

</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_universitas']; ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Program Studi

</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_prodi']; ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

No HP

</label>

<input
type="text"
class="form-control"
value="<?= $row['no_hp']; ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Email

</label>

<input
type="text"
class="form-control"
value="<?= $row['email']; ?>"
readonly>

</div>

</div>

<div class="col-md-6">

<div class="mb-3">

<label class="form-label">

Bidang

</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_bidang']; ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Pembimbing

</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_pembimbing']; ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

NIP Pembimbing

</label>

<input
type="text"
class="form-control"
value="<?= $row['nip']; ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Tanggal

</label>

<input
type="text"
class="form-control"
value="<?= date('d-m-Y',strtotime($row['tanggal'])); ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Jam Masuk

</label>

<input
type="text"
class="form-control"
value="<?= $row['jam_masuk'] ?: '-'; ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Jam Pulang

</label>

<input
type="text"
class="form-control"
value="<?= $row['jam_pulang'] ?: '-'; ?>"
readonly>

</div>

</div>

</div>

<hr>

<div class="row">

<div class="col-md-6">

<label class="form-label">

Status Absensi

</label>

<br>

<?php

switch($row['status']){

case "hadir":

echo '<span class="badge bg-success fs-6">Hadir</span>';

break;

case "izin":

echo '<span class="badge bg-warning text-dark fs-6">Izin</span>';

break;

case "sakit":

echo '<span class="badge bg-info fs-6">Sakit</span>';

break;

case "alpha":

echo '<span class="badge bg-danger fs-6">Alpha</span>';

break;

}

?>

</div>

<div class="col-md-6">

<label class="form-label">

Keterangan

</label>

<textarea
class="form-control"
rows="4"
readonly><?= $row['keterangan']; ?></textarea>

</div>

</div>

<hr>

<div class="text-end">

<a
href="index.php"
class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Kembali

</a>

</div>

</div>

</div>

</div>

<?php

include "../../include/footer.php";

?>
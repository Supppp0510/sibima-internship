<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$query = mysqli_query($conn,"
SELECT

l.*,

m.nama,
m.nim,
m.no_hp,
m.email,

u.nama_universitas,

ps.nama_prodi,

b.nama_bidang,

pb.nama AS nama_pembimbing,
pb.nip

FROM logbook l

LEFT JOIN mahasiswa m
ON l.mahasiswa_id=m.id

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

WHERE l.id='$id'
");

if(mysqli_num_rows($query)==0){

    echo "<script>

    alert('Data tidak ditemukan.');

    window.location='index.php';

    </script>";

    exit;

}

$row=mysqli_fetch_assoc($query);

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

Detail Logbook PKL

</h4>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<div class="mb-3">

<label>Nama Mahasiswa</label>

<input
type="text"
class="form-control"
value="<?= $row['nama']; ?>"
readonly>

</div>

<div class="mb-3">

<label>NIM</label>

<input
type="text"
class="form-control"
value="<?= $row['nim']; ?>"
readonly>

</div>

<div class="mb-3">

<label>Universitas</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_universitas']; ?>"
readonly>

</div>

<div class="mb-3">

<label>Program Studi</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_prodi']; ?>"
readonly>

</div>

<div class="mb-3">

<label>No HP</label>

<input
type="text"
class="form-control"
value="<?= $row['no_hp']; ?>"
readonly>

</div>

<div class="mb-3">

<label>Email</label>

<input
type="text"
class="form-control"
value="<?= $row['email']; ?>"
readonly>

</div>

</div>

<div class="col-md-6">

<div class="mb-3">

<label>Bidang</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_bidang']; ?>"
readonly>

</div>

<div class="mb-3">

<label>Pembimbing</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_pembimbing']; ?>"
readonly>

</div>

<div class="mb-3">

<label>NIP Pembimbing</label>

<input
type="text"
class="form-control"
value="<?= $row['nip']; ?>"
readonly>

</div>

<div class="mb-3">

<label>Tanggal</label>

<input
type="text"
class="form-control"
value="<?= date('d-m-Y',strtotime($row['tanggal'])); ?>"
readonly>

</div>

<div class="mb-3">

<label>Judul Kegiatan</label>

<input
type="text"
class="form-control"
value="<?= $row['judul']; ?>"
readonly>

</div>

</div>

</div>

<hr>

<div class="mb-3">

<label>Kegiatan</label>

<textarea
rows="8"
class="form-control"
readonly><?= $row['kegiatan']; ?></textarea>

</div>

<hr>

<div class="mb-3">

<label>Dokumentasi</label>

<br><br>

<?php if(!empty($row['dokumentasi'])){ ?>

<img

src="<?= BASE_URL ?>assets/upload/mahasiswa/dokumentasi/<?= $row['dokumentasi']; ?>"

class="img-fluid rounded border"

style="max-width:500px;">

<br><br>

<a

target="_blank"

href="<?= BASE_URL ?>assets/upload/mahasiswa/dokumentasi/<?= $row['dokumentasi']; ?>"

class="btn btn-primary">

Lihat Ukuran Asli

</a>

<?php }else{ ?>

<span class="text-danger">

Belum ada dokumentasi.

</span>

<?php } ?>

</div>

<hr>

<div class="row">

<div class="col-md-6">

<label>Status</label>

<br><br>

<?php

switch($row['status']){

case "menunggu":

echo '<span class="badge bg-warning text-dark fs-6">Menunggu</span>';

break;

case "disetujui":

echo '<span class="badge bg-success fs-6">Disetujui</span>';

break;

case "revisi":

echo '<span class="badge bg-danger fs-6">Revisi</span>';

break;

}

?>

</div>

<div class="col-md-6">

<label>Komentar Admin</label>

<textarea

class="form-control"

rows="5"

readonly><?= $row['komentar']; ?></textarea>

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

<?php if($row['status']=="menunggu"){ ?>

<a

href="verifikasi.php?id=<?= $row['id']; ?>"

class="btn btn-success">

<i class="fa fa-check"></i>

Verifikasi

</a>

<?php } ?>

</div>

</div>

</div>

</div>

<?php

include "../../include/footer.php";

?>
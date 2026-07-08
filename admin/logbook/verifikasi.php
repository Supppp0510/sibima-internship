<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$query = mysqli_query($conn,"
SELECT

l.*,

m.nama,
m.nim,

u.nama_universitas,

ps.nama_prodi,

b.nama_bidang,

pb.nama AS nama_pembimbing

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

    header("Location:index.php");
    exit;

}

$row=mysqli_fetch_assoc($query);

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h4 class="mb-0">

Verifikasi Logbook

</h4>

</div>

<div class="card-body">

<form method="POST" action="proses_verifikasi.php">

<input
type="hidden"
name="id"
value="<?= $row['id']; ?>">

<div class="mb-3">

<label>Mahasiswa</label>

<input
type="text"
class="form-control"
value="<?= $row['nama']; ?>"
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

<label>Judul</label>

<input
type="text"
class="form-control"
value="<?= $row['judul']; ?>"
readonly>

</div>

<div class="mb-3">

<label>Kegiatan</label>

<textarea
rows="8"
class="form-control"
readonly><?= $row['kegiatan']; ?></textarea>

</div>

<div class="mb-3">

<label>Dokumentasi</label>

<br><br>

<?php if(!empty($row['dokumentasi'])){ ?>

<img

src="<?= BASE_URL ?>assets/upload/mahasiswa/dokumentasi/<?= $row['dokumentasi']; ?>"

class="img-fluid rounded border"

style="max-width:450px;">

<?php }else{ ?>

Tidak ada dokumentasi.

<?php } ?>

</div>

<hr>

<div class="mb-3">

<label>Status</label>

<select
name="status"
class="form-select"
required>

<option value="menunggu"
<?= $row['status']=="menunggu" ? "selected" : ""; ?>>

Menunggu

</option>

<option value="disetujui"
<?= $row['status']=="disetujui" ? "selected" : ""; ?>>

Disetujui

</option>

<option value="revisi"
<?= $row['status']=="revisi" ? "selected" : ""; ?>>

Revisi

</option>

</select>

</div>

<div class="mb-3">

<label>Komentar</label>

<textarea

name="komentar"

rows="5"

class="form-control"><?= $row['komentar']; ?></textarea>

</div>

<div class="text-end">

<a
href="index.php"
class="btn btn-secondary">

Kembali

</a>

<button
type="submit"
class="btn btn-success">

Simpan Verifikasi

</button>

</div>

</form>

</div>

</div>

</div>

<?php

include "../../include/footer.php";

?>
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$query = mysqli_query($conn, "
SELECT

pn.*,

m.nama,
m.nim,
m.email,
m.no_hp,

u.nama_universitas,

ps.nama_prodi,

b.nama_bidang,

pb.nama AS nama_pembimbing,
pb.nip

FROM penilaian pn

LEFT JOIN mahasiswa m
ON pn.mahasiswa_id = m.id

LEFT JOIN universitas u
ON m.universitas_id = u.id

LEFT JOIN program_studi ps
ON m.program_studi_id = ps.id

LEFT JOIN pembimbing pb
ON pn.pembimbing_id = pb.id

LEFT JOIN pendaftaran pd
ON pd.mahasiswa_id = m.id
AND pd.status='diterima'

LEFT JOIN bidang b
ON pd.bidang_id = b.id

WHERE pn.id='$id'
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

<div class="card-header bg-info text-white">

<h4 class="mb-0">

Detail Penilaian PKL

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

<label>Bidang PKL</label>

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

<label>Nilai Akhir</label>

<input
type="text"
class="form-control fw-bold text-success"
value="<?= number_format($row['nilai_akhir'],2); ?>"
readonly>

</div>

</div>

</div>

<hr>

<h5 class="mb-3">

Komponen Penilaian

</h5>

<table class="table table-bordered">

<tr>

<th width="35%">Disiplin</th>

<td><?= $row['disiplin']; ?></td>

</tr>

<tr>

<th>Komunikasi</th>

<td><?= $row['komunikasi']; ?></td>

</tr>

<tr>

<th>Kerjasama</th>

<td><?= $row['kerjasama']; ?></td>

</tr>

<tr>

<th>Tanggung Jawab</th>

<td><?= $row['tanggung_jawab']; ?></td>

</tr>

<tr>

<th>Inisiatif</th>

<td><?= $row['inisiatif']; ?></td>

</tr>

<tr class="table-success">

<th>Nilai Akhir</th>

<td>

<strong>

<?= number_format($row['nilai_akhir'],2); ?>

</strong>

</td>

</tr>

</table>

<div class="mb-3">

<label>Catatan Pembimbing</label>

<textarea

class="form-control"

rows="6"

readonly><?= $row['catatan']; ?></textarea>

</div>

<div class="text-end">

<a

href="index.php"

class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Kembali

</a>

<a

href="edit.php?id=<?= $row['id']; ?>"

class="btn btn-warning">

<i class="fa fa-edit"></i>

Edit

</a>

</div>

</div>

</div>

</div>

<?php

include "../../include/footer.php";

?>
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$query = mysqli_query($conn,"
SELECT

pn.*,

m.nama,
m.nim,

u.nama_universitas,

ps.nama_prodi,

b.nama_bidang,

pb.nama AS nama_pembimbing

FROM penilaian pn

LEFT JOIN mahasiswa m
ON pn.mahasiswa_id=m.id

LEFT JOIN universitas u
ON m.universitas_id=u.id

LEFT JOIN program_studi ps
ON m.program_studi_id=ps.id

LEFT JOIN pembimbing pb
ON pn.pembimbing_id=pb.id

LEFT JOIN pendaftaran pd
ON pd.mahasiswa_id=m.id
AND pd.status='diterima'

LEFT JOIN bidang b
ON pd.bidang_id=b.id

WHERE pn.id='$id'
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

<div class="card-header bg-warning">

<h4>Edit Penilaian PKL</h4>

</div>

<div class="card-body">

<form
method="POST"
action="proses_edit.php">

<input
type="hidden"
name="id"
value="<?= $row['id']; ?>">

<div class="mb-3">

<label>Mahasiswa</label>

<input
type="text"
class="form-control"
value="<?= $row['nama']; ?> (<?= $row['nim']; ?>)"
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

<hr>

<div class="row">

<div class="col-md-6">

<label>Disiplin</label>

<input
type="number"
name="disiplin"
class="form-control"
min="0"
max="100"
value="<?= $row['disiplin']; ?>"
required>

</div>

<div class="col-md-6">

<label>Komunikasi</label>

<input
type="number"
name="komunikasi"
class="form-control"
min="0"
max="100"
value="<?= $row['komunikasi']; ?>"
required>

</div>

</div>

<br>

<div class="row">

<div class="col-md-6">

<label>Kerjasama</label>

<input
type="number"
name="kerjasama"
class="form-control"
min="0"
max="100"
value="<?= $row['kerjasama']; ?>"
required>

</div>

<div class="col-md-6">

<label>Tanggung Jawab</label>

<input
type="number"
name="tanggung_jawab"
class="form-control"
min="0"
max="100"
value="<?= $row['tanggung_jawab']; ?>"
required>

</div>

</div>

<br>

<div class="mb-3">

<label>Inisiatif</label>

<input
type="number"
name="inisiatif"
class="form-control"
min="0"
max="100"
value="<?= $row['inisiatif']; ?>"
required>

</div>

<div class="mb-3">

<label>Catatan</label>

<textarea
name="catatan"
rows="5"
class="form-control"><?= $row['catatan']; ?></textarea>

</div>

<div class="text-end">

<a
href="index.php"
class="btn btn-secondary">

Kembali

</a>

<button
type="submit"
class="btn btn-warning">

Update Penilaian

</button>

</div>

</form>

</div>

</div>

</div>

<?php

include "../../include/footer.php";

?>
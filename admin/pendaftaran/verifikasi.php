<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

// ===============================
// Ambil Data Pendaftaran
// ===============================

$data = mysqli_query($conn,"
SELECT
p.*,
m.nama,
m.nim,
u.nama_universitas,
ps.nama_prodi,
pr.nama_periode,
b.nama_bidang

FROM pendaftaran p

LEFT JOIN mahasiswa m
ON p.mahasiswa_id=m.id

LEFT JOIN universitas u
ON m.universitas_id=u.id

LEFT JOIN program_studi ps
ON m.program_studi_id=ps.id

LEFT JOIN periode_pkl pr
ON p.periode_id=pr.id

LEFT JOIN bidang b
ON p.bidang_id=b.id

WHERE p.id='$id'
");

if(mysqli_num_rows($data)==0){

    header("Location:index.php");
    exit;

}

$row = mysqli_fetch_assoc($data);

// ===============================
// Ambil Pembimbing Sesuai Bidang
// ===============================

$pembimbing = mysqli_query($conn,"
SELECT *
FROM pembimbing
WHERE bidang_id='".$row['bidang_id']."'
ORDER BY nama ASC
");

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h4 class="mb-0">

Verifikasi Pendaftaran PKL

</h4>

</div>

<div class="card-body">

<form
method="POST"
action="proses_verifikasi.php">

<input
type="hidden"
name="id"
value="<?= $row['id']; ?>">

<div class="row">

<div class="col-md-6">

<div class="mb-3">

<label class="form-label">

Mahasiswa

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

</div>

<div class="col-md-6">

<div class="mb-3">

<label class="form-label">

Periode

</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_periode']; ?>"
readonly>

</div>

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

Tanggal Daftar

</label>

<input
type="text"
class="form-control"
value="<?= date('d-m-Y',strtotime($row['tanggal_daftar'])); ?>"
readonly>

</div>

</div>

</div>

<hr>

<h5>Berkas</h5>

<div class="mb-3">

<a
target="_blank"
class="btn btn-outline-primary"

href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['surat_pengantar']; ?>">

Surat Pengantar

</a>

<a
target="_blank"
class="btn btn-outline-success"

href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['cv']; ?>">

CV

</a>

<a
target="_blank"
class="btn btn-outline-warning"

href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['transkrip']; ?>">

Transkrip

</a>

<a
target="_blank"
class="btn btn-outline-danger"

href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['proposal']; ?>">

Proposal

</a>

</div>

<hr>

<div class="mb-3">

<label class="form-label">

Pembimbing

</label>

<select
name="pembimbing_id"
class="form-select">

<option value="">

-- Pilih Pembimbing --

</option>

<?php while($pb=mysqli_fetch_assoc($pembimbing)){ ?>

<option
value="<?= $pb['id']; ?>"

<?= $row['pembimbing_id']==$pb['id'] ? "selected" : ""; ?>>

<?= $pb['nama']; ?>

(<?= $pb['nip']; ?>)

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Status

</label>

<select
name="status"
class="form-select">

<option
value="menunggu"

<?= $row['status']=="menunggu" ? "selected" : ""; ?>>

Menunggu

</option>

<option
value="diterima"

<?= $row['status']=="diterima" ? "selected" : ""; ?>>

Diterima

</option>

<option
value="ditolak"

<?= $row['status']=="ditolak" ? "selected" : ""; ?>>

Ditolak

</option>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Catatan Admin

</label>

<textarea
name="catatan_admin"
rows="5"
class="form-control"><?= $row['catatan_admin']; ?></textarea>

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
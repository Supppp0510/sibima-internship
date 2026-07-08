<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$universitas=mysqli_query($conn,"
SELECT *
FROM universitas
ORDER BY nama_universitas ASC
");

$prodi=mysqli_query($conn,"
SELECT *
FROM program_studi
ORDER BY nama_prodi ASC
");

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

Tambah Mahasiswa

</h4>

</div>

<div class="card-body">

<form
action="proses_tambah.php"
method="POST"
enctype="multipart/form-data">

<div class="row">

<div class="col-md-6">

<label class="form-label">

NIM

</label>

<input
type="text"
name="nim"
class="form-control"
required>

</div>

<div class="col-md-6">

<label class="form-label">

Nama Mahasiswa

</label>

<input
type="text"
name="nama"
class="form-control"
required>

</div>

</div>

<br>

<div class="row">

<div class="col-md-6">

<label class="form-label">

Universitas

</label>

<select
name="universitas_id"
class="form-select"
required>

<option value="">-- Pilih Universitas --</option>

<?php

while($u=mysqli_fetch_assoc($universitas)){

?>

<option value="<?= $u['id']; ?>">

<?= $u['nama_universitas']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-6">

<label class="form-label">

Program Studi

</label>

<select
name="program_studi_id"
class="form-select"
required>

<option value="">-- Pilih Program Studi --</option>

<?php

while($p=mysqli_fetch_assoc($prodi)){

?>

<option value="<?= $p['id']; ?>">

<?= $p['nama_prodi']; ?>

</option>

<?php } ?>

</select>

</div>

</div>

<br>

<div class="row">

<div class="col-md-6">

<label class="form-label">

No HP

</label>

<input
type="text"
name="no_hp"
class="form-control"
required>

</div>

<div class="col-md-6">

<label class="form-label">

Email

</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

</div>

<br>

<div class="mb-3">

<label class="form-label">

Alamat

</label>

<textarea
name="alamat"
class="form-control"
rows="3"
required></textarea>

</div>

<div class="mb-4">

<label class="form-label">

Foto Mahasiswa

</label>

<input
type="file"
name="foto"
class="form-control"
accept=".jpg,.jpeg,.png">

<small class="text-muted">

Format: JPG, JPEG, PNG

</small>

</div>

<button
type="submit"
class="btn btn-success">

<i class="fa-solid fa-floppy-disk"></i>

Simpan

</button>

<a
href="index.php"
class="btn btn-secondary">

Kembali

</a>

</form>

</div>

</div>

</div>

<?php

include "../../include/footer.php";

?>
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4>

Tambah Periode PKL

</h4>

</div>

<div class="card-body">

<form action="proses_tambah.php" method="POST">

<div class="mb-3">

<label>Nama Periode</label>

<input
type="text"
name="nama_periode"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Tanggal Mulai</label>

<input
type="date"
name="tanggal_mulai"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Tanggal Selesai</label>

<input
type="date"
name="tanggal_selesai"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Status</label>

<select
name="status"
class="form-control">

<option value="aktif">Aktif</option>
<option value="selesai">Selesai</option>

</select>

</div>

<button class="btn btn-success">

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
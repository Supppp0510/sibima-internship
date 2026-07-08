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

Tambah Program Studi

</h4>

</div>

<div class="card-body">

<form action="proses_tambah.php" method="POST">

<div class="mb-3">

<label>

Nama Program Studi

</label>

<input
type="text"
name="nama_prodi"
class="form-control"
required>

</div>

<div class="mb-3">

<label>

Jenjang

</label>

<select
name="jenjang"
class="form-control"
required>

<option value="">-- Pilih Jenjang --</option>

<option value="D3">D3</option>

<option value="D4">D4</option>

<option value="S1">S1</option>

<option value="S2">S2</option>

</select>

</div>

<button
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
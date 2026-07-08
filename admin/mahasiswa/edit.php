<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$data = mysqli_query($conn,"
SELECT *
FROM mahasiswa
WHERE id='$id'
");

if(mysqli_num_rows($data)==0){

    header("Location:index.php");
    exit;

}

$row = mysqli_fetch_assoc($data);

$universitas = mysqli_query($conn,"
SELECT *
FROM universitas
ORDER BY nama_universitas ASC
");

$prodi = mysqli_query($conn,"
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

<div class="card-header bg-warning text-dark">

<h4 class="mb-0">

Edit Mahasiswa

</h4>

</div>

<div class="card-body">

<form
action="proses_edit.php"
method="POST"
enctype="multipart/form-data">

<input
type="hidden"
name="id"
value="<?= $row['id']; ?>">

<input
type="hidden"
name="foto_lama"
value="<?= $row['foto']; ?>">

<div class="row">

<div class="col-md-6">

<label>NIM</label>

<input
type="text"
name="nim"
class="form-control"
value="<?= $row['nim']; ?>"
required>

</div>

<div class="col-md-6">

<label>Nama Mahasiswa</label>

<input
type="text"
name="nama"
class="form-control"
value="<?= $row['nama']; ?>"
required>

</div>

</div>

<br>

<div class="row">

<div class="col-md-6">

<label>Universitas</label>

<select
name="universitas_id"
class="form-select"
required>

<?php

while($u=mysqli_fetch_assoc($universitas)){

?>

<option
value="<?= $u['id']; ?>"

<?= ($u['id']==$row['universitas_id']) ? "selected" : ""; ?>

>

<?= $u['nama_universitas']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-6">

<label>Program Studi</label>

<select
name="program_studi_id"
class="form-select"
required>

<?php

while($p=mysqli_fetch_assoc($prodi)){

?>

<option
value="<?= $p['id']; ?>"

<?= ($p['id']==$row['program_studi_id']) ? "selected" : ""; ?>

>

<?= $p['nama_prodi']; ?>

</option>

<?php } ?>

</select>

</div>

</div>

<br>

<div class="row">

<div class="col-md-6">

<label>No HP</label>

<input
type="text"
name="no_hp"
class="form-control"
value="<?= $row['no_hp']; ?>"
required>

</div>

<div class="col-md-6">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
value="<?= $row['email']; ?>"
required>

</div>

</div>

<br>

<div class="mb-3">

<label>Alamat</label>

<textarea
name="alamat"
class="form-control"
rows="3"
required><?= $row['alamat']; ?></textarea>

</div>

<div class="mb-3">

<label>Foto Lama</label>

<br>

<?php

if($row['foto']==""){

echo "Belum ada foto";

}else{

?>

<img
src="../../assets/upload/mahasiswa/foto/<?= $row['foto']; ?>"
width="120"
class="img-thumbnail">

<?php } ?>

</div>

<div class="mb-4">

<label>Ganti Foto</label>

<input
type="file"
name="foto"
class="form-control">

<small class="text-muted">

Kosongkan jika tidak ingin mengganti foto.

</small>

</div>

<button
class="btn btn-warning">

<i class="fa-solid fa-pen"></i>

Update

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
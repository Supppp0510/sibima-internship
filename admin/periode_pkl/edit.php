<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$data = mysqli_query($conn,"
SELECT *
FROM periode_pkl
WHERE id='$id'
");

if(mysqli_num_rows($data)==0){

    header("Location:index.php");
    exit;

}

$row = mysqli_fetch_assoc($data);

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-warning">

<h4>Edit Periode PKL</h4>

</div>

<div class="card-body">

<form action="proses_edit.php" method="POST">

<input
type="hidden"
name="id"
value="<?= $row['id']; ?>">

<div class="mb-3">

<label>Nama Periode</label>

<input
type="text"
name="nama_periode"
class="form-control"
value="<?= $row['nama_periode']; ?>"
required>

</div>

<div class="mb-3">

<label>Tanggal Mulai</label>

<input
type="date"
name="tanggal_mulai"
class="form-control"
value="<?= $row['tanggal_mulai']; ?>"
required>

</div>

<div class="mb-3">

<label>Tanggal Selesai</label>

<input
type="date"
name="tanggal_selesai"
class="form-control"
value="<?= $row['tanggal_selesai']; ?>"
required>

</div>

<div class="mb-3">

<label>Status</label>

<select
name="status"
class="form-control">

<option
value="aktif"
<?= ($row['status']=="aktif") ? "selected" : ""; ?>>
Aktif
</option>

<option
value="selesai"
<?= ($row['status']=="selesai") ? "selected" : ""; ?>>
Selesai
</option>

</select>

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
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$data = mysqli_query($conn,"
SELECT *
FROM program_studi
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

<h4>Edit Program Studi</h4>

</div>

<div class="card-body">

<form action="proses_edit.php" method="POST">

<input
type="hidden"
name="id"
value="<?= $row['id']; ?>">

<div class="mb-3">

<label>Nama Program Studi</label>

<input
type="text"
name="nama_prodi"
class="form-control"
value="<?= $row['nama_prodi']; ?>"
required>

</div>

<div class="mb-3">

<label>Jenjang</label>

<select
name="jenjang"
class="form-control">

<option
value="D3"
<?= $row['jenjang']=="D3" ? "selected" : ""; ?>>
D3
</option>

<option
value="D4"
<?= $row['jenjang']=="D4" ? "selected" : ""; ?>>
D4
</option>

<option
value="S1"
<?= $row['jenjang']=="S1" ? "selected" : ""; ?>>
S1
</option>

<option
value="S2"
<?= $row['jenjang']=="S2" ? "selected" : ""; ?>>
S2
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
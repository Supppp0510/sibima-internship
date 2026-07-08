<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$data = mysqli_query($conn,"
SELECT *
FROM pembimbing
WHERE id='$id'
");

if(mysqli_num_rows($data)==0){

    header("Location:index.php");
    exit;

}

$row = mysqli_fetch_assoc($data);

$bidang = mysqli_query($conn,"
SELECT *
FROM bidang
ORDER BY nama_bidang ASC
");

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-warning text-dark">

<h4 class="mb-0">

Edit Pembimbing

</h4>

</div>

<div class="card-body">

<form
action="proses_edit.php"
method="POST">

<input
type="hidden"
name="id"
value="<?= $row['id']; ?>">

<div class="row">

<div class="col-md-6">

<label class="form-label">

NIP

</label>

<input
type="text"
name="nip"
class="form-control"
value="<?= $row['nip']; ?>"
required>

</div>

<div class="col-md-6">

<label class="form-label">

Nama Pembimbing

</label>

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

<label class="form-label">

Bidang

</label>

<select
name="bidang_id"
class="form-select"
required>

<?php

while($b=mysqli_fetch_assoc($bidang)){

?>

<option
value="<?= $b['id']; ?>"
<?= ($b['id']==$row['bidang_id']) ? "selected" : ""; ?>>

<?= $b['nama_bidang']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-6">

<label class="form-label">

Jabatan

</label>

<input
type="text"
name="jabatan"
class="form-control"
value="<?= $row['jabatan']; ?>"
required>

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
value="<?= $row['no_hp']; ?>"
required>

</div>

</div>

<br>

<button
type="submit"
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
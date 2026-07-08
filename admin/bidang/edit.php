<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$data = mysqli_query($conn,"
SELECT *
FROM bidang
WHERE id='$id'
");

$row = mysqli_fetch_assoc($data);

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header">
<h4>Edit Bidang</h4>
</div>

<div class="card-body">

<form action="proses_edit.php" method="POST">

<input type="hidden" name="id" value="<?= $row['id']; ?>">

<div class="mb-3">
<label>Nama Bidang</label>
<input
type="text"
name="nama_bidang"
class="form-control"
value="<?= $row['nama_bidang']; ?>"
required>
</div>

<div class="mb-3">
<label>Kuota</label>
<input
type="number"
name="kuota"
class="form-control"
value="<?= $row['kuota']; ?>"
required>
</div>

<div class="mb-3">
<label>Deskripsi</label>

<textarea
name="deskripsi"
class="form-control"
rows="4"><?= $row['deskripsi']; ?></textarea>

</div>

<div class="mb-3">

<label>Status</label>

<select
name="status"
class="form-control">

<option value="aktif"
<?= $row['status']=='aktif'?'selected':''; ?>>
Aktif
</option>

<option value="nonaktif"
<?= $row['status']=='nonaktif'?'selected':''; ?>>
Non Aktif
</option>

</select>

</div>

<button class="btn btn-primary">
Update
</button>

<a href="index.php"
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
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

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

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

Tambah Pembimbing

</h4>

</div>

<div class="card-body">

<form
action="proses_tambah.php"
method="POST">

<div class="row">

<div class="col-md-6">

<label class="form-label">

NIP

</label>

<input
type="text"
name="nip"
class="form-control"
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

<option value="">-- Pilih Bidang --</option>

<?php

while($b=mysqli_fetch_assoc($bidang)){

?>

<option value="<?= $b['id']; ?>">

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
required>

</div>

</div>

<br>

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
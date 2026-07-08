<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$query = mysqli_query($conn,"
SELECT

m.id,
m.nama,
m.nim,

u.nama_universitas,

ps.nama_prodi,

pb.id AS pembimbing_id,
pb.nama AS nama_pembimbing,

b.nama_bidang

FROM mahasiswa m

INNER JOIN pendaftaran pd
ON pd.mahasiswa_id=m.id
AND pd.status='diterima'

LEFT JOIN universitas u
ON m.universitas_id=u.id

LEFT JOIN program_studi ps
ON m.program_studi_id=ps.id

LEFT JOIN bidang b
ON pd.bidang_id=b.id

LEFT JOIN pembimbing pb
ON pd.pembimbing_id=pb.id

WHERE m.id NOT IN(

SELECT mahasiswa_id

FROM penilaian

)

ORDER BY m.nama ASC
");

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4>Tambah Penilaian PKL</h4>

</div>

<div class="card-body">

<form
method="POST"
action="proses_tambah.php">

<div class="mb-3">

<label>Mahasiswa</label>

<select
name="mahasiswa_id"
class="form-select"
required>

<option value="">-- Pilih Mahasiswa --</option>

<?php while($m=mysqli_fetch_assoc($query)){ ?>

<option

value="<?= $m['id']; ?>"

data-pembimbing="<?= $m['pembimbing_id']; ?>">

<?= $m['nama']; ?>

(<?= $m['nim']; ?>)

-

<?= $m['nama_bidang']; ?>

-

<?= $m['nama_pembimbing']; ?>

</option>

<?php } ?>

</select>

</div>

<input
type="hidden"
name="pembimbing_id"
id="pembimbing_id">

<hr>

<div class="row">

<div class="col-md-6">

<label>Disiplin</label>

<input
type="number"
name="disiplin"
class="form-control"
min="0"
max="100"
required>

</div>

<div class="col-md-6">

<label>Komunikasi</label>

<input
type="number"
name="komunikasi"
class="form-control"
min="0"
max="100"
required>

</div>

</div>

<br>

<div class="row">

<div class="col-md-6">

<label>Kerjasama</label>

<input
type="number"
name="kerjasama"
class="form-control"
min="0"
max="100"
required>

</div>

<div class="col-md-6">

<label>Tanggung Jawab</label>

<input
type="number"
name="tanggung_jawab"
class="form-control"
min="0"
max="100"
required>

</div>

</div>

<br>

<div class="mb-3">

<label>Inisiatif</label>

<input
type="number"
name="inisiatif"
class="form-control"
min="0"
max="100"
required>

</div>

<div class="mb-3">

<label>Catatan</label>

<textarea
name="catatan"
rows="5"
class="form-control"></textarea>

</div>

<div class="text-end">

<a
href="index.php"
class="btn btn-secondary">

Kembali

</a>

<button
type="submit"
class="btn btn-primary">

Simpan

</button>

</div>

</form>

</div>

</div>

</div>

<script>

const mahasiswa=document.querySelector("select[name='mahasiswa_id']");

mahasiswa.addEventListener("change",function(){

document.getElementById("pembimbing_id").value=

this.options[this.selectedIndex].dataset.pembimbing;

});

</script>

<?php

include "../../include/footer.php";

?>
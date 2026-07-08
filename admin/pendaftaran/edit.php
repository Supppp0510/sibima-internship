<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

// ==========================================
// Ambil Data Pendaftaran
// ==========================================

$data = mysqli_query($conn,"
SELECT
p.*,
m.nim,
m.nama AS nama_mahasiswa,
u.nama_universitas,
pr.nama_prodi,
b.nama_bidang
FROM pendaftaran p
LEFT JOIN mahasiswa m
ON p.mahasiswa_id=m.id
LEFT JOIN universitas u
ON m.universitas_id=u.id
LEFT JOIN program_studi pr
ON m.program_studi_id=pr.id
LEFT JOIN bidang b
ON p.bidang_id=b.id
WHERE p.id='$id'
");

if(mysqli_num_rows($data)==0){

    header("Location:index.php");
    exit;

}

$row=mysqli_fetch_assoc($data);

// ==========================================
// Ambil Semua Bidang
// ==========================================

$listBidang=mysqli_query($conn,"
SELECT *
FROM bidang
ORDER BY nama_bidang ASC
");

// ==========================================
// Ambil Pembimbing Sesuai Bidang
// ==========================================

$listPembimbing=mysqli_query($conn,"
SELECT *
FROM pembimbing
WHERE bidang_id='".$row['bidang_id']."'
ORDER BY nama ASC
");

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-warning text-dark">

<h4 class="mb-0">

Edit Pendaftaran PKL

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

<label>Mahasiswa</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_mahasiswa']; ?>"
readonly>

</div>

<div class="col-md-6">

<label>NIM</label>

<input
type="text"
class="form-control"
value="<?= $row['nim']; ?>"
readonly>

</div>

</div>

<br>

<div class="row">

<div class="col-md-6">

<label>Universitas</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_universitas']; ?>"
readonly>

</div>

<div class="col-md-6">

<label>Program Studi</label>

<input
type="text"
class="form-control"
value="<?= $row['nama_prodi']; ?>"
readonly>

</div>

</div>

<br>

<div class="row">

<div class="col-md-6">

<label>Bidang</label>

<select
name="bidang_id"
id="bidang"
class="form-select"
required>

<?php

while($b=mysqli_fetch_assoc($listBidang)){

?>

<option

value="<?= $b['id']; ?>"

<?= $row['bidang_id']==$b['id'] ? "selected" : ""; ?>>

<?= $b['nama_bidang']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-6">

<label>Pembimbing</label>

<select
name="pembimbing_id"
class="form-select"
required>

<option value="">

-- Pilih Pembimbing --

</option>

<?php

while($pb=mysqli_fetch_assoc($listPembimbing)){

?>

<option

value="<?= $pb['id']; ?>"

<?= $pb['id']==$row['pembimbing_id'] ? "selected" : ""; ?>>

<?= $pb['nama']; ?>

</option>

<?php } ?>

</select>

</div>

</div>

<br>

<label>Status</label>

<select
name="status"
class="form-select"
required>

<option
value="menunggu"
<?= $row['status']=="menunggu" ? "selected" : ""; ?>>

Menunggu

</option>

<option
value="diterima"
<?= $row['status']=="diterima" ? "selected" : ""; ?>>

Diterima

</option>

<option
value="ditolak"
<?= $row['status']=="ditolak" ? "selected" : ""; ?>>

Ditolak

</option>

</select>

<br>

<label>Catatan Admin</label>

<textarea
name="catatan_admin"
rows="5"
class="form-control"><?= $row['catatan_admin']; ?></textarea>

<br>

<h5>Berkas</h5>

<a
target="_blank"
class="btn btn-primary btn-sm"
href="../../assets/upload/pendaftaran/surat_pengantar/<?= $row['surat_pengantar']; ?>">

Surat Pengantar

</a>

<a
target="_blank"
class="btn btn-success btn-sm"
href="../../assets/upload/pendaftaran/cv/<?= $row['cv']; ?>">

CV

</a>

<a
target="_blank"
class="btn btn-warning btn-sm"
href="../../assets/upload/pendaftaran/transkrip/<?= $row['transkrip']; ?>">

Transkrip

</a>

<a
target="_blank"
class="btn btn-danger btn-sm"
href="../../assets/upload/pendaftaran/proposal/<?= $row['proposal']; ?>">

Proposal

</a>

<br><br>
<div class="text-end">

<button
type="submit"
class="btn btn-warning">

<i class="fa fa-save"></i>

Update Pendaftaran

</button>

<a
href="index.php"
class="btn btn-secondary">

Kembali

</a>

</div>

</form>

</div>

</div>

</div>

<script>

const bidang=document.getElementById("bidang");

const pembimbing=document.querySelector("select[name='pembimbing_id']");

const pembimbingLama=pembimbing.value;

bidang.addEventListener("change",function(){

fetch(

"get_pembimbing.php?bidang_id="+this.value+

"&selected="+pembimbing.value

)

.then(res=>res.text())

.then(html=>{

pembimbing.innerHTML=html;

});

});

</script>

<?php

include "../../include/footer.php";

?>
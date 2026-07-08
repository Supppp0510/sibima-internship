<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$data = mysqli_query($conn, "
SELECT
    p.*,
    m.nama,
    m.nim,
    m.no_hp,
    m.email,
    u.nama_universitas,
    ps.nama_prodi,
    pr.nama_periode,
    b.nama_bidang,
    pb.nama AS nama_pembimbing

FROM pendaftaran p

LEFT JOIN mahasiswa m
ON p.mahasiswa_id = m.id

LEFT JOIN universitas u
ON m.universitas_id = u.id

LEFT JOIN program_studi ps
ON m.program_studi_id = ps.id

LEFT JOIN periode_pkl pr
ON p.periode_id = pr.id

LEFT JOIN bidang b
ON p.bidang_id = b.id

LEFT JOIN pembimbing pb
ON p.pembimbing_id = pb.id

WHERE p.id='$id'
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

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

Detail Pendaftaran PKL

</h4>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<table class="table">

<tr>
<th width="180">Nama</th>
<td><?= $row['nama']; ?></td>
</tr>

<tr>
<th>NIM</th>
<td><?= $row['nim']; ?></td>
</tr>

<tr>
<th>Universitas</th>
<td><?= $row['nama_universitas']; ?></td>
</tr>

<tr>
<th>Program Studi</th>
<td><?= $row['nama_prodi']; ?></td>
</tr>

<tr>
<th>No HP</th>
<td><?= $row['no_hp']; ?></td>
</tr>

<tr>
<th>Email</th>
<td><?= $row['email']; ?></td>
</tr>

</table>

</div>

<div class="col-md-6">

<table class="table">

<tr>
<th width="180">Periode</th>
<td><?= $row['nama_periode']; ?></td>
</tr>

<tr>
<th>Bidang</th>
<td><?= $row['nama_bidang']; ?></td>
</tr>

<tr>
<th>Tanggal Daftar</th>
<td><?= date('d-m-Y',strtotime($row['tanggal_daftar'])); ?></td>
</tr>

<tr>
<th>Pembimbing</th>
<td>

<?= $row['nama_pembimbing'] ? $row['nama_pembimbing'] : "-"; ?>

</td>
</tr>

<tr>
<th>Status</th>

<td>

<?php

if($row['status']=="menunggu"){

echo "<span class='badge bg-warning text-dark'>Menunggu</span>";

}elseif($row['status']=="diterima"){

echo "<span class='badge bg-success'>Diterima</span>";

}else{

echo "<span class='badge bg-danger'>Ditolak</span>";

}

?>

</td>

</tr>

</table>

</div>

</div>

<hr>

<h5>Berkas</h5>

<table class="table table-bordered">

<tr>

<th width="220">

Surat Pengantar

</th>

<td>

<a
target="_blank"
class="btn btn-primary btn-sm"

href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['surat_pengantar']; ?>">

Lihat PDF

</a>

</td>

</tr>

<tr>

<th>CV</th>

<td>

<a
target="_blank"
class="btn btn-success btn-sm"

href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['cv']; ?>">

Lihat PDF

</a>

</td>

</tr>

<tr>

<th>Transkrip</th>

<td>

<a
target="_blank"
class="btn btn-warning btn-sm"

href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['transkrip']; ?>">

Lihat PDF

</a>

</td>

</tr>

<tr>

<th>Proposal</th>

<td>

<a
target="_blank"
class="btn btn-danger btn-sm"

href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['proposal']; ?>">

Lihat PDF

</a>

</td>

</tr>

</table>

<hr>

<h5>Catatan Admin</h5>

<div class="border rounded p-3">

<?= $row['catatan_admin'] ? nl2br($row['catatan_admin']) : "-"; ?>

</div>

<br>

<a
href="index.php"
class="btn btn-secondary">

Kembali

</a>

<?php

if($row['status']=="menunggu"){

?>

<a

href="verifikasi.php?id=<?= $row['id']; ?>"

class="btn btn-success">

Verifikasi

</a>

<?php } ?>

</div>

</div>

</div>

<?php

include "../../include/footer.php";

?>
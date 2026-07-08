<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = $_GET['id'];

$query = mysqli_query($conn,"
SELECT

s.*,

m.nama,
m.nim,

u.nama_universitas,

ps.nama_prodi,

pn.nilai_akhir,

b.nama_bidang,

pb.nama AS pembimbing

FROM sertifikat s

LEFT JOIN mahasiswa m
ON s.mahasiswa_id=m.id

LEFT JOIN universitas u
ON m.universitas_id=u.id

LEFT JOIN program_studi ps
ON m.program_studi_id=ps.id

LEFT JOIN penilaian pn
ON pn.mahasiswa_id=m.id

LEFT JOIN pendaftaran pd
ON pd.mahasiswa_id=m.id

LEFT JOIN bidang b
ON pd.bidang_id=b.id

LEFT JOIN pembimbing pb
ON pd.pembimbing_id=pb.id

WHERE s.id='$id'
");

if(mysqli_num_rows($query)==0){

    header("Location:index.php");
    exit;

}

$row=mysqli_fetch_assoc($query);

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-info text-white">

<h4>Detail Sertifikat</h4>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th width="250">Nomor Sertifikat</th>

<td><?= $row['nomor_sertifikat']; ?></td>

</tr>

<tr>

<th>Nama Mahasiswa</th>

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

<th>Bidang</th>

<td><?= $row['nama_bidang']; ?></td>

</tr>

<tr>

<th>Pembimbing</th>

<td><?= $row['pembimbing']; ?></td>

</tr>

<tr>

<th>Nilai Akhir</th>

<td><?= number_format($row['nilai_akhir'],2); ?></td>

</tr>

<tr>

<th>Tanggal Terbit</th>

<td><?= date("d-m-Y",strtotime($row['tanggal_terbit'])); ?></td>

</tr>

</table>

<div class="text-end">

<a
href="index.php"
class="btn btn-secondary">

Kembali

</a>

<a
href="download.php?id=<?= $row['id']; ?>"
class="btn btn-success">

Download PDF

</a>

</div>

</div>

</div>

</div>

<?php

include "../../include/footer.php";

?>
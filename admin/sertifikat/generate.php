<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

require('../../library/fpdf/fpdf.php');

$id = $_GET['id'];

// ======================================
// Cek Sertifikat
// ======================================

$cek = mysqli_query($conn,"
SELECT *
FROM sertifikat
WHERE mahasiswa_id='$id'
");

if(mysqli_num_rows($cek)>0){

    echo "<script>
    alert('Sertifikat sudah dibuat.');
    window.location='index.php';
    </script>";
    exit;

}

// ======================================
// Ambil Data Mahasiswa
// ======================================

$query = mysqli_query($conn,"

SELECT

m.id,
m.nama,
m.nim,

u.nama_universitas,

ps.nama_prodi,

b.nama_bidang,

pb.nama AS pembimbing,

pn.nilai_akhir

FROM mahasiswa m

INNER JOIN pendaftaran pd
ON pd.mahasiswa_id=m.id

INNER JOIN penilaian pn
ON pn.mahasiswa_id=m.id

LEFT JOIN universitas u
ON u.id=m.universitas_id

LEFT JOIN program_studi ps
ON ps.id=m.program_studi_id

LEFT JOIN bidang b
ON b.id=pd.bidang_id

LEFT JOIN pembimbing pb
ON pb.id=pd.pembimbing_id

WHERE

m.id='$id'

AND

pd.status='diterima'

");

if(mysqli_num_rows($query)==0){

    echo "<script>
    alert('Data tidak ditemukan.');
    window.location='index.php';
    </script>";
    exit;

}

$data=mysqli_fetch_assoc($query);

// ======================================
// Nomor Sertifikat
// ======================================

$q=mysqli_query($conn,"
SELECT COUNT(*) total
FROM sertifikat
");

$r=mysqli_fetch_assoc($q);

$no=$r['total']+1;

$nomor=

sprintf("%03d",$no).

"/SIPKL/BM-JATIM/".

date("Y");

// ======================================

$tanggal=date("Y-m-d");

$namaFile="sertifikat_".$data['nim'].".pdf";

$folder="../../assets/upload/sertifikat/";

if(!is_dir($folder)){

    mkdir($folder,0777,true);

}

// ======================================
// PDF
// ======================================

$pdf=new FPDF();

$pdf->AddPage();

$pdf->SetTitle("Sertifikat PKL");

$pdf->SetFont('Arial','B',18);

$pdf->Cell(

190,

10,

'SERTIFIKAT PRAKTIK KERJA LAPANGAN',

0,

1,

'C'

);

$pdf->Ln(5);

$pdf->SetFont('Arial','',12);

$pdf->Cell(

190,

8,

'Nomor : '.$nomor,

0,

1,

'C'

);

$pdf->Ln(8);

$pdf->MultiCell(

190,

8,

'Diberikan kepada mahasiswa yang telah menyelesaikan Praktik Kerja Lapangan (PKL) di Dinas PU Bina Marga Provinsi Jawa Timur.',

0,

'C'

);

$pdf->Ln(8);

$pdf->SetFont('Arial','',12);

$pdf->Cell(55,8,'Nama',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->Cell(120,8,$data['nama'],0,1);

$pdf->Cell(55,8,'NIM',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->Cell(120,8,$data['nim'],0,1);

$pdf->Cell(55,8,'Universitas',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->MultiCell(120,8,$data['nama_universitas']);

$pdf->Cell(55,8,'Program Studi',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->Cell(120,8,$data['nama_prodi'],0,1);

$pdf->Cell(55,8,'Bidang PKL',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->Cell(120,8,$data['nama_bidang'],0,1);

$pdf->Cell(55,8,'Pembimbing',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->Cell(120,8,$data['pembimbing'],0,1);

$pdf->Cell(55,8,'Nilai Akhir',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->Cell(120,8,number_format($data['nilai_akhir'],2),0,1);

$pdf->Ln(15);

$pdf->MultiCell(

190,

8,

'Sertifikat ini diberikan agar dapat dipergunakan sebagaimana mestinya.',

0,

'C'

);

$pdf->Ln(20);

$pdf->Cell(120);

$pdf->Cell(

60,

8,

'Malang, '.date('d-m-Y'),

0,

1,

'C'

);

$pdf->Cell(120);

$pdf->Cell(

60,

8,

'Mengetahui',

0,

1,

'C'

);

$pdf->Ln(20);

$pdf->Cell(120);

$pdf->Cell(

60,

8,

'(...........................)',

0,

1,

'C'

);

// ======================================
// Simpan PDF
// ======================================

$pathFile = $folder . $namaFile;

$pdf->Output("F", $pathFile);

// ======================================
// Simpan ke Database
// ======================================

$simpan = mysqli_query($conn, "

INSERT INTO sertifikat(

mahasiswa_id,
nomor_sertifikat,
tanggal_terbit,
file_pdf

)

VALUES(

'".$data['id']."',

'".$nomor."',

'".$tanggal."',

'".$namaFile."'

)

");

if($simpan){

    activityLog(

        $conn,

        "Generate Sertifikat PKL : ".$data['nama']

    );

    echo "

    <script>

    alert('Sertifikat berhasil dibuat.');

    window.location='index.php';

    </script>

    ";

}else{

    // Jika gagal simpan database,
    // hapus file PDF yang sudah dibuat

    if(file_exists($pathFile)){

        unlink($pathFile);

    }

    die('Gagal menyimpan data : '.mysqli_error($conn));

}

?>
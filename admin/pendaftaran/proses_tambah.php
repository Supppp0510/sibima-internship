<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

// ===============================
// Ambil Data
// ===============================

$mahasiswa_id = $_POST['mahasiswa_id'];
$periode_id   = $_POST['periode_id'];
$bidang_id    = $_POST['bidang_id'];
$tanggal      = $_POST['tanggal_daftar'];
$catatan_admin = mysqli_real_escape_string($conn,$_POST['catatan_admin']);

// ===============================
// Cek Mahasiswa
// ===============================

$cek = mysqli_query($conn,"
SELECT id
FROM pendaftaran
WHERE mahasiswa_id='$mahasiswa_id'
AND status IN ('menunggu','diterima')
");

if(mysqli_num_rows($cek)>0){

    echo "<script>

    alert('Mahasiswa sudah memiliki pendaftaran PKL.');

    history.back();

    </script>";

    exit;

}

// ===============================
// Folder Upload
// ===============================

$folder = "../../assets/upload/pendaftaran/";

// jika folder belum ada
if(!is_dir($folder)){

    mkdir($folder,0777,true);

}

// ===============================
// Fungsi Upload
// ===============================

function uploadPDF($input,$prefix,$folder){

    if($_FILES[$input]['error'] != 0){

        die("Upload gagal. Error Code : ".$_FILES[$input]['error']);

    }

    $ext = strtolower(pathinfo($_FILES[$input]['name'],PATHINFO_EXTENSION));

    if($ext!="pdf"){

        die("File ".$prefix." harus PDF.");

    }

    $namaBaru =

        $prefix."_".time()."_".rand(1000,9999).".pdf";

    if(!move_uploaded_file(

        $_FILES[$input]['tmp_name'],

        $folder.$namaBaru

    )){

        die("Gagal upload ".$prefix);

    }

    return $namaBaru;

}

// ===============================
// Upload
// ===============================

$surat = uploadPDF(

    "surat_pengantar",

    "surat",

    $folder

);

$cv = uploadPDF(

    "cv",

    "cv",

    $folder

);

$transkrip = uploadPDF(

    "transkrip",

    "transkrip",

    $folder

);

$proposal = uploadPDF(

    "proposal",

    "proposal",

    $folder

);

// ===============================
// Simpan
// ===============================

$simpan = mysqli_query($conn,"
INSERT INTO pendaftaran
(

mahasiswa_id,

periode_id,

bidang_id,

pembimbing_id,

tanggal_daftar,

surat_pengantar,

cv,

transkrip,

proposal,

status,

catatan_admin

)

VALUES
(

'$mahasiswa_id',

'$periode_id',

'$bidang_id',

NULL,

'$tanggal',

'$surat',

'$cv',

'$transkrip',

'$proposal',

'menunggu',

'$catatan_admin'

)

");

if($simpan){

    $mhs = mysqli_query($conn,"
    SELECT nama
    FROM mahasiswa
    WHERE id='$mahasiswa_id'
    ");

    $m = mysqli_fetch_assoc($mhs);

    activityLog(

        $conn,

        "Menambahkan Pendaftaran PKL : ".$m['nama']

    );

    header("Location:index.php");

}else{

    die(mysqli_error($conn));

}

?>
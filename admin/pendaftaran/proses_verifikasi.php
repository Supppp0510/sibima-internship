<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

// ===============================
// Ambil Data
// ===============================

$id = $_POST['id'];
$status = $_POST['status'];
$catatan_admin = mysqli_real_escape_string($conn, $_POST['catatan_admin']);

$pembimbing_id = !empty($_POST['pembimbing_id'])
    ? $_POST['pembimbing_id']
    : NULL;

// ===============================
// Ambil Data Pendaftaran
// ===============================

$q = mysqli_query($conn,"
SELECT *
FROM pendaftaran
WHERE id='$id'
");

if(mysqli_num_rows($q)==0){

    echo "<script>
    alert('Data tidak ditemukan!');
    window.location='index.php';
    </script>";

    exit;

}

$data = mysqli_fetch_assoc($q);

// ===============================
// Jika Diterima
// ===============================

if($status=="diterima"){

    // wajib pilih pembimbing

    if(empty($pembimbing_id)){

        echo "<script>

        alert('Silakan pilih pembimbing.');

        history.back();

        </script>";

        exit;

    }

    // cek pembimbing sesuai bidang

    $cek = mysqli_query($conn,"
    SELECT *
    FROM pembimbing
    WHERE id='$pembimbing_id'
    AND bidang_id='".$data['bidang_id']."'
    ");

    if(mysqli_num_rows($cek)==0){

        echo "<script>

        alert('Pembimbing tidak sesuai bidang.');

        history.back();

        </script>";

        exit;

    }

    // ==========================
    // Cek Kuota Bidang
    // ==========================

    $qBidang = mysqli_query($conn,"
    SELECT kuota
    FROM bidang
    WHERE id='".$data['bidang_id']."'
    ");

    $bidang = mysqli_fetch_assoc($qBidang);

    $qJumlah = mysqli_query($conn,"
    SELECT COUNT(*) AS total
    FROM pendaftaran
    WHERE bidang_id='".$data['bidang_id']."'
    AND status='diterima'
    AND id<>'$id'
    ");

    $jumlah = mysqli_fetch_assoc($qJumlah);

    if($jumlah['total'] >= $bidang['kuota']){

        echo "<script>

        alert('Kuota bidang sudah penuh.');

        history.back();

        </script>";

        exit;

    }

}

// ===============================
// Query Update
// ===============================

if($pembimbing_id==NULL){

    $sql = "
    UPDATE pendaftaran
    SET
        pembimbing_id = NULL,
        status = '$status',
        catatan_admin = '$catatan_admin'
    WHERE id='$id'
    ";

}else{

    $sql = "
    UPDATE pendaftaran
    SET
        pembimbing_id = '$pembimbing_id',
        status = '$status',
        catatan_admin = '$catatan_admin'
    WHERE id='$id'
    ";

}

$update = mysqli_query($conn,$sql);

if(!$update){

    die("MYSQL ERROR : ".mysqli_error($conn));

}

// ===============================
// Activity Log
// ===============================

$mhs = mysqli_query($conn,"
SELECT nama
FROM mahasiswa
WHERE id='".$data['mahasiswa_id']."'
");

if(mysqli_num_rows($mhs)>0){

    $m = mysqli_fetch_assoc($mhs);

    activityLog(

        $conn,

        "Memverifikasi pendaftaran PKL mahasiswa : ".$m['nama']." (".$status.")"

    );

}

// ===============================
// Redirect
// ===============================

echo "<script>

alert('Verifikasi berhasil disimpan.');

window.location='index.php';

</script>";

exit;

?>
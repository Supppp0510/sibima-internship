<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_POST['id'];

$bidang_id = $_POST['bidang_id'];

$pembimbing_id = !empty($_POST['pembimbing_id'])
    ? $_POST['pembimbing_id']
    : NULL;

$status = $_POST['status'];

$catatan = mysqli_real_escape_string(
    $conn,
    $_POST['catatan_admin']
);

// =====================================
// Ambil Data Lama
// =====================================

$q = mysqli_query($conn,"
SELECT *
FROM pendaftaran
WHERE id='$id'
");

if(mysqli_num_rows($q)==0){

    echo "<script>

    alert('Data tidak ditemukan.');

    window.location='index.php';

    </script>";

    exit;

}

$data = mysqli_fetch_assoc($q);

// =====================================
// Jika Status Ditolak
// =====================================

if($status=="ditolak"){

    $pembimbing_id = NULL;

}

// =====================================
// Jika Status Diterima
// =====================================

if($status=="diterima"){

    if(empty($pembimbing_id)){

        echo "<script>

        alert('Silakan pilih pembimbing.');

        history.back();

        </script>";

        exit;

    }

    // ===========================
    // Validasi Pembimbing
    // ===========================

    $cekPembimbing = mysqli_query($conn,"
    SELECT *
    FROM pembimbing
    WHERE

    id='$pembimbing_id'

    AND

    bidang_id='$bidang_id'
    ");

    if(mysqli_num_rows($cekPembimbing)==0){

        echo "<script>

        alert('Pembimbing tidak sesuai bidang.');

        history.back();

        </script>";

        exit;

    }

    // ===========================
    // Validasi Kuota Bidang
    // ===========================

    $qBidang=mysqli_query($conn,"
    SELECT kuota
    FROM bidang
    WHERE id='$bidang_id'
    ");

    $bidang=mysqli_fetch_assoc($qBidang);

    $qJumlah=mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM pendaftaran
    WHERE

    bidang_id='$bidang_id'

    AND

    status='diterima'

    AND

    id<>'$id'
    ");

    $jumlah=mysqli_fetch_assoc($qJumlah);

    if($jumlah['total'] >= $bidang['kuota']){

        echo "<script>

        alert('Kuota bidang sudah penuh.');

        history.back();

        </script>";

        exit;

    }

}

// =====================================
// Update
// =====================================

$pembimbingValue = $pembimbing_id
    ? "'".$pembimbing_id."'"
    : "NULL";

$update = mysqli_query($conn,"
UPDATE pendaftaran
SET

bidang_id='$bidang_id',

pembimbing_id=$pembimbingValue,

status='$status',

catatan_admin='$catatan'

WHERE id='$id'
");

if($update){

    $mhs = mysqli_query($conn,"
    SELECT nama
    FROM mahasiswa
    WHERE id='".$data['mahasiswa_id']."'
    ");

    $nama = mysqli_fetch_assoc($mhs);

    activityLog(

        $conn,

        "Mengubah Pendaftaran PKL : ".$nama['nama']." menjadi ".$status

    );

    echo "<script>

    alert('Data berhasil diperbarui.');

    window.location='index.php';

    </script>";

}else{

    die(mysqli_error($conn));

}

?>
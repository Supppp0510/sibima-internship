<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../config/koneksi.php";
include "../../helper/activity_log.php";

$id            = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : '';
$status        = isset($_POST['status']) ? $_POST['status'] : '';
$catatan_admin = isset($_POST['catatan_admin']) ? mysqli_real_escape_string($conn, $_POST['catatan_admin']) : '';

// PERBAIKAN: Menangkap unit_kerja_id dengan aman dari form POST
$unit_kerja_id = ! empty($_POST['unit_kerja_id']) ? $_POST['unit_kerja_id'] : null;
$pembimbing_id = ! empty($_POST['pembimbing_id']) ? $_POST['pembimbing_id'] : null;

// Jika unit_kerja_id kosong dari input hidden, coba cek apakah dikirim dari select lain
if (empty($unit_kerja_id) && ! empty($_POST['unit_kerja_es_iv'])) {
    $unit_kerja_id = $_POST['unit_kerja_es_iv'];
}

// Penanganan ruangan (ID ruangan bisa string/angka atau NULL)
$id_ruangan = ! empty($_POST['id_ruangan']) ? "'" . mysqli_real_escape_string($conn, $_POST['id_ruangan']) . "'" : 'NULL';

$q = mysqli_query($conn, "SELECT * FROM pendaftaran WHERE id='$id'");

if (mysqli_num_rows($q) == 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}
$data       = mysqli_fetch_assoc($q);
$pelajar_id = $data['pelajar_id'];

// Kembalikan ke bawaan database jika ditolak
if ($status == "ditolak") {
    $unit_kerja_id = $data['unit_kerja_id'] ? "'" . $data['unit_kerja_id'] . "'" : 'NULL';
    $pembimbing_id = 'NULL';
    $id_ruangan    = 'NULL';
} else {
    $pembimbing_id = empty($pembimbing_id) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $pembimbing_id) . "'";
    $unit_kerja_id = empty($unit_kerja_id) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $unit_kerja_id) . "'";
}

if ($status == "diterima") {
    // Validasi Kelengkapan
    if ($pembimbing_id == 'NULL' || $unit_kerja_id == 'NULL') {
        echo "<script>alert('Silakan tentukan Unit Kerja dan Pilih Pembimbing terlebih dahulu.'); history.back();</script>";
        exit;
    }

    // Validasi Pembimbing: Pastikan ID Pembimbing tersebut benar-benar ada di Unit Kerja yang disubmit
    $cek_pb = mysqli_query($conn, "
        SELECT id FROM pembimbing
        WHERE id = " . $pembimbing_id . " AND unit_kerja_id = " . $unit_kerja_id . "
    ");

    if (mysqli_num_rows($cek_pb) == 0) {
        echo "<script>alert('Pembimbing tidak sesuai dengan Unit Kerja yang dipilih.'); history.back();</script>";
        exit;
    }

    // Cek Kuota Unit Kerja
    $qUnitKerja = mysqli_query($conn, "SELECT kuota FROM unit_kerja WHERE id=" . $unit_kerja_id);
    $unit_kerja = mysqli_fetch_assoc($qUnitKerja);

    $qJumlah = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM pendaftaran
        WHERE unit_kerja_id = " . $unit_kerja_id . "
        AND status = 'diterima'
        AND id <> '$id'
    ");
    $jumlah = mysqli_fetch_assoc($qJumlah);

    if ($unit_kerja && $jumlah['total'] >= $unit_kerja['kuota']) {
        echo "<script>alert('Kuota di Unit Kerja tersebut sudah penuh.'); history.back();</script>";
        exit;
    }
}

// 1. Eksekusi Update ke Database Pendaftaran
$sql = "UPDATE pendaftaran SET
            unit_kerja_id = $unit_kerja_id,
            pembimbing_id = $pembimbing_id,
            status = '$status',
            catatan_admin = '$catatan_admin'
        WHERE id='$id'";

$update = mysqli_query($conn, $sql);

if (! $update) {
    die("MYSQL ERROR (Pendaftaran) : " . mysqli_error($conn));
}

// 2. Eksekusi Update ke Database Pelajar (Mendaftarkan Ruangan)
$sql_ruangan    = "UPDATE pelajar SET id_ruangan = $id_ruangan WHERE id = '$pelajar_id'";
$update_ruangan = mysqli_query($conn, $sql_ruangan);

if (! $update_ruangan) {
    die("MYSQL ERROR (Ruangan Pelajar) : " . mysqli_error($conn));
}

// Activity Log
$pelajar = mysqli_query($conn, "SELECT nama FROM pelajar WHERE id='" . $pelajar_id . "'");

if (mysqli_num_rows($pelajar) > 0) {
    $p = mysqli_fetch_assoc($pelajar);
    activityLog($conn, "Memverifikasi pendaftaran PKL pelajar : " . $p['nama'] . " (" . $status . ")");
}

echo "<script>alert('Verifikasi berhasil disimpan.'); window.location='index.php';</script>";
exit;
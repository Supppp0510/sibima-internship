<?php
session_start();

// Kunci pengaman disamakan persis dengan dashboard_pelajar.php
if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php?pesan=belum_login");
    exit();
}

include "../config/koneksi.php";
include "../helper/activity_log.php"; 

// 🟢 Cari ID Asli Pelajar berdasarkan user_id dari session akun login
$id_user = $_SESSION['id_user']; 
$query_mhs = mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '$id_user'");
$data_mhs = mysqli_fetch_assoc($query_mhs);
$pelajar_id = $data_mhs['id'] ?? 0; 

$periode_id    = trim($_POST['periode_id'] ?? '');
$semester      = trim($_POST['semester'] ?? '');
$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$tanggal       = date('Y-m-d'); 

// 🟢 VALIDASI: Tolak sedini mungkin kalau data utama kosong
if ($pelajar_id == 0) {
    die("Data pelajar tidak ditemukan untuk akun ini. Silakan hubungi admin.");
}

if ($periode_id === '') {
    die("Pendaftaran gagal: tidak ada Periode PKL yang berstatus AKTIF saat ini. Silakan hubungi admin untuk mengaktifkan periode PKL terlebih dahulu.");
}

if ($semester === '' || $tanggal_mulai === '' || $tanggal_selesai === '') {
    die("Pendaftaran gagal: Semester, Tanggal Mulai, dan Tanggal Selesai wajib diisi.");
}

// Cek status pendaftaran aktif menggunakan $pelajar_id
$cek = mysqli_query($conn, "
    SELECT id FROM pendaftaran 
    WHERE pelajar_id='$pelajar_id' 
    AND status IN ('menunggu','diterima')
");

if (mysqli_num_rows($cek) > 0) {
    // 🔴 JIKA SUDAH DAFTAR: Redirect kembali ke pendaftaran.php dengan parameter
    header("Location: pendaftaran.php?pesan=sudah_daftar");
    exit();
}

$folder = "../assets/upload/pendaftaran/";

if(!is_dir($folder)){
    mkdir($folder, 0777, true);
}

function uploadPDF($input, $prefix, $folder) {
    if(!isset($_FILES[$input]) || $_FILES[$input]['error'] != 0){
        $kode = $_FILES[$input]['error'] ?? 'file tidak terkirim';
        die("Upload berkas " . $prefix . " gagal. Kode Error: " . $kode);
    }
    
    $ext = strtolower(pathinfo($_FILES[$input]['name'], PATHINFO_EXTENSION));
    if($ext != "pdf"){
        die("Format file " . $prefix . " wajib berupa PDF.");
    }
    
    $namaBaru = $prefix . "_" . time() . "_" . rand(1000, 9999) . ".pdf";
    
    if(!move_uploaded_file($_FILES[$input]['tmp_name'], $folder . $namaBaru)){
        die("Gagal memindahkan file " . $prefix . " ke server.");
    }
    
    return $namaBaru;
}

$surat     = uploadPDF("surat_pengantar", "surat", $folder);
$cv        = uploadPDF("cv", "cv", $folder);
$transkrip = uploadPDF("transkrip", "transkrip", $folder);
$proposal  = uploadPDF("proposal", "proposal", $folder);

// 🟢 Escape String
$pelajar_id_esc    = mysqli_real_escape_string($conn, $pelajar_id);
$periode_id_esc    = mysqli_real_escape_string($conn, $periode_id);
$semester_esc      = mysqli_real_escape_string($conn, $semester);
$tgl_mulai_esc     = mysqli_real_escape_string($conn, $tanggal_mulai);
$tgl_selesai_esc   = mysqli_real_escape_string($conn, $tanggal_selesai);

// Catatan: Kolom unit_kerja_id/bidang_id di-set NULL karena sudah dihapus dari form pendaftaran
$simpan = mysqli_query($conn, "
    INSERT INTO pendaftaran 
    (pelajar_id, periode_id, unit_kerja_id, pembimbing_id, tanggal_daftar, surat_pengantar, cv, transkrip, proposal, status, catatan_admin)
    VALUES 
    ('$pelajar_id_esc', '$periode_id_esc', NULL, NULL, '$tanggal', '$surat', '$cv', '$transkrip', '$proposal', 'menunggu', '')
");

if($simpan){
    $mhs = mysqli_query($conn, "SELECT nama FROM pelajar WHERE id='$pelajar_id_esc'");
    $m = mysqli_fetch_assoc($mhs);

    if(function_exists('activityLog')) {
        activityLog($conn, "Pelajar melakukan pendaftaran PKL: " . $m['nama']);
    }
    
    // 🟢 JIKA BERHASIL: Redirect kembali ke dashboard_pelajar.php dengan parameter
    header("Location: dashboard_pelajar.php?pesan=berhasil_daftar");
    exit(); 
} else {
    die("Gagal menyimpan ke database: " . mysqli_error($conn));
}
?>
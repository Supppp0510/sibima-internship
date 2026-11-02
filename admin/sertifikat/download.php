<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    die("ID sertifikat tidak valid.");
}

$q = mysqli_query($conn, "
    SELECT s.*, m.nama 
    FROM sertifikat s
    LEFT JOIN pelajar m ON s.pelajar_id = m.id
    WHERE s.id = '$id'
");

if (mysqli_num_rows($q) == 0) {
    die("Data sertifikat tidak ditemukan di database.");
}

$data = mysqli_fetch_assoc($q);

$file_pdf = $data['file_pdf'];

if (empty($file_pdf)) {
    die("Nama file PDF belum tercatat di database.");
}

// Menyesuaikan jalur direktori penyimpanan file PDF sertifikat
$file = "../../assets/upload/sertifikat/" . $file_pdf;

if (!file_exists($file)) {
    die("File PDF fisik tidak ditemukan di direktori penyimpanan server.");
}

// Header untuk memicu download file PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($file_pdf) . '"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($file);
exit;

?>
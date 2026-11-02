<?php
include_once __DIR__ . "/config.php"; 
date_default_timezone_set('Asia/Jakarta');

// Cek hari KEMARIN yang jam operasionalnya sudah resmi ditutup
$tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));

$query_auto_alpha = "
    INSERT INTO absensi (pelajar_id, tanggal, status, keterangan)
    SELECT p.id, '$tanggal_kemarin', 'alpha', 'Tidak melakukan absensi (Tanpa Keterangan)'
    FROM pelajar p
    WHERE p.id NOT IN (
        SELECT pelajar_id FROM absensi WHERE tanggal = '$tanggal_kemarin'
    )
";

mysqli_query($conn, $query_auto_alpha);
?>
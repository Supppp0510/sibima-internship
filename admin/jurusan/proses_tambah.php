<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

// Pastikan menangkap data nama_jurusan dan jenjang dari form POST
$nama    = mysqli_real_escape_string($conn, $_POST['nama_jurusan']);
$jenjang = mysqli_real_escape_string($conn, $_POST['jenjang']);

// Jalankan query insert dengan menyertakan kolom jenjang
$simpan = mysqli_query($conn, "
    INSERT INTO jurusan (nama_jurusan, jenjang)
    VALUES ('$nama', '$jenjang')
");

if ($simpan) {
    activityLog(
        $conn,
        "Menambahkan Jurusan : " . $nama
    );

    header("Location: index.php");
    exit;
} else {
    die(mysqli_error($conn));
}
?>
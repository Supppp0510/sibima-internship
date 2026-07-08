<?php

$host = "localhost";
$user = "root";
$pass = "Root123!";
$db   = "sipkl_bm_jatim";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi Database Gagal : " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
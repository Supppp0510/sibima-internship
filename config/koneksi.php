<?php

$host = "localhost";
$user = "root";
$pass = "Root123!";
$db   = "sibima-internship";
$port = "3307";

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (! $conn) {
    die("Koneksi Database Gagal : " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
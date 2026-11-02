<?php

$host = "localhost";
$user = "root";
$pass = ""; //isi sendiri sesuai password mysql di komputer masing-masing
$db   = "sibima-internship";
$port = ""; //isi sendiri sesuai port mysql di komputer masing-masing, default 3306

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (! $conn) {
    die("Koneksi Database Gagal : " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
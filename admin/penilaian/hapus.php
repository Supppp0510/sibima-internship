<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";

$id = $_GET['id'];

// ===============================
// Cek Data Penilaian
// ===============================

$query = mysqli_query($conn, "
SELECT

pn.id,

m.nama

FROM penilaian pn

LEFT JOIN mahasiswa m
ON pn.mahasiswa_id = m.id

WHERE pn.id='$id'
");

if(mysqli_num_rows($query)==0){

    echo "<script>

    alert('Data penilaian tidak ditemukan.');

    window.location='index.php';

    </script>";

    exit;

}

$data = mysqli_fetch_assoc($query);

// ===============================
// Hapus Data
// ===============================

$hapus = mysqli_query($conn,"
DELETE FROM penilaian
WHERE id='$id'
");

if($hapus){

    activityLog(

        $conn,

        "Menghapus Penilaian PKL : ".$data['nama']

    );

    echo "<script>

    alert('Data penilaian berhasil dihapus.');

    window.location='index.php';

    </script>";

}else{

    die("Error : ".mysqli_error($conn));

}

?>
<?php
session_start();
include "../config/config.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = $_SESSION['id_user'];
    $pelajar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '$id_user'"));
    $id_pelajar = $pelajar['id'] ?? 0;

    $id       = mysqli_real_escape_string($conn, $_POST['id']);
    $tanggal  = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $judul    = mysqli_real_escape_string($conn, $_POST['judul']);
    $kegiatan = mysqli_real_escape_string($conn, $_POST['kegiatan']);

    // Validasi Keamanan: pastikan logbook ini benar-benar milik pelajar yang login,
    // sekaligus ambil status & foto lama
    $cek_query = mysqli_query($conn, "SELECT status, dokumentasi FROM logbook WHERE id = '$id' AND pelajar_id = '$id_pelajar'");
    $data_lama = mysqli_fetch_assoc($cek_query);

    if (!$data_lama) {
        header("Location: logbook.php?pesan=gagal");
        exit;
    }

    // Cegah edit jika status sudah disetujui (status di DB huruf kecil: menunggu/disetujui/revisi/ditolak)
    if (strtolower($data_lama['status']) == 'disetujui') {
        header("Location: logbook.php?pesan=terkunci");
        exit;
    }

    $folder_dokumentasi = "../assets/upload/pelajar/dokumentasi/";

    $foto_baru = $_FILES['dokumentasi']['name'] ?? '';

    if (!empty($foto_baru)) {
        $ext = pathinfo($foto_baru, PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (!in_array(strtolower($ext), $allowed_ext)) {
            header("Location: logbook.php?pesan=gagal");
            exit;
        }

        if (!is_dir($folder_dokumentasi)) {
            mkdir($folder_dokumentasi, 0777, true);
        }

        // Generate nama file unik agar tidak bertabrakan
        $nama_file_baru = "log_" . time() . "_" . uniqid() . "." . $ext;
        $target_direktori = $folder_dokumentasi . $nama_file_baru;

        if (move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $target_direktori)) {
            // Hapus berkas foto lama dari storage server jika ada
            if (!empty($data_lama['dokumentasi']) && file_exists($folder_dokumentasi . $data_lama['dokumentasi'])) {
                unlink($folder_dokumentasi . $data_lama['dokumentasi']);
            }

            // Status dikembalikan ke "menunggu" (huruf kecil sesuai enum) agar bisa diperiksa ulang oleh pembimbing
            $query = "UPDATE logbook SET tanggal = '$tanggal', judul = '$judul', kegiatan = '$kegiatan', dokumentasi = '$nama_file_baru', status = 'menunggu', komentar = '' WHERE id = '$id'";
        } else {
            header("Location: logbook.php?pesan=gagal");
            exit;
        }
    } else {
        // Query update tanpa mengganti foto lama
        $query = "UPDATE logbook SET tanggal = '$tanggal', judul = '$judul', kegiatan = '$kegiatan', status = 'menunggu', komentar = '' WHERE id = '$id'";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: logbook.php?pesan=sukses");
    } else {
        header("Location: logbook.php?pesan=gagal");
    }
    exit;
} else {
    header("Location: logbook.php");
    exit;
}
?>
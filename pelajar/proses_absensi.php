<?php
session_start(); // Memastikan session aktif di paling atas
include "../config/config.php";
date_default_timezone_set('Asia/Jakarta'); // Menyelaraskan zona waktu server dengan WIB

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // =========================================================================
    // DETEKSI ID PELAJAR
    // =========================================================================
    $pelajar_id = null;

    if (isset($_SESSION['pelajar_id'])) {
        $pelajar_id = $_SESSION['pelajar_id'];
    } elseif (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $query_mhs = mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '$user_id'");
        $data_mhs = mysqli_fetch_assoc($query_mhs);
        $pelajar_id = $data_mhs['id'] ?? null;
    } elseif (isset($_SESSION['id'])) {
        $user_id = $_SESSION['id'];
        $query_mhs = mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '$user_id' OR id = '$user_id'");
        $data_mhs = mysqli_fetch_assoc($query_mhs);
        $pelajar_id = $data_mhs['id'] ?? null;
    }

    // Proteksi utama: Gagal mengenali ID
    if (empty($pelajar_id)) {
        $_SESSION['swal_icon']  = 'error';
        $_SESSION['swal_title'] = 'Akses Ditolak';
        $_SESSION['swal_text']  = 'Sistem tidak mengenali ID Pelajar Anda. Silakan log out dan login kembali.';
        header("Location: ../logout.php");
        exit;
    }
    // =========================================================================

    $aksi = $_POST['aksi']; // Bernilai: 'masuk', 'pulang', atau 'izin'
    
    // Tentukan direktori penyimpanan file gambar bukti absen
    $dir_tujuan = "../assets/upload/absensi/";
    if (!is_dir($dir_tujuan)) {
        mkdir($dir_tujuan, 0777, true);
    }

    $nama_foto_saved = "";

    // OPSI 1: JIKA MENGGUNAKAN WEBCAM (BASE64)
    if (!empty($_POST['foto_base64'])) {
        $raw_base64 = $_POST['foto_base64'];
        
        $header = "";
        if (strpos($raw_base64, ',') !== false) {
            list($header, $raw_base64) = explode(',', $raw_base64);
        }
        
        $filtered_base64 = str_replace(' ', '+', $raw_base64);
        $decoded_binary = base64_decode($filtered_base64);

        $ext = ".jpg"; 
        if (strpos($header, 'image/png') !== false) {
            $ext = ".png"; 
        }

        $nama_foto_saved = "cam_" . $aksi . "_" . $pelajar_id . "_" . time() . $ext;
        file_put_contents($dir_tujuan . $nama_foto_saved, $decoded_binary);
    } 
    // OPSI 2: JIKA MENGGUNAKAN UPLOAD FILE TRADISIONAL
    elseif (!empty($_FILES['foto_file']['name'])) {
        $ext = pathinfo($_FILES['foto_file']['name'], PATHINFO_EXTENSION);
        $nama_foto_saved = "file_" . $aksi . "_" . $pelajar_id . "_" . time() . "." . $ext;
        move_uploaded_file($_FILES['foto_file']['tmp_name'], $dir_tujuan . $nama_foto_saved);
    }

    // Validasi foto/bukti kosong
    if (empty($nama_foto_saved)) {
        $_SESSION['swal_icon']  = 'warning';
        $_SESSION['swal_title'] = 'Bukti Wajib Ada';
        $_SESSION['swal_text']  = 'Foto bukti kehadiran atau dokumen izin wajib disertakan!';
        header("Location: absensi.php");
        exit;
    }

    // KONFIGURASI JAM KERJA STANDAR
    $batas_jam_masuk  = "08:00:00"; 
    $batas_jam_pulang = "16:00:00"; 
    $jam_sekarang     = date('H:i:s');

    // =========================================================================
    // PROSES EKSEKUSI DATA BERDASARKAN AKSI
    // =========================================================================
    
    // 1. JIKA PELAJAR KLIK ABSEN MASUK
    if ($aksi == 'masuk') {
        $cek = mysqli_query($conn, "SELECT * FROM absensi WHERE pelajar_id = '$pelajar_id' AND tanggal = CURDATE()");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['swal_icon']  = 'warning';
            $_SESSION['swal_title'] = 'Sudah Absen';
            $_SESSION['swal_text']  = 'Anda sudah merekam kehadiran atau izin hari ini!';
            header("Location: absensi.php");
            exit;
        }

        $keterangan = ($jam_sekarang > $batas_jam_masuk) ? "Terlambat masuk PKL" : "Hadir tepat waktu";

        // Status diset sementara ke 'tanpa_absen_pulang' hingga pelajar melakukan absen pulang
        $query = "INSERT INTO absensi (pelajar_id, tanggal, jam_masuk, status, foto_masuk, keterangan) 
                  VALUES ('$pelajar_id', CURDATE(), CURTIME(), 'tanpa_absen_pulang', '$nama_foto_saved', '$keterangan')";
    } 
    
    // 2. JIKA PELAJAR KLIK ABSEN PULANG
    elseif ($aksi == 'pulang') {
        $cek = mysqli_query($conn, "SELECT * FROM absensi WHERE pelajar_id = '$pelajar_id' AND tanggal = CURDATE()");
        if (mysqli_num_rows($cek) == 0) {
            $_SESSION['swal_icon']  = 'error';
            $_SESSION['swal_title'] = 'Gagal Absen Pulang';
            $_SESSION['swal_text']  = 'Anda tidak bisa absen pulang karena belum melakukan absen masuk hari ini.';
            header("Location: absensi.php");
            exit;
        }

        // Tentukan status: jika pulang sebelum jam 16:00 maka 'pulang_cepat', jika >= 16:00 maka 'hadir'
        $status_pulang = ($jam_sekarang < $batas_jam_pulang) ? 'pulang_cepat' : 'hadir';

        $query = "UPDATE absensi SET 
                  jam_pulang = CURTIME(), 
                  status = '$status_pulang',
                  foto_pulang = '$nama_foto_saved' 
                  WHERE pelajar_id = '$pelajar_id' AND tanggal = CURDATE()";
    } 
    
    // 3. JIKA PELAJAR MENGAJUKAN IZIN / SAKIT
    elseif ($aksi == 'izin') {
        $cek = mysqli_query($conn, "SELECT * FROM absensi WHERE pelajar_id = '$pelajar_id' AND tanggal = CURDATE()");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['swal_icon']  = 'warning';
            $_SESSION['swal_title'] = 'Sudah Ada Data';
            $_SESSION['swal_text']  = 'Anda sudah mengisi kehadiran atau memiliki data izin pada hari ini.';
            header("Location: absensi.php");
            exit;
        }

        $status_izin = $_POST['status_izin']; 
        $keterangan  = $_POST['keterangan'];   

        $query = "INSERT INTO absensi (pelajar_id, tanggal, status, foto_masuk, keterangan) 
                  VALUES ('$pelajar_id', CURDATE(), '$status_izin', '$nama_foto_saved', '$keterangan')";
    }

    // Eksekusi akhir query ke database
    if (isset($query) && mysqli_query($conn, $query)) {
        $_SESSION['swal_icon']  = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text']  = 'Data absensi Anda berhasil direkam.';
    } else {
        $_SESSION['swal_icon']  = 'error';
        $_SESSION['swal_title'] = 'Gagal Database';
        $_SESSION['swal_text']  = 'Terjadi kesalahan: ' . mysqli_error($conn);
    }

    header("Location: absensi.php");
    exit;
}
?>
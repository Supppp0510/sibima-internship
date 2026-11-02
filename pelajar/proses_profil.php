<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['simpan_profil'])) {
    $id_user          = $_SESSION['id_user'];
    
    $nim              = mysqli_real_escape_string($conn, $_POST['nim']);
    $nama             = mysqli_real_escape_string($conn, $_POST['nama']);
    $jenjang          = mysqli_real_escape_string($conn, $_POST['jenjang']); // Jenjang yang dipilih user
    
    $input_instansi   = trim($_POST['nama_instansi']);
    $alamat_instansi  = mysqli_real_escape_string($conn, $_POST['alamat_instansi']);
    
    $input_jurusan    = trim($_POST['nama_jurusan']); 
    
    $no_hp            = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email            = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat           = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    $foto_lama        = $_POST['foto_lama'] ?? '';

    // =========================================================================
    // 1. OTOMATIS EKSTRAK KOTA DARI ALAMAT LENGKAP
    // =========================================================================
    $kota_terdeteksi = '-';
    $daftar_kota = ['surabaya', 'malang', 'jakarta', 'sidoarjo', 'gresik', 'mojokerto', 'kediri', 'blitar', 'madiun', 'jember', 'banyuwangi', 'pasuruan', 'probolinggo', 'tuban', 'lamongan', 'bojonegoro', 'nganjuk', 'magetan', 'ngawi', 'ponorogo', 'pacitan', 'trenggalek', 'tulungagung', 'lumajang', 'bondowoso', 'situbondo', 'batu', 'bangkalan', 'sampang', 'pamekasan', 'sumenep'];
    
    $alamat_lower = strtolower($alamat_instansi);
    foreach ($daftar_kota as $k) {
        if (strpos($alamat_lower, $k) !== false) {
            $kota_terdeteksi = ucwords($k); 
            break;
        }
    }

    // =========================================================================
    // 2. CEK & SIMPAN INSTANSI (PENCEGAHAN DUPLIKAT, TYPO & UPPER/LOWERCASE)
    // =========================================================================
    $instansi_id = null;
    $input_ins_bersih = strtolower(preg_replace('/\s+/', ' ', $input_instansi));

    $cek_exact_ins = mysqli_query($conn, "SELECT id FROM instansi WHERE LOWER(TRIM(nama_instansi)) = '$input_ins_bersih' LIMIT 1");

    if (mysqli_num_rows($cek_exact_ins) > 0) {
        $data_exact = mysqli_fetch_assoc($cek_exact_ins);
        $instansi_id = $data_exact['id'];
        mysqli_query($conn, "UPDATE instansi SET alamat = '$alamat_instansi', kota = '$kota_terdeteksi' WHERE id = '$instansi_id'");
    } else {
        $query_semua_ins = mysqli_query($conn, "SELECT id, nama_instansi FROM instansi");
        $ditemukan_mirip_ins = false;

        while ($row_ins = mysqli_fetch_assoc($query_semua_ins)) {
            $nama_db_bersih = strtolower(trim($row_ins['nama_instansi']));
            similar_text($input_ins_bersih, $nama_db_bersih, $persen);
            if ($persen >= 85) {
                $instansi_id = $row_ins['id'];
                $ditemukan_mirip_ins = true;
                break;
            }
        }

        if (!$ditemukan_mirip_ins) {
            $nama_instansi_aman = mysqli_real_escape_string($conn, $input_instansi);
            $insert_ins = mysqli_query($conn, "INSERT INTO instansi (nama_instansi, alamat, kota) VALUES ('$nama_instansi_aman', '$alamat_instansi', '$kota_terdeteksi')");
            if ($insert_ins) {
                $instansi_id = mysqli_insert_id($conn);
            } else {
                echo "<script>alert('Gagal menambahkan instansi baru.'); window.history.back();</script>";
                exit();
            }
        }
    }

    // =========================================================================
    // 3. CEK & SIMPAN JURUSAN & JENJANG (PENCEGAHAN DUPLIKAT KE TABEL JURUSAN)
    // =========================================================================
    $jurusan_id = null;
    $input_jur_bersih = strtolower(preg_replace('/\s+/', ' ', $input_jurusan));

    // Cek apakah nama jurusan DAN jenjangnya sudah ada di database
    $cek_exact_jur = mysqli_query($conn, "SELECT id FROM jurusan WHERE LOWER(TRIM(nama_jurusan)) = '$input_jur_bersih' AND jenjang = '$jenjang' LIMIT 1");

    if (mysqli_num_rows($cek_exact_jur) > 0) {
        $data_jur_exact = mysqli_fetch_assoc($cek_exact_jur);
        $jurusan_id = $data_jur_exact['id'];
    } else {
        $query_semua_jur = mysqli_query($conn, "SELECT id, nama_jurusan, jenjang FROM jurusan");
        $ditemukan_mirip_jur = false;

        while ($row_jur = mysqli_fetch_assoc($query_semua_jur)) {
            $jur_db_bersih = strtolower(trim($row_jur['nama_jurusan']));
            similar_text($input_jur_bersih, $jur_db_bersih, $persen_jur);
            
            // Cocok jika kemiripan >= 85% DAN jenjangnya sama
            if ($persen_jur >= 85 && $row_jur['jenjang'] == $jenjang) {
                $jurusan_id = $row_jur['id'];
                $ditemukan_mirip_jur = true;
                break;
            }
        }

        if (!$ditemukan_mirip_jur) {
            $nama_jurusan_aman = mysqli_real_escape_string($conn, $input_jurusan);
            // Simpan nama_jurusan DAN jenjang langsung ke tabel jurusan
            $insert_jur = mysqli_query($conn, "INSERT INTO jurusan (nama_jurusan, jenjang) VALUES ('$nama_jurusan_aman', '$jenjang')");
            if ($insert_jur) {
                $jurusan_id = mysqli_insert_id($conn);
            } else {
                echo "<script>alert('Gagal menambahkan jurusan baru.'); window.history.back();</script>";
                exit();
            }
        }
    }

    // =========================================================================
    // 4. PROSES UPLOAD FOTO PROFIL (SUDAH DIPERBAIKI)
    // =========================================================================
    $folder_foto = "../assets/upload/pelajar/profile/";
    if (!is_dir($folder_foto)) {
        mkdir($folder_foto, 0777, true);
    }

    $nama_foto_akhir = $foto_lama; // Default tetap gunakan foto lama

    // Cek apakah user memilih file foto baru dan tidak ada error upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['foto']['tmp_name'];
        $file_size = $_FILES['foto']['size'];
        $file_name = $_FILES['foto']['name'];
        
        // Ambil ekstensi aman menggunakan pathinfo
        $ekstensi  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');

        // 1. Validasi Ekstensi
        if (!in_array($ekstensi, $ekstensi_diperbolehkan)) {
            echo "<script>alert('Format file foto harus berupa JPG, JPEG, atau PNG!'); window.history.back();</script>";
            exit();
        }

        // 2. Validasi Ukuran File (Maksimal 5MB)
        if ($file_size > 5 * 1024 * 1024) {
            echo "<script>alert('Ukuran foto terlalu besar! Maksimal 5MB.'); window.history.back();</script>";
            exit();
        }

        // 3. Nama foto baru yang unik
        $foto_baru_nama = "profil_" . time() . "_" . rand(100, 999) . "." . $ekstensi;

        // 4. Pindahkan file & hanya update nama variabel jika BERHASIL pindah
        if (move_uploaded_file($file_tmp, $folder_foto . $foto_baru_nama)) {
            // Hapus foto lama dari server jika ada
            if (!empty($foto_lama) && file_exists($folder_foto . $foto_lama)) {
                unlink($folder_foto . $foto_lama);
            }
            $nama_foto_akhir = $foto_baru_nama; // Baru diperbarui di sini setelah sukses
        } else {
            echo "<script>alert('Gagal menyimpan foto ke direktori server.'); window.history.back();</script>";
            exit();
        }
    }

    // =========================================================================
    // 5. SIMPAN DATA KE TABEL PELAJAR
    // =========================================================================
    $cek_mhs = mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '$id_user'");
    
    if (mysqli_num_rows($cek_mhs) > 0) {
        $query_save = "UPDATE pelajar SET 
                        nim = '$nim',
                        nama = '$nama',
                        instansi_id = '$instansi_id',
                        jurusan_id = '$jurusan_id',
                        no_hp = '$no_hp',
                        email = '$email',
                        alamat = '$alamat',
                        foto = '$nama_foto_akhir'
                       WHERE user_id = '$id_user'";
    } else {
        $query_save = "INSERT INTO pelajar 
                        (user_id, nim, nama, instansi_id, jurusan_id, no_hp, email, alamat, foto) 
                       VALUES 
                        ('$id_user', '$nim', '$nama', '$instansi_id', '$jurusan_id', '$no_hp', '$email', '$alamat', '$nama_foto_akhir')";
    }

    if (mysqli_query($conn, $query_save)) {
        $_SESSION['nama'] = $nama; 
        $_SESSION['foto'] = $nama_foto_akhir;
        
        // =========================================================================
        // 🟢 CEK STATUS MAGANG (DINAMIS UNTUK SWEETALERT)
        // =========================================================================
        $magang_aktif = false;

        $qCekStatus = mysqli_query($conn, "
            SELECT p.status AS status_pendaftaran, pr.tanggal_selesai 
            FROM pendaftaran p 
            LEFT JOIN periode_pkl pr ON p.periode_id = pr.id 
            WHERE p.pelajar_id = (SELECT id FROM pelajar WHERE user_id = '$id_user' LIMIT 1) 
            ORDER BY p.id DESC LIMIT 1
        ");
        $status_data = $qCekStatus ? mysqli_fetch_assoc($qCekStatus) : null;

        if ($status_data && $status_data['status_pendaftaran'] === 'diterima') {
            $tgl_selesai = $status_data['tanggal_selesai'];
            if (empty($tgl_selesai) || strtotime($tgl_selesai) >= strtotime(date('Y-m-d'))) {
                $magang_aktif = true;
            }
        }

        // Tentukan teks dan link tujuan SweetAlert berdasarkan status magang
        if ($magang_aktif) {
            $pesan_swal = "Data profil, instansi, dan jurusan Anda berhasil diperbarui.";
            $btn_swal   = "Kembali ke Profil";
            $link_swal  = "profil.php";
        } else {
            $pesan_swal = "Data profil, instansi, dan jurusan Anda berhasil disimpan.";
            $btn_swal   = "Lanjut ke Pendaftaran";
            $link_swal  = "pendaftaran.php";
        }

        echo "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Proses Berhasil</title>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <style>body { background-color: #f4f6f9; }</style>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Profil Diperbarui!',
                    text: '$pesan_swal',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: '$btn_swal',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '$link_swal';
                    }
                });
            </script>
        </body>
        </html>";
        exit();
        
    } else {
        echo "<script>alert('Gagal menyimpan data ke database.'); window.history.back();</script>";
        exit();
    }
}
?>

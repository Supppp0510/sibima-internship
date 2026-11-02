<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../config/koneksi.php";

if (isset($_POST['import'])) {
    $fileTmp  = $_FILES['file_excel']['tmp_name'];
    $fileName = $_FILES['file_excel']['name'];

    if (!empty($fileName)) {
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (strtolower($fileExtension) == 'csv') {
            if (($handle = fopen($fileTmp, "r")) !== FALSE) {
                $successCount = 0;
                $rowNo = 0;

                while (($data = fgetcsv($handle, 3000, ",")) !== FALSE) {
                    // Jika delimiter koma menghasilkan array 1 elemen, coba gunakan titik koma
                    if (count($data) <= 1) {
                        rewind($handle);
                        $data = fgetcsv($handle, 3000, ";");
                    }

                    $rowNo++;
                    if ($rowNo == 1) continue; // Lewati baris judul header
                    if (empty($data[1]) && empty($data[2])) continue;

                    // Pemetaan Berdasarkan Kolom CSV Anda:
                    // [0] = No
                    // [1] = NIP
                    // [2] = Nama Pembimbing & Gelar
                    // [3] = Email
                    // [4] = Unit Kerja
                    // [5] = Golongan Ruang
                    // [6] = Pangkat
                    // [7] = Nama Jabatan
                    // [8] = Jenis Jabatan

                    $nip           = mysqli_real_escape_string($conn, trim($data[1] ?? ''));
                    $nama          = mysqli_real_escape_string($conn, trim($data[2] ?? ''));
                    $email         = mysqli_real_escape_string($conn, trim($data[3] ?? ''));
                    $nama_uk       = mysqli_real_escape_string($conn, trim($data[4] ?? ''));
                    $gol_ruang     = mysqli_real_escape_string($conn, trim($data[5] ?? ''));
                    $pangkat       = mysqli_real_escape_string($conn, trim($data[6] ?? ''));
                    $nama_jabatan  = mysqli_real_escape_string($conn, trim($data[7] ?? ''));
                    $jenis_jabatan = mysqli_real_escape_string($conn, trim($data[8] ?? ''));
                    
                    // Generate email jika kosong
                    if (empty($email) && !empty($nama)) {
                        $nama_bersih = preg_replace('/[^a-zA-Z\s]/', '', $nama);
                        $parts = explode(' ', trim($nama_bersih));
                        $email = strtolower(($parts[0] ?? 'pembimbing') . '.' . ($parts[1] ?? $rowNo)) . '@jatimprov.go.id';
                    }

                    // Cari ID Unit Kerja
                    $uk_id = "NULL";
                    if (!empty($nama_uk) && $nama_uk != '-') {
                        $q_uk = mysqli_query($conn, "SELECT id FROM unit_kerja WHERE unit_kerja = '$nama_uk' LIMIT 1");
                        if ($row_uk = mysqli_fetch_assoc($q_uk)) {
                            $uk_id = $row_uk['id'];
                        }
                    }

                    if (!empty($nama)) {
                        // 1. Insert ke tabel `users`
                        $role_id_pembimbing = 2; 
                        $default_password   = password_hash('123456', PASSWORD_DEFAULT); 
                        $status_akun        = 'Aktif';

                        $query_user = "INSERT INTO users (role_id, name, email, password, status, created_at) 
                                       VALUES ('$role_id_pembimbing', '$nama', '$email', '$default_password', '$status_akun', NOW())";
                        
                        $user_id = "NULL";
                        if (mysqli_query($conn, $query_user)) {
                            $user_id = mysqli_insert_id($conn); 
                        }

                        // 2. Insert ke tabel `pembimbing`
                        if ($uk_id == 'NULL') {
                            $query_pembimbing = "INSERT INTO pembimbing (user_id, nip, nama, email, golongan_ruang, pangkat, nama_jabatan, jenis_jabatan, unit_kerja_id, created_at) 
                                                 VALUES ('$user_id', '$nip', '$nama', '$email', '$gol_ruang', '$pangkat', '$nama_jabatan', '$jenis_jabatan', NULL, NOW())";
                        } else {
                            $query_pembimbing = "INSERT INTO pembimbing (user_id, nip, nama, email, golongan_ruang, pangkat, nama_jabatan, jenis_jabatan, unit_kerja_id, created_at) 
                                                 VALUES ('$user_id', '$nip', '$nama', '$email', '$gol_ruang', '$pangkat', '$nama_jabatan', '$jenis_jabatan', '$uk_id', NOW())";
                        }
                        
                        if (mysqli_query($conn, $query_pembimbing)) {
                            $successCount++;
                        }
                    }
                }
                fclose($handle);

                echo "<script>alert('Berhasil mengimport $successCount data pembimbing!'); window.location='index.php';</script>";
            } else {
                echo "<script>alert('Gagal membuka file CSV!'); window.location='index.php';</script>";
            }
        } else {
            echo "<script>alert('Mohon gunakan file berformat .csv!'); window.location='index.php';</script>";
        }
    } else {
        echo "<script>alert('Pilih file terlebih dahulu!'); window.location='index.php';</script>";
    }
}
?>
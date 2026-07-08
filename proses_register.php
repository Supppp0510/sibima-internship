<?php
// Catatan: Ubah menjadi "../config/koneksi.php" jika letak folder config-mu berada di luar folder ini
include "config/koneksi.php"; 

if (isset($_POST['register'])) {
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $email       = mysqli_real_escape_string($conn, $_POST['email']);
    $password    = $_POST['password'];
    $re_password = $_POST['re_password'];

    // Validasi ulang di backend demi keamanan
    if ($password !== $re_password) {
        echo "<script>alert('Konfirmasi password tidak cocok!'); window.location='register.php';</script>";
        exit();
    }
    if (strlen($password) < 6) {
        echo "<script>alert('Password minimal harus 6 karakter!'); window.location='register.php';</script>";
        exit();
    }

    // Cek apakah email sudah terdaftar di tabel users
    $cekEmail = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cekEmail) > 0) {
        echo "<script>alert('Email sudah terdaftar! Gunakan email lain.'); window.location='register.php';</script>";
        exit();
    }

    // Enkripsi password menggunakan Bcrypt (Hasil enkripsi muat di varchar 255 kamu)
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
    
    $role_id    = 3; // Angka 3 otomatis mengunci status sebagai Mahasiswa
    $status     = 'aktif';
    $created_at = date('Y-m-d H:i:s');

    // INSERT 1: Masukkan data akun ke tabel 'users'
    $queryUser = "INSERT INTO users (role_id, name, email, password, status, created_at) 
                  VALUES ('$role_id', '$name', '$email', '$password_hashed', '$status', '$created_at')";

    if (mysqli_query($conn, $queryUser)) {
        
        // Ambil ID user yang otomatis dibuat oleh tabel users tadi
        $id_user_baru = mysqli_insert_id($conn);

        // INSERT 2: Otomatis buatkan baris data profil kosong di tabel 'mahasiswa'
        $queryMahasiswa = "INSERT INTO mahasiswa (user_id, nama, email, created_at) 
                           VALUES ('$id_user_baru', '$name', '$email', '$created_at')";

        if (mysqli_query($conn, $queryMahasiswa)) {
            echo "<script>alert('Pendaftaran berhasil! Silakan login menggunakan akun Anda.'); window.location='login.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal membuat profil mahasiswa.'); window.location='register.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Terjadi kesalahan database.'); window.location='register.php';</script>";
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
?>
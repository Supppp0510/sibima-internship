<?php
session_start();
include 'config/koneksi.php';

/**
 * Helper function untuk menampilkan SweetAlert2 lalu redirect
 */
function showSweetAlert($title, $text, $icon, $targetUrl = 'login.php') {
    $btnColor = ($icon === 'error') ? '#dc3545' : (($icon === 'warning') ? '#ffc107' : '#0d6efd');
    $btnText  = ($icon === 'error') ? 'Coba Lagi' : 'OK';

    echo "
    <!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body style='background-color: #f4f6f9; font-family: sans-serif;'>
        <script>
            Swal.fire({
                title: '{$title}',
                text: '{$text}',
                icon: '{$icon}',
                confirmButtonColor: '{$btnColor}',
                confirmButtonText: '{$btnText}',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{$targetUrl}';
                }
            });
        </script>
    </body>
    </html>";
    exit;
}

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // 1. Ambil data user berdasarkan email
    $query = mysqli_query($conn, "
        SELECT users.*, roles.nama_role 
        FROM users 
        JOIN roles ON users.role_id = roles.id 
        WHERE users.email = '$email'
    ");

    if (!$query) {
        die("Query Error : " . mysqli_error($conn));
    }

    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);

        // Cek status aktif
        if (isset($data['status']) && $data['status'] !== 'aktif') {
            showSweetAlert('Akun Nonaktif!', 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.', 'warning', 'login.php');
        }

        // 2. JALUR PENGECEKAN PASSWORD (Bcrypt ATAU Plain Text/MD5 Lama)
        $password_cocok = false;

        if (password_verify($password, $data['password'])) {
            // Jika cocok dengan Bcrypt baru
            $password_cocok = true;
        } elseif ($password === $data['password'] || md5($password) === $data['password']) {
            // Jika database masih menyimpan teks biasa (admin123) atau MD5 lama
            $password_cocok = true;
        }

        // 3. JIKA PASSWORD COCOK, SET SESSION & REDIRECT
        if ($password_cocok) {
            $_SESSION['id']      = $data['id'];
            $_SESSION['id_user'] = $data['id']; 
            $_SESSION['nama']    = $data['name']; 
            $_SESSION['email']   = $data['email'];
            $_SESSION['role_id'] = $data['role_id'];
            $_SESSION['role']    = strtolower($data['nama_role']); 

            if ($data['role_id'] == 1) {
                // Jalankan log aktivitas admin milikmu jika filenya ada
                if (file_exists("helper/activity_log.php")) {
                    include "helper/activity_log.php";
                    activityLog($conn, "Login ke sistem");
                }
                header("Location: admin/dashboard.php");
            } 
            elseif ($data['role_id'] == 2) {
    header("Location: dospem/dashboard_pembimbing.php");
}
            elseif ($data['role_id'] == 3) {
                header("Location: pelajar/dashboard_pelajar.php");
            }
            exit;
        } else {
            // Jika password salah setelah melewati semua pengecekan
            showSweetAlert('Login Gagal!', 'Email atau Password Salah.', 'error', 'login.php');
        }
    } else {
        // Email tidak ditemukan
        showSweetAlert('Login Gagal!', 'Email atau Password Salah.', 'error', 'login.php');
    }
} else {
    header("Location: login.php");
    exit;
}
?>
<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . "/config/koneksi.php";

/**
 * Helper function untuk menampilkan SweetAlert2 agar kode lebih bersih (DRY)
 */
function showSweetAlert($title, $text, $icon, $targetUrl = null, $isBack = false) {
    $btnColor = ($icon === 'error') ? '#dc3545' : (($icon === 'warning') ? '#ffc107' : '#0d6efd');
    $btnText  = ($icon === 'error') ? 'Coba Lagi' : 'OK';
    $actionScript = $isBack ? "window.history.back();" : "window.location.href = '{$targetUrl}';";

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
                    {$actionScript}
                }
            });
        </script>
    </body>
    </html>";
    exit();
}

// Proteksi jika file diakses langsung tanpa POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// Ambil dan bersihkan data input
$name        = trim($_POST['name'] ?? '');
$email       = trim($_POST['email'] ?? '');
$password    = $_POST['password'] ?? '';
$re_password = $_POST['re_password'] ?? '';

// 1. Validasi Input
if ($password !== $re_password) {
    showSweetAlert('Validasi Gagal!', 'Konfirmasi password tidak cocok!', 'warning', null, true);
}

if (strlen($password) < 6) {
    showSweetAlert('Validasi Gagal!', 'Password minimal 6 karakter!', 'warning', null, true);
}

// 2. Cek Email Unik (Menggunakan Prepared Statement)
$stmtCek = mysqli_prepare($conn, "SELECT email FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmtCek, "s", $email);
mysqli_stmt_execute($stmtCek);
mysqli_stmt_store_result($stmtCek);

if (mysqli_stmt_num_rows($stmtCek) > 0) {
    mysqli_stmt_close($stmtCek);
    showSweetAlert('Email Terdaftar!', 'Email sudah terdaftar, silakan gunakan email lain!', 'warning', null, true);
}
mysqli_stmt_close($stmtCek);

// 3. Persiapan Data
$password_hashed = password_hash($password, PASSWORD_BCRYPT);
$role_id    = 3; 
$status     = 'aktif';
$created_at = date('Y-m-d H:i:s');

// 4. Proses Insert ke Database (Menggunakan Transaksi & Prepared Statements)
mysqli_begin_transaction($conn);

try {
    // INSERT ke tabel 'users'
    $stmtUser = mysqli_prepare($conn, "INSERT INTO users (role_id, name, email, password, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmtUser, "isssss", $role_id, $name, $email, $password_hashed, $status, $created_at);
    
    if (!mysqli_stmt_execute($stmtUser)) {
        throw new Exception(mysqli_stmt_error($stmtUser));
    }
    
    $id_user_baru = mysqli_insert_id($conn);
    mysqli_stmt_close($stmtUser);

    // INSERT ke tabel 'pelajar'
    $stmtPelajar = mysqli_prepare($conn, "INSERT INTO pelajar (user_id, nama, email, created_at) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmtPelajar, "isss", $id_user_baru, $name, $email, $created_at);
    
    if (!mysqli_stmt_execute($stmtPelajar)) {
        throw new Exception(mysqli_stmt_error($stmtPelajar));
    }
    mysqli_stmt_close($stmtPelajar);

    // Commit jika semua operasi berhasil
    mysqli_commit($conn);

    // SweetAlert2 - Notifikasi Berhasil
    showSweetAlert('Pendaftaran Berhasil!', 'Akun Anda telah terdaftar. Silakan login.', 'success', 'login.php');

} catch (Exception $e) {
    // Rollback jika terjadi kesalahan
    mysqli_rollback($conn);

    // SweetAlert2 - Notifikasi Gagal
    $errorMessage = addslashes($e->getMessage());
    showSweetAlert('Pendaftaran Gagal!', 'Terjadi kesalahan sistem: ' . $errorMessage, 'error', null, true);
}
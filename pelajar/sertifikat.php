<?php
session_start();

// GATEKEEPER: Kunci halaman jika belum login atau bukan pelajar (Role ID = 3)
if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php?pesan=belum_login");
    exit();
}

include "../config/koneksi.php";

if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}

$id_user = $_SESSION['id_user']; 

// 1. Cari ID Pelajar berdasarkan user yang login
$query_mhs = mysqli_query($conn, "SELECT id, nama FROM pelajar WHERE user_id = '$id_user'");
$data_mhs  = mysqli_fetch_assoc($query_mhs);
$pelajar_id = $data_mhs['id'] ?? 0;
$nama_pelajar = $data_mhs['nama'] ?? 'Siswa';

// 2. Cek apakah sertifikat sudah diterbitkan secara resmi di tabel 'sertifikat'
$query_sertifikat = mysqli_query($conn, "SELECT * FROM sertifikat WHERE pelajar_id = '$pelajar_id' LIMIT 1");
$sertifikat       = mysqli_fetch_assoc($query_sertifikat);

// 3. Pengecekan Ekstra: Cek status laporan akhir & nilai dari pembimbing
$query_cek_syarat = mysqli_query($conn, "
    SELECT la.status AS status_laporan, pn.nilai_akhir 
    FROM laporan_akhir la 
    LEFT JOIN penilaian pn ON la.pelajar_id = pn.pelajar_id 
    WHERE la.pelajar_id = '$pelajar_id' 
    LIMIT 1
");
$data_syarat = mysqli_fetch_assoc($query_cek_syarat);

$status_laporan = $data_syarat['status_laporan'] ?? '';
$nilai_akhir    = $data_syarat['nilai_akhir'] ?? null;

// Syarat kelulusan terpenuhi jika laporan disetujui dan nilai akhir sudah diinput
$syarat_terpenuhi = ($status_laporan == 'disetujui' && $nilai_akhir !== null);

if (!$sertifikat && $syarat_terpenuhi) {
    $sertifikat = [
        'nomor_sertifikat' => 'SIBIMA/' . date('Y') . '/CERT/' . sprintf("%03d", $pelajar_id),
        'tanggal_terbit'   => date('Y-m-d'),
        'file_pdf'         => '#'
    ];
}

$is_available = ($sertifikat) ? true : false;

// Path file sertifikat
$file_pdf_name = $sertifikat['file_pdf'] ?? '';
$pdf_path = "../assets/upload/sertifikat/" . $file_pdf_name;
$has_pdf = ($file_pdf_name !== '#' && !empty($file_pdf_name) && file_exists($pdf_path));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Magang - SIBIMA JATIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        html, body { width: 100%; margin: 0; padding: 0; }
        body { background-color: #f4f6f9; overflow-x: hidden; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        *, *::before, *::after { box-sizing: border-box; }

        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; transition: all 0.3s ease; }
        .content-body { flex: 1; }

        body.sidebar-hidden .main-content { margin-left: 0; width: 100%; }

        .welcome-banner { background: linear-gradient(135deg, #0f172a, #1e40af); border-radius: 20px; padding: 1.8rem; color: #fff; margin-bottom: 1.8rem; box-shadow: 0 10px 25px rgba(30,64,175,.20); }
        .section-card { border: none; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,.06); background: #ffffff; }

        /* Container Preview Sertifikat */
        .cert-preview-container { 
            position: relative;
            width: 100%;
            aspect-ratio: 1.414 / 1; /* Rasio A4 Landscape agar pas 1 halaman */
            border: 2px dashed #3b82f6; 
            background: #ffffff; 
            border-radius: 15px; 
            overflow: hidden; /* Memotong scrollbar & halaman berikutnya agar tidak tampil */
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cert-preview-container:hover {
            transform: translateY(-4px);
            border-color: #1d4ed8;
            box-shadow: 0 10px 20px rgba(30, 64, 175, 0.2);
        }

        /* Iframe dibuat lebih lebar & tinggi untuk mendorong scrollbar bawaan PDF keluar bingkai */
        .cert-iframe {
            width: calc(100% + 30px);
            height: calc(100% + 30px);
            margin-top: -2px;
            margin-left: -2px;
            border: none;
            pointer-events: none; /* Agar klik langsung mengaktifkan tautan full screen */
        }

        /* Overlay transparan saat kursor diarahkan */
        .cert-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.55);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            opacity: 0;
            transition: opacity 0.3s ease;
            backdrop-filter: blur(2px);
            z-index: 10;
        }

        .cert-preview-container:hover .cert-overlay {
            opacity: 1;
        }

        @media(max-width:768px){
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <?php 
        if (file_exists("../include/sidebar_pelajar.php")) {
            include "../include/sidebar_pelajar.php";
        }
    ?>

    <div class="main-content">
        <?php 
            if (file_exists("../include/navbar_pelajar.php")) {
                include "../include/navbar_pelajar.php";
            }
        ?>

        <div class="content-body container-fluid px-4">

            <!-- Banner Header -->
            <div class="welcome-banner d-flex justify-content-between align-items-center">
                <div>
                    <h3><i class="bi bi-award me-2"></i>Sertifikat Magang</h3>
                    <p class="mb-0">Unduh sertifikat kelulusan resmi program magang SIBIMA JATIM Anda di sini.</p>
                </div>
            </div>

            <?php if ($is_available): ?>
                <!-- 🟢 TAMPILAN 1: SERTIFIKAT SUDAH TERBIT -->
                <div class="card section-card p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-lg-5 mb-4 mb-lg-0">
                            
                            <?php if ($has_pdf): ?>
                                <!-- Container Preview Sertifikat (Khusus Halaman 1 Tanpa Scrollbar) -->
                                <a href="<?= htmlspecialchars($pdf_path); ?>" target="_blank" class="text-decoration-none d-block" title="Klik untuk menampilkan sertifikat full">
                                    <div class="cert-preview-container">
                                        <iframe src="<?= htmlspecialchars($pdf_path); ?>#page=1&view=Fit&toolbar=0&navpanes=0&scrollbar=0" class="cert-iframe" scrolling="no"></iframe>
                                        <div class="cert-overlay">
                                            <i class="bi bi-arrows-angle-expand display-6 mb-2"></i>
                                            <span class="fw-bold fs-6">Klik untuk Tampilkan Full</span>
                                        </div>
                                    </div>
                                </a>
                            <?php else: ?>
                                <div class="cert-preview-container d-flex flex-column align-items-center justify-content-center text-center p-3 opacity-75">
                                    <i class="bi bi-file-earmark-pdf-fill text-secondary display-1 mb-2"></i>
                                    <h5 class="fw-bold text-dark mb-1">Sertifikat Kelulusan</h5>
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu Upload File Admin
                                    </span>
                                </div>
                            <?php endif; ?>

                        </div>
                        <div class="col-lg-7 ps-lg-4">
                            <div class="d-inline-block bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill mb-2 fs-7">
                                SIBIMA INTERNSHIP CERTIFICATE
                            </div>
                            <h3 class="fw-bold text-dark mb-3">Selamat! Sertifikat Anda Sudah Terbit</h3>
                            
                            <table class="table table-borderless fs-6 mb-4" style="max-width: 500px;">
                                <tr>
                                    <td class="text-muted ps-0" width="35%">Nama Peserta</td>
                                    <td class="fw-bold text-dark">: <?= htmlspecialchars($nama_pelajar); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Nomor Sertifikat</td>
                                    <td class="fw-bold text-dark">: <?= htmlspecialchars($sertifikat['nomor_sertifikat']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Tanggal Terbit</td>
                                    <td class="fw-bold text-dark">: <?= date('d F Y', strtotime($sertifikat['tanggal_terbit'])); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Status Penerbitan</td>
                                    <td>: <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span></td>
                                </tr>
                            </table>

                            <!-- Tombol Download Utama -->
                            <div class="d-flex flex-wrap gap-2">
                                <?php if ($has_pdf): ?>
                                    <a href="<?= htmlspecialchars($pdf_path); ?>" download class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                                        <i class="bi bi-download me-2"></i>Download Sertifikat (PDF)
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-lg rounded-pill px-4 shadow-sm" disabled>
                                        <i class="bi bi-hourglass-split me-2"></i>File PDF Menunggu Upload Admin
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- 🔴 TAMPILAN 2: SERTIFIKAT BELUM TERBIT -->
                <div class="card section-card p-5 text-center mb-4">
                    <div class="py-4">
                        <div class="bg-warning-subtle text-warning d-inline-flex p-3 rounded-circle mb-3">
                            <i class="bi bi-hourglass-split display-4"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Sertifikat Belum Diterbitkan</h4>
                        <p class="text-muted mx-auto" style="max-width: 550px;">
                            Sertifikat kelulusan magang Anda sedang dalam proses verifikasi atau pembuatan oleh Admin. Silakan pastikan Anda telah menyelesaikan Laporan Akhir dan Nilai telah diinput oleh Pembimbing.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                                <i class="bi bi-info-circle me-1"></i> Cek kembali halaman ini secara berkala.
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php 
    if (file_exists("../include/footer_pelajar.php")) {
        include "../include/footer_pelajar.php";
    }
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
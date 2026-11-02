<?php
session_start();
include '../config/koneksi.php';
include '../helper/foto_helper.php';

// Validasi Login Pembimbing
if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}

$id_user = $_SESSION['id_user'];
// Menggunakan $conn dan mencocokkan kolom ke user_id
$query_pembimbing = mysqli_query($conn, "SELECT * FROM pembimbing WHERE user_id = '$id_user'");
$data_pembimbing = mysqli_fetch_assoc($query_pembimbing);
$id_pembimbing = $data_pembimbing['id'] ?? 0;
$nama_pembimbing = $data_pembimbing['nama'] ?? (isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Dosen Pembimbing');

// Menghitung Statistik
// 1. Mengambil jumlah pelajar yang statusnya 'diterima' dari tabel pendaftaran
$res_total_mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftaran WHERE pembimbing_id = '$id_pembimbing' AND status = 'diterima'"));

// 2. Mengambil jumlah request pelajar yang statusnya 'menunggu' dari tabel pendaftaran
$res_req_mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftaran WHERE pembimbing_id = '$id_pembimbing' AND status = 'menunggu'"));

$hari_ini = date('Y-m-d');

// 3. Menghitung absensi hari ini dengan JOIN ke tabel pendaftaran (Diset menggunakan pelajar_id)
$res_absen = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM absensi a JOIN pendaftaran p ON a.pelajar_id = p.pelajar_id WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima' AND a.tanggal = '$hari_ini' AND a.status = 'hadir'"));

// 4. Menghitung logbook yang menunggu review (Diset menggunakan tabel pelajar dan kolom pelajar_id)
$res_logbook = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM logbook l JOIN pelajar pl ON l.pelajar_id = pl.id JOIN pendaftaran p ON pl.id = p.pelajar_id WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima' AND l.status = 'menunggu'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pembimbing - SIBIMA JATIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- SweetAlert Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        html, body { width: 100%; margin: 0; padding: 0; }
        body { background-color: #f4f6f9; overflow-x: hidden; }
        *, *::before, *::after { box-sizing: border-box; }

        /* LOGIKA TOGGLE SIDEBAR */
        .sidebar {
            transition: all 0.3s ease;
        }
        
        .main-content { 
            margin-left: 260px; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            transition: all 0.3s ease; 
        }
        
        .content-body { flex: 1; }

        body.sidebar-hidden .sidebar {
            transform: translateX(-260px);
        }
        
        body.sidebar-hidden .main-content {
            margin-left: 0;
            width: 100%;
        }

        .navbar-custom { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 10px 20px; 
            background-color: #ffffff; 
            height: 70px; 
            border-bottom: 1px solid #eef2f6;
            margin-bottom: 24px;
        }
        
        .footer { 
            background-color: #ffffff; 
            border-top: 1px solid #eef2f6; 
            padding: 20px; 
            text-align: center; 
            color: #777777; 
            font-size: 14px; 
            margin-top: auto; 
            margin-left: 260px;
            transition: all 0.3s ease;
        }

        body.sidebar-hidden .footer {
            margin-left: 0;
        }

        /* WELCOME BANNER */
        .welcome-banner{
            background: linear-gradient(135deg, #0f172a, #1e40af);
            border-radius: 20px;
            padding: 1.8rem;
            color: #fff;
            margin-bottom: 1.8rem;
            box-shadow: 0 10px 25px rgba(30,64,175,.20);
        }

        #realtime-clock{ letter-spacing: 2px; font-size: 2rem; font-weight: 700; }

        /* STAT CARD */
        .stat-card{
            border: none;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            transition: all .3s ease;
            color: #fff;
            box-shadow: 0 8px 20px rgba(30,58,138,.15);
        }

        .stat-card:hover{
            box-shadow: 0 15px 30px rgba(30,58,138,.25);
            transform: translateY(-4px) scale(1.02);
        }

        .stat-card::before{
            content: '';
            position: absolute;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            top: -40px;
            right: -30px;
        }

        .stat-card .card-body{
            position: relative;
            z-index: 2;
            padding: 1.5rem;
        }

        .stat-card .stat-icon{
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 3rem;
            opacity: .15;
            z-index: 1;
            transition: all .3s ease;
        }

        .stat-card:hover .stat-icon{
            opacity: .25;
            transform: scale(1.1) rotate(-5deg);
        }

        .stat-card .stat-label{
            font-size: .85rem;
            font-weight: 500;
            opacity: .9;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: .5rem;
        }

        .stat-card .stat-value{
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-aktif{ background: linear-gradient(135deg, #0f172a, #1e3a8a); }
        .stat-permintaan{ background: linear-gradient(135deg, #1e3a8a, #2563eb); }
        .stat-hadir{ background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .stat-logbook{ background: linear-gradient(135deg, #3b82f6, #60a5fa); }

        /* CARD TABEL */
        .section-card{ border: none; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,.06); overflow: hidden; }
        .table-modern thead th{
            --bs-table-bg: #1e3a8a;
            --bs-table-color: #fff;
            background-color: #1e3a8a;
            color: #fff;
            border-bottom: none;
        }
        .table-modern th, .table-modern td{ vertical-align: middle; padding: 1rem; }
        .table-modern tbody tr:hover{ --bs-table-bg: #f8fafc; background: #f8fafc; }

        .avatar-circle{
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: white; display: inline-flex; align-items: center; justify-content: center;
            font-weight: 600; margin-right: .7rem; overflow: hidden; font-size: .85rem;
        }

        .badge-status{ padding: .45rem .9rem; border-radius: 30px; font-size: .78rem; font-weight: 500; }

        /* EMPTY STATE TABEL */
        .table-empty-state{
            text-align: center;
            padding: 3rem 1.5rem;
            color: #9ca3af;
        }
        .table-empty-state i{
            font-size: 2.8rem;
            display: block;
            margin-bottom: .8rem;
            color: #cbd5e1;
        }
        .table-empty-state p{
            margin: 0;
            font-size: .92rem;
            color: #6b7280;
        }
        .table-empty-state span.small-sub{
            display: block;
            font-size: .8rem;
            color: #9ca3af;
            margin-top: .25rem;
        }

        @media(max-width:768px){
            .main-content { margin-left: 0; }
            .footer { margin-left: 0; } 
            .sidebar { transform: translateX(-260px); }
            body.sidebar-hidden .sidebar { transform: translateX(0); }
            .welcome-banner{ flex-direction: column; align-items: start !important; }
            #realtime-clock{ margin-top: 15px; font-size: 1.5rem; }
            .stat-card .stat-value{ font-size: 1.7rem; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Load Sidebar Terpisah -->
    <?php include "../include/sidebar_pembimbing.php"; ?>

    <div class="main-content">
        
        <!-- Load Navbar Terpisah -->
        <?php include '../include/navbar_pembimbing.php'; ?>

        <div class="content-body container-fluid px-4">

            <div class="welcome-banner d-flex justify-content-between align-items-center">
                <div>
                    <h3><i class="bi bi-speedometer2 me-2"></i>Dashboard Pembimbing</h3>
                    <p class="mb-0">Selamat datang, <b><?= htmlspecialchars($nama_pembimbing); ?></b></p>
                </div>
                <div id="realtime-clock" class="fw-bold fs-4 pe-3">--:--:--</div>
            </div>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'berhasil_approve'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Siswa berhasil disetujui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card stat-card stat-aktif shadow">
                        <i class="bi bi-people-fill stat-icon"></i>
                        <div class="card-body">
                            <div class="stat-label">Bimbingan Aktif</div>
                            <p class="stat-value"><?= $res_total_mhs['total'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card stat-hadir shadow">
                        <i class="bi bi-geo-alt-fill stat-icon"></i>
                        <div class="card-body">
                            <div class="stat-label">Hadir (Hari Ini)</div>
                            <p class="stat-value"><?= $res_absen['total'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card stat-logbook shadow">
                        <i class="bi bi-book-fill stat-icon"></i>
                        <div class="card-body">
                            <div class="stat-label">Logbook Perlu Review</div>
                            <p class="stat-value"><?= $res_logbook['total'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-card mb-4">
                <div class="card-body p-0">
                    <div class="p-4 pb-0">
                        <h5 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>Daftar Siswa Aktif</h5>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr><th>No</th><th>Nama Siswa</th><th>Instansi / Sekolah</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                // Mengubah JOIN pelajar ke pelajar & p.pelajar_id ke p.pelajar_id
                                $query_tabel = mysqli_query($conn, "SELECT p.*, pl.nama, pl.foto, i.nama_instansi 
                                                                    FROM pendaftaran p
                                                                    JOIN pelajar pl ON p.pelajar_id = pl.id
                                                                    LEFT JOIN instansi i ON pl.instansi_id = i.id
                                                                    WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima'");
                                if ($query_tabel && mysqli_num_rows($query_tabel) > 0) {
                                    $no = 1;
                                    while ($mhs = mysqli_fetch_assoc($query_tabel)) {
                                        $inisial_mhs = strtoupper(substr($mhs['nama'],0,1));
                                        $foto_mhs_dash = getFotoProfilUrl($mhs['foto']);
                                        $avatar_html = $foto_mhs_dash
                                            ? "<img src='".$foto_mhs_dash."' style='width:100%;height:100%;object-fit:cover;border-radius:50%;' alt=''>"
                                            : $inisial_mhs;
                                        echo "<tr><td>".$no++."</td><td><span class='avatar-circle'>".$avatar_html."</span>".htmlspecialchars($mhs['nama'])."</td><td>".htmlspecialchars($mhs['nama_instansi'] ?? '-')."</td>";
                                        echo "<td><span class='badge-status bg-success text-white'>Aktif</span></td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>
                                            <div class='table-empty-state'>
                                                <i class='bi bi-people'></i>
                                                <p>Belum ada siswa bimbingan aktif saat ini.</p>
                                                <span class='small-sub'>Siswa yang Anda setujui akan muncul di sini.</span>
                                            </div>
                                          </td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Load Footer Terpisah -->
        <?php include '../include/footer_pembimbing.php'; ?>
    </div>
</div>
</body>
</html>
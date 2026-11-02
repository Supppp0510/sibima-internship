<?php
session_start();

// GATEKEEPER: Kunci halaman jika belum login atau bukan pelajar (Role ID = 3)
if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php?pesan=belum_login");
    exit();
}

include "../config/koneksi.php";

// Definisikan BASE_URL jika belum didefinisikan (berguna untuk pathing asset)
if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}

$id_user = $_SESSION['id_user'] ?? 0; 

// Cari ID asli pelajar dan simpan ke variabel $pelajar_id
$query_pelajar   = mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '$id_user'");
$data_pelajar   = ($query_pelajar) ? mysqli_fetch_assoc($query_pelajar) : null;
$pelajar_id     = $data_pelajar['id'] ?? 0;

// DETEKSI MODE: Cek apakah pelajar sudah diterima magang
$sudah_magang = false; 
$query_cek = mysqli_query($conn, "SELECT status FROM pendaftaran WHERE pelajar_id = '$pelajar_id' AND status = 'diterima'");

if ($query_cek && mysqli_num_rows($query_cek) > 0) {
    $sudah_magang = true;
}

// =========================================================================
// QUERY STATISTIK PRESENSI & LOGBOOK
// =========================================================================

// 1. Hitung Total Presensi Hadir
$total_presensi = 0;
$query_presensi = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM absensi 
    WHERE (pelajar_id = '$pelajar_id' OR pelajar_id = '$id_user')
    AND (status = 'hadir' OR status IS NULL OR status = '')
");

if ($query_presensi) {
    $data_presensi  = mysqli_fetch_assoc($query_presensi);
    $total_presensi = $data_presensi['total'] ?? 0;
}

// 2. Hitung Total Logbook Terisi
$total_logbook = 0;
$query_logbook = mysqli_query($conn, "SELECT COUNT(*) AS total FROM logbook WHERE pelajar_id = '$pelajar_id'");
if ($query_logbook) {
    $data_logbook = mysqli_fetch_assoc($query_logbook);
    $total_logbook = $data_logbook['total'] ?? 0;
}

// 3. Ambil Nilai Akhir dari tabel penilaian
$nilai_akhir = null;
$query_nilai = mysqli_query($conn, "SELECT nilai_akhir FROM penilaian WHERE pelajar_id = '$pelajar_id' LIMIT 1");
if ($query_nilai && mysqli_num_rows($query_nilai) > 0) {
    $data_nilai  = mysqli_fetch_assoc($query_nilai);
    $nilai_akhir = $data_nilai['nilai_akhir'] ?? null;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelajar - SIBIMA JATIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        html, body { width: 100%; margin: 0; padding: 0; }
        body { background-color: #f4f6f9; overflow-x: hidden; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        *, *::before, *::after { box-sizing: border-box; }

        /* LOGIKA TOGGLE SIDEBAR */
        .sidebar { transition: all 0.3s ease; }
        .main-content { 
            margin-left: 260px; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            transition: all 0.3s ease; 
        }
        .content-body { flex: 1; padding-bottom: 2rem; }

        body.sidebar-hidden .sidebar { transform: translateX(-260px); }
        body.sidebar-hidden .main-content { margin-left: 0; width: 100%; }

        /* Topbar / Navbar Custom */
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

        body.sidebar-hidden .footer { margin-left: 0; }

        /* WELCOME BANNER */
        .welcome-banner{
            background: linear-gradient(135deg, #0f172a, #1e40af);
            border-radius: 20px;
            padding: 1.8rem;
            color: #fff;
            margin-bottom: 1.8rem;
            box-shadow: 0 10px 25px rgba(30,64,175,.20);
        }
        
        .banner-pramagang { 
            background: linear-gradient(135deg, #fffbeb, #fef3c7); 
            border-left: 6px solid #f59e0b; 
            border-radius: 12px; 
            padding: 1.5rem; 
            color: #92400e; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.04); 
            margin-bottom: 1.5rem;
        }

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
            transform: scale(1.02);
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
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-status { background: linear-gradient(135deg, #0f172a, #1e3a8a); }
        .stat-presensi { background: linear-gradient(135deg, #1e3a8a, #2563eb); }
        .stat-logbook { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .stat-nilai { background: linear-gradient(135deg, #3b82f6, #60a5fa); }

        /* CARD STYLES */
        .section-card{ border: none; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,.06); overflow: hidden; }

        /* Badge Status */
        .badge-soft { padding: 0.45rem 0.9rem; border-radius: 30px; font-weight: 600; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.3rem; }
        .badge-soft-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-soft-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-soft-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        @media(max-width:768px){
            .main-content { margin-left: 0; }
            .footer { margin-left: 0; } 
            .sidebar { transform: translateX(-260px); }
            body.sidebar-hidden .sidebar { transform: translateX(0); }
            .welcome-banner{ flex-direction: column; align-items: start !important; }
            .stat-card .stat-value{ font-size: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Load Sidebar Pelajar -->
    <?php include "../include/sidebar_pelajar.php"; ?>

    <div class="main-content">
        <!-- Load Navbar Pelajar -->
        <?php include "../include/navbar_pelajar.php"; ?>

        <div class="content-body container-fluid px-4">

            <!-- Welcome Banner -->
            <div class="welcome-banner d-flex justify-content-between align-items-center">
                <div>
                    <h3><i class="bi bi-speedometer2 me-2"></i>Dashboard Pelajar</h3>
                    <p class="mb-0">Selamat datang, <b><?= htmlspecialchars(ucwords(strtolower($_SESSION['nama'] ?? 'Pelajar'))); ?></b></p>
                </div>
            </div>

            <?php if (!$sudah_magang): ?>
                
                <div class="banner-pramagang">
                    <h5 class="fw-bold mb-2"><i class="bi bi-info-circle-fill me-2 text-warning"></i>Silakan Melakukan Pendaftaran Magang</h5>
                    <p class="mb-0">Saat ini Anda berstatus sebagai <strong>Calon Peserta</strong>. Silakan menuju menu <strong>Pendaftaran</strong> untuk mengajukan magang.</p>
                </div>

            <?php else: ?>

                <!-- Statistik Data Magang Aktif -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card stat-status shadow">
                            <i class="bi bi-person-badge-fill stat-icon"></i>
                            <div class="card-body">
                                <div class="stat-label">Status Akun</div>
                                <h4 class="stat-value">Aktif Magang</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <a href="absensi.php" style="text-decoration: none; color: inherit;">
                            <div class="card stat-card stat-presensi shadow">
                                <i class="bi bi-geo-alt-fill stat-icon"></i>
                                <div class="card-body">
                                    <div class="stat-label">Presensi Hadir</div>
                                    <h4 class="stat-value"><?= $total_presensi; ?> Hari</h4>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="logbook.php" style="text-decoration: none; color: inherit;">
                            <div class="card stat-card stat-logbook shadow">
                                <i class="bi bi-journal-bookmark-fill stat-icon"></i>
                                <div class="card-body">
                                    <div class="stat-label">Logbook Terisi</div>
                                    <h4 class="stat-value"><?= $total_logbook ?? 0; ?> Jurnal</h4>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="laporan_akhir.php" style="text-decoration: none; color: inherit;">
                            <div class="card stat-card stat-nilai shadow">
                                <i class="bi bi-graph-up-arrow stat-icon"></i>
                                <div class="card-body">
                                    <div class="stat-label">Nilai Akhir</div>
                                    <h4 class="stat-value">
                                        <?php if ($nilai_akhir !== null && $nilai_akhir !== ''): ?>
                                            <?= number_format((float)$nilai_akhir, 2); ?>
                                        <?php else: ?>
                                            Belum Rilis
                                        <?php endif; ?>
                                    </h4>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

            <?php endif; ?>

            <!-- ================= CARD STATUS PENDAFTARAN TERBARU ================= -->
            <?php
            // Query mengambil pendaftaran terbaru pelajar
            $query_riwayat = mysqli_query($conn, "
                SELECT 
                    p.*, 
                    uk.unit_kerja AS nama_unit_kerja, 
                    r.nama_ruangan,
                    pb.nama AS nama_pembimbing
                FROM pendaftaran p
                LEFT JOIN unit_kerja uk ON p.unit_kerja_id = uk.id 
                LEFT JOIN pelajar m ON p.pelajar_id = m.id
                LEFT JOIN ruangan r ON m.id_ruangan = r.id_ruangan
                LEFT JOIN pembimbing pb ON p.pembimbing_id = pb.id
                WHERE p.pelajar_id = '" . mysqli_real_escape_string($conn, $pelajar_id) . "' 
                ORDER BY p.id DESC
                LIMIT 1
            ");

            if ($query_riwayat && mysqli_num_rows($query_riwayat) > 0):
                $row = mysqli_fetch_assoc($query_riwayat);
                
                $badge_color = 'badge-soft-warning'; 
                $icon = 'bi-hourglass-split';
                
                if ($row['status'] == 'diterima') {
                    $badge_color = 'badge-soft-success';
                    $icon = 'bi-check-circle';
                } elseif ($row['status'] == 'ditolak') {
                    $badge_color = 'badge-soft-danger';
                    $icon = 'bi-x-circle';
                }
            ?>
                <div class="card section-card mb-4 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                    <!-- Header Card -->
                    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Status Pendaftaran Terbaru
                        </h5>
                        <span class="badge-soft <?= $badge_color; ?> fs-6 px-3 py-2">
                            <i class="bi <?= $icon; ?> me-1"></i> <?= ucfirst($row['status']); ?>
                        </span>
                    </div>
                    
                    <!-- Body Card Grid Info -->
                    <div class="card-body p-4 bg-white">
                        <div class="row g-3">
                            <!-- Unit Kerja -->
                            <div class="col-md-3 col-sm-6">
                                <div class="p-3 bg-light rounded-3 h-100 border border-light-subtle">
                                    <small class="text-muted d-block fw-semibold mb-1 text-uppercase" style="font-size: 0.75rem;">
                                        <i class="bi bi-building me-1 text-primary"></i> Unit Kerja
                                    </small>
                                    <div class="fw-bold text-dark">
                                        <?php 
                                            if ($row['status'] == 'diterima') {
                                                echo htmlspecialchars($row['nama_unit_kerja'] ?? 'Belum Ditentukan');
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Ruangan -->
                            <div class="col-md-3 col-sm-6">
                                <div class="p-3 bg-light rounded-3 h-100 border border-light-subtle">
                                    <small class="text-muted d-block fw-semibold mb-1 text-uppercase" style="font-size: 0.75rem;">
                                        <i class="bi bi-door-open me-1 text-primary"></i> Ruangan
                                    </small>
                                    <div class="fw-bold text-dark">
                                        <?php 
                                            if ($row['status'] == 'diterima') {
                                                echo htmlspecialchars($row['nama_ruangan'] ?? '-');
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Pembimbing -->
                            <div class="col-md-3 col-sm-6">
                                <div class="p-3 bg-light rounded-3 h-100 border border-light-subtle">
                                    <small class="text-muted d-block fw-semibold mb-1 text-uppercase" style="font-size: 0.75rem;">
                                        <i class="bi bi-person-badge me-1 text-primary"></i> Pembimbing
                                    </small>
                                    <div class="fw-bold text-dark">
                                        <?php 
                                            if ($row['status'] == 'diterima') {
                                                echo htmlspecialchars($row['nama_pembimbing'] ?? '-');
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Tanggal Pengajuan -->
                            <div class="col-md-3 col-sm-6">
                                <div class="p-3 bg-light rounded-3 h-100 border border-light-subtle">
                                    <small class="text-muted d-block fw-semibold mb-1 text-uppercase" style="font-size: 0.75rem;">
                                        <i class="bi bi-calendar-event me-1 text-primary"></i> Tanggal Pengajuan
                                    </small>
                                    <div class="fw-bold text-dark">
                                        <?= !empty($row['tanggal_daftar']) ? date('d M Y', strtotime($row['tanggal_daftar'])) : '-'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Card Jika Belum Ada Pengajuan Magang -->
                <div class="card section-card mb-4 border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-5 text-center bg-white">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <h6 class="fw-bold text-dark mb-1">Belum Ada Pengajuan Magang</h6>
                        <p class="text-muted small mb-0">Anda belum mengirimkan data pengajuan magang.</p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div> <!-- End Main Content -->
</div> <!-- End Wrapper -->

<!-- Load Footer Pelajar -->
<?php include "../include/footer_pelajar.php"; ?>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil_daftar'): ?>
<script>
    Swal.fire({
        title: 'Pendaftaran Berhasil!',
        text: 'Pendaftaran PKL Berhasil Dikirim! Silakan tunggu verifikasi dari Admin.',
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#2563eb'
    });
</script>
<?php endif; ?>

</body>
</html>
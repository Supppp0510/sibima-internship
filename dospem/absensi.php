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
$query_pembimbing = mysqli_query($conn, "SELECT * FROM pembimbing WHERE user_id = '$id_user'");
$pembimbing = mysqli_fetch_assoc($query_pembimbing);
$id_pembimbing = $pembimbing['id'] ?? 0;
$nama_pembimbing = $pembimbing['nama'] ?? (isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Dosen Pembimbing');

// ==========================================================
// SINKRONISASI HARI LIBUR (tanggal merah yang diinput admin)
// ==========================================================
$data_libur_nasional = [];
$q_libur_nasional = mysqli_query($conn, "SELECT * FROM hari_libur");
if ($q_libur_nasional) {
    while ($row_l = mysqli_fetch_assoc($q_libur_nasional)) {
        $data_libur_nasional[$row_l['tanggal']] = $row_l['keterangan'];
    }
}

/**
 * Auto-tandai Alpha untuk pelajar yang belum absen sampai jam 15:00.
 * Sabtu/Minggu & tanggal merah (hari_libur) DIKECUALIKAN dari penandaan Alpha.
 */
if (!function_exists('autoTandaiAlpha')) {
    function autoTandaiAlpha($conn, $id_pembimbing, $data_libur_nasional = []) {
        $jam_batas = '15:00:00';
        $sekarang  = date('H:i:s');
        $hari_ini  = date('Y-m-d');
        $hari_ke   = (int)date('N', strtotime($hari_ini));

        // Kalau belum lewat jam 15:00, tidak perlu proses apa-apa
        if ($sekarang < $jam_batas) {
            return;
        }

        // Sabtu (6), Minggu (7), atau tanggal merah -> tidak ada kewajiban absen, jangan tandai Alpha
        if ($hari_ke >= 6 || isset($data_libur_nasional[$hari_ini])) {
            return;
        }

        // Cari pelajar bimbingan yang aktif TAPI belum punya record absensi hari ini
        $query = mysqli_query($conn, "
            SELECT pl.id as pelajar_id
            FROM pendaftaran p
            JOIN pelajar pl ON p.pelajar_id = pl.id
            LEFT JOIN absensi a ON pl.id = a.pelajar_id AND a.tanggal = '$hari_ini'
            WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima'
              AND a.id IS NULL
        ");

        if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
        $mhs_id = (int)$row['pelajar_id'];
        mysqli_query($conn, "
            INSERT INTO absensi (pelajar_id, tanggal, jam_masuk, jam_pulang, status, keterangan)
            VALUES ('$mhs_id', '$hari_ini', NULL, NULL, 'alpha', 'Otomatis: tidak absen hingga jam 15:00')
        ");
         }
        }
    }
}

autoTandaiAlpha($conn, $id_pembimbing, $data_libur_nasional);

$tanggal_filter = isset($_GET['tanggal']) && $_GET['tanggal'] !== '' ? $_GET['tanggal'] : date('Y-m-d');
$status_filter   = isset($_GET['status']) ? $_GET['status'] : 'semua';
$cari           = isset($_GET['cari']) ? trim($_GET['cari']) : '';

// Validasi sederhana format tanggal, fallback ke hari ini kalau salah
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_filter)) {
    $tanggal_filter = date('Y-m-d');
}

// Apakah tanggal yang sedang dilihat adalah hari libur (weekend / tanggal merah)?
$hari_ke_filter    = (int)date('N', strtotime($tanggal_filter));
$is_libur_hari_ini = ($hari_ke_filter >= 6) || isset($data_libur_nasional[$tanggal_filter]);
$keterangan_libur  = $data_libur_nasional[$tanggal_filter] ?? ($hari_ke_filter >= 6 ? 'Akhir Pekan' : '');

$status_map_label = [
    'hadir' => 'Hadir',
    'izin'  => 'Izin',
    'sakit' => 'Sakit',
    'alpha' => 'Alpha',
];
// REVISI: Samakan penamaan class dengan badge-soft agar seragam
$status_map_badge = [
    'hadir'       => 'badge-soft-success',
    'izin'        => 'badge-soft-warning',
    'sakit'       => 'badge-soft-warning',
    'alpha'       => 'badge-soft-danger',
    'belum_absen' => 'bg-secondary text-white', // Tetap grey tapi nanti dibantu padding badge-soft
    'libur'       => 'badge-soft-dark',
];

// ==========================================================
// STATISTIK KARTU (berdasarkan tanggal_filter)
// ==========================================================
$total_mhs = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM pendaftaran
    WHERE pembimbing_id = '$id_pembimbing' AND status = 'diterima'
"))['total'] ?? 0;

$rekap = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0];
$res_rekap = mysqli_query($conn, "
    SELECT a.status, COUNT(*) as jml
    FROM absensi a
    JOIN pendaftaran p ON a.pelajar_id = p.pelajar_id
    WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima'
      AND a.tanggal = '$tanggal_filter'
    GROUP BY a.status
");
if ($res_rekap) {
    while ($r = mysqli_fetch_assoc($res_rekap)) {
        if (isset($rekap[$r['status']])) $rekap[$r['status']] = (int)$r['jml'];
    }
}
$sudah_absen = array_sum($rekap);
$belum_absen_count = max($total_mhs - $sudah_absen, 0);
// Kalau hari yang dilihat adalah hari libur, tidak ada kewajiban absen -> jangan hitung sebagai "belum absen"
if ($is_libur_hari_ini) {
    $belum_absen_count = 0;
}
$ada_data_ringkasan = ($total_mhs > 0);

// ==========================================================
// DATA TABEL: KEHADIRAN PADA TANGGAL YANG DIPILIH
// ==========================================================
$where_status = "";
if ($status_filter !== 'semua') {
    if ($status_filter === 'belum_absen') {
        $where_status = " AND a.id IS NULL ";
    } else {
        $status_filter_safe = mysqli_real_escape_string($conn, $status_filter);
        $where_status = " AND a.status = '$status_filter_safe' ";
    }
}
$cari_safe = mysqli_real_escape_string($conn, $cari);
$where_cari = $cari !== '' ? " AND pl.nama LIKE '%$cari_safe%' " : "";

// PERBAIKAN: Menghapus pl.nisn dari SELECT query agar tidak error
$sql_hari_ini = "
    SELECT pl.id as pelajar_id, pl.nama, pl.foto,
           i.nama_instansi,
           a.jam_masuk, a.jam_pulang, a.status, a.keterangan, a.foto_masuk, a.foto_pulang
    FROM pendaftaran p
    JOIN pelajar pl ON p.pelajar_id = pl.id
    LEFT JOIN instansi i ON pl.instansi_id = i.id
    LEFT JOIN absensi a ON pl.id = a.pelajar_id AND a.tanggal = '$tanggal_filter'
    WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima'
    $where_status $where_cari
    ORDER BY pl.nama ASC
";
$query_hari_ini = mysqli_query($conn, $sql_hari_ini);

// Fungsi hitung durasi kerja
function hitungDurasi($masuk, $pulang) {
    if (!$masuk || !$pulang) return '-';
    $t1 = strtotime($masuk);
    $t2 = strtotime($pulang);
    if ($t2 < $t1) return '-';
    $diff = $t2 - $t1;
    $jam = floor($diff / 3600);
    $menit = floor(($diff % 3600) / 60);
    return $jam . "j " . $menit . "m";
}

// Apakah ada filter aktif (untuk membedakan pesan empty state)
$ada_filter_aktif = ($status_filter !== 'semua') || ($cari !== '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Siswa - SIBIMA JATIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        html, body { width: 100%; margin: 0; padding: 0; }
        body { background-color: #f4f6f9; overflow-x: hidden; }
        *, *::before, *::after { box-sizing: border-box; }

        .sidebar { transition: all 0.3s ease; }
        .main-content {
            margin-left: 260px; min-height: 100vh;
            display: flex; flex-direction: column; transition: all 0.3s ease;
        }
        .content-body { flex: 1; }
        body.sidebar-hidden .sidebar { transform: translateX(-260px); }
        body.sidebar-hidden .main-content { margin-left: 0; width: 100%; }

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
            background-color: #ffffff; border-top: 1px solid #eef2f6;
            padding: 20px; text-align: center; color: #777777; font-size: 14px;
            margin-top: auto; margin-left: 260px; transition: all 0.3s ease;
        }
        body.sidebar-hidden .footer { margin-left: 0; }

        /* ==========================
           PAGE HEADER (kotak gradient gelap, konsisten dengan halaman lain)
        ========================== */
        .page-header-bar {
            background: linear-gradient(135deg, #0f172a, #1e40af);
            border-radius: 20px;
            padding: 1.8rem;
            color: #fff;
            margin-bottom: 1.8rem;
            box-shadow: 0 10px 25px rgba(30,64,175,.20);
        }
        .page-header-bar h3 { color: #fff; }
        .page-header-bar p { color: rgba(255,255,255,0.85); }

        .section-card { border: none; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,.06); overflow: hidden; }

        .stat-card {
            border: none; border-radius: 18px; padding: 1.25rem 1.4rem;
            box-shadow: 0 5px 20px rgba(0,0,0,.05); display: flex; align-items: center; gap: 1rem;
            background: #fff; height: 100%;
        }
        .stat-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;
        }
        .stat-value { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
        .stat-label { font-size: .8rem; color: #6b7280; }
        .stat-sub { font-size: .72rem; color: #9ca3af; }

        .table-modern thead th {
            background-color: #1e3a8a; color: #fff; border-bottom: none;
        }
        .table-modern th, .table-modern td { vertical-align: middle; padding: 1rem; }
        .table-modern tbody tr:hover { background: #f8fafc; }

        .avatar-circle {
            width: 36px; height: 36px; border-radius: 50%; overflow: hidden;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: white; display: inline-flex; align-items: center; justify-content: center;
            font-weight: 600; margin-right: .7rem; font-size: .85rem; flex-shrink: 0;
        }
        .avatar-circle img { width: 100%; height: 100%; object-fit: cover; }

        /* REVISI: Perbaikan Class Badge */
        .badge-soft { padding: 0.45rem 0.9rem; border-radius: 30px; font-weight: 600; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.3rem; white-space: nowrap; }
        .badge-soft-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-soft-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-soft-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-soft-dark { background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; }

        .search-box { border-radius: 30px; padding-left: 1.2rem; }
        .search-icon { border-radius: 30px 0 0 30px; background: #fff; }

        .side-panel-card { border: none; border-radius: 18px; box-shadow: 0 5px 20px rgba(0,0,0,.05); }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        .list-mini-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: .55rem 0; border-bottom: 1px solid #f1f3f6; font-size: .85rem;
        }
        .list-mini-item:last-child { border-bottom: none; }

        /* EMPTY STATE (tabel & panel) */
        .table-empty-state, .panel-empty-state {
            text-align: center;
            color: #9ca3af;
            padding: 2.6rem 1.2rem;
        }
        .table-empty-state i, .panel-empty-state i {
            font-size: 2.6rem;
            display: block;
            margin-bottom: .7rem;
            color: #cbd5e1;
        }
        .table-empty-state p, .panel-empty-state p {
            margin: 0;
            font-size: .9rem;
            color: #6b7280;
            font-weight: 500;
        }
        .table-empty-state span.small-sub, .panel-empty-state span.small-sub {
            display: block;
            font-size: .78rem;
            color: #9ca3af;
            margin-top: .25rem;
        }
        .stat-empty-note {
            font-size: .72rem; color: #9ca3af; font-style: italic;
        }

        @media(max-width:768px){
            .main-content { margin-left: 0; }
            .footer { margin-left: 0; }
            .sidebar { transform: translateX(-260px); }
            body.sidebar-hidden .sidebar { transform: translateX(0); }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include "../include/sidebar_pembimbing.php"; ?>

    <div class="main-content">
        <?php include '../include/navbar_pembimbing.php'; ?>

        <div class="content-body container-fluid px-4">

            <div class="page-header-bar">
                <h3 class="fw-bold m-0"><i class="bi bi-geo-alt me-2"></i>Monitoring Kehadiran Siswa</h3>
                <p class="mb-0">Pantau kehadiran dan aktivitas harian siswa bimbingan Anda</p>
            </div>

            <?php if ($is_libur_hari_ini): ?>
                <div class="alert alert-secondary d-flex align-items-center mb-4" style="border-radius: 14px;">
                    <i class="bi bi-moon-stars-fill fs-4 me-3"></i>
                    <div>
                        <strong>Tanggal ini hari libur<?= $keterangan_libur ? ' — ' . htmlspecialchars($keterangan_libur) : '' ?></strong>
                        <div class="small text-muted">Siswa tidak diwajibkan absen pada tanggal ini.</div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ================= KARTU STATISTIK ================= -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#e8f0fe;color:#1e3a8a;"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="stat-value"><?= $total_mhs ?></div>
                            <div class="stat-label">Siswa Aktif</div>
                            <?php if ($total_mhs === 0): ?>
                                <div class="stat-empty-note">Belum ada siswa bimbingan</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <!-- REVISI: Perbaikan warna latar ikon -->
                        <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-check-circle-fill"></i></div>
                        <div>
                            <div class="stat-value"><?= $rekap['hadir'] ?></div>
                            <div class="stat-label">Hadir</div>
                            <div class="stat-sub"><?= $total_mhs > 0 ? round($rekap['hadir']/$total_mhs*100,1) : 0 ?>% dari total</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <!-- REVISI: Perbaikan warna latar ikon -->
                        <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-calendar2-week-fill"></i></div>
                        <div>
                            <div class="stat-value"><?= $rekap['izin'] + $rekap['sakit'] ?></div>
                            <div class="stat-label">Izin / Sakit</div>
                            <div class="stat-sub"><?= $total_mhs > 0 ? round(($rekap['izin']+$rekap['sakit'])/$total_mhs*100,1) : 0 ?>% dari total</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <!-- REVISI: Perbaikan warna latar ikon -->
                        <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-person-x-fill"></i></div>
                        <div>
                            <div class="stat-value"><?= $rekap['alpha'] ?></div>
                            <div class="stat-label">Alpha</div>
                            <div class="stat-sub"><?= $total_mhs > 0 ? round($rekap['alpha']/$total_mhs*100,1) : 0 ?>% dari total</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <!-- REVISI: Perbaikan warna latar ikon -->
                        <div class="stat-icon" style="background:#f3f4f6;color:#6b7280;"><i class="bi bi-clock-history"></i></div>
                        <div>
                            <div class="stat-value"><?= $belum_absen_count ?></div>
                            <div class="stat-label">Belum Absen</div>
                            <div class="stat-sub"><?= $total_mhs > 0 ? round($belum_absen_count/$total_mhs*100,1) : 0 ?>% dari total</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- ================= KOLOM KIRI: FILTER + TABEL ================= -->
                <div class="col-lg-8">

                    <form method="GET" class="card section-card mb-4">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Tanggal</label>
                                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal_filter) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Filter Status</label>
                                    <select name="status" class="form-select">
                                        <option value="semua" <?= $status_filter=='semua'?'selected':'' ?>>Semua Status</option>
                                        <option value="hadir" <?= $status_filter=='hadir'?'selected':'' ?>>Hadir</option>
                                        <option value="izin" <?= $status_filter=='izin'?'selected':'' ?>>Izin</option>
                                        <option value="sakit" <?= $status_filter=='sakit'?'selected':'' ?>>Sakit</option>
                                        <option value="alpha" <?= $status_filter=='alpha'?'selected':'' ?>>Alpha</option>
                                        <option value="belum_absen" <?= $status_filter=='belum_absen'?'selected':'' ?>>Belum Absen</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Cari nama siswa</label>
                                    <div class="input-group">
                                        <span class="input-group-text search-icon border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                        <input type="text" name="cari" class="form-control border-start-0" placeholder="Nama siswa..." value="<?= htmlspecialchars($cari) ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="bi bi-funnel me-1"></i>Terapkan Filter</button>
                                <a href="absensi.php" class="btn btn-outline-secondary rounded-pill px-4">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="card section-card mb-4">
                        <div class="card-body p-0">
                            <div class="p-4 pb-0">
                                <h5 class="fw-bold mb-0"><i class="bi bi-calendar2-check text-primary me-2"></i>Kehadiran Tanggal <?= date('d M Y', strtotime($tanggal_filter)) ?></h5>
                            </div>
                            <div class="table-responsive mt-3">
                                <table class="table table-modern align-middle mb-0">
                                    <thead>
                                        <!-- REVISI: Penyesuaian lebar kolom agar proporsional -->
                                        <tr>
                                            <th class="ps-4 text-center" width="5%">No</th>
                                            <th width="30%">Nama Siswa</th>
                                            <th width="35%">Instansi / Sekolah</th>
                                            <th width="10%">Masuk</th>
                                            <th width="10%">Pulang</th>
                                            <th width="10%">Durasi</th>
                                            <th class="text-center" width="10%">Status</th>
                                            <th class="text-center" width="5%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($query_hari_ini && mysqli_num_rows($query_hari_ini) > 0) {
                                            $no = 1;
                                            while ($h = mysqli_fetch_assoc($query_hari_ini)) {
                                                $status_real = $h['status'] ?? null;
                                                if (!$status_real && $is_libur_hari_ini) {
                                                    $label = 'Libur';
                                                    $badge = $status_map_badge['libur'];
                                                } else {
                                                    $label = $status_real ? ($status_map_label[$status_real] ?? $status_real) : 'Belum Absen';
                                                    // REVISI: Perbaikan Badge
                                                    $badge = $status_real ? ($status_map_badge[$status_real] ?? 'bg-secondary text-white') : $status_map_badge['belum_absen'];
                                                }
                                                $durasi = hitungDurasi($h['jam_masuk'], $h['jam_pulang']);
                                                $inisial = strtoupper(substr($h['nama'], 0, 1));

                                                echo "<tr>";
                                                echo "<td class='ps-4 text-center'>".$no++."</td>";
                                                echo "<td class='fw-bold text-dark d-flex align-items-center'>";
                                                if (!empty($h['foto'])) {
                                                    $foto_mhs = getFotoProfilUrl($h['foto']);
                                                    echo "<span class='avatar-circle'>";
                                                    if ($foto_mhs) {
                                                        echo "<img src='".$foto_mhs."' alt=''>";
                                                    } else {
                                                        echo $inisial;
                                                    }
                                                    echo "</span>";
                                                } else {
                                                    echo "<span class='avatar-circle'>".$inisial."</span>";
                                                }
                                                echo htmlspecialchars($h['nama'])."</td>";
                                                
                                                // REVISI: Memotong text instansi jika terlalu panjang
                                                echo "<td style='max-width: 150px;' class='text-truncate' title='".htmlspecialchars($h['nama_instansi'] ?? '-')."'>".htmlspecialchars($h['nama_instansi'] ?? '-')."</td>";
                                                
                                                echo "<td>".($h['jam_masuk'] ? date('H:i', strtotime($h['jam_masuk'])) : '-')."</td>";
                                                echo "<td>".($h['jam_pulang'] ? date('H:i', strtotime($h['jam_pulang'])) : '-')."</td>";
                                                echo "<td>$durasi</td>";
                                                echo "<td class='text-center'><span class='badge-soft $badge'>$label</span></td>";
                                                echo "<td class='text-center'>
                                                        <button type='button' class='btn btn-sm btn-outline-primary rounded-circle' title='Lihat detail'
                                                            data-bs-toggle='modal' data-bs-target='#modalDetail'
                                                            data-nama='".htmlspecialchars($h['nama'], ENT_QUOTES)."'
                                                            data-instansi='".htmlspecialchars($h['nama_instansi'] ?? '-', ENT_QUOTES)."'
                                                            data-masuk='".($h['jam_masuk'] ? date('H:i', strtotime($h['jam_masuk'])) : '-')."'
                                                            data-pulang='".($h['jam_pulang'] ? date('H:i', strtotime($h['jam_pulang'])) : '-')."'
                                                            data-durasi='$durasi'
                                                            data-status='$label'
                                                            data-ket=\"".htmlspecialchars($h['keterangan'] ?? '-', ENT_QUOTES)."\"
                                                            data-foto='".($h['foto_masuk'] ? BASE_URL.'assets/upload/absensi/'.htmlspecialchars($h['foto_masuk'], ENT_QUOTES) : '')."'>
                                                            <i class='bi bi-eye'></i>
                                                        </button>
                                                      </td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='8'>
                                                    <div class='table-empty-state'>
                                                        <i class='bi bi-calendar-x'></i>";
                                            if ($ada_filter_aktif) {
                                                echo "<p>Tidak ada data yang cocok dengan filter ini.</p>
                                                      <span class='small-sub'>Coba ubah tanggal, status, atau kata kunci pencarian.</span>";
                                            } else {
                                                echo "<p>Belum ada data kehadiran untuk tanggal ini.</p>
                                                      <span class='small-sub'>Data akan muncul setelah siswa melakukan absensi.</span>";
                                            }
                                            echo "  </div>
                                                  </td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ================= LINK KE RIWAYAT LENGKAP (halaman terpisah) ================= -->
                    <div class="card section-card mb-4">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat & Log Keseluruhan Absensi</h6>
                                <p class="text-muted small mb-0">Lihat seluruh riwayat kehadiran siswa bimbingan dari semua tanggal.</p>
                            </div>
                            <a href="riwayat_absensi.php" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-list-ul me-1"></i> Lihat Riwayat Lengkap
                            </a>
                        </div>
                    </div>

                </div>

                <!-- ================= KOLOM KANAN: RINGKASAN ================= -->
                <div class="col-lg-4">

                    <div class="card side-panel-card mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Ringkasan Kehadiran (<?= date('d M Y', strtotime($tanggal_filter)) ?>)</h6>

                            <?php if ($ada_data_ringkasan): ?>
                                <!-- REVISI: Pembatasan Tinggi Chart.js -->
                                <div style="position: relative; height: 220px; width: 100%;">
                                    <canvas id="chartRingkasan"></canvas>
                                </div>
                                <div class="mt-3">
                                    <div class="list-mini-item"><span><span class="legend-dot" style="background:#16a34a;"></span>Hadir</span><b><?= $rekap['hadir'] ?></b></div>
                                    <div class="list-mini-item"><span><span class="legend-dot" style="background:#d97706;"></span>Izin</span><b><?= $rekap['izin'] ?></b></div>
                                    <div class="list-mini-item"><span><span class="legend-dot" style="background:#0ea5e9;"></span>Sakit</span><b><?= $rekap['sakit'] ?></b></div>
                                    <div class="list-mini-item"><span><span class="legend-dot" style="background:#dc2626;"></span>Alpha</span><b><?= $rekap['alpha'] ?></b></div>
                                    <div class="list-mini-item"><span><span class="legend-dot" style="background:#9ca3af;"></span>Belum Absen</span><b><?= $belum_absen_count ?></b></div>
                                </div>
                            <?php else: ?>
                                <div class="panel-empty-state">
                                    <i class="bi bi-pie-chart"></i>
                                    <p>Belum ada data untuk ditampilkan.</p>
                                    <span class="small-sub">Ringkasan akan muncul setelah ada siswa bimbingan aktif.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card side-panel-card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0">Siswa Belum Absen</h6>
                                <span class="badge-status bg-secondary text-white p-1 px-2 rounded"><?= $belum_absen_count ?> siswa</span>
                            </div>
                            <div style="max-height: 260px; overflow-y: auto;">
                            <?php
                            if ($is_libur_hari_ini) {
                                echo "<p class='text-muted small mb-0'><i class='bi bi-moon-stars-fill text-secondary me-1'></i>Hari ini libur, tidak ada kewajiban absen.</p>";
                            } else {
                                $sql_belum = "
                                    SELECT pl.nama
                                    FROM pendaftaran p
                                    JOIN pelajar pl ON p.pelajar_id = pl.id
                                    LEFT JOIN absensi a ON pl.id = a.pelajar_id AND a.tanggal = '$tanggal_filter'
                                    WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima' AND a.id IS NULL
                                    ORDER BY pl.nama ASC
                                ";
                                $res_belum = mysqli_query($conn, $sql_belum);
                                if ($res_belum && mysqli_num_rows($res_belum) > 0) {
                                    while ($b = mysqli_fetch_assoc($res_belum)) {
                                        echo "<div class='list-mini-item'><span><i class='bi bi-person-fill text-muted me-2'></i>".htmlspecialchars($b['nama'])."</span><span class='badge-status bg-secondary text-white p-1 px-2 rounded' style='font-size: 0.75rem;'>Belum Absen</span></div>";
                                    }
                                } else {
                                    echo "<p class='text-muted small mb-0'><i class='bi bi-check2-circle text-success me-1'></i>Semua siswa sudah melakukan absensi.</p>";
                                }
                            }
                            ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

<!-- ================= MODAL DETAIL ABSENSI ================= -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalNama">Detail Kehadiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalFotoWrap" class="text-center mb-3" style="display:none;">
                    <img id="modalFoto" src="" class="img-fluid rounded" style="max-height:220px;">
                </div>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" width="40%">Instansi / Sekolah</td><td id="modalInstansi">-</td></tr>
                    <tr><td class="text-muted">Jam Masuk</td><td id="modalMasuk">-</td></tr>
                    <tr><td class="text-muted">Jam Pulang</td><td id="modalPulang">-</td></tr>
                    <tr><td class="text-muted">Durasi Kerja</td><td id="modalDurasi">-</td></tr>
                    <tr><td class="text-muted">Status</td><td id="modalStatus">-</td></tr>
                    <tr><td class="text-muted">Keterangan</td><td id="modalKet">-</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Isi modal detail dari data-attribute tombol yang diklik
document.getElementById('modalDetail').addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    document.getElementById('modalNama').textContent     = btn.getAttribute('data-nama');
    document.getElementById('modalInstansi').textContent = btn.getAttribute('data-instansi') || '-';
    document.getElementById('modalMasuk').textContent    = btn.getAttribute('data-masuk');
    document.getElementById('modalPulang').textContent   = btn.getAttribute('data-pulang');
    document.getElementById('modalDurasi').textContent   = btn.getAttribute('data-durasi');
    document.getElementById('modalStatus').textContent   = btn.getAttribute('data-status');
    document.getElementById('modalKet').textContent      = btn.getAttribute('data-ket') || '-';

    const foto = btn.getAttribute('data-foto');
    const wrap = document.getElementById('modalFotoWrap');
    const img  = document.getElementById('modalFoto');
    if (foto) {
        img.src = foto;
        wrap.style.display = 'block';
    } else {
        wrap.style.display = 'none';
    }
});

<?php if ($ada_data_ringkasan): ?>
// Donut chart ringkasan kehadiran
new Chart(document.getElementById('chartRingkasan'), {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Izin', 'Sakit', 'Alpha', 'Belum Absen'],
        datasets: [{
            data: [<?= $rekap['hadir'] ?>, <?= $rekap['izin'] ?>, <?= $rekap['sakit'] ?>, <?= $rekap['alpha'] ?>, <?= $belum_absen_count ?>],
            backgroundColor: ['#16a34a', '#d97706', '#0ea5e9', '#dc2626', '#9ca3af'],
            borderWidth: 0
        }]
    },
    // REVISI: Tambahan opsi maintainAspectRatio agar grafik tidak membesar
    options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: { legend: { display: false } },
        cutout: '70%'
    }
});
<?php endif; ?>
</script>

        <?php include '../include/footer_pembimbing.php'; ?>
    </div>
</div>
</body>
</html>
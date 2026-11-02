<?php
session_start();
include '../config/koneksi.php';
include '../helper/foto_helper.php';
include '../helper/absensi_helper.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}

$id_user = $_SESSION['id_user'];
$pembimbing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pembimbing WHERE user_id = '$id_user'"));
$id_pembimbing = $pembimbing['id'] ?? 0;

$hari_ini = date('Y-m-d');

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

$status_map_label = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'];
$status_map_badge = [
    'hadir' => 'bg-success text-white', 'izin' => 'bg-warning text-dark',
    'sakit' => 'bg-info text-dark', 'alpha' => 'bg-danger text-white',
];

// ==========================================================
// FILTER
// ==========================================================
$cari       = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$mhs_filter = isset($_GET['mhs']) && $_GET['mhs'] !== '' ? (int)$_GET['mhs'] : 0;

/**
 * Helper: klasifikasikan status 1 tanggal untuk 1 siswa.
 * (definisi fungsi klasifikasiTanggal & klasifikasikanTanggalSafe
 *  dipindah ke helper/absensi_helper.php supaya bisa dipakai bareng
 *  oleh halaman cetak)
 */

if ($mhs_filter > 0) {

    // ======================================================================
    // MODE DETAIL: Kalender kehadiran penuh 1 siswa (dari awal s/d akhir PKL)
    // ======================================================================
    $q_siswa = mysqli_query($conn, "
        SELECT pl.id, pl.nama, pl.foto, i.nama_instansi,
               pr.tanggal_mulai, pr.tanggal_selesai
        FROM pendaftaran p
        JOIN pelajar pl ON p.pelajar_id = pl.id
        LEFT JOIN instansi i ON pl.instansi_id = i.id
        LEFT JOIN periode_pkl pr ON p.periode_id = pr.id
        WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima' AND pl.id = '$mhs_filter'
        ORDER BY p.id DESC LIMIT 1
    ");
    $siswa = mysqli_fetch_assoc($q_siswa);

    if (!$siswa) {
        header("Location: riwayat_absensi.php");
        exit();
    }

    $tgl_mulai   = $siswa['tanggal_mulai'] ?: date('Y-m-01');
    $tgl_selesai = $siswa['tanggal_selesai'] ?: date('Y-m-t');

    // Filter sub-rentang tanggal opsional (dibatasi dalam periode PKL siswa)
    $filter_mulai   = isset($_GET['tgl_mulai']) && $_GET['tgl_mulai'] !== '' ? $_GET['tgl_mulai'] : '';
    $filter_selesai = isset($_GET['tgl_selesai']) && $_GET['tgl_selesai'] !== '' ? $_GET['tgl_selesai'] : '';

    $start_loop = $tgl_mulai;
    $end_loop   = $tgl_selesai;
    if ($filter_mulai !== '' && strtotime($filter_mulai) >= strtotime($tgl_mulai)) {
        $start_loop = $filter_mulai;
    }
    if ($filter_selesai !== '' && strtotime($filter_selesai) <= strtotime($tgl_selesai)) {
        $end_loop = $filter_selesai;
    }

    // Ambil semua absensi milik siswa ini
    $q_absen = mysqli_query($conn, "SELECT * FROM absensi WHERE pelajar_id = '$mhs_filter'");
    $data_absen = [];
    while ($row = mysqli_fetch_assoc($q_absen)) {
        $data_absen[$row['tanggal']] = $row;
    }

    // Hitung rekap keseluruhan (dari tanggal_mulai s/d hari ini atau tanggal_selesai, mana yang lebih dulu)
    $rekap_siswa = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0, 'libur' => 0];
    $batas_rekap = (strtotime($tgl_selesai) < strtotime($hari_ini)) ? $tgl_selesai : $hari_ini;
    $tp = $tgl_mulai;
    while (strtotime($tp) <= strtotime($batas_rekap)) {
        $k = klasifikasikanTanggalSafe($tp, $data_absen, $data_libur_nasional, $hari_ini);
        if (isset($rekap_siswa[$k])) $rekap_siswa[$k]++;
        $tp = date('Y-m-d', strtotime($tp . ' +1 day'));
    }
    $total_wajib = $rekap_siswa['hadir'] + $rekap_siswa['izin'] + $rekap_siswa['sakit'] + $rekap_siswa['alpha'];
    $persen_hadir = $total_wajib > 0 ? round($rekap_siswa['hadir'] / $total_wajib * 100, 1) : 0;

}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Absensi - SIBIMA JATIM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
html, body { width: 100%; margin: 0; padding: 0; }
body { background: #f4f6f9; overflow-x: hidden; }
*, *::before, *::after { box-sizing: border-box; }
.sidebar { transition: all 0.3s ease; }
body.sidebar-hidden .sidebar { transform: translateX(-260px); }
body.sidebar-hidden .main-content { margin-left: 0; width: 100%; }
body.sidebar-hidden .footer { margin-left: 0; }
@media(max-width:768px){ .sidebar { transform: translateX(-260px); } body.sidebar-hidden .sidebar { transform: translateX(0); } }
.main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
.content-body { flex: 1; }
@media(max-width:768px){ .main-content { margin-left: 0; } }
.navbar-custom { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background-color: #ffffff; height: 70px; border-bottom: 1px solid #eef2f6; margin-bottom: 24px; flex-wrap: nowrap !important; gap: 8px; }
.footer { background-color: #ffffff; border-top: 1px solid #eef2f6; padding: 20px; text-align: center; color: #777777; font-size: 14px; margin-top: auto; margin-left: 260px; }
@media(max-width:768px){ .footer { margin-left: 0; } }
.page-header-bar { background: linear-gradient(135deg, #0f172a, #1e40af); border-radius: 20px; padding: 1.8rem; color: #fff; margin-bottom: 1.8rem; box-shadow: 0 10px 25px rgba(30,64,175,.20); }
.page-header-bar h3 { color: #fff; }
.page-header-bar p { color: rgba(255,255,255,0.85); }
.section-card { border: none; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,.06); overflow: hidden; }
.table-modern thead th { background-color: #1e3a8a; color: #fff; border-bottom: none; }
.table-modern th, .table-modern td { vertical-align: middle; padding: 1rem; }
.table-modern tbody tr:hover { background: #f8fafc; }
.avatar-circle { width: 32px; height: 32px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #1e3a8a, #2563eb); color: white; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; margin-right: .6rem; font-size: .8rem; flex-shrink: 0; }
.avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
.badge-status { padding: .4rem .85rem; border-radius: 30px; font-size: .76rem; font-weight: 500; }
.table-empty-state { text-align: center; color: #9ca3af; padding: 3rem 1.2rem; }
.table-empty-state i { font-size: 2.6rem; display: block; margin-bottom: .6rem; }
.stat-mini { background: #fff; border-radius: 14px; padding: 1rem 1.2rem; box-shadow: 0 4px 14px rgba(0,0,0,.05); text-align:center; }
.stat-mini .val { font-size: 1.4rem; font-weight: 700; }
.stat-mini .lbl { font-size: .75rem; color: #6b7280; }
.progress-persen { height: 8px; border-radius: 10px; }
</style>
</head>
<body>
<div class="wrapper">
    <?php include __DIR__.'/sidebar_pembimbing.php'; ?>
    <div class="main-content">
        <?php include '../include/navbar_pembimbing.php'; ?>
        <div class="content-body container-fluid px-4">

            <div class="page-header-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h3 class="fw-bold m-0"><i class="bi bi-clock-history me-2"></i>Riwayat Absensi</h3>
                    <p class="mb-0">
                        <?= $mhs_filter > 0 ? 'Kalender kehadiran penuh selama masa PKL' : 'Rekap absensi keseluruhan per siswa bimbingan Anda' ?>
                    </p>
                </div>
                <a href="absensi.php" class="btn btn-light rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Absensi
                </a>
            </div>

            <?php if ($mhs_filter > 0): ?>
            <!-- FILTER RENTANG TANGGAL (MODE DETAIL) -->
            <form method="GET" class="card section-card mb-4">
                <input type="hidden" name="mhs" value="<?= $mhs_filter ?>">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Dari Tanggal</label>
                            <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($filter_mulai) ?>" min="<?= $tgl_mulai ?>" max="<?= $tgl_selesai ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($filter_selesai) ?>" min="<?= $tgl_mulai ?>" max="<?= $tgl_selesai ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 w-100"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                        </div>
                        <div class="col-md-3">
                            <a href="riwayat_absensi.php" class="btn btn-outline-secondary rounded-pill px-4 w-100"><i class="bi bi-arrow-left me-1"></i>Kembali ke Rekap</a>
                        </div>
                    </div>
                </div>
            </form>
            <?php else: ?>
            <!-- PENCARIAN LANGSUNG KETIK (MODE REKAP) -->
            <div class="card section-card mb-4">
                <div class="card-body">
                    <label class="form-label small text-muted mb-1">Cari nama siswa / instansi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="cariLive" class="form-control" placeholder="Ketik nama siswa atau instansi..." value="<?= htmlspecialchars($cari) ?>" autocomplete="off">
                        <button type="button" id="btnHapusCari" class="btn btn-outline-secondary" style="display:none;" title="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($mhs_filter > 0): ?>
            <!-- ======================================================================
                 MODE DETAIL: KALENDER KEHADIRAN PENUH 1 SISWA
            ====================================================================== -->

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center">
                    <span class="avatar-circle" style="width:44px;height:44px;font-size:1rem;">
                        <?php $foto_url = getFotoProfilUrl($siswa['foto']); if ($foto_url): ?>
                            <img src="<?= $foto_url ?>" alt="">
                        <?php else: echo strtoupper(substr($siswa['nama'],0,1)); endif; ?>
                    </span>
                    <div>
                        <div class="fw-bold fs-5"><?= htmlspecialchars($siswa['nama']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($siswa['nama_instansi'] ?? '-') ?> &bull; Periode PKL: <?= date('d M Y', strtotime($tgl_mulai)) ?> — <?= date('d M Y', strtotime($tgl_selesai)) ?></div>
                    </div>
                </div>
                <a href="cetak_riwayat_absensi.php?<?= http_build_query(['mhs' => $mhs_filter, 'tgl_mulai' => $filter_mulai, 'tgl_selesai' => $filter_selesai]) ?>"
                   target="_blank" class="btn btn-danger rounded-pill px-4 shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Kalender Kehadiran
                </a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-2"><div class="stat-mini"><div class="val text-success"><?= $rekap_siswa['hadir'] ?></div><div class="lbl">Hadir</div></div></div>
                <div class="col-6 col-md-2"><div class="stat-mini"><div class="val text-warning"><?= $rekap_siswa['izin'] ?></div><div class="lbl">Izin</div></div></div>
                <div class="col-6 col-md-2"><div class="stat-mini"><div class="val text-info"><?= $rekap_siswa['sakit'] ?></div><div class="lbl">Sakit</div></div></div>
                <div class="col-6 col-md-2"><div class="stat-mini"><div class="val text-danger"><?= $rekap_siswa['alpha'] ?></div><div class="lbl">Alpha</div></div></div>
                <div class="col-6 col-md-2"><div class="stat-mini"><div class="val text-secondary"><?= $rekap_siswa['libur'] ?></div><div class="lbl">Libur</div></div></div>
                <div class="col-6 col-md-2">
                    <div class="stat-mini">
                        <div class="val"><?= $persen_hadir ?>%</div>
                        <div class="lbl">Kehadiran</div>
                        <div class="progress progress-persen mt-1">
                            <div class="progress-bar bg-<?= $persen_hadir>=85?'success':($persen_hadir>=70?'warning':'danger') ?>" style="width: <?= $persen_hadir ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-card mb-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern align-middle text-center mb-0">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="14%">Tanggal</th>
                                    <th width="10%">Hari</th>
                                    <th width="9%">Masuk</th>
                                    <th width="9%">Pulang</th>
                                    <th width="13%">Status</th>
                                    <th>Keterangan</th>
                                    <th width="10%">Bukti Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $map_hari = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu'];
                            $no = 1;
                            $curr = $start_loop;
                            if (strtotime($start_loop) <= strtotime($end_loop)):
                                while (strtotime($curr) <= strtotime($end_loop)):
                                    $k = klasifikasiTanggal($curr, $data_absen, $data_libur_nasional, $hari_ini);
                                    $hari_indo = $map_hari[date('l', strtotime($curr))] ?? date('l', strtotime($curr));
                                    $row = $k['row'];
                                    $jam_masuk = $row['jam_masuk'] ?? '-';
                                    $jam_pulang = $row['jam_pulang'] ?? '-';

                                    switch ($k['kode']) {
                                        case 'hadir': $badge = '<span class="badge bg-success">Hadir</span>'; break;
                                        case 'izin': $badge = '<span class="badge bg-warning text-dark">Izin</span>'; break;
                                        case 'sakit': $badge = '<span class="badge bg-info text-dark">Sakit</span>'; break;
                                        case 'alpha': $badge = '<span class="badge bg-danger">Alpha</span>'; break;
                                        case 'libur': $badge = '<span class="badge bg-secondary">Libur</span>'; break;
                                        case 'belum_absen': $badge = '<span class="badge bg-warning text-dark">Belum Absen</span>'; break;
                                        case 'belum_waktu': $badge = '<span class="badge bg-light text-muted border">Belum Waktunya</span>'; break;
                                        default: $badge = '<span class="badge bg-light text-dark">'.ucfirst($k['kode']).'</span>';
                                    }

                                    $btn_foto = '<span class="text-muted small">-</span>';
                                    if ($row) {
                                        $links = [];
                                        if (!empty($row['foto_masuk'])) {
                                            $is_b64 = strpos($row['foto_masuk'], 'data:image') === 0;
                                            $p = $is_b64 ? $row['foto_masuk'] : "../assets/upload/absensi/".$row['foto_masuk'];
                                            $links[] = '<a href="'.$p.'" target="_blank" class="btn btn-sm btn-outline-success rounded-pill py-0 px-2" style="font-size:.72rem;">Masuk</a>';
                                        }
                                        if (!empty($row['foto_pulang'])) {
                                            $is_b64 = strpos($row['foto_pulang'], 'data:image') === 0;
                                            $p = $is_b64 ? $row['foto_pulang'] : "../assets/upload/absensi/".$row['foto_pulang'];
                                            $links[] = '<a href="'.$p.'" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill py-0 px-2" style="font-size:.72rem;">Pulang</a>';
                                        }
                                        if (!empty($links)) $btn_foto = implode(' ', $links);
                                    }
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td class="fw-bold text-dark"><?= date('d M Y', strtotime($curr)) ?></td>
                                    <td><?= $hari_indo ?></td>
                                    <td><?= $jam_masuk ? date('H:i', strtotime($jam_masuk)) : '-' ?></td>
                                    <td><?= $jam_pulang ? date('H:i', strtotime($jam_pulang)) : '-' ?></td>
                                    <td><?= $badge ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($k['ket']) ?></td>
                                    <td><?= $btn_foto ?></td>
                                </tr>
                            <?php
                                    $curr = date('Y-m-d', strtotime($curr . ' +1 day'));
                                endwhile;
                            else:
                            ?>
                                <tr><td colspan="8">
                                    <div class="table-empty-state"><i class="bi bi-calendar-x"></i><p>Rentang tanggal tidak valid.</p></div>
                                </td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- ======================================================================
                 MODE REKAP: RINGKASAN ABSENSI KESELURUHAN PER SISWA
            ====================================================================== -->

            <div class="d-flex justify-content-end mb-3">
                <a id="linkCetakRekap" href="cetak_riwayat_absensi.php?<?= http_build_query(['cari' => $cari]) ?>"
                   target="_blank" class="btn btn-danger rounded-pill px-4 shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Rekap Semua Siswa
                </a>
            </div>

            <div class="card section-card mb-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4" width="5%">No</th>
                                    <th width="24%">Nama Siswa</th>
                                    <th width="18%">Instansi / Sekolah</th>
                                    <th width="15%">Periode PKL</th>
                                    <th class="text-center">Hadir</th>
                                    <th class="text-center">Izin</th>
                                    <th class="text-center">Sakit</th>
                                    <th class="text-center">Alpha</th>
                                    <th class="text-center">Libur</th>
                                    <th class="text-center" width="12%">% Kehadiran</th>
                                    <th class="text-center" width="6%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $cari_safe = mysqli_real_escape_string($conn, $cari);
                            $where_cari = ""; // pencarian dilakukan langsung di browser (live), semua siswa dimuat

                            $q_all_siswa = mysqli_query($conn, "
                                SELECT pl.id, pl.nama, pl.foto, i.nama_instansi,
                                       pr.tanggal_mulai, pr.tanggal_selesai
                                FROM pendaftaran p
                                JOIN pelajar pl ON p.pelajar_id = pl.id
                                LEFT JOIN instansi i ON pl.instansi_id = i.id
                                LEFT JOIN periode_pkl pr ON p.periode_id = pr.id
                                WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima'
                                $where_cari
                                ORDER BY pl.nama ASC
                            ");

                            $ada_baris = false;
                            if ($q_all_siswa && mysqli_num_rows($q_all_siswa) > 0):
                                $no = 1;
                                while ($s = mysqli_fetch_assoc($q_all_siswa)):
                                    $ada_baris = true;
                                    $tm = $s['tanggal_mulai'] ?: date('Y-m-01');
                                    $ts = $s['tanggal_selesai'] ?: date('Y-m-t');

                                    $q_abs = mysqli_query($conn, "SELECT * FROM absensi WHERE pelajar_id = '".(int)$s['id']."'");
                                    $data_absen_s = [];
                                    while ($ra = mysqli_fetch_assoc($q_abs)) { $data_absen_s[$ra['tanggal']] = $ra; }

                                    $rk = ['hadir'=>0,'izin'=>0,'sakit'=>0,'alpha'=>0,'libur'=>0];
                                    $batas = (strtotime($ts) < strtotime($hari_ini)) ? $ts : $hari_ini;
                                    $tp = $tm;
                                    while (strtotime($tp) <= strtotime($batas)) {
                                        $kd = klasifikasikanTanggalSafe($tp, $data_absen_s, $data_libur_nasional, $hari_ini);
                                        if (isset($rk[$kd])) $rk[$kd]++;
                                        $tp = date('Y-m-d', strtotime($tp . ' +1 day'));
                                    }
                                    $tw = $rk['hadir']+$rk['izin']+$rk['sakit']+$rk['alpha'];
                                    $persen = $tw > 0 ? round($rk['hadir']/$tw*100,1) : 0;
                                    $foto_url_s = getFotoProfilUrl($s['foto']);
                            ?>
                                <tr class="row-siswa" data-search="<?= htmlspecialchars(mb_strtolower($s['nama'] . ' ' . ($s['nama_instansi'] ?? ''), 'UTF-8'), ENT_QUOTES) ?>">
                                    <td class="ps-4 no-cell"><?= $no++ ?></td>
                                    <td class="fw-bold text-dark">
                                        <span class="avatar-circle"><?php if($foto_url_s): ?><img src="<?= $foto_url_s ?>" alt=""><?php else: echo strtoupper(substr($s['nama'],0,1)); endif; ?></span>
                                        <?= htmlspecialchars($s['nama']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($s['nama_instansi'] ?? '-') ?></td>
                                    <td class="small text-muted"><?= date('d/m/y', strtotime($tm)) ?> - <?= date('d/m/y', strtotime($ts)) ?></td>
                                    <td class="text-center"><span class="badge-status bg-success text-white"><?= $rk['hadir'] ?></span></td>
                                    <td class="text-center"><span class="badge-status bg-warning text-dark"><?= $rk['izin'] ?></span></td>
                                    <td class="text-center"><span class="badge-status bg-info text-dark"><?= $rk['sakit'] ?></span></td>
                                    <td class="text-center"><span class="badge-status bg-danger text-white"><?= $rk['alpha'] ?></span></td>
                                    <td class="text-center"><span class="badge-status bg-secondary text-white"><?= $rk['libur'] ?></span></td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-2 justify-content-center">
                                            <div class="progress progress-persen flex-grow-1" style="max-width:70px;">
                                                <div class="progress-bar bg-<?= $persen>=85?'success':($persen>=70?'warning':'danger') ?>" style="width: <?= $persen ?>%"></div>
                                            </div>
                                            <span class="small fw-bold"><?= $persen ?>%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="?mhs=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Lihat detail kalender">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; endif;
                            if (!$ada_baris): ?>
                                <tr><td colspan="11">
                                    <div class="table-empty-state">
                                        <i class="bi bi-journal-x"></i>
                                        <p>Tidak ada siswa bimbingan yang cocok.</p>
                                    </div>
                                </td></tr>
                            <?php endif; ?>
                                <tr id="barisKosongCari" style="display:none;"><td colspan="11">
                                    <div class="table-empty-state">
                                        <i class="bi bi-search"></i>
                                        <p>Tidak ada siswa yang cocok dengan pencarian.</p>
                                    </div>
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <p class="text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                Rekap dihitung dari tanggal mulai PKL sampai hari ini (atau sampai tanggal selesai PKL jika sudah lewat). Klik ikon <i class="bi bi-eye"></i> untuk melihat kalender kehadiran harian lengkap per siswa.
            </p>

            <script>
            (function () {
                var input   = document.getElementById('cariLive');
                var btnClr  = document.getElementById('btnHapusCari');
                var linkCtk = document.getElementById('linkCetakRekap');
                var kosong  = document.getElementById('barisKosongCari');
                if (!input) return;

                var rows = document.querySelectorAll('tr.row-siswa');
                var baseCetak = 'cetak_riwayat_absensi.php';

                function terapkan() {
                    var q = input.value.trim().toLowerCase();
                    var nomor = 1, tampil = 0;

                    rows.forEach(function (tr) {
                        var cocok = q === '' || (tr.getAttribute('data-search') || '').indexOf(q) !== -1;
                        tr.style.display = cocok ? '' : 'none';
                        if (cocok) {
                            tr.querySelector('.no-cell').textContent = nomor++;
                            tampil++;
                        }
                    });

                    if (kosong) kosong.style.display = (rows.length > 0 && tampil === 0) ? '' : 'none';
                    if (btnClr) btnClr.style.display = q === '' ? 'none' : '';

                    // Link cetak ikut mengikuti kata kunci yang sedang diketik
                    if (linkCtk) {
                        linkCtk.href = baseCetak + (q === '' ? '' : '?cari=' + encodeURIComponent(input.value.trim()));
                    }
                }

                input.addEventListener('input', terapkan);
                input.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
                if (btnClr) btnClr.addEventListener('click', function () { input.value = ''; terapkan(); input.focus(); });

                terapkan();
            })();
            </script>

            <?php endif; ?>

        </div>
        <?php include '../include/footer_pembimbing.php'; ?>
    </div>
</div>
</body>
</html>
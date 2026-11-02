<?php
session_start();
include '../config/koneksi.php';
include '../helper/foto_helper.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 2) {
    header('Location: ../login.php');
    exit();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}

$id_user = $_SESSION['id_user'];
$pembimbing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pembimbing WHERE user_id='$id_user'"));
$id_pembimbing = $pembimbing['id'] ?? 0;
$nama_pembimbing = $pembimbing['nama'] ?? ($_SESSION['nama'] ?? 'Dosen Pembimbing');

// ==========================================================
// Peta status: DB pakai huruf kecil (menunggu, disetujui, revisi, ditolak)
// ==========================================================
$status_label = [
    'menunggu'  => 'Menunggu Review',
    'disetujui' => 'Disetujui',
    'revisi'    => 'Perlu Revisi',
    'ditolak'   => 'Ditolak',
];
$status_badge = [
    'menunggu'  => 'bg-warning text-dark',
    'disetujui' => 'bg-success text-white',
    'revisi'    => 'bg-danger text-white',
    'ditolak'   => 'bg-secondary text-white',
];

// ==========================================================
// FILTER (GET)
// ==========================================================
$tab           = isset($_GET['tab']) ? $_GET['tab'] : 'semua';
$mhs_filter    = isset($_GET['mhs']) ? $_GET['mhs'] : '';
$cari          = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$tgl_mulai     = isset($_GET['tgl_mulai']) && $_GET['tgl_mulai'] !== '' ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_akhir     = isset($_GET['tgl_akhir']) && $_GET['tgl_akhir'] !== '' ? $_GET['tgl_akhir'] : date('Y-m-d');

$where = " WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima' ";
$where .= " AND l.tanggal BETWEEN '".mysqli_real_escape_string($conn,$tgl_mulai)."' AND '".mysqli_real_escape_string($conn,$tgl_akhir)."' ";

if ($mhs_filter !== '') {
    $where .= " AND pl.id = '".mysqli_real_escape_string($conn, $mhs_filter)."' ";
}
if ($cari !== '') {
    $c = mysqli_real_escape_string($conn, $cari);
    $where .= " AND (pl.nama LIKE '%$c%' OR l.kegiatan LIKE '%$c%' OR l.judul LIKE '%$c%') ";
}
if ($tab !== 'semua' && isset($status_label[$tab])) {
    $where .= " AND l.status = '$tab' ";
}

// ==========================================================
// STATISTIK KARTU (mengikuti filter tanggal & pelajar, tapi bukan tab)
// ==========================================================
$where_stat = " WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima'
                AND l.tanggal BETWEEN '".mysqli_real_escape_string($conn,$tgl_mulai)."' AND '".mysqli_real_escape_string($conn,$tgl_akhir)."' ";
if ($mhs_filter !== '') {
    $where_stat .= " AND pl.id = '".mysqli_real_escape_string($conn, $mhs_filter)."' ";
}

$rekap = ['menunggu' => 0, 'disetujui' => 0, 'revisi' => 0, 'ditolak' => 0];
$res_rekap = mysqli_query($conn, "
    SELECT l.status, COUNT(*) as jml
    FROM logbook l
    JOIN pelajar pl ON l.pelajar_id = pl.id
    JOIN pendaftaran p ON pl.id = p.pelajar_id
    $where_stat
    GROUP BY l.status
");
if ($res_rekap) {
    while ($r = mysqli_fetch_assoc($res_rekap)) {
        if (isset($rekap[$r['status']])) $rekap[$r['status']] = (int)$r['jml'];
    }
}
$total_logbook = array_sum($rekap);

// ==========================================================
// KARTU BESAR (real-time HARI INI, tidak ikut filter tanggal)
// ==========================================================
$where_today = " WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima'
                  AND l.tanggal = CURDATE() ";

$rekap_today = ['menunggu' => 0, 'disetujui' => 0, 'revisi' => 0, 'ditolak' => 0];
$res_rekap_today = mysqli_query($conn, "
    SELECT l.status, COUNT(*) as jml
    FROM logbook l
    JOIN pelajar pl ON l.pelajar_id = pl.id
    JOIN pendaftaran p ON pl.id = p.pelajar_id
    $where_today
    GROUP BY l.status
");
if ($res_rekap_today) {
    while ($r = mysqli_fetch_assoc($res_rekap_today)) {
        if (isset($rekap_today[$r['status']])) $rekap_today[$r['status']] = (int)$r['jml'];
    }
}
$total_logbook_today = array_sum($rekap_today);

$total_mhs = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT p.pelajar_id) as total FROM pendaftaran p
    JOIN pelajar pl ON p.pelajar_id = pl.id
    WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima'
"))['total'] ?? 0;

// ==========================================================
// DAFTAR PELAJAR / SISWA (untuk dropdown filter)
// ==========================================================
$list_mhs = mysqli_query($conn, "
    SELECT pl.id, pl.nama
    FROM pendaftaran p JOIN pelajar pl ON p.pelajar_id = pl.id
    WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima'
    ORDER BY pl.nama ASC
");

// ==========================================================
// DATA TABEL LOGBOOK
// ==========================================================
$sql_log = "
    SELECT l.*, pl.nama as nama_pelajar, pl.foto, i.nama_instansi
    FROM logbook l
    JOIN pelajar pl ON l.pelajar_id = pl.id
    JOIN pendaftaran p ON pl.id = p.pelajar_id
    LEFT JOIN instansi i ON pl.instansi_id = i.id
    $where
    ORDER BY l.tanggal DESC, l.id DESC
";
$query_log = mysqli_query($conn, $sql_log);
if (!$query_log) { die("MYSQL ERROR: ".mysqli_error($conn)); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Validasi Logbook - SIBIMA JATIM</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
html, body { width: 100%; margin: 0; padding: 0; }
body { background: #f4f6f9; overflow-x: hidden; }
*, *::before, *::after { box-sizing: border-box; }

.sidebar { transition: all 0.3s ease; }
.main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; transition: all 0.3s ease; }
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
    background-color: #ffffff; border-top: 1px solid #eef2f6; padding: 20px;
    text-align: center; color: #777777; font-size: 14px; margin-top: auto;
    margin-left: 260px; transition: all 0.3s ease;
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

.section-card { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,.06); }

.stat-card {
    border: none; border-radius: 18px; padding: 1.25rem 1.4rem; box-shadow: 0 5px 20px rgba(0,0,0,.05);
    display: flex; align-items: center; gap: 1rem; background: #fff; height: 100%;
}
.stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
.stat-value { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
.stat-label { font-size: .8rem; color: #6b7280; }
.stat-sub { font-size: .72rem; color: #9ca3af; }

.table-modern thead th { background-color: #1e3a8a; color: #fff; border-bottom: none; }
.table-modern th, .table-modern td { vertical-align: middle; padding: 1rem; }

/* REVISI: Perataan kiri pada Thead dan Tbody */
.table-modern thead th.text-left-col { text-align: left !important; }
.table-modern tbody td.text-left-col { text-align: left !important; }

.table-modern tbody tr:hover { background: #f8fafc; }
.table-modern tbody tr.row-active { background: #eff6ff; }

.avatar-circle {
    width: 36px; height: 36px; border-radius: 50%; overflow: hidden;
    background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 600; margin-right: .7rem; font-size: .85rem; flex-shrink: 0;
}
.avatar-circle img { width:100%; height:100%; object-fit:cover; }

.badge-status { padding: .4rem .85rem; border-radius: 30px; font-size: .76rem; font-weight: 500; }

/* ==========================
   TAB STATUS / BUTTON FILTER EFEK VISUAL
========================== */
.tab-status { 
    display: flex; 
    gap: .5rem; 
    flex-wrap: wrap; 
    border-bottom: 1px solid #eef2f6; 
    padding: .8rem 1rem; 
}

.tab-status a {
    padding: .5rem 1rem; 
    font-size: .82rem; 
    font-weight: 600; 
    color: #4b5563;
    background-color: #f3f4f6; /* Warna background saat tidak aktif */
    text-decoration: none; 
    border-radius: 30px; /* Membuat bentuk pill/kapsul */
    transition: all 0.25s ease;
    border: 1px solid #e5e7eb;
    display: inline-flex;
    align-items: center;
}

/* Efek saat kursor diarahkan (Hover) */
.tab-status a:hover {
    background-color: #e5e7eb;
    color: #1e3a8a;
    transform: translateY(-1px);
}

/* Efek Tombol saat Aktif (Diklik) */
.tab-status a.active { 
    color: #ffffff !important; 
    background: linear-gradient(135deg, #1e3a8a, #2563eb); /* Warna biru gradient */
    border-color: #1e3a8a;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); /* Efek bayangan glow */
}

.bukti-thumb { width: 42px; height: 42px; border-radius: 8px; object-fit: cover; margin-right: 4px; }

.side-panel-card { border: none; border-radius: 18px; box-shadow: 0 5px 20px rgba(0,0,0,.05); position: sticky; top: 1rem; }
.detail-row { display:flex; gap:.6rem; font-size:.85rem; padding:.45rem 0; border-bottom:1px solid #f1f3f6; }
.detail-row:last-child{ border-bottom:none; }
.detail-label{ width:42%; color:#6b7280; flex-shrink:0; }
.detail-value{ font-weight:500; color:#1f2937; }
.detail-placeholder{ text-align:center; color:#9ca3af; padding: 3rem 1rem; }

@media(max-width:768px){
    .main-content { margin-left: 0; }
    .footer { margin-left: 0; }
    .sidebar { transform: translateX(-260px); }
    body.sidebar-hidden .sidebar { transform: translateX(0); }
    .side-panel-card { position: static; margin-top: 1.5rem; }
}
</style>
</head>

<body>
<div class="wrapper">
    <?php include __DIR__.'/sidebar_pembimbing.php'; ?>

    <div class="main-content">
        <?php include '../include/navbar_pembimbing.php'; ?>

        <div class="content-body container-fluid px-4">

            <div class="page-header-bar">
                <h3 class="fw-bold m-0"><i class="bi bi-book-half me-2"></i>Validasi Logbook Siswa</h3>
                <p class="mb-0">Pantau dan validasi perkembangan kegiatan harian siswa bimbingan</p>
            </div>

            <?php if (isset($_GET['pesan'])): ?>
                <div class="alert alert-<?= $_GET['pesan']=='sukses' ? 'success' : 'warning' ?> alert-dismissible fade show">
                    <?php
                    $pesan_map = ['sukses'=>'Perubahan status logbook berhasil disimpan.'];
                    echo $pesan_map[$_GET['pesan']] ?? 'Aksi berhasil diproses.';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- ================= KARTU STATISTIK (REAL-TIME HARI INI) ================= -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#f1edfe;color:#7c3aed;"><i class="bi bi-journal-bookmark-fill"></i></div>
                        <div>
                            <div class="stat-value"><?= $total_logbook_today ?></div>
                            <div class="stat-label">Logbook Masuk Hari Ini</div>
                            <div class="stat-sub">Dari <?= $total_mhs ?> Siswa</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#e6f7ee;color:#16a34a;"><i class="bi bi-check-circle-fill"></i></div>
                        <div>
                            <div class="stat-value"><?= $rekap_today['disetujui'] ?></div>
                            <div class="stat-label">Disetujui</div>
                            <div class="stat-sub"><?= $total_logbook_today>0 ? round($rekap_today['disetujui']/$total_logbook_today*100,2) : 0 ?>%</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#fff7e6;color:#d97706;"><i class="bi bi-clock-fill"></i></div>
                        <div>
                            <div class="stat-value"><?= $rekap_today['menunggu'] ?></div>
                            <div class="stat-label">Menunggu Review</div>
                            <div class="stat-sub"><?= $total_logbook_today>0 ? round($rekap_today['menunggu']/$total_logbook_today*100,2) : 0 ?>%</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#fdeaea;color:#dc2626;"><i class="bi bi-exclamation-circle-fill"></i></div>
                        <div>
                            <div class="stat-value"><?= $rekap_today['revisi'] ?></div>
                            <div class="stat-label">Perlu Revisi</div>
                            <div class="stat-sub"><?= $total_logbook_today>0 ? round($rekap_today['revisi']/$total_logbook_today*100,2) : 0 ?>%</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#f1f2f4;color:#4b5563;"><i class="bi bi-x-circle-fill"></i></div>
                        <div>
                            <div class="stat-value"><?= $rekap_today['ditolak'] ?></div>
                            <div class="stat-label">Ditolak</div>
                            <div class="stat-sub"><?= $total_logbook_today>0 ? round($rekap_today['ditolak']/$total_logbook_today*100,2) : 0 ?>%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- ================= KOLOM KIRI ================= -->
                <div class="col-lg-8">

                    <!-- FILTER -->
                    <form method="GET" class="card section-card mb-0">
                        <div class="card-body pb-2">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" name="cari" class="form-control" placeholder="Cari siswa, kegiatan, atau tanggal..." value="<?= htmlspecialchars($cari) ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="mhs" class="form-select">
                                        <option value="">Semua Siswa</option>
                                        <?php if($list_mhs): while($m = mysqli_fetch_assoc($list_mhs)): ?>
                                            <option value="<?= $m['id'] ?>" <?= $mhs_filter==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['nama']) ?></option>
                                        <?php endwhile; endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai) ?>">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="tgl_akhir" class="form-control" value="<?= htmlspecialchars($tgl_akhir) ?>">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
                                </div>
                                <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
                            </div>
                        </div>

                        <!-- TABS STATUS -->
                        <div class="tab-status mt-2">
                            <?php
                            $tabs = [
                                'semua' => 'Semua Logbook ('.$total_logbook.')',
                                'menunggu' => 'Menunggu Review ('.$rekap['menunggu'].')',
                                'revisi' => 'Perlu Revisi ('.$rekap['revisi'].')',
                                'disetujui' => 'Disetujui ('.$rekap['disetujui'].')',
                                'ditolak' => 'Ditolak ('.$rekap['ditolak'].')',
                            ];
                            foreach ($tabs as $key => $label) {
                                $qs = $_GET; $qs['tab'] = $key;
                                $url = '?'.http_build_query($qs);
                                // Menambahkan class active sesuai kunci tab
                                $activeClass = $tab === $key ? "active active-$key" : '';
                                echo "<a href='".htmlspecialchars($url)."' class='$activeClass'>".$label."</a>";
                            }
                            ?>
                            </div>
                    </form>

                    <!-- TABEL -->
                    <div class="card section-card mt-0 mb-4" style="border-top-left-radius:0;border-top-right-radius:0;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-modern align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <!-- REVISI: Pembagian lebar kolom -->
                                            <th class="ps-4" width="5%">No</th>
                                            <th width="15%">Tanggal</th>
                                            <th class="text-left-col" width="30%">Siswa</th>
                                            <th class="text-left-col" width="25%">Kegiatan</th>
                                            <th width="10%">Bukti</th>
                                            <th class="text-center" width="10%">Status</th>
                                            <th class="text-center" width="5%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($query_log) > 0):
                                            $no = 1;
                                            while ($log = mysqli_fetch_assoc($query_log)):
                                                $st = $log['status'];
                                                $label = $status_label[$st] ?? $st;
                                                $badge = $status_badge[$st] ?? 'bg-secondary text-white';
                                                $inisial = strtoupper(substr($log['nama_pelajar'],0,1));
                                                $foto_mhs = getFotoProfilUrl($log['foto']);
                                                $bukti = !empty($log['dokumentasi']) ? BASE_URL.'assets/upload/pelajar/dokumentasi/'.$log['dokumentasi'] : '';
                                        ?>
                                        <tr id="row-log-<?= $log['id'] ?>">
                                            <td class="ps-4"><?= $no++ ?></td>
                                            <td><?= date('d M Y', strtotime($log['tanggal'])) ?><br><small class="text-muted"><?= date('l', strtotime($log['tanggal'])) ?></small></td>
                                            
                                            <!-- REVISI: Perataan kiri dan Text Truncate untuk Siswa dan Instansi -->
                                            <td class="text-left-col fw-bold text-dark d-flex align-items-center border-0" style="padding-left: 0;">
                                                <span class="avatar-circle">
                                                    <?php if($foto_mhs): ?><img src="<?= $foto_mhs ?>" alt=""><?php else: echo $inisial; endif; ?>
                                                </span>
                                                <div class="w-100 d-inline-block">
                                                    <div class="text-truncate" style="max-width:180px;" title="<?= htmlspecialchars($log['nama_pelajar']) ?>"><?= htmlspecialchars($log['nama_pelajar']) ?></div>
                                                    <div class="small text-muted fw-normal text-truncate" style="max-width:180px;" title="<?= htmlspecialchars($log['nama_instansi'] ?? '-') ?>"><?= htmlspecialchars($log['nama_instansi'] ?? '-') ?></div>
                                                </div>
                                            </td>
                                            
                                            <!-- REVISI: Perataan kiri dan Text Truncate untuk Kegiatan -->
                                            <td class="text-left-col">
                                                <div class="fw-semibold small mb-1 text-truncate" style="max-width:180px;" title="<?= htmlspecialchars($log['judul'] ?? '-') ?>"><?= htmlspecialchars($log['judul'] ?? '-') ?></div>
                                                <div class="text-muted small text-truncate" style="max-width:180px;" title="<?= htmlspecialchars($log['kegiatan']) ?>"><?= htmlspecialchars($log['kegiatan']) ?></div>
                                            </td>
                                            
                                            <td>
                                                <?php if($bukti): ?>
                                                    <img src="<?= $bukti ?>" class="bukti-thumb" onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><span class="badge-status <?= $badge ?>"><?= $label ?></span></td>
                                            <td class="text-center">
                                                <!-- REVISI: Tombol aksi disingkat dan ditambahkan tooltip -->
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle btn-lihat-detail"
                                                    data-bs-toggle="tooltip" title="Lihat & validasi"
                                                    data-id="<?= $log['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($log['nama_pelajar'], ENT_QUOTES) ?>"
                                                    data-univ="<?= htmlspecialchars($log['nama_instansi'] ?? '-', ENT_QUOTES) ?>"
                                                    data-foto="<?= htmlspecialchars($foto_mhs, ENT_QUOTES) ?>"
                                                    data-inisial="<?= $inisial ?>"
                                                    data-tanggal="<?= date('d M Y (l)', strtotime($log['tanggal'])) ?>"
                                                    data-judul="<?= htmlspecialchars($log['judul'] ?? '-', ENT_QUOTES) ?>"
                                                    data-kegiatan="<?= htmlspecialchars($log['kegiatan'], ENT_QUOTES) ?>"
                                                    data-bukti="<?= htmlspecialchars($bukti, ENT_QUOTES) ?>"
                                                    data-status="<?= $label ?>"
                                                    data-badge="<?= $badge ?>"
                                                    data-komentar="<?= htmlspecialchars($log['komentar'] ?? '', ENT_QUOTES) ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; else: ?>
                                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data logbook untuk filter ini.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= KOLOM KANAN: DETAIL LOGBOOK ================= -->
                <div class="col-lg-4">
                    <div class="card side-panel-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0">Detail Logbook</h6>
                                <span id="dtlBadge" class="badge-status" style="display:none;"></span>
                            </div>

                            <div id="dtlPlaceholder" class="detail-placeholder">
                                <i class="bi bi-journal-text fs-1 d-block mb-2"></i>
                                Klik ikon <i class="bi bi-eye"></i> pada salah satu baris logbook untuk melihat &amp; memvalidasi detailnya di sini.
                            </div>

                            <div id="dtlContent" style="display:none;">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="avatar-circle" style="width:52px;height:52px;font-size:1.1rem;" id="dtlAvatar">?</span>
                                    <div>
                                        <div class="fw-bold" id="dtlNama">-</div>
                                        <div class="small text-muted" id="dtlUniv">-</div>
                                    </div>
                                </div>

                                <div class="detail-row"><div class="detail-label">Tanggal</div><div class="detail-value" id="dtlTanggal">-</div></div>
                                <div class="detail-row"><div class="detail-label">Judul Kegiatan</div><div class="detail-value" id="dtlJudul">-</div></div>
                                <div class="detail-row"><div class="detail-label">Deskripsi</div><div class="detail-value" id="dtlKegiatan">-</div></div>

                                <div id="dtlBuktiWrap" class="mb-3" style="display:none;">
                                    <div class="detail-label mb-1">Bukti Kegiatan</div>
                                    <img id="dtlBukti" src="" class="img-fluid rounded" style="max-height:180px;">
                                </div>

                                <form method="POST" action="proses_logbook.php" id="formValidasi" class="mt-3">
                                    <input type="hidden" name="id_log" id="dtlIdLog" value="">
                                    <label class="form-label small text-muted mb-1">Catatan Pembimbing</label>
                                    <textarea name="komentar" id="dtlKomentar" class="form-control mb-3" rows="3" maxlength="250" placeholder="Berikan catatan atau feedback untuk siswa..."></textarea>

                                    <div class="d-flex gap-2">
                                        <button type="submit" name="aksi_tolak" class="btn btn-outline-danger flex-fill" onclick="return confirmAksi('menolak')">Tolak</button>
                                        <button type="submit" name="aksi_revisi" class="btn btn-outline-warning flex-fill" onclick="return confirmAksi('meminta revisi')">Minta Revisi</button>
                                        <button type="submit" name="aksi_setuju" class="btn btn-success flex-fill" onclick="return confirmAksi('menyetujui')">Setuju</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<script>
// REVISI: Inisialisasi Tooltip
document.addEventListener("DOMContentLoaded", function(){
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});

document.querySelectorAll('.btn-lihat-detail').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.querySelectorAll('tr.row-active').forEach(r => r.classList.remove('row-active'));
        document.getElementById('row-log-' + this.dataset.id)?.classList.add('row-active');

        document.getElementById('dtlPlaceholder').style.display = 'none';
        document.getElementById('dtlContent').style.display = 'block';

        document.getElementById('dtlIdLog').value = this.dataset.id;
        document.getElementById('dtlNama').textContent = this.dataset.nama;
        document.getElementById('dtlUniv').textContent = this.dataset.univ;
        document.getElementById('dtlTanggal').textContent = this.dataset.tanggal;
        document.getElementById('dtlJudul').textContent = this.dataset.judul;
        document.getElementById('dtlKegiatan').textContent = this.dataset.kegiatan;
        document.getElementById('dtlKomentar').value = this.dataset.komentar || '';

        var badge = document.getElementById('dtlBadge');
        badge.textContent = this.dataset.status;
        badge.className = 'badge-status ' + this.dataset.badge;
        badge.style.display = 'inline-block';

        var avatar = document.getElementById('dtlAvatar');
        if (this.dataset.foto) {
            avatar.innerHTML = '<img src="'+this.dataset.foto+'" alt="">';
        } else {
            avatar.textContent = this.dataset.inisial;
        }

        var buktiWrap = document.getElementById('dtlBuktiWrap');
        if (this.dataset.bukti) {
            document.getElementById('dtlBukti').src = this.dataset.bukti;
            buktiWrap.style.display = 'block';
        } else {
            buktiWrap.style.display = 'none';
        }
    });
});

function confirmAksi(aksi){
    return confirm('Yakin ingin ' + aksi + ' logbook ini?');
}
</script>

        <?php include '../include/footer_pembimbing.php'; ?>
    </div>
</div>
</body>
</html>
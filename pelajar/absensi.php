<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../config/config.php"; // Hubungkan koneksi database

// =========================================================================
// SISTEM DETEKSI ID PELAJAR SECARA AMAN (PREPARED STATEMENTS)
// =========================================================================
$pelajar_id = null;

if (!empty($_SESSION['pelajar_id'])) {
    $pelajar_id = $_SESSION['pelajar_id'];
} else {
    $user_id_check = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
    
    if (!empty($user_id_check)) {
        $stmt_mhs = $conn->prepare("SELECT id FROM pelajar WHERE user_id = ? OR id = ? LIMIT 1");
        if ($stmt_mhs) {
            $stmt_mhs->bind_param("ss", $user_id_check, $user_id_check);
            $stmt_mhs->execute();
            $res_mhs = $stmt_mhs->get_result();
            if ($data_mhs = $res_mhs->fetch_assoc()) {
                $pelajar_id = $data_mhs['id'];
            }
            $stmt_mhs->close();
        }
    }
}

// Proteksi Keamanan: Jika gagal mendeteksi ID, lempar kembali ke login
if (empty($pelajar_id)) {
    header("Location: ../login.php?pesan=belum_login");
    exit;
}

// =========================================================================
// 1. AMBIL TANGGAL MULAI & SELESAI DARI TABEL PERIODE_PKL & PENDAFTARAN
// =========================================================================
$tgl_mulai   = date('Y-m-01');
$tgl_selesai = date('Y-m-t');

$stmt_periode = $conn->prepare("
    SELECT pr.tanggal_mulai, pr.tanggal_selesai, p.created_at AS tgl_daftar
    FROM pendaftaran p 
    JOIN periode_pkl pr ON p.periode_id = pr.id 
    WHERE p.pelajar_id = ? 
    ORDER BY p.id DESC LIMIT 1
");

if ($stmt_periode) {
    $stmt_periode->bind_param("s", $pelajar_id);
    $stmt_periode->execute();
    $q_periode = $stmt_periode->get_result();
    if ($q_periode && $q_periode->num_rows > 0) {
        $dt_p        = $q_periode->fetch_assoc();
        $tgl_selesai = $dt_p['tanggal_selesai'];
        
        $tgl_mulai_periode  = $dt_p['tanggal_mulai'];
        $tgl_daftar_pelajar = !empty($dt_p['tgl_daftar']) ? date('Y-m-d', strtotime($dt_p['tgl_daftar'])) : date('Y-m-d');
        
        if (strtotime($tgl_daftar_pelajar) > strtotime($tgl_mulai_periode)) {
            $tgl_mulai = $tgl_daftar_pelajar;
        } else {
            $tgl_mulai = $tgl_mulai_periode;
        }
    }
    $stmt_periode->close();
}

// =========================================================================
// PERBAIKAN: BERSIHKAN DATA ALPHA SAMPAH SEBELUM TANGGAL TERDAFTAR
// =========================================================================
$stmt_clean = $conn->prepare("DELETE FROM absensi WHERE pelajar_id = ? AND status = 'Alpha' AND tanggal < ?");
if ($stmt_clean) {
    $stmt_clean->bind_param("ss", $pelajar_id, $tgl_mulai);
    $stmt_clean->execute();
    $stmt_clean->close();
}

// =========================================================================
// 2. AMBIL SEMUA ABSENSI PELAJAR YANG VALID DARI DATABASE
// =========================================================================
$data_absen = [];
$stmt_absen = $conn->prepare("SELECT * FROM absensi WHERE pelajar_id = ? AND tanggal >= ?");
if ($stmt_absen) {
    $stmt_absen->bind_param("ss", $pelajar_id, $tgl_mulai);
    $stmt_absen->execute();
    $q_absensi = $stmt_absen->get_result();
    while ($row = $q_absensi->fetch_assoc()) {
        $data_absen[$row['tanggal']] = $row;
    }
    $stmt_absen->close();
}

// =========================================================================
// AMBIL DATA HARI LIBUR NASIONAL / TANGGAL MERAH DARI ADMIN
// =========================================================================
$data_libur_nasional = [];
$q_libur_nasional = mysqli_query($conn, "SELECT tanggal, keterangan FROM hari_libur");
if ($q_libur_nasional) {
    while ($row_l = mysqli_fetch_assoc($q_libur_nasional)) {
        $data_libur_nasional[$row_l['tanggal']] = $row_l['keterangan'];
    }
}

// =========================================================================
// 3. GENERATE ALPHA OTOMATIS (HANYA HARI KERJA & BUKAN LIBUR NASIONAL)
// =========================================================================
$jam_sekarang    = date('H:i:s');
$jam_batas_alpha = '15:00:00'; // Batas jam absen harian
$hari_ini        = date('Y-m-d');

// Tentukan batas tanggal penagihan Alpha
if ($jam_sekarang >= $jam_batas_alpha) {
    $tgl_batas = $hari_ini;
} else {
    $tgl_batas = date('Y-m-d', strtotime('-1 day'));
}

$tgl_proses = $tgl_mulai;
$stmt_ins_alpha = $conn->prepare("INSERT INTO absensi (pelajar_id, tanggal, status, keterangan) VALUES (?, ?, 'Alpha', 'Tidak melakukan absensi (Tanpa Keterangan)')");

while (strtotime($tgl_proses) <= strtotime($tgl_batas) && strtotime($tgl_proses) <= strtotime($tgl_selesai)) {
    $hari_ke = date('N', strtotime($tgl_proses));

    // Jika bukan Sabtu (6) / Minggu (7), BUKAN libur nasional, dan belum ada record di DB, buatkan Alpha
    if ($hari_ke != 6 && $hari_ke != 7 && !isset($data_libur_nasional[$tgl_proses]) && !isset($data_absen[$tgl_proses])) {
        if ($stmt_ins_alpha) {
            $stmt_ins_alpha->bind_param("ss", $pelajar_id, $tgl_proses);
            if ($stmt_ins_alpha->execute()) {
                $data_absen[$tgl_proses] = [
                    'id'          => $conn->insert_id,
                    'pelajar_id'  => $pelajar_id,
                    'tanggal'     => $tgl_proses,
                    'status'      => 'Alpha',
                    'keterangan'  => 'Tidak melakukan absensi (Tanpa Keterangan)',
                    'jam_masuk'   => null,
                    'jam_pulang'  => null,
                    'foto_masuk'  => null,
                    'foto_pulang' => null
                ];
            }
        }
    }
    $tgl_proses = date('Y-m-d', strtotime($tgl_proses . ' +1 day'));
}
if ($stmt_ins_alpha) {
    $stmt_ins_alpha->close();
}

// =========================================================================
// 4. HITUNG REKAPITULASI DARI DATA ABSENSI SAMPAI HARI INI
// =========================================================================
$total_hadir = 0;
$total_izin  = 0;
$total_sakit = 0;
$total_alpha = 0;

foreach ($data_absen as $tgl_key => $absen_item) {
    if (strtotime($tgl_key) <= strtotime($hari_ini)) {
        $st_item = strtolower(trim($absen_item['status'] ?? ''));
        
        if ($st_item === 'izin') {
            $total_izin++;
        } elseif ($st_item === 'sakit') {
            $total_sakit++;
        } elseif ($st_item === 'alpha') {
            $total_alpha++;
        } elseif (!empty($absen_item['jam_masuk']) || !empty($st_item)) {
            // Selagi pelajar melakukan absensi (apapun statusnya selain Izin, Sakit, Alpha), dihitung Hadir
            $total_hadir++;
        }
    }
}

$total_hari_kerja_berjalan = $total_hadir + $total_izin + $total_sakit + $total_alpha;
$persentase_hadir = ($total_hari_kerja_berjalan > 0) ? round(($total_hadir / $total_hari_kerja_berjalan) * 100, 1) : 0;

if ($persentase_hadir >= 85) {
    $badge_persen = 'bg-success';
    $label_persen = 'Sangat Baik';
} elseif ($persentase_hadir >= 70) {
    $badge_persen = 'bg-warning text-dark';
    $label_persen = 'Cukup';
} else {
    $badge_persen = 'bg-danger';
    $label_persen = 'Kurang';
}

// =========================================================================
// 5. LOGIKA FILTER RENTANG TANGGAL + PAGINATION RIWAYAT
// =========================================================================
$filter_tgl_mulai   = filter_input(INPUT_GET, 'tgl_mulai', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$filter_tgl_selesai = filter_input(INPUT_GET, 'tgl_selesai', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

$start_loop = $tgl_mulai;
$end_loop   = $tgl_selesai;

if (!empty($filter_tgl_mulai) && strtotime($filter_tgl_mulai) >= strtotime($tgl_mulai)) {
    $start_loop = $filter_tgl_mulai;
}
if (!empty($filter_tgl_selesai) && strtotime($filter_tgl_selesai) <= strtotime($tgl_selesai)) {
    $end_loop = $filter_tgl_selesai;
}

// Riwayat hanya sampai hari ini (masa mendatang tidak ditampilkan)
if (strtotime($end_loop) > strtotime($hari_ini)) {
    $end_loop = $hari_ini;
}

// Kumpulkan tanggal hari kerja (Senin-Jumat), urut dari TERBARU ke terlama
$daftar_tanggal = [];
if (strtotime($start_loop) <= strtotime($end_loop)) {
    $d = $end_loop;
    while (strtotime($d) >= strtotime($start_loop)) {
        if (date('N', strtotime($d)) < 6) {
            $daftar_tanggal[] = $d;
        }
        $d = date('Y-m-d', strtotime($d . ' -1 day'));
    }
}

$per_halaman   = 10;
$total_data    = count($daftar_tanggal);
$total_halaman = max(1, (int) ceil($total_data / $per_halaman));
$halaman       = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$halaman       = max(1, min($halaman, $total_halaman));
$offset        = ($halaman - 1) * $per_halaman;
$tanggal_halaman = array_slice($daftar_tanggal, $offset, $per_halaman);

function urlHalaman($p, $mulai, $selesai) {
    $q = ['page' => $p];
    if ($mulai !== '')   $q['tgl_mulai']   = $mulai;
    if ($selesai !== '') $q['tgl_selesai'] = $selesai;
    return 'absensi.php?' . http_build_query($q);
}

// Helper: tombol foto + modal (masuk / pulang)
function tombolFoto($row, $jenis, &$modals_html) {
    $foto = $row['foto_' . $jenis] ?? '';
    if (empty($foto)) return '';

    $is_base64 = (strpos($foto, 'data:image') === 0);
    $path = $is_base64 ? $foto : "../assets/upload/absensi/" . htmlspecialchars($foto);
    $ext  = $is_base64 ? 'png' : strtolower(pathinfo($foto, PATHINFO_EXTENSION));
    $label = ucfirst($jenis);
    $kelas = ($jenis === 'masuk') ? 'success' : 'danger';

    if ($ext === 'pdf') {
        return '<a href="' . $path . '" target="_blank" class="btn btn-sm btn-' . $kelas . ' rounded-pill px-3 py-1 mx-1 fw-bold" style="font-size: 0.75rem;"><i class="bi bi-file-earmark-pdf-fill me-1"></i> ' . $label . ' (PDF)</a>';
    }

    $modal_id = 'modal' . $label . (int)$row['id'];
    $modals_html .= '
    <div class="modal fade" id="' . $modal_id . '" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-0 bg-light rounded-top">
                    <h6 class="modal-title fw-bold text-dark"><i class="bi bi-camera-fill me-2 text-' . $kelas . '"></i>Bukti Foto Absen ' . $label . '</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <img src="' . $path . '" class="img-fluid rounded shadow-sm border" alt="Bukti Absen ' . $label . '" onerror="this.src=\'https://placehold.co/600x450?text=Foto+Tidak+Ditemukan\';">
                </div>
                <div class="modal-footer border-0 bg-light justify-content-center rounded-bottom">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm fw-bold" data-bs-dismiss="modal">Tutup</button>
                    ' . (!$is_base64 ? '<a href="' . $path . '" download class="btn btn-primary rounded-pill px-4 btn-sm fw-bold"><i class="bi bi-download me-1"></i> Unduh</a>' : '') . '
                </div>
            </div>
        </div>
    </div>';

    return '<button type="button" data-bs-toggle="modal" data-bs-target="#' . $modal_id . '" class="btn btn-sm btn-' . $kelas . ' rounded-pill px-3 py-1 mx-1 fw-bold" style="font-size: 0.75rem;"><i class="bi bi-camera-fill me-1"></i> ' . $label . '</button>';
}

// Deteksi aksi menu harian (?aksi=masuk / pulang / izin)
$aksi = filter_input(INPUT_GET, 'aksi', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Pelajar - SIBIMA JATIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        html, body { width: 100%; margin: 0; padding: 0; }
        body { background-color: #f4f6f9; overflow-x: hidden; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        *, *::before, *::after { box-sizing: border-box; }

        .sidebar { transition: all 0.3s ease; }
        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; transition: all 0.3s ease; }
        .content-body { flex: 1; padding-bottom: 2rem; }

        body.sidebar-hidden .sidebar { transform: translateX(-260px); }
        body.sidebar-hidden .main-content { margin-left: 0; width: 100%; }

        .page-header-bar {
            background: linear-gradient(135deg, #0f172a, #1e40af);
            border-radius: 20px;
            padding: 1.8rem;
            color: #fff;
            margin-bottom: 1.8rem;
            box-shadow: 0 10px 25px rgba(30,64,175,.20);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-header-bar h3 { font-weight: 700; color: #fff; margin: 0; }
        .page-header-bar p { color: rgba(255,255,255,0.85); font-size: 0.85rem; margin: 0.2rem 0 0; }
        
        .card-modern { border: none; border-radius: 16px; box-shadow: 0 4px 18px rgba(0,0,0,0.06); margin-bottom: 1.5rem; overflow: hidden; }
        .card-header-custom { background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff; padding: 1rem 1.5rem; }
        
        .btn-menu-absen { border: none; padding: 1.5rem; border-radius: 16px; color: white; display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; }
        .btn-menu-absen:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); color: white; }
        
        .stat-rekap-card { background: #ffffff; border-radius: 16px; border: none; transition: transform 0.2s; }
        .stat-rekap-card:hover { transform: translateY(-3px); }
        .stat-icon-wrapper { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }

        #webcam-preview { width: 100%; max-width: 420px; height: auto; transform: scaleX(-1); border-radius: 12px; background: #1e293b; }
        #canvas { display: none; }
        .preview-result { width: 100%; max-width: 320px; border-radius: 12px; display: none; margin-top: 15px; border: 3px solid #2563eb; }
        .nav-pills .nav-link.active { background-color: #2563eb; }
        
        .gps-tracker-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #2563eb; border-radius: 8px; padding: 0.8rem 1rem; text-align: left; margin-bottom: 1.5rem; }

        .table-modern thead th { background-color: #1e3a8a; color: #fff; border-bottom: none; text-transform: uppercase; font-size: 0.78rem; letter-spacing: 0.5px; white-space: nowrap; }
        .table-modern th, .table-modern td { vertical-align: middle; padding: 0.8rem 1rem; }
        .table-modern tbody tr:hover { background: #f8fafc; }

        .pagination .page-link { color: #1e3a8a; border-radius: 8px; margin: 0 2px; border: 1px solid #e2e8f0; }
        .pagination .page-item.active .page-link { background-color: #2563eb; border-color: #2563eb; color: #fff; }
        .pagination .page-item.disabled .page-link { color: #94a3b8; }

        @media(max-width:768px){
            .main-content { margin-left: 0; }
            .footer { margin-left: 0; } 
            .sidebar { transform: translateX(-260px); }
            body.sidebar-hidden .sidebar { transform: translateX(0); }
        }

        @media(max-width:576px){
            .page-header-bar { padding: 1.2rem; border-radius: 16px; }
            .page-header-bar h3 { font-size: 1.2rem; }
            #realtime-date { font-size: 0.9rem !important; }
            #realtime-clock { font-size: 1.4rem !important; }
            .filter-form { width: 100%; }
            .filter-form .input-group { width: 100% !important; }
            .filter-form .btn { flex: 1 1 auto; }
            .table-modern th, .table-modern td { padding: 0.6rem 0.7rem; font-size: 0.85rem; white-space: nowrap; }
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
    </style>
</head>
<body>

<div class="wrapper">
    <?php include "../include/sidebar_pelajar.php"; ?>

    <div class="main-content">
        <?php include "../include/navbar_pelajar.php"; ?>

        <div class="content-body container-fluid px-3 px-md-4">

            <div class="page-header-bar">
                <!-- Sisi Kiri: Judul & Deskripsi -->
                <div>
                    <h3><i class="fa-solid fa-camera me-2"></i>Sistem Absensi Harian</h3>
                    <p>Kelola pencatatan kehadiran masuk, pulang, atau ajukan izin magang.</p>
                </div>

                <!-- Sisi Kanan: Pembungkus Bersama (Jam + Tombol Kembali Rapat) -->
                <div class="d-flex align-items-center gap-3">
                    <div style="text-align: right;">
                        <div id="realtime-date" style="font-size: 1.05rem; font-weight: 600; opacity: 0.95; margin-bottom: 2px;"></div>
                        <div id="realtime-clock" style="font-size: 1.75rem; font-weight: 800; letter-spacing: 1px; font-family: monospace;"></div>
                    </div>
                    <?php if (!empty($aksi)): ?>
                        <a href="absensi.php" class="btn btn-outline-light btn-sm rounded-pill px-3 shadow-sm" onclick="matikanKamera()">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($aksi == 'masuk' || $aksi == 'pulang'): ?>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card card-modern">
                            <div class="card-header-custom">
                                <h5 class="mb-0 fw-bold text-capitalize"><i class="bi bi-person-bounding-box me-2"></i>Form Absen <?= htmlspecialchars($aksi); ?></h5>
                            </div>
                            <div class="card-body p-4 text-center">
                                
                                <div id="gps-info" class="gps-tracker-box">
                                    <div class="d-flex align-items-center">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        <span class="small fw-semibold text-secondary">Sedang mengunci koordinat GPS & mencari alamat...</span>
                                    </div>
                                </div>

                                <form action="proses_absensi.php" method="POST" enctype="multipart/form-data" id="formAbsen">
                                    <input type="hidden" name="aksi" value="<?= htmlspecialchars($aksi); ?>">
                                    
                                    <ul class="nav nav-pills justify-content-center mb-3" id="pills-tab" role="tablist">
                                        <li class="nav-item">
                                            <button class="nav-link active btn-sm mx-1" id="pills-camera-tab" data-bs-toggle="pill" data-bs-target="#pills-camera" type="button" onclick="aktifkanKamera()"><i class="bi bi-webcam me-1"></i> Gunakan Kamera</button>
                                        </li>
                                        <li class="nav-item">
                                            <button class="nav-link btn-sm mx-1" id="pills-upload-tab" data-bs-toggle="pill" data-bs-target="#pills-upload" type="button" onclick="matikanKamera()"><i class="bi bi-upload me-1"></i> Unggah Gambar</button>
                                        </li>
                                    </ul>

                                    <div class="tab-content border p-3 rounded bg-light mb-4">
                                        <div class="tab-pane fade show active" id="pills-camera">
                                            <video id="webcam-preview" autoplay playsinline class="mb-2 shadow-sm"></video>
                                            <canvas id="canvas"></canvas>
                                            <br>
                                            <button type="button" class="btn btn-dark btn-sm rounded-pill px-4 py-2 mt-2" onclick="ambilFoto()"><i class="bi bi-camera-fill me-1"></i> Ambil Foto Absen</button>
                                            <br>
                                            <div class="text-center mt-2">
                                                <img id="hasil-snap" class="preview-result shadow-sm" alt="Hasil snapshot">
                                                <p class="text-success small mt-1 fw-bold" id="label-berhasil-snap" style="display:none;"><i class="bi bi-check-circle-fill"></i> Foto berstempel GPS berhasil dibuat!</p>
                                            </div>
                                            <input type="hidden" name="foto_base64" id="foto_base64">
                                        </div>
                                        
                                        <div class="tab-pane fade" id="pills-upload">
                                            <div class="text-start mb-3">
                                                <label class="form-label small fw-bold text-secondary">Pilih Gambar Bukti:</label>
                                                <input type="file" id="foto_file" name="foto_file" class="form-control bg-white" accept="image/*">
                                                <div class="form-text small text-muted">Sistem akan secara otomatis menyisipkan stempel koordinat dan alamat terkini pada berkas foto yang Anda unggah.</div>
                                            </div>
                                            <div class="text-center">
                                                <img id="upload-preview" class="preview-result shadow-sm" alt="Preview Foto Upload">
                                                <p class="text-success small mt-1 fw-bold" id="label-berhasil-upload" style="display:none;"><i class="bi bi-check-circle-fill"></i> Stempel GPS berhasil dipasang pada foto!</p>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
                                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Kirim Absen Sekarang
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($aksi == 'izin'): ?>

                <div class="row justify-content-center">
                    <div class="col-lg-7">
                        <div class="card card-modern">
                            <div class="card-header-custom" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-envelope-paper-fill me-2"></i>Form Pengajuan Izin / Sakit</h5>
                            </div>
                            <div class="card-body p-4">
                                
                                <form action="proses_absensi.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="aksi" value="izin">
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">Jenis Pengajuan <span class="text-danger">*</span></label>
                                        <select name="status_izin" class="form-select bg-light" required>
                                            <option value="izin">Izin (Keperluan Mendesak)</option>
                                            <option value="sakit">Sakit</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">Alasan / Keterangan <span class="text-danger">*</span></label>
                                        <textarea name="keterangan" class="form-control bg-light" rows="4" placeholder="Tuliskan alasan detail ketidakhadiran Anda..." required></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-secondary">Upload Bukti Surat Izin / Surat Dokter (PDF/JPG) <span class="text-danger">*</span></label>
                                        <input type="file" name="foto_file" class="form-control bg-light" accept="image/*,application/pdf" required>
                                    </div>

                                    <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark rounded-pill shadow-sm">
                                        <i class="bi bi-send-fill me-1"></i> Kirim Pengajuan Izin
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>

                <!-- 1. TIGA TOMBOL UTAMA ABSENSI HARIAN -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <a href="absensi.php?aksi=masuk" class="btn-menu-absen shadow-sm" style="background: linear-gradient(135deg, #0f172a, #1e3a8a);">
                            <div>
                                <span class="d-block small opacity-75">Klik untuk mengisi</span>
                                <strong class="fs-5">Absen Masuk</strong>
                            </div>
                            <i class="bi bi-box-arrow-in-right fs-1 opacity-50"></i>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="absensi.php?aksi=pulang" class="btn-menu-absen shadow-sm" style="background: linear-gradient(135deg, #1e3a8a, #2563eb);">
                            <div>
                                <span class="d-block small opacity-75">Klik untuk mengisi</span>
                                <strong class="fs-5">Absen Pulang</strong>
                            </div>
                            <i class="bi bi-box-arrow-left fs-1 opacity-50"></i>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="absensi.php?aksi=izin" class="btn-menu-absen shadow-sm" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                            <div>
                                <span class="d-block small opacity-75">Klik untuk mengajukan</span>
                                <strong class="fs-5">Izin / Sakit</strong>
                            </div>
                            <i class="bi bi-envelope-open fs-1 opacity-50"></i>
                        </a>
                    </div>
                </div>

                <!-- 2. KARTU RINGKASAN REKAPITULASI ABSENSI (5 KARTU) -->
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
                    <div class="col">
                        <div class="card stat-rekap-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold d-block text-uppercase">Total Hadir</span>
                                    <h3 class="fw-bold text-success mb-0 mt-1"><?= $total_hadir; ?> <small class="fs-6 text-muted fw-normal">Hari</small></h3>
                                </div>
                                <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-check-circle-fill fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card stat-rekap-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold d-block text-uppercase">Total Izin</span>
                                    <h3 class="fw-bold text-warning mb-0 mt-1"><?= $total_izin; ?> <small class="fs-6 text-muted fw-normal">Hari</small></h3>
                                </div>
                                <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-envelope-fill fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card stat-rekap-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold d-block text-uppercase">Total Sakit</span>
                                    <h3 class="fw-bold text-info mb-0 mt-1"><?= $total_sakit; ?> <small class="fs-6 text-muted fw-normal">Hari</small></h3>
                                </div>
                                <div class="stat-icon-wrapper bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-hospital-fill fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card stat-rekap-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold d-block text-uppercase">Total Alpha</span>
                                    <h3 class="fw-bold text-danger mb-0 mt-1"><?= $total_alpha; ?> <small class="fs-6 text-muted fw-normal">Hari</small></h3>
                                </div>
                                <div class="stat-icon-wrapper bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-x-circle-fill fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card stat-rekap-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold d-block text-uppercase">Persentase</span>
                                    <h3 class="fw-bold text-primary mb-0 mt-1"><?= $persentase_hadir; ?>%</h3>
                                    <span class="badge <?= $badge_persen; ?> rounded-pill mt-1" style="font-size:0.7rem;"><?= $label_persen; ?></span>
                                </div>
                                <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-pie-chart-fill fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. TABEL RIWAYAT ABSENSI HARIAN -->
                <div class="card card-modern">
                    <div class="card-body p-0">
                        <div class="p-3 p-md-4 pb-3 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
                            <h5 class="fw-bold text-dark mb-0">
                                <i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Absensi Anda
                            </h5>
                            
                            <form method="GET" action="absensi.php" class="filter-form d-flex align-items-center gap-2 flex-wrap">
                                <div class="input-group input-group-sm" style="width: auto;">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($filter_tgl_mulai); ?>" title="Dari Tanggal" autocomplete="off">
                                </div>
                                <span class="text-muted small fw-bold">s/d</span>
                                <div class="input-group input-group-sm" style="width: auto;">
                                    <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($filter_tgl_selesai); ?>" title="Sampai Tanggal" autocomplete="off">
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                                    <i class="bi bi-filter me-1"></i> Cari
                                </button>
                                <?php if (!empty($filter_tgl_mulai) || !empty($filter_tgl_selesai)): ?>
                                    <a href="absensi.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" title="Reset Filter">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                    </a>
                                <?php endif; ?>
                                <a href="cetak_absensi.php?tgl_mulai=<?= urlencode($filter_tgl_mulai); ?>&tgl_selesai=<?= urlencode($filter_tgl_selesai); ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak PDF
                                </a>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-modern table-hover align-middle text-center mb-0">
                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>Tanggal</th>
                                        <th>Hari</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Pulang</th>
                                        <th>Bukti Foto</th> 
                                        <th>Status Kehadiran</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $modals_html = ''; 
                                    $no = $offset + 1;

                                    $map_hari = [
                                        'Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 
                                        'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu', 'Sunday'=>'Minggu'
                                    ];

                                    if ($total_data > 0) {
                                        foreach ($tanggal_halaman as $curr_date) {
                                            $nama_hari = date('l', strtotime($curr_date)); 
                                            $hari_indo = $map_hari[$nama_hari] ?? $nama_hari;

                                            $jam_masuk    = '-';
                                            $jam_pulang   = '-';
                                            $keterangan   = '-';
                                            $badge        = '<span class="text-muted small">-</span>';
                                            $buttons_html = '<span class="text-muted small">-</span>';

                                            if (isset($data_libur_nasional[$curr_date])) {
                                                $badge      = '<span class="badge bg-danger text-white px-3 py-1 rounded-pill fw-bold"><i class="bi bi-calendar-x-fill me-1"></i> Libur Nasional</span>';
                                                $keterangan = htmlspecialchars($data_libur_nasional[$curr_date]);
                                            }
                                            elseif (isset($data_absen[$curr_date])) {
                                    $row    = $data_absen[$curr_date];
                                    $st_val = strtolower(trim($row['status'] ?? ''));
                                    
                                    $jam_masuk  = !empty($row['jam_masuk']) ? htmlspecialchars($row['jam_masuk']) : '-';
                                    $jam_pulang = !empty($row['jam_pulang']) ? htmlspecialchars($row['jam_pulang']) : '-';
                                    $keterangan = htmlspecialchars($row['keterangan'] ?? '-');

                                    // Jika nilai status di database kosong/NULL tetapi jam_masuk terisi, set otomatis sebagai 'hadir'
                                    if (empty($st_val) && !empty($row['jam_masuk'])) {
                                        $st_val = 'hadir';
                                    }

                                    switch ($st_val) {
                                        case 'hadir':
                                            $badge = '<span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Hadir</span>';
                                            break;
                                        case 'terlambat':
                                        case 'terlambat_masuk':
                                            $badge = '<span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold"><i class="bi bi-clock-history me-1"></i> Terlambat</span>';
                                            break;
                                        case 'pulang_cepat':
                                            $badge = '<span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold"><i class="bi bi-clock-history me-1"></i> Pulang Cepat</span>';
                                            break;
                                        case 'tanpa_absen_pulang':
                                            $badge = '<span class="badge bg-info text-dark px-3 py-1 rounded-pill fw-bold"><i class="bi bi-exclamation-circle-fill me-1"></i> Belum Absen Pulang</span>';
                                            break;
                                        case 'alpha':
                                            $badge = '<span class="badge bg-danger text-white px-3 py-1 rounded-pill fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Alpha</span>';
                                            break;
                                        case 'izin':
                                            $badge = '<span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold"><i class="bi bi-envelope-fill me-1"></i> Izin</span>';
                                            break;
                                        case 'sakit':
                                            $badge = '<span class="badge bg-secondary text-white px-3 py-1 rounded-pill fw-bold"><i class="bi bi-hospital-fill me-1"></i> Sakit</span>';
                                            break;
                                        default:
                                            // Fallback: Selagi jam masuk terisi, tampilkan badge Hadir
                                            if (!empty($row['jam_masuk'])) {
                                                $badge = '<span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Hadir</span>';
                                            } else {
                                                $badge = '<span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Hadir</span>';
                                            }
                                            break;
                                    }

                                                $btn_temp  = tombolFoto($row, 'masuk', $modals_html);
                                                $btn_temp .= tombolFoto($row, 'pulang', $modals_html);
                                                if (!empty($btn_temp)) {
                                                    $buttons_html = $btn_temp;
                                                }
                                            } 
                                            elseif ($curr_date == $hari_ini) {
                                                $badge      = '<span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Belum Absen</span>';
                                                $keterangan = 'Silakan melakukan absensi hari ini';
                                            }
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td class="fw-bold text-dark"><?= date('d M Y', strtotime($curr_date)); ?></td>
                                            <td><?= $hari_indo; ?></td>
                                            <td><span class="text-success fw-semibold"><?= $jam_masuk; ?></span></td>
                                            <td><span class="text-danger fw-semibold"><?= $jam_pulang; ?></span></td>
                                            <td>
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <?= $buttons_html; ?>
                                                </div>
                                            </td>
                                            <td><?= $badge; ?></td>
                                            <td class="text-muted text-center"><?= $keterangan; ?></td>
                                        </tr>
                                    <?php 
                                        }
                                    } else { 
                                    ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-5">
                                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                                Tidak ada data pada rentang tanggal ini.
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_data > 0): ?>
                        <!-- PAGINATION -->
                        <div class="p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top">
                            <span class="small text-muted">
                                Menampilkan <?= $offset + 1; ?>–<?= min($offset + $per_halaman, $total_data); ?> dari <?= $total_data; ?> hari kerja
                            </span>

                            <?php if ($total_halaman > 1): ?>
                            <nav aria-label="Navigasi halaman riwayat">
                                <ul class="pagination pagination-sm mb-0 flex-wrap">
                                    <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?= urlHalaman($halaman - 1, $filter_tgl_mulai, $filter_tgl_selesai); ?>">&laquo;</a>
                                    </li>
                                    <?php
                                    $awal  = max(1, $halaman - 2);
                                    $akhir = min($total_halaman, $halaman + 2);
                                    if ($awal > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="' . urlHalaman(1, $filter_tgl_mulai, $filter_tgl_selesai) . '">1</a></li>';
                                        if ($awal > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                                    }
                                    for ($i = $awal; $i <= $akhir; $i++) {
                                        echo '<li class="page-item ' . ($i == $halaman ? 'active' : '') . '"><a class="page-link" href="' . urlHalaman($i, $filter_tgl_mulai, $filter_tgl_selesai) . '">' . $i . '</a></li>';
                                    }
                                    if ($akhir < $total_halaman) {
                                        if ($akhir < $total_halaman - 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                                        echo '<li class="page-item"><a class="page-link" href="' . urlHalaman($total_halaman, $filter_tgl_mulai, $filter_tgl_selesai) . '">' . $total_halaman . '</a></li>';
                                    }
                                    ?>
                                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?= urlHalaman($halaman + 1, $filter_tgl_mulai, $filter_tgl_selesai); ?>">&raquo;</a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?= $modals_html; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include "../include/footer_pelajar.php"; ?>

<script>
function updateRealTimeClock() {
    const now = new Date();
    const optionsDate = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    const formattedDate = now.toLocaleDateString('id-ID', optionsDate);

    const hours   = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const formattedTime = `${hours}:${minutes} WIB`;

    const dateEl = document.getElementById('realtime-date');
    const timeEl = document.getElementById('realtime-clock');

    if (dateEl) dateEl.textContent = formattedDate;
    if (timeEl) timeEl.textContent = formattedTime;
}

updateRealTimeClock();
setInterval(updateRealTimeClock, 1000);
</script>

<?php if ($aksi == 'masuk' || $aksi == 'pulang'): ?>
<script>
let streamLocal = null;
const video = document.getElementById('webcam-preview');
const canvas = document.getElementById('canvas');
const fotoBase64 = document.getElementById('foto_base64');
const hasilSnap = document.getElementById('hasil-snap');

let latitude = "";
let longitude = "";
let alamat = "Mencari lokasi...";

function dapatkanLokasi() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            latitude = position.coords.latitude.toFixed(6);
            longitude = position.coords.longitude.toFixed(6);
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 6000);

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`, {
                signal: controller.signal
            })
            .then(response => response.json())
            .then(data => {
                clearTimeout(timeoutId);
                if (data && data.display_name) {
                    alamat = data.display_name;
                } else {
                    alamat = "Sinyal GPS Terkunci (Detail Alamat tidak tersedia)";
                }
                updateGpsBoxUI();
            })
            .catch(err => {
                clearTimeout(timeoutId);
                alamat = "Sinyal GPS Terkunci (" + latitude + ", " + longitude + ")";
                updateGpsBoxUI();
            });
        }, function(error) {
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    alamat = "Gagal melacak: Izin akses GPS ditolak pengguna.";
                    break;
                default:
                    alamat = "Gagal melacak: GPS perangkat tidak aktif / lemah.";
            }
            updateGpsBoxUI(true);
        }, { enableHighAccuracy: true, timeout: 10000 });
    } else {
        alamat = "Gagal melacak: Browser tidak mendukung API Geolocation.";
        updateGpsBoxUI(true);
    }
}

function updateGpsBoxUI(isError = false) {
    const box = $('#gps-info');
    if (isError) {
        box.css('border-left-color', '#dc2626').html(`
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill text-danger me-2 fs-5"></i>
                <div>
                    <strong class="text-danger small d-block">Peringatan Keamanan GPS:</strong>
                    <span class="small text-muted">${alamat} Mohon aktifkan GPS perangkat Anda dan beri izin peramban.</span>
                </div>
            </div>
        `);
    } else {
        box.css('border-left-color', '#16a34a').html(`
            <div class="d-flex align-items-start">
                <i class="bi bi-geo-alt-fill text-success me-2 fs-5 mt-1"></i>
                <div>
                    <strong class="text-success small d-block">Koordinat GPS Aktif & Terkunci</strong>
                    <span class="small text-dark d-block"><b>Koordinat:</b> ${latitude}, ${longitude}</span>
                    <span class="small text-muted d-block" style="font-size:0.78rem; line-height:1.2;"><b>Lokasi:</b> ${alamat}</span>
                </div>
            </div>
        `);
    }
}

function aktifkanKamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Browser Anda tidak mendukung fitur kamera.");
        return;
    }
    navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 }, audio: false })
    .then(function(stream) {
        streamLocal = stream;
        video.srcObject = stream;
    })
    .catch(function(err) {
        alert("Gagal memuat kamera: " + err.message);
    });
}

function matikanKamera() {
    if (streamLocal) {
        streamLocal.getTracks().forEach(track => track.stop());
        streamLocal = null;
    }
}

function ambilFoto() {
    if (!streamLocal) { alert("Kamera belum aktif!"); return; }
    
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    
    const ctx = canvas.getContext('2d');
    
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    
    const barHeight = 110;
    ctx.fillStyle = "rgba(0, 0, 0, 0.65)";
    ctx.fillRect(0, canvas.height - barHeight, canvas.width, barHeight);
    
    ctx.strokeStyle = "rgba(255, 255, 255, 0.4)";
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    ctx.moveTo(0, canvas.height - barHeight);
    ctx.lineTo(canvas.width, canvas.height - barHeight);
    ctx.stroke();

    ctx.fillStyle = "#ffffff";
    ctx.font = "bold 13px Arial";
    
    const aksiHeader = "<?= strtoupper(htmlspecialchars($aksi)); ?>";
    ctx.fillText("SIBIMA JATIM - ABSENSI " + aksiHeader, 15, canvas.height - barHeight + 25);
    
    const sekarang = new Date();
    const formatWaktu = sekarang.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + " | " + sekarang.toLocaleTimeString('id-ID') + " WIB";
    
    ctx.font = "11px Arial";
    ctx.fillText("Waktu       : " + formatWaktu, 15, canvas.height - barHeight + 48);
    ctx.fillText("Koordinat  : " + (latitude ? (latitude + ", " + longitude) : "Mencari GPS..."), 15, canvas.height - barHeight + 68);
    
    let alamatPotong = alamat;
    if (alamatPotong.length > 60) {
        alamatPotong = alamatPotong.substring(0, 100) + "...";
    }
    ctx.fillText("Lokasi       : " + alamatPotong, 15, canvas.height - barHeight + 88);
    
    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
    fotoBase64.value = dataUrl;
    
    hasilSnap.src = dataUrl;
    hasilSnap.style.display = 'inline-block';
    $('#label-berhasil-snap').fadeIn();
}

$('#foto_file').on('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(event) {
        const img = new Image();
        img.onload = function() {
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            
            const barHeight = Math.round(img.height * 0.16); 
            const padX = Math.round(img.width * 0.03); 
            const fontSizeHeader = Math.max(14, Math.round(img.height * 0.026)); 
            const fontSizeText = Math.max(11, Math.round(img.height * 0.018)); 
            const lineSpacing = Math.round(img.height * 0.028); 
            
            ctx.fillStyle = "rgba(0, 0, 0, 0.68)";
            ctx.fillRect(0, img.height - barHeight, img.width, barHeight);
            
            ctx.fillStyle = "#ffffff";
            ctx.font = "bold " + fontSizeHeader + "px Arial";
            const aksiHeader = "<?= strtoupper(htmlspecialchars($aksi)); ?>";
            ctx.fillText("SIBIMA JATIM - ABSENSI " + aksiHeader, padX, img.height - barHeight + (lineSpacing * 1.1));
            
            const sekarang = new Date();
            const formatWaktu = sekarang.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + " | " + sekarang.toLocaleTimeString('id-ID') + " WIB";
            
            ctx.font = fontSizeText + "px Arial";
            ctx.fillText("Waktu      : " + formatWaktu, padX, img.height - barHeight + (lineSpacing * 2.2));
            ctx.fillText("Koordinat  : " + (latitude ? (latitude + ", " + longitude) : "Mencari GPS..."), padX, img.height - padX + (lineSpacing * 3.2));
            
            let maxChars = Math.round(img.width / (fontSizeText * 0.58));
            let alamatPotong = alamat;
            if (alamatPotong.length > maxChars) {
                alamatPotong = alamatPotong.substring(0, maxChars - 3) + "...";
            }
            ctx.fillText("Lokasi       : " + alamatPotong, padX, img.height - barHeight + (lineSpacing * 4.2));
            
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            fotoBase64.value = dataUrl;
            
            $('#upload-preview').attr('src', dataUrl).show();
            $('#label-berhasil-upload').fadeIn();
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
});

$('#formAbsen').on('submit', function(e) {
    const isCameraTab = $('#pills-camera-tab').hasClass('active');
    
    if (isCameraTab && $('#foto_base64').val() === "") {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Foto Belum Diambil',
            text: 'Harap klik tombol "Ambil Foto Absen" terlebih dahulu!',
            confirmButtonColor: '#2563eb'
        });
        return false;
    }

    if (!isCameraTab) {
        if ($('#foto_base64').val() === "") {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Berkas Foto',
                text: 'Silakan pilih foto bukti absensi Anda terlebih dahulu.',
                confirmButtonColor: '#2563eb'
            });
            return false;
        } else {
            $('#foto_file').val('');
        }
    }
});

$(document).ready(function(){
    dapatkanLokasi();
    if ($('#pills-camera-tab').length) {
        aktifkanKamera();
    }
});
</script>
<?php endif; ?>

<?php if (isset($_SESSION['swal_icon'])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: '<?= htmlspecialchars($_SESSION['swal_icon']); ?>',
                title: '<?= htmlspecialchars($_SESSION['swal_title']); ?>',
                text: '<?= htmlspecialchars($_SESSION['swal_text']); ?>',
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: { popup: 'shadow-lg border-0' }
            });
        });
    </script>
    <?php 
        unset($_SESSION['swal_icon']);
        unset($_SESSION['swal_title']);
        unset($_SESSION['swal_text']);
    endif; 
    ?>
</body>
</html>
<?php
    session_start();
    include '../config/koneksi.php';
    include '../helper/foto_helper.php';

    if (! isset($_SESSION['id_user']) || $_SESSION['role_id'] != 2) {
    header('Location: ../login.php');
    exit();
    }
    if (! defined('BASE_URL')) {
    define('BASE_URL', '../');
    }

    $id_user         = $_SESSION['id_user'];
    $pembimbing      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pembimbing WHERE user_id = '$id_user'"));
    $id_pembimbing   = $pembimbing['id'] ?? 0;
    $nama_pembimbing = $pembimbing['nama'] ?? ($_SESSION['nama'] ?? 'Dosen Pembimbing');

    function getPredikat($nilai)
    {
    if ($nilai === null) {
        return ['-', '-', 'secondary'];
    }

    $nilai = (float) $nilai;
    if ($nilai >= 90) {
        return ['A', 'Sangat Baik (Istimewa)', 'success'];
    }

    if ($nilai >= 85) {
        return ['A-', 'Sangat Baik', 'success'];
    }

    if ($nilai >= 80) {
        return ['B+', 'Baik Sekali', 'primary'];
    }

    if ($nilai >= 75) {
        return ['B', 'Baik', 'primary'];
    }

    if ($nilai >= 70) {
        return ['B-', 'Cukup Baik', 'warning'];
    }

    if ($nilai >= 65) {
        return ['C', 'Cukup (Batas KKM)', 'warning'];
    }

    return ['D/E', 'Kurang / Gagal', 'danger'];
    }

    // ==========================================================
    // FILTER
    // ==========================================================
    $cari     = isset($_GET['cari']) ? trim($_GET['cari']) : '';
    $status_f = isset($_GET['status']) ? $_GET['status'] : 'semua';

    $where = " WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima' ";
    if ($cari !== '') {
    $c      = mysqli_real_escape_string($conn, $cari);
    $where .= " AND (pl.nama LIKE '%$c%' OR i.nama_instansi LIKE '%$c%') ";
    }

    $sql = "
    SELECT pl.id, pl.nama, pl.nim, pl.foto,
           i.nama_instansi, j.nama_jurusan, uk.unit_kerja,
           pd.tanggal_mulai, pd.tanggal_selesai,
           pn.nilai_akhir, pn.catatan,
           s.id as sertifikat_id, s.nomor_sertifikat, s.tanggal_terbit, s.file_pdf
    FROM pendaftaran p
    JOIN pelajar pl ON p.pelajar_id = pl.id
    LEFT JOIN instansi i ON pl.instansi_id = i.id
    LEFT JOIN jurusan j ON pl.jurusan_id = j.id
    LEFT JOIN unit_kerja uk ON p.unit_kerja_id = uk.id
    LEFT JOIN periode_pkl pd ON p.periode_id = pd.id
    LEFT JOIN penilaian pn ON pl.id = pn.pelajar_id AND pn.pembimbing_id = '$id_pembimbing'
    LEFT JOIN sertifikat s ON s.pelajar_id = pl.id
    $where
    ORDER BY pl.nama ASC
";
    $res_all = mysqli_query($conn, $sql);
    if (! $res_all) {die('MYSQL ERROR: ' . mysqli_error($conn));}

    $rows   = [];
    $total  = 0;
    $terbit = 0;
    $belum  = 0;
    while ($r = mysqli_fetch_assoc($res_all)) {
    $total++;
    $r['status_sertifikat'] = ! empty($r['sertifikat_id']) ? 'terbit' : 'belum';
    if ($r['status_sertifikat'] === 'terbit') {
        $terbit++;
    } else {
        $belum++;
    }

    $rows[] = $r;
    }

    if ($status_f !== 'semua') {
    $rows = array_values(array_filter($rows, fn($r) => $r['status_sertifikat'] === $status_f));
    }
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat - SIBIMA JATIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
    html,
    body {
        width: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        background: #f4f6f9;
        overflow-x: hidden;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    .sidebar {
        transition: all 0.3s ease;
    }

    body.sidebar-hidden .sidebar {
        transform: translateX(-260px);
    }

    body.sidebar-hidden .main-content {
        margin-left: 0;
        width: 100%;
    }

    body.sidebar-hidden .footer {
        margin-left: 0;
    }

    @media(max-width:768px) {
        .sidebar {
            transform: translateX(-260px);
        }

        body.sidebar-hidden .sidebar {
            transform: translateX(0);
        }
    }

    .main-content {
        margin-left: 260px;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .content-body {
        flex: 1;
    }

    @media(max-width:768px) {
        .main-content {
            margin-left: 0;
        }
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
        flex-wrap: nowrap !important;
        gap: 8px;
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
    }

    @media(max-width:768px) {
        .footer {
            margin-left: 0;
        }
    }

    .page-header-bar {
        background: linear-gradient(135deg, #0f172a, #1e40af);
        border-radius: 20px;
        padding: 1.8rem;
        color: #fff;
        margin-bottom: 1.8rem;
        box-shadow: 0 10px 25px rgba(30, 64, 175, .20);
    }

    .page-header-bar h3 {
        color: #fff;
    }

    .page-header-bar p {
        color: rgba(255, 255, 255, 0.85);
    }

    .section-card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, .06);
    }

    .stat-card {
        border: none;
        border-radius: 18px;
        padding: 1.25rem 1.4rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        display: flex;
        align-items: center;
        gap: 1rem;
        background: #fff;
        height: 100%;
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .stat-label {
        font-size: .8rem;
        color: #6b7280;
    }

    .table-modern thead th {
        background-color: #1e3a8a;
        color: #fff;
        border-bottom: none;
    }

    /* REVISI: Perataan kiri pada Thead dan Tbody */
    .table-modern thead th.text-left-col {
        text-align: left !important;
    }
    
    .table-modern tbody td.text-left-col {
        text-align: left !important;
    }

    .table-modern th,
    .table-modern td {
        vertical-align: middle;
        padding: 1rem;
    }

    .table-modern tbody tr:hover {
        background: #f8fafc;
    }

    .avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        overflow: hidden;
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: .7rem;
        font-size: .85rem;
        flex-shrink: 0;
    }

    .avatar-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .badge-status {
        padding: .4rem .85rem;
        border-radius: 30px;
        font-size: .76rem;
        font-weight: 500;
    }

    .badge-predikat {
        padding: .35rem .6rem;
        border-radius: 8px;
        font-size: .78rem;
        font-weight: 700;
    }

    /* ==== KARTU SERTIFIKAT (depan/belakang) ==== */
    .sertifikat-card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        position: sticky;
        top: 1rem;
        overflow: hidden;
    }

    .sertifikat-tabs {
        display: flex;
        border-bottom: 1px solid #eef2f6;
    }

    .sertifikat-tabs button {
        flex: 1;
        border: none;
        background: none;
        padding: .8rem;
        font-weight: 600;
        font-size: .85rem;
        color: #6b7280;
        cursor: pointer;
    }

    .sertifikat-tabs button.active {
        color: #1e3a8a;
        border-bottom: 2px solid #1e3a8a;
    }

    .sertifikat-face {
        background: linear-gradient(135deg, #0f172a, #1e3a8a);
        color: #fff;
        border-radius: 14px;
        padding: 1.6rem;
        text-align: center;
        min-height: 280px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border: 3px solid #fbbf24;
    }

    .sertifikat-face .kop {
        font-size: .68rem;
        letter-spacing: 2px;
        opacity: .8;
        margin-bottom: .6rem;
    }

    .sertifikat-face .foto-cert {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fbbf24;
        margin-bottom: .8rem;
    }

    .sertifikat-face .nama-cert {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: .2rem;
    }

    .sertifikat-face .sub-cert {
        font-size: .78rem;
        opacity: .85;
    }

    .sertifikat-face .nomor-cert {
        margin-top: 1rem;
        font-size: .7rem;
        background: rgba(255, 255, 255, .15);
        padding: .3rem .8rem;
        border-radius: 20px;
    }

    .detail-placeholder {
        text-align: center;
        color: #9ca3af;
        padding: 3rem 1rem;
    }

    .detail-row {
        display: flex;
        gap: .6rem;
        font-size: .85rem;
        padding: .45rem 0;
        border-bottom: 1px solid #f1f3f6;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        width: 45%;
        color: #6b7280;
        flex-shrink: 0;
    }

    .detail-value {
        font-weight: 500;
        color: #1f2937;
        text-align: right;
        flex: 1;
    }

    /* REVISI: Action button styling (menghilangkan text) */
    .btn-lihat-sertifikat {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    </style>
</head>

<body>
    <div class="wrapper">
        <?php include '../include/sidebar_pembimbing.php'; ?>
        <div class="main-content">
            <?php include '../include/navbar_pembimbing.php'; ?>
            <div class="content-body container-fluid px-4">

                <div class="page-header-bar">
                    <h3 class="fw-bold m-0"><i class="bi bi-award-fill me-2"></i>Sertifikat Siswa</h3>
                    <p class="mb-0">Pantau status penerbitan sertifikat siswa bimbingan Anda</p>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i>
                    Sertifikat diterbitkan &amp; dicetak oleh <b>Admin</b> menggunakan template resmi. Halaman ini hanya
                    untuk <b>memantau status</b> dan melihat pratinjau informasi yang tercantum di sertifikat siswa
                    bimbingan Anda.
                </div>

                <!-- KARTU STATISTIK -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-4">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#e8f0fe;color:#1e3a8a;"><i
                                    class="bi bi-people-fill"></i></div>
                            <div>
                                <div class="stat-value"><?php echo $total ?></div>
                                <div class="stat-label">Total Siswa Bimbingan</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#e6f7ee;color:#16a34a;"><i
                                    class="bi bi-patch-check-fill"></i></div>
                            <div>
                                <div class="stat-value"><?php echo $terbit ?></div>
                                <div class="stat-label">Sertifikat Terbit</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#fdeaea;color:#dc2626;"><i
                                    class="bi bi-hourglass-split"></i></div>
                            <div>
                                <div class="stat-value"><?php echo $belum ?></div>
                                <div class="stat-label">Belum Terbit</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <form method="GET" class="card section-card mb-4">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i
                                                    class="bi bi-search text-muted"></i></span>
                                            <input type="text" name="cari" class="form-control"
                                                placeholder="Cari nama siswa/instansi..."
                                                value="<?php echo htmlspecialchars($cari) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="status" class="form-select">
                                            <option value="semua" <?php echo $status_f == 'semua' ? 'selected' : '' ?>>
                                                Semua
                                                Status
                                            </option>
                                            <option value="terbit"
                                                <?php echo $status_f == 'terbit' ? 'selected' : '' ?>>Sudah
                                                Terbit
                                            </option>
                                            <option value="belum" <?php echo $status_f == 'belum' ? 'selected' : '' ?>>
                                                Belum
                                                Terbit
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100"><i
                                                class="bi bi-funnel"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="card section-card mb-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-modern align-middle mb-0">
                                        <thead>
                                            <!-- REVISI: Pembagian lebar kolom (width) disesuaikan agar proporsional -->
                                            <tr>
                                                <th class="ps-4" width="5%">No</th>
                                                <th class="text-left-col" width="30%">Nama Siswa</th>
                                                <th class="text-left-col" width="30%">Instansi</th>
                                                <th class="text-center" width="10%">Nilai Akhir</th>
                                                <th class="text-center" width="15%">Status Sertifikat</th>
                                                <th class="text-center" width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($rows) > 0): $no = 1;foreach ($rows as $r):
                                                        $inisial                                                           = strtoupper(substr($r['nama'], 0, 1));
                                                        $foto_url                                                          = getFotoProfilUrl($r['foto']);
                                                        [$kode_p, $label_p, $warna_p]                                      = getPredikat($r['nilai_akhir']);
                                            ?>
                                            <tr>
                                                <td class="ps-4"><?php echo $no++ ?></td>
                                                <td class="fw-bold text-dark text-left-col">
                                                    <span class="avatar-circle"><?php if ($foto_url): ?><img
                                                            src="<?php echo $foto_url ?>"
                                                            alt=""><?php else: echo $inisial;endif; ?></span>
                                                    <!-- REVISI: Penambahan max-width dan class text-truncate untuk memotong teks panjang -->
                                                    <span class="d-inline-block text-truncate align-middle" style="max-width: 150px;" title="<?php echo htmlspecialchars($r['nama']) ?>"><?php echo htmlspecialchars($r['nama']) ?></span>
                                                </td>
                                                <!-- REVISI: Penambahan max-width dan class text-truncate untuk memotong teks panjang -->
                                                <td style="max-width: 180px;" class="text-left-col text-truncate" title="<?php echo htmlspecialchars($r['nama_instansi'] ?? '-') ?>">
                                                    <?php echo htmlspecialchars($r['nama_instansi'] ?? '-') ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($r['nilai_akhir'] !== null): ?>
                                                    <span class="d-block fw-bold"><?php echo number_format($r['nilai_akhir'], 2) ?></span>
                                                    <span class="badge-predikat bg-<?php echo $warna_p ?> text-white mt-1 d-inline-block"><?php echo $kode_p ?></span>
                                                    <?php else: ?><span class="text-muted small">-</span><?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($r['status_sertifikat'] === 'terbit'): ?>
                                                    <span class="badge-status bg-success text-white">Terbit</span>
                                                    <?php else: ?>
                                                    <span class="badge-status bg-secondary text-white">Belum
                                                        Terbit</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <!-- REVISI: Menambahkan Tooltip -->
                                                    <button type="button"
                                                        class="btn btn-outline-primary rounded-circle btn-lihat-sertifikat"
                                                        data-bs-toggle="tooltip" 
                                                        title="Lihat Pratinjau"
                                                        data-nama="<?php echo htmlspecialchars($r['nama'], ENT_QUOTES) ?>"
                                                        data-nim="<?php echo htmlspecialchars($r['nim'] ?? '-', ENT_QUOTES) ?>"
                                                        data-instansi="<?php echo htmlspecialchars($r['nama_instansi'] ?? '-', ENT_QUOTES) ?>"
                                                        data-jurusan="<?php echo htmlspecialchars($r['nama_jurusan'] ?? '-', ENT_QUOTES) ?>"
                                                        data-unitkerja="<?php echo htmlspecialchars($r['unit_kerja'] ?? '-', ENT_QUOTES) ?>"
                                                        data-periode="<?php echo $r['tanggal_mulai'] ? date('d M Y', strtotime($r['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($r['tanggal_selesai'])) : '-' ?>"
                                                        data-foto="<?php echo htmlspecialchars($foto_url, ENT_QUOTES) ?>"
                                                        data-inisial="<?php echo $inisial ?>"
                                                        data-nilai="<?php echo $r['nilai_akhir'] ?? '' ?>"
                                                        data-predikat="<?php echo $kode_p ?>"
                                                        data-predikat-label="<?php echo htmlspecialchars($label_p, ENT_QUOTES) ?>"
                                                        data-catatan="<?php echo htmlspecialchars($r['catatan'] ?? '-', ENT_QUOTES) ?>"
                                                        data-nomor="<?php echo htmlspecialchars($r['nomor_sertifikat'] ?? '', ENT_QUOTES) ?>"
                                                        data-tanggal-terbit="<?php echo $r['tanggal_terbit'] ? date('d M Y', strtotime($r['tanggal_terbit'])) : '' ?>"
                                                        data-file-pdf="<?php echo ! empty($r['file_pdf']) ? BASE_URL . 'assets/upload/sertifikat/' . htmlspecialchars($r['file_pdf'], ENT_QUOTES) : '' ?>">
                                                        <i class="bi bi-eye"></i></button>
                                                </td>
                                            </tr>
                                            <?php endforeach;else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">Belum ada siswa
                                                    aktif bimbingan Anda.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL SERTIFIKAT (DEPAN / BELAKANG) -->
                    <div class="col-lg-4">
                        <div class="card sertifikat-card">
                            <div id="dtlPlaceholder" class="detail-placeholder">
                                <i class="bi bi-award fs-1 d-block mb-2"></i>
                                Klik ikon <i class="bi bi-eye"></i> pada salah satu siswa untuk melihat pratinjau
                                sertifikatnya di sini.
                            </div>

                            <div id="dtlContent" style="display:none;">
                                <div class="sertifikat-tabs">
                                    <button type="button" class="tab-btn active" data-tab="depan">Halaman Depan</button>
                                    <button type="button" class="tab-btn" data-tab="belakang">Halaman Belakang</button>
                                </div>
                                <div class="card-body">

                                    <!-- ==== HALAMAN DEPAN: PROFIL SISWA ==== -->
                                    <div id="faceDepan" class="tab-content">
                                        <div class="sertifikat-face">
                                            <div class="kop">SERTIFIKAT PRAKTIK KERJA LAPANGAN</div>
                                            <img id="dtlFotoCert" src="" class="foto-cert" alt="" style="display:none;">
                                            <div id="dtlAvatarCert" class="avatar-circle"
                                                style="width:70px;height:70px;font-size:1.4rem;margin:0 0 .8rem 0;">
                                            </div>
                                            <div class="nama-cert" id="dtlNamaCert">-</div>
                                            <div class="sub-cert" id="dtlNimCert">-</div>
                                            <div class="sub-cert" id="dtlInstansiCert">-</div>
                                            <div class="sub-cert" id="dtlJurusanCert">-</div>
                                            <div class="nomor-cert" id="dtlNomorCert">Nomor: -</div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="detail-row">
                                                <div class="detail-label">Unit Kerja</div>
                                                <div class="detail-value" id="dtlUnitKerjaCert">-</div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Periode PKL</div>
                                                <div class="detail-value" id="dtlPeriodeCert">-</div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Tanggal Terbit</div>
                                                <div class="detail-value" id="dtlTanggalTerbitCert">-</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ==== HALAMAN BELAKANG: PENILAIAN ==== -->
                                    <div id="faceBelakang" class="tab-content" style="display:none;">
                                        <div class="sertifikat-face" style="min-height:auto;padding:1.4rem;">
                                            <div class="kop">HASIL PENILAIAN AKHIR</div>
                                            <div class="nama-cert" style="font-size:2.2rem;" id="dtlNilaiCert">-</div>
                                            <div class="sub-cert" id="dtlPredikatCert">-</div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label small text-muted mb-1">Catatan &amp; Saran
                                                Pembimbing</label>
                                            <div class="p-2 small"
                                                style="background:#f8fafc;border-radius:10px;min-height:60px;"
                                                id="dtlCatatanCert">-</div>
                                        </div>
                                        <div class="mt-3 text-center">
                                            <div id="dtlBelumNilai" class="text-muted small">Nilai akhir belum diinput.
                                                Lengkapi di halaman <a href="penilaian_akhir.php">Penilaian Akhir</a>.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 text-center">
                                        <a id="dtlBtnDownloadPdf" href="#" target="_blank"
                                            class="btn btn-primary rounded-pill px-4" style="display:none;">
                                            <i class="bi bi-download me-1"></i> Unduh Sertifikat (PDF)
                                        </a>
                                        <div id="dtlBelumTerbit" class="text-muted small" style="display:none;">
                                            <i class="bi bi-hourglass-split me-1"></i> Sertifikat belum diterbitkan oleh
                                            Admin.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inisialisasi JS untuk Tooltip dan interaksi panel Sertifikat -->
            <script>
            // REVISI: Inisialisasi Tooltip Bootstrap
            document.addEventListener('DOMContentLoaded', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });

            document.querySelectorAll('.btn-lihat-sertifikat').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('dtlPlaceholder').style.display = 'none';
                    document.getElementById('dtlContent').style.display = 'block';

                    document.getElementById('dtlNamaCert').textContent = this.dataset.nama;
                    document.getElementById('dtlNimCert').textContent = 'NIM/NISN: ' + this.dataset.nim;
                    document.getElementById('dtlInstansiCert').textContent = this.dataset.instansi;
                    document.getElementById('dtlJurusanCert').textContent = this.dataset.jurusan;
                    document.getElementById('dtlUnitKerjaCert').textContent = this.dataset.unitkerja;
                    document.getElementById('dtlPeriodeCert').textContent = this.dataset.periode;

                    const foto = this.dataset.foto;
                    const fotoImg = document.getElementById('dtlFotoCert');
                    const avatarFallback = document.getElementById('dtlAvatarCert');
                    if (foto) {
                        fotoImg.src = foto;
                        fotoImg.style.display = 'inline-block';
                        avatarFallback.style.display = 'none';
                    } else {
                        fotoImg.style.display = 'none';
                        avatarFallback.style.display = 'flex';
                        avatarFallback.textContent = this.dataset.inisial;
                    }

                    const nomor = this.dataset.nomor;
                    const tglTerbit = this.dataset.tanggalTerbit;
                    const filePdf = this.dataset.filePdf;

                    document.getElementById('dtlNomorCert').textContent = 'Nomor: ' + (nomor ||
                        'Belum diterbitkan');
                    document.getElementById('dtlTanggalTerbitCert').textContent = tglTerbit || '-';

                    const btnPdf = document.getElementById('dtlBtnDownloadPdf');
                    const belumTerbit = document.getElementById('dtlBelumTerbit');
                    if (filePdf) {
                        btnPdf.href = filePdf;
                        btnPdf.style.display = 'inline-block';
                        belumTerbit.style.display = 'none';
                    } else {
                        btnPdf.style.display = 'none';
                        belumTerbit.style.display = 'block';
                    }

                    // Halaman belakang: penilaian
                    const nilai = this.dataset.nilai;
                    const belumNilai = document.getElementById('dtlBelumNilai');
                    if (nilai) {
                        document.getElementById('dtlNilaiCert').textContent = parseFloat(nilai).toFixed(
                            2);
                        document.getElementById('dtlPredikatCert').textContent = this.dataset.predikat +
                            ' - ' + this.dataset.predikatLabel;
                        document.getElementById('dtlCatatanCert').textContent = this.dataset.catatan ||
                            '-';
                        belumNilai.style.display = 'none';
                    } else {
                        document.getElementById('dtlNilaiCert').textContent = '-';
                        document.getElementById('dtlPredikatCert').textContent = '-';
                        document.getElementById('dtlCatatanCert').textContent = '-';
                        belumNilai.style.display = 'block';
                    }
                });
            });

            document.querySelectorAll('.tab-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    document.getElementById('faceDepan').style.display = this.dataset.tab === 'depan' ?
                        'block' : 'none';
                    document.getElementById('faceBelakang').style.display = this.dataset.tab ===
                        'belakang' ? 'block' : 'none';
                });
            });
            </script>

            <?php include '../include/footer_pembimbing.php'; ?>
        </div>
    </div>
</body>

</html>
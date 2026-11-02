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
    // FILTER & QUERY
    // ==========================================================
    $cari           = isset($_GET['cari']) ? trim($_GET['cari']) : '';
    $pelajar_filter = isset($_GET['pelajar']) ? $_GET['pelajar'] : '';
    $status_f       = isset($_GET['status']) ? $_GET['status'] : 'semua';

    $where = " WHERE p.pembimbing_id = '$id_pembimbing' AND p.status = 'diterima' ";
    if ($cari !== '') {
    $c      = mysqli_real_escape_string($conn, $cari);
    $where .= " AND (pl.nama LIKE '%$c%' OR i.nama_instansi LIKE '%$c%') ";
    }
    if ($pelajar_filter !== '') {
    $where .= " AND pl.id = '" . mysqli_real_escape_string($conn, $pelajar_filter) . "' ";
    }

    $sql_pelajar = "
    SELECT pl.id AS id_pelajar, pl.nama, pl.foto, i.nama_instansi, b.unit_kerja,
           pn.id AS id_penilaian, pn.disiplin, pn.komunikasi, pn.kerjasama, pn.tanggung_jawab,
           pn.inisiatif, pn.absensi, pn.logbook, pn.tugas_akhir, pn.nilai_akhir, pn.catatan,
           pd.tanggal_mulai, pd.tanggal_selesai,
           la.file_laporan, la.link_gdrive, la.judul AS judul_ta,
           (SELECT COUNT(*) FROM absensi a WHERE a.pelajar_id = pl.id) AS total_hari_absen,
           (SELECT COUNT(*) FROM absensi a WHERE a.pelajar_id = pl.id AND a.status IN ('hadir', 'izin', 'sakit')) AS total_masuk_absen,
           (SELECT COUNT(*) FROM logbook lb WHERE lb.pelajar_id = pl.id) AS total_logbook_diisi
    FROM pendaftaran p
    JOIN pelajar pl ON p.pelajar_id = pl.id
    LEFT JOIN instansi i ON pl.instansi_id = i.id
    LEFT JOIN unit_kerja b ON p.unit_kerja_id = b.id
    LEFT JOIN periode_pkl pd ON p.periode_id = pd.id
    LEFT JOIN penilaian pn ON pl.id = pn.pelajar_id
    LEFT JOIN laporan_akhir la ON pl.id = la.pelajar_id
    $where
    ORDER BY pl.nama ASC
";
    $res_all = mysqli_query($conn, $sql_pelajar);
    if (! $res_all) {die('MYSQL ERROR: ' . mysqli_error($conn));}

    $rows          = [];
    $total_pelajar = 0;
    $selesai       = 0;
    $belum         = 0;
    $sum_nilai     = 0;
    $count_nilai   = 0;
    $distribusi    = ['A' => 0, 'A-' => 0, 'B+' => 0, 'B' => 0, 'B-' => 0, 'C' => 0, 'D/E' => 0];

    while ($r = mysqli_fetch_assoc($res_all)) {
    $total_pelajar++;
    $total_hari   = (int) $r['total_hari_absen'];
    $total_masuk  = (int) $r['total_masuk_absen'];
    $persen_hadir = ($total_hari > 0) ? ($total_masuk / $total_hari) * 100 : 0;

    $target_logbook = 30;
    $total_log      = (int) $r['total_logbook_diisi'];
    $persen_logbook = ($target_logbook > 0) ? min(($total_log / $target_logbook) * 100, 100) : 0;

    if (! isset($r['absensi']) || $r['absensi'] == 0) {
        $r['absensi'] = round($persen_hadir, 2);
    }
    if (! isset($r['logbook']) || $r['logbook'] == 0) {
        $r['logbook'] = round($persen_logbook, 2);
    }

    if (isset($r['nilai_akhir']) && $r['nilai_akhir'] !== null) {
        $status_pn  = 'selesai';
        $selesai++;
        $sum_nilai += $r['nilai_akhir'];
        $count_nilai++;
        [$kode]  = getPredikat($r['nilai_akhir']);
        if (isset($distribusi[$kode])) {
            $distribusi[$kode]++;
        }
    } else {
        $status_pn = 'belum';
        $belum++;
    }
    $r['status_pn'] = $status_pn;
    $rows[]         = $r;
    }
    $rata_rata = $count_nilai > 0 ? round($sum_nilai / $count_nilai, 2) : 0;

    if ($status_f !== 'semua') {
    $rows = array_values(array_filter($rows, fn($r) => $r['status_pn'] === $status_f));
    }

    $status_label_pn = ['selesai' => 'Selesai Dinilai', 'proses' => 'Dalam Proses', 'belum' => 'Belum Dinilai'];
    // REVISI: Menggunakan class badge-soft agar seragam dengan UI lainnya
    $status_badge_pn = ['selesai' => 'badge-soft-success', 'proses' => 'badge-soft-warning', 'belum' => 'badge-soft-danger'];

    $list_pelajar_dropdown = mysqli_query($conn, "
    SELECT pl.id, pl.nama FROM pendaftaran p JOIN pelajar pl ON p.pelajar_id = pl.id
    WHERE p.pembimbing_id='$id_pembimbing' AND p.status='diterima' ORDER BY pl.nama ASC
");

    // ==========================================================
    // PROSES SIMPAN NILAI (POST)
    // ==========================================================
    if (isset($_POST['submit_nilai'])) {
    $pelajar_id = isset($_POST['pelajar_id']) ? trim($_POST['pelajar_id']) : '';

    if (empty($pelajar_id)) {
        echo "<script>alert('Pilih siswa terlebih dahulu!'); window.location='penilaian_akhir.php';</script>";
        exit();
    }

    $disiplin       = isset($_POST['disiplin']) && $_POST['disiplin'] !== '' ? (float) $_POST['disiplin'] : 0;
    $komunikasi     = isset($_POST['komunikasi']) && $_POST['komunikasi'] !== '' ? (float) $_POST['komunikasi'] : 0;
    $kerjasama      = isset($_POST['kerjasama']) && $_POST['kerjasama'] !== '' ? (float) $_POST['kerjasama'] : 0;
    $tanggung_jawab = isset($_POST['tanggung_jawab']) && $_POST['tanggung_jawab'] !== '' ? (float) $_POST['tanggung_jawab'] : 0;
    $inisiatif      = isset($_POST['inisiatif']) && $_POST['inisiatif'] !== '' ? (float) $_POST['inisiatif'] : 0;

    $absensi     = isset($_POST['absensi']) && $_POST['absensi'] !== '' ? (float) $_POST['absensi'] : 0;
    $logbook     = isset($_POST['logbook']) && $_POST['logbook'] !== '' ? (float) $_POST['logbook'] : 0;
    $tugas_akhir = isset($_POST['tugas_akhir']) && $_POST['tugas_akhir'] !== '' ? (float) $_POST['tugas_akhir'] : 0;
    $catatan     = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');

    $rata_pembimbing    = ($disiplin + $komunikasi + $kerjasama + $tanggung_jawab + $inisiatif) / 5;
    $nilai_akhir_hitung = ($rata_pembimbing * 0.45) + ($absensi * 0.15) + ($logbook * 0.15) + ($tugas_akhir * 0.25);

    $cek = mysqli_query($conn, "SELECT id FROM penilaian WHERE pelajar_id = '$pelajar_id'");

    if (mysqli_num_rows($cek) > 0) {
        $update_query = "UPDATE penilaian SET
            pembimbing_id = '$id_pembimbing',
            disiplin = '$disiplin',
            komunikasi = '$komunikasi',
            kerjasama = '$kerjasama',
            tanggung_jawab = '$tanggung_jawab',
            inisiatif = '$inisiatif',
            absensi = '$absensi',
            logbook = '$logbook',
            tugas_akhir = '$tugas_akhir',
            nilai_akhir = '$nilai_akhir_hitung',
            catatan = '$catatan',
            updated_at = NOW()
            WHERE pelajar_id = '$pelajar_id'";
        $exec = mysqli_query($conn, $update_query);
    } else {
        $insert_query = "INSERT INTO penilaian
            (pelajar_id, pembimbing_id, disiplin, komunikasi, kerjasama, tanggung_jawab, inisiatif, absensi, logbook, tugas_akhir, nilai_akhir, catatan, created_at, updated_at)
            VALUES
            ('$pelajar_id', '$id_pembimbing', '$disiplin', '$komunikasi', '$kerjasama', '$tanggung_jawab', '$inisiatif', '$absensi', '$logbook', '$tugas_akhir', '$nilai_akhir_hitung', '$catatan', NOW(), NOW())";
        $exec = mysqli_query($conn, $insert_query);
    }

    if (! $exec) {
        die("MySQL Error: " . mysqli_error($conn));
    }

    mysqli_query($conn, "UPDATE laporan_akhir SET status = 'disetujui' WHERE pelajar_id = '$pelajar_id'");

    header('Location: penilaian_akhir.php?pesan=sukses');
    exit();
    }
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Akhir - SIBIMA JATIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
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

    .stat-sub {
        font-size: .72rem;
        color: #9ca3af;
    }

    .table-modern thead th {
        background-color: #1e3a8a;
        color: #fff;
        border-bottom: none;
    }

    /* REVISI: Perataan kiri untuk th dan td */
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

    .table-modern tbody tr.row-active {
        background: #eff6ff;
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

    /* REVISI: Tambahan class badge-soft agar seragam dengan halaman lain */
    .badge-soft {
        padding: 0.45rem 0.9rem;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        white-space: nowrap;
    }
    .badge-soft-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-soft-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-soft-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .badge-predikat {
        padding: .35rem .6rem;
        border-radius: 8px;
        font-size: .78rem;
        font-weight: 700;
    }

    .side-panel-card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        position: sticky;
        top: 1rem;
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

    .detail-placeholder {
        text-align: center;
        color: #9ca3af;
        padding: 3rem 1rem;
    }

    .nilai-akhir-box {
        background: #f8fafc;
        border-radius: 14px;
        padding: 1rem;
        text-align: center;
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
                    <h3 class="fw-bold m-0"><i class="bi bi-graph-up-arrow me-2"></i>Penilaian Akhir Siswa</h3>
                    <p class="mb-0">Lakukan penilaian akhir terhadap siswa bimbingan Anda</p>
                </div>
                <?php if (isset($_GET['pesan'])): ?>
                <div
                    class="alert alert-<?php echo $_GET['pesan'] == 'sukses' ? 'success' : 'danger' ?> alert-dismissible fade show">
                    <?php echo $_GET['pesan'] == 'sukses' ? 'Nilai berhasil disimpan.' : 'Gagal menyimpan nilai.' ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#e8f0fe;color:#1e3a8a;"><i
                                    class="bi bi-people-fill"></i></div>
                            <div>
                                <div class="stat-value"><?php echo $total_pelajar ?></div>
                                <div class="stat-label">Siswa Aktif Dibimbing</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#e6f7ee;color:#16a34a;"><i
                                    class="bi bi-clipboard-check-fill"></i></div>
                            <div>
                                <div class="stat-value"><?php echo $selesai ?></div>
                                <div class="stat-label">Selesai Dinilai</div>
                                <div class="stat-sub">
                                    <?php echo $total_pelajar > 0 ? round($selesai / $total_pelajar * 100, 2) : 0 ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#fdeaea;color:#dc2626;"><i
                                    class="bi bi-exclamation-circle-fill"></i></div>
                            <div>
                                <div class="stat-value"><?php echo $belum ?></div>
                                <div class="stat-label">Belum Dinilai</div>
                                <div class="stat-sub">
                                    <?php echo $total_pelajar > 0 ? round($belum / $total_pelajar * 100, 2) : 0 ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#f1edfe;color:#7c3aed;"><i
                                    class="bi bi-star-fill"></i></div>
                            <div>
                                <div class="stat-value"><?php echo number_format($rata_rata, 2) ?></div>
                                <div class="stat-label">Rata-rata Nilai Keseluruhan</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-7">
                        <form method="GET" class="card section-card mb-4">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <div class="input-group"><span class="input-group-text bg-white"><i
                                                    class="bi bi-search text-muted"></i></span><input type="text"
                                                name="cari" class="form-control"
                                                placeholder="Cari siswa atau instansi..."
                                                value="<?php echo htmlspecialchars($cari) ?>"></div>
                                    </div>
                                    <div class="col-md-4"><select name="pelajar" class="form-select">
                                            <option value="">Semua Siswa</option>
                                            <?php if ($list_pelajar_dropdown): while ($m = mysqli_fetch_assoc($list_pelajar_dropdown)): ?>
                                            <option value="<?php echo $m['id'] ?>"
                                                <?php echo $pelajar_filter == $m['id'] ? 'selected' : '' ?>>
                                                <?php echo htmlspecialchars($m['nama']) ?></option>
                                            <?php endwhile;endif; ?>
                                        </select></div>
                                    <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i
                                                class="bi bi-funnel me-1"></i>Filter</button></div>
                                </div>
                            </div>
                        </form>
                        <div class="card section-card mb-4">
                            <div class="card-body p-0">
                                <div class="p-4 pb-0">
                                    <h5 class="fw-bold mb-0">Daftar Siswa Bimbingan</h5>
                                </div>
                                <div class="table-responsive mt-3">
                                    <table class="table table-modern align-middle mb-0">
                                        <thead>
                                            <!-- REVISI: Pembagian lebar kolom (width) disesuaikan agar proporsional -->
                                            <tr>
                                                <th class="ps-4" width="5%">No</th>
                                                <th class="text-left-col" width="20%">Nama Siswa</th>
                                                <th class="text-left-col" width="25%">Instansi / Sekolah</th>
                                                <th class="text-left-col" width="15%">Unit Kerja</th>
                                                <th width="12%">Tugas Akhir</th>
                                                <th class="text-center" width="13%">Status</th>
                                                <th class="text-center" width="5%">Nilai Akhir</th>
                                                <th class="text-center" width="5%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($rows) > 0): $no = 1;foreach ($rows as $r):
                                                        $inisial                                                         = strtoupper(substr($r['nama'], 0, 1));
                                                        $foto_url                                                        = getFotoProfilUrl($r['foto']);
                                                        $nilai_akhir_val                                                 = $r['nilai_akhir'] ?? null;
                                                        [$kode_p, $label_p, $warna_p]                                    = getPredikat($nilai_akhir_val);
                                            ?>
                                            <tr id="row-pelajar-<?php echo $r['id_pelajar'] ?>">
                                                <td class="ps-4"><?php echo $no++ ?></td>
                                                <td class="fw-bold text-dark text-left-col"><span
                                                        class="avatar-circle"><?php if ($foto_url): ?><img
                                                            src="<?php echo $foto_url ?>"
                                                            alt=""><?php else: echo $inisial;endif; ?></span>
                                                    <!-- REVISI: Penambahan max-width dan class text-truncate untuk memotong teks panjang -->
                                                    <span class="d-inline-block text-truncate align-middle" style="max-width: 120px;" title="<?php echo htmlspecialchars($r['nama']) ?>"><?php echo htmlspecialchars($r['nama']) ?></span>
                                                </td>
                                                <!-- REVISI: Penambahan max-width dan class text-truncate untuk memotong teks panjang -->
                                                <td style="max-width: 150px;" class="text-left-col text-truncate" title="<?php echo htmlspecialchars($r['nama_instansi'] ?? '-') ?>">
                                                    <?php echo htmlspecialchars($r['nama_instansi'] ?? '-') ?>
                                                </td>
                                                <td style="max-width: 120px;" class="text-left-col text-truncate" title="<?php echo htmlspecialchars($r['unit_kerja'] ?? '-') ?>">
                                                    <?php echo htmlspecialchars($r['unit_kerja'] ?? '-') ?>
                                                </td>
                                                <td>
                                                <?php if (!empty($r['file_laporan']) || !empty($r['link_gdrive'])): ?>
                                                    <div class="d-flex flex-column gap-1">
                                                        <?php if (!empty($r['file_laporan'])): ?>
                                                            <a href="../uploads/laporan_akhir/<?= $r['file_laporan'] ?>" download class="btn btn-xs btn-outline-primary rounded-pill py-0 px-2 small" style="font-size:0.75rem;">
                                                                <i class="bi bi-file-earmark-zip me-1"></i>ZIP
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (!empty($r['link_gdrive'])): ?>
                                                            <a href="<?= htmlspecialchars($r['link_gdrive']) ?>" target="_blank" class="btn btn-xs btn-outline-success rounded-pill py-0 px-2 small" style="font-size:0.75rem;">
                                                                <i class="bi bi-google me-1"></i>GDrive
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small fst-italic">Belum upload</span>
                                                <?php endif; ?>
                                                </td>
                                                
                                                <!-- REVISI: Memanggil class badge-soft -->
                                                <td class="text-center">
                                                    <span class="badge-soft <?php echo $status_badge_pn[$r['status_pn']] ?>"><?php echo $status_label_pn[$r['status_pn']] ?></span>
                                                </td>

                                                <td class="text-center">
                                                    <?php if ($nilai_akhir_val !== null): ?><span
                                                        class="fw-bold"><?php echo number_format($nilai_akhir_val, 2) ?></span><span
                                                        class="badge-predikat bg-<?php echo $warna_p ?> text-white ms-1"><?php echo $kode_p ?></span><?php else: ?><span
                                                        class="text-muted small">-</span><?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary rounded-circle btn-lihat-nilai"
                                                        data-bs-toggle="tooltip"
                                                        title="Lihat & input nilai"
                                                        data-file-zip="<?= $r['file_laporan'] ?? '' ?>"
                                                        data-link-gdrive="<?= htmlspecialchars($r['link_gdrive'] ?? '', ENT_QUOTES) ?>"
                                                        data-id="<?php echo $r['id_pelajar'] ?>"
                                                        data-nama="<?php echo htmlspecialchars($r['nama'], ENT_QUOTES) ?>"
                                                        data-instansi="<?php echo htmlspecialchars($r['nama_instansi'] ?? '-', ENT_QUOTES) ?>"
                                                        data-unit-kerja="<?php echo htmlspecialchars($r['unit_kerja'] ?? '-', ENT_QUOTES) ?>"
                                                        data-periode="<?php echo $r['tanggal_mulai'] ? date('d M Y', strtotime($r['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($r['tanggal_selesai'])) : '-' ?>"
                                                        data-foto="<?php echo htmlspecialchars($foto_url, ENT_QUOTES) ?>"
                                                        data-inisial="<?php echo $inisial ?>"
                                                        data-disiplin="<?php echo $r['disiplin'] ?? '' ?>"
                                                        data-komunikasi="<?php echo $r['komunikasi'] ?? '' ?>"
                                                        data-kerjasama="<?php echo $r['kerjasama'] ?? '' ?>"
                                                        data-tanggung-jawab="<?php echo $r['tanggung_jawab'] ?? '' ?>"
                                                        data-inisiatif="<?php echo $r['inisiatif'] ?? '' ?>"
                                                        data-absensi="<?php echo $r['absensi'] ?? '' ?>"
                                                        data-logbook="<?php echo $r['logbook'] ?? '' ?>"
                                                        data-tugas-akhir="<?php echo $r['tugas_akhir'] ?? '' ?>"
                                                        data-catatan="<?php echo htmlspecialchars($r['catatan'] ?? '', ENT_QUOTES) ?>"><i class="bi bi-eye"></i></button>
                                                </td>
                                            </tr>
                                            <?php endforeach;else: ?><tr>
                                                <td colspan="8" class="text-center py-4 text-muted">Belum ada siswa
                                                    aktif bimbingan Anda.</td>
                                            </tr><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card section-card">
                                    <div class="card-body">
                                        <!-- REVISI: Pembatasan Tinggi Chart.js -->
                                        <h6 class="fw-bold mb-3">Rekap Nilai Keseluruhan</h6>
                                        <div style="position: relative; height: 240px; width: 100%;">
                                            <canvas id="chartRekap"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card section-card">
                                    <div class="card-body">
                                        <!-- REVISI: Pembatasan Tinggi Chart.js -->
                                        <h6 class="fw-bold mb-3">Distribusi Predikat</h6>
                                        <div style="position: relative; height: 240px; width: 100%;">
                                            <canvas id="chartDistribusi"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card side-panel-card">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Detail &amp; Input Penilaian</h6>
                                <div id="dtlPlaceholder" class="detail-placeholder">
                                    <i class="bi bi-clipboard-data fs-1 d-block mb-2"></i> Klik ikon <i
                                        class="bi bi-eye"></i> pada salah satu siswa untuk melihat &amp; menginput nilai
                                    di sini.
                                </div>
                                <form method="POST" id="formNilai">
                                    <input type="hidden" name="pelajar_id" id="dtlIdpelajar" value="">
                                    <div id="dtlContent" style="display:none;">
                                        <div class="detail-row">
                                        <div class="detail-label">Berkas Tugas Akhir</div>
                                        <div class="detail-value" id="dtlBerkasTA">
                                            <span class="text-muted small">-</span>
                                        </div>
                                      </div>
                                        <div class="d-flex align-items-center mb-3"><span class="avatar-circle"
                                                style="width:52px;height:52px;font-size:1.1rem;" id="dtlAvatar">?</span>
                                            <div>
                                                <div class="fw-bold" id="dtlNama">-</div>
                                                <div class="small text-muted" id="dtlInstansi">-</div>
                                            </div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Unit Kerja</div>
                                            <div class="detail-value" id="dtlUnitKerja">-</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Periode PKL</div>
                                            <div class="detail-value" id="dtlPeriode">-</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Pembimbing</div>
                                            <div class="detail-value"><?php echo htmlspecialchars($nama_pembimbing) ?>
                                            </div>
                                        </div>
                                        <input type="hidden" name="absensi" id="inputAbsensi"><input type="hidden"
                                            name="logbook" id="inputLogbook">
                                        <h6 class="fw-bold small text-primary mb-2 mt-3">1. Sub-Komponen Pembimbing
                                            (Skala 0-100)</h6>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6"><label
                                                    class="form-label small mb-1">Disiplin</label><input type="number"
                                                    name="disiplin" id="inputDisiplin"
                                                    class="form-control form-control-sm input-score" min="0" max="100"
                                                    step="0.01" required></div>
                                            <div class="col-6"><label
                                                    class="form-label small mb-1">Komunikasi</label><input type="number"
                                                    name="komunikasi" id="inputKomunikasi"
                                                    class="form-control form-control-sm input-score" min="0" max="100"
                                                    step="0.01" required></div>
                                            <div class="col-6"><label
                                                    class="form-label small mb-1">Kerjasama</label><input type="number"
                                                    name="kerjasama" id="inputKerjasama"
                                                    class="form-control form-control-sm input-score" min="0" max="100"
                                                    step="0.01" required></div>
                                            <div class="col-6"><label class="form-label small mb-1">Tanggung
                                                    Jawab</label><input type="number" name="tanggung_jawab"
                                                    id="inputTanggungJawab"
                                                    class="form-control form-control-sm input-score" min="0" max="100"
                                                    step="0.01" required></div>
                                            <div class="col-12"><label
                                                    class="form-label small mb-1">Inisiatif</label><input type="number"
                                                    name="inisiatif" id="inputInisiatif"
                                                    class="form-control form-control-sm input-score" min="0" max="100"
                                                    step="0.01" required></div>
                                        </div>
                                        <h6 class="fw-bold small text-primary mb-2">2. Komponen Pendukung & Tugas Akhir
                                        </h6>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="p-2 bg-light rounded border text-center"><small
                                                        class="text-muted d-block">Kehadiran (15%)</small><span
                                                        id="textAbsensi" class="fw-bold text-primary">0.00%</span></div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 bg-light rounded border text-center"><small
                                                        class="text-muted d-block">Logbook (15%)</small><span
                                                        id="textLogbook" class="fw-bold text-primary">0.00%</span></div>
                                            </div>
                                            <div class="col-12 mt-2"><label class="form-label small mb-1">Tugas Akhir /
                                                    Laporan (25%)</label><input type="number" name="tugas_akhir"
                                                    id="inputTugasAkhir"
                                                    class="form-control form-control-sm input-score" min="0" max="100"
                                                    step="0.01" placeholder="Nilai Tugas Akhir (0-100)" required></div>
                                        </div>
                                        <div class="nilai-akhir-box mb-3">
                                            <div class="small text-muted">Estimasi Nilai Akhir Final</div>
                                            <div id="previewNilaiAkhir" class="fw-bold fs-4 text-dark my-1">0.00</div>
                                            <div id="previewPredikat"
                                                class="badge-predikat bg-secondary text-white d-inline-block fs-6">-
                                            </div>
                                        </div>
                                        <label class="form-label small text-muted">Catatan &amp; Saran
                                            Pembimbing</label><textarea name="catatan" id="dtlCatatan"
                                            class="form-control mb-3" rows="3"
                                            placeholder="Berikan catatan untuk siswa..."></textarea>
                                        <button type="submit" name="submit_nilai" class="btn btn-primary w-100"><i
                                                class="bi bi-save me-1"></i> Simpan Penilaian</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
            function getPredikatJS(nilai) {
                if (nilai === null || isNaN(nilai) || nilai === '') return {
                    kode: '-',
                    warna: 'secondary'
                };
                nilai = parseFloat(nilai);
                if (nilai >= 90) return {
                    kode: 'A (Sangat Baik)',
                    warna: 'success'
                };
                if (nilai >= 85) return {
                    kode: 'A- (Sangat Baik)',
                    warna: 'success'
                };
                if (nilai >= 80) return {
                    kode: 'B+ (Baik Sekali)',
                    warna: 'primary'
                };
                if (nilai >= 75) return {
                    kode: 'B (Baik)',
                    warna: 'primary'
                };
                if (nilai >= 70) return {
                    kode: 'B- (Cukup Baik)',
                    warna: 'warning'
                };
                if (nilai >= 65) return {
                    kode: 'C (Cukup)',
                    warna: 'warning'
                };
                return {
                    kode: 'D/E (Kurang)',
                    warna: 'danger'
                };
            }

            function hitungNilaiOtomatis() {
                const d = parseFloat(document.getElementById('inputDisiplin').value) || 0;
                const k = parseFloat(document.getElementById('inputKomunikasi').value) || 0;
                const j = parseFloat(document.getElementById('inputKerjasama').value) || 0;
                const t = parseFloat(document.getElementById('inputTanggungJawab').value) || 0;
                const i = parseFloat(document.getElementById('inputInisiatif').value) || 0;
                const abs = parseFloat(document.getElementById('inputAbsensi').value) || 0;
                const log = parseFloat(document.getElementById('inputLogbook').value) || 0;
                const ta = parseFloat(document.getElementById('inputTugasAkhir').value) || 0;
                const rataPembimbing = (d + k + j + t + i) / 5;
                const nilaiAkhir = (rataPembimbing * 0.45) + (abs * 0.15) + (log * 0.15) + (ta * 0.25);
                document.getElementById('previewNilaiAkhir').textContent = nilaiAkhir.toFixed(2);
                const badge = document.getElementById('previewPredikat');
                const p = getPredikatJS(nilaiAkhir);
                badge.textContent = p.kode;
                badge.className = 'badge-predikat bg-' + p.warna + ' text-white d-inline-block fs-6';
            }
            document.querySelectorAll('.input-score').forEach(el => el.addEventListener('input', hitungNilaiOtomatis));

            // REVISI: Inisialisasi Tooltip Bootstrap
            document.addEventListener('DOMContentLoaded', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });

            document.querySelectorAll('.btn-lihat-nilai').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    // Tambahkan di dalam event listener btn-lihat-nilai:
                    const fileZip = this.getAttribute('data-file-zip');
                    const linkGdrive = this.getAttribute('data-link-gdrive');
                    let berkasHtml = '';

                    if (fileZip) {
                        berkasHtml += `<a href="../uploads/laporan_akhir/${fileZip}" download class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-file-earmark-zip me-1"></i>ZIP</a>`;
                    }
                    if (linkGdrive) {
                        berkasHtml += `<a href="${linkGdrive}" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-google me-1"></i>GDrive</a>`;
                    }
                    if (!berkasHtml) {
                        berkasHtml = '<span class="text-muted small fst-italic">Belum mengunggah</span>';
                    }
                    document.getElementById('dtlBerkasTA').innerHTML = berkasHtml;
                    
                    document.querySelectorAll('tr.row-active').forEach(r => r.classList.remove(
                        'row-active'));
            

                    const pelajarId = this.getAttribute('data-id');

                    document.getElementById('row-pelajar-' + pelajarId)?.classList.add('row-active');
                    document.getElementById('dtlPlaceholder').style.display = 'none';
                    document.getElementById('dtlContent').style.display = 'block';

                    document.getElementById('dtlIdpelajar').value = pelajarId;

                    document.getElementById('dtlNama').textContent = this.dataset.nama;
                    document.getElementById('dtlInstansi').textContent = this.dataset.instansi;
                    document.getElementById('dtlUnitKerja').textContent = this.dataset.unitKerja;
                    document.getElementById('dtlPeriode').textContent = this.dataset.periode;
                    document.getElementById('dtlCatatan').value = this.dataset.catatan || '';
                    document.getElementById('inputDisiplin').value = this.dataset.disiplin || '';
                    document.getElementById('inputKomunikasi').value = this.dataset.komunikasi || '';
                    document.getElementById('inputKerjasama').value = this.dataset.kerjasama || '';
                    document.getElementById('inputTanggungJawab').value = this.getAttribute(
                        'data-tanggung-jawab') || '';
                    document.getElementById('inputInisiatif').value = this.dataset.inisiatif || '';
                    const valAbs = this.dataset.absensi || '0';
                    const valLog = this.dataset.logbook || '0';
                    document.getElementById('inputAbsensi').value = valAbs;
                    document.getElementById('inputLogbook').value = valLog;
                    document.getElementById('textAbsensi').textContent = parseFloat(valAbs).toFixed(2) +
                        '%';
                    document.getElementById('textLogbook').textContent = parseFloat(valLog).toFixed(2) +
                        '%';
                    document.getElementById('inputTugasAkhir').value = this.dataset.tugasAkhir || '';
                    hitungNilaiOtomatis();
                    var avatar = document.getElementById('dtlAvatar');
                    if (this.dataset.foto) {
                        avatar.innerHTML = '<img src="' + this.dataset.foto + '" alt="">';
                    } else {
                        avatar.textContent = this.dataset.inisial;
                    }
                });
            });

            new Chart(document.getElementById('chartRekap'), {
                type: 'doughnut',
                data: {
                    labels: ['Sangat Baik (A/A-)', 'Baik (B+/B)', 'Cukup (B-/C)', 'Kurang (D/E)'],
                    datasets: [{
                        data: [<?php echo $distribusi['A'] + $distribusi['A-'] ?>,
                            <?php echo $distribusi['B+'] + $distribusi['B'] ?>,
                            <?php echo $distribusi['B-'] + $distribusi['C'] ?>,
                            <?php echo $distribusi['D/E'] ?>
                        ],
                        backgroundColor: ['#16a34a', '#2563eb', '#d97706', '#dc2626'],
                        borderWidth: 0
                    }]
                },
                // REVISI: Menambahkan maintainAspectRatio dan responsive
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
            new Chart(document.getElementById('chartDistribusi'), {
                type: 'bar',
                data: {
                    labels: ['A', 'A-', 'B+', 'B', 'B-', 'C', 'D/E'],
                    datasets: [{
                        label: 'Jumlah Siswa',
                        data: [<?php echo $distribusi['A'] ?>, <?php echo $distribusi['A-'] ?>,
                            <?php echo $distribusi['B+'] ?>, <?php echo $distribusi['B'] ?>,
                            <?php echo $distribusi['B-'] ?>, <?php echo $distribusi['C'] ?>,
                            <?php echo $distribusi['D/E'] ?>
                        ],
                        backgroundColor: '#1e3a8a',
                        borderRadius: 6
                    }]
                },
                // REVISI: Menambahkan maintainAspectRatio dan responsive
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
            </script>
            <?php include '../include/footer_pembimbing.php'; ?>
        </div>
    </div>
</body>

</html>
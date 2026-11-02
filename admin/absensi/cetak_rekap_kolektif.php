<?php
session_start();
include "../../helper/auth_admin.php";
include "../../config/config.php";

// 1. Tangkap Parameter Modal
$jenis_cetak = $_GET['jenis_cetak'] ?? 'semua';
$tgl_mulai   = $_GET['tgl_mulai'] ?? null;
$tgl_selesai = $_GET['tgl_selesai'] ?? null;
$ttd_input   = trim($_GET['ttd_nama'] ?? '');

// Format Tanggal Cetak Indonesia
$bulan_indo = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$tgl_cetak = date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y');

// 2. Pemrosesan Data TTD Penanggung Jawab
$ttd_nama    = "_________________________";
$ttd_nip     = "___________________";
$ttd_jabatan = "Pengelola Magang / Subbagian Umum";

if (!empty($ttd_input)) {
    $q_check_ttd = mysqli_query($conn, "SELECT nama, nip, nama_jabatan FROM pembimbing WHERE nama = '" . mysqli_real_escape_string($conn, $ttd_input) . "' OR nip = '" . mysqli_real_escape_string($conn, $ttd_input) . "' LIMIT 1");
    if ($q_check_ttd && mysqli_num_rows($q_check_ttd) > 0) {
        $dt_ttd = mysqli_fetch_assoc($q_check_ttd);
        $ttd_nama    = $dt_ttd['nama'];
        $ttd_nip     = !empty($dt_ttd['nip']) ? $dt_ttd['nip'] : '-';
        $ttd_jabatan = !empty($dt_ttd['nama_jabatan']) ? $dt_ttd['nama_jabatan'] : 'Pengelola Magang / Subbagian Umum';
    } else {
        $ttd_nama = $ttd_input;
    }
}

// 3. Tentukan Filter JOIN Absensi dan Filter Pelajar Aktif
$where_aktif = "";
if ($jenis_cetak === 'periode' && !empty($tgl_mulai) && !empty($tgl_selesai)) {
    $join_absensi  = "LEFT JOIN absensi a ON a.pelajar_id = p.id AND a.tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
    $periode_label = date('d M Y', strtotime($tgl_mulai)) . ' s/d ' . date('d M Y', strtotime($tgl_selesai));
    
    // Filter khusus: Hanya tampilkan pelajar yang periode magangnya masih aktif pada rentang tanggal tersebut
    $where_aktif   = " AND pk.tanggal_mulai <= '$tgl_selesai' AND pk.tanggal_selesai >= '$tgl_mulai' ";
} else {
    $join_absensi  = "LEFT JOIN absensi a ON a.pelajar_id = p.id";
    $periode_label = "Keseluruhan";
}

// 4. Query SQL Rekapitulasi Presensi Kolektif (Disesuaikan agar status apapun selain Izin/Sakit/Alpha dihitung Hadir)
$query_rekap = "
    SELECT 
        p.id AS pelajar_id,
        p.nama,
        p.nim,
        u.nama_instansi,
        uk.unit_kerja AS nama_bidang,
        pb.nama AS nama_pembimbing,
        pk.tanggal_mulai AS tgl_mulai_pkl,
        pk.tanggal_selesai AS tgl_selesai_pkl,
        SUM(CASE WHEN a.status NOT IN ('Izin', 'Sakit', 'Alpha') AND (a.jam_masuk IS NOT NULL OR a.status IS NOT NULL) THEN 1 ELSE 0 END) AS total_hadir,
        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) AS total_izin,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) AS total_sakit,
        SUM(CASE WHEN a.status = 'Alpha' THEN 1 ELSE 0 END) AS total_alpha
    FROM pelajar p
    JOIN pendaftaran pd ON pd.pelajar_id = p.id AND pd.status = 'diterima'
    LEFT JOIN periode_pkl pk ON pd.periode_id = pk.id
    LEFT JOIN instansi u ON p.instansi_id = u.id
    LEFT JOIN unit_kerja uk ON pd.unit_kerja_id = uk.id
    LEFT JOIN pembimbing pb ON pd.pembimbing_id = pb.id
    $join_absensi
    WHERE 1=1 $where_aktif
    GROUP BY p.id
    ORDER BY p.nama ASC
";
$res_rekap = mysqli_query($conn, $query_rekap);

if (!$res_rekap) {
    die("Query Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap_Absensi_Kolektif_Pelajar</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; margin: 0; padding: 1.5cm; }
        .kop-official {position: relative;display: flex;align-items: center;border-bottom: 3px double #000;padding-bottom: 6px;margin-bottom: 18px;}
        .kop-official .kop-logo {position: absolute;left: 5px;top: 50%;transform: translateY(-50%);width: 95px;height: 125px;object-fit: contain;}
        .kop-official .kop-text {width: 100%;text-align: center;padding-left: 100px;}
        .kop-official .kop-text .baris1 {font-family: 'Times New Roman', Times, serif;font-size: 13pt;font-weight: bold;margin: 0;letter-spacing: 0.5px;text-transform: uppercase;}
        .kop-official .kop-text .baris2 {font-family: 'Times New Roman', Times, serif;font-size: 14pt;font-weight: bold;margin: 2px 0;text-transform: uppercase;}
        .kop-official .kop-text .baris3 {font-family: 'Times New Roman', Times, serif;font-size: 9.5pt;margin: 2px 0;line-height: 1.25;}
        .kop-official .kop-text .baris4 {font-family: 'Times New Roman', Times, serif;font-size: 11pt;font-weight: bold;text-decoration: underline;margin-top: 3px;letter-spacing: 1px;}
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 5px 6px; font-size: 8.5pt; text-align: center; }
        .data-table th { background-color: #f2f2f2; text-transform: uppercase; font-size: 8pt; }

        .ttd-container { width: 100%; margin-top: 25px; page-break-inside: avoid; }
        
        @media print {
            @page { size: A4 landscape; margin: 0; }
            body { padding: 1cm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 18px; background: #2563eb; color: #fff; border: none; border-radius: 20px; cursor: pointer; font-weight: bold;">
            🖨️ Cetak Rekap Kolektif PDF
        </button>
    </div>

    <div class="kop-official">
        <img class="kop-logo" src="../../assets/img/jatim_logo.png" alt="Logo Jawa Timur">
        <div class="kop-text">
            <div class="baris1">PROVINSI JAWA TIMUR</div>
            <div class="baris2">DINAS PEKERJAAN UMUM BINA MARGA</div>
            <div class="baris3">
                Jl. Gayung Kebonsari No. 167, Surabaya Telp. (031) 8280919, 8288773 Fax. (031) 8293026<br>
                Website: binamarga.jatimprov.go.id &bull; Email: binamarga@jatimprov.go.id
            </div>
            <div class="baris4">SURABAYA</div>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 15px;">
        <h4 style="font-weight: bold; margin: 0 0 4px 0; font-size: 12pt;">REKAPITULASI PRESENSI KOLEKTIF PELAJAR MAGANG / PKL</h4>
        <span style="font-size: 10pt;">Periode Absensi: <?= $periode_label; ?></span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" width="3%">NO</th>
                <th rowspan="2" width="15%">NAMA PELAJAR</th>
                <th rowspan="2" width="8%">NIM / NISN</th>
                <th rowspan="2" width="14%">SEKOLAH / KAMPUS</th>
                <th rowspan="2" width="17%">UNIT KERJA PENEMPATAN</th>
                <th colspan="2" width="14%">PERIODE MAGANG</th>
                <th rowspan="2" width="11%">PEMBIMBING</th>
                <th rowspan="2" width="4%">HADIR</th>
                <th rowspan="2" width="4%">IZIN</th>
                <th rowspan="2" width="4%">SAKIT</th>
                <th rowspan="2" width="4%">ALPHA</th>
            </tr>
            <tr>
                <th width="7%">AWAL</th>
                <th width="7%">AKHIR</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (mysqli_num_rows($res_rekap) > 0):
                $no = 1;
                while ($r = mysqli_fetch_assoc($res_rekap)):
                    // Format Tanggal Mulai dan Selesai
                    $tgl_awal  = !empty($r['tgl_mulai_pkl']) ? date('d/m/Y', strtotime($r['tgl_mulai_pkl'])) : '-';
                    $tgl_akhir = !empty($r['tgl_selesai_pkl']) ? date('d/m/Y', strtotime($r['tgl_selesai_pkl'])) : '-';
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td style="text-align: left;"><strong><?= htmlspecialchars($r['nama']); ?></strong></td>
                <td><?= htmlspecialchars($r['nim']); ?></td>
                <td style="text-align: left;"><?= htmlspecialchars($r['nama_instansi'] ?? '-'); ?></td>
                <td style="text-align: left;"><?= htmlspecialchars($r['nama_bidang'] ?? '-'); ?></td>
                <td><?= $tgl_awal; ?></td>
                <td><?= $tgl_akhir; ?></td>
                <td style="text-align: left;"><?= htmlspecialchars($r['nama_pembimbing'] ?? '-'); ?></td>
                <td><?= $r['total_hadir']; ?></td>
                <td><?= $r['total_izin']; ?></td>
                <td><?= $r['total_sakit']; ?></td>
                <td><?= $r['total_alpha']; ?></td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="12" style="padding: 15px;">Data pelajar magang tidak ditemukan.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- TTD PENANGGUNG JAWAB DINAMIS -->
    <div class="ttd-container">
        <div style="float: right; width: 320px; text-align: center; font-size: 10pt;">
            Surabaya, <?= $tgl_cetak; ?><br>
            <strong><?= htmlspecialchars($ttd_jabatan); ?></strong>
            <br><br><br><br><br>
            <strong><u><?= htmlspecialchars($ttd_nama); ?></u></strong><br>
            <span>NIP. <?= htmlspecialchars($ttd_nip); ?></span>
        </div>
        <div style="clear: both;"></div>
    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
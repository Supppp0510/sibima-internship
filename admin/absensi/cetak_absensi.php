<?php
session_start();
include "../../helper/auth_admin.php";
include "../../config/config.php";

// 1. Tangkap ID Pelajar dari Parameter URL (GET)
$pelajar_id = $_GET['pelajar_id'] ?? null;

if (empty($pelajar_id)) {
    exit("<script>alert('ID Pelajar tidak ditemukan.'); window.close();</script>");
}

// 2. Ambil Tanggal Terawal dan Terakhir Absensi dari Database
$q_range = mysqli_query($conn, "SELECT MIN(tanggal) AS tgl_awal, MAX(tanggal) AS tgl_akhir FROM absensi WHERE pelajar_id = '$pelajar_id'");
$dt_range = mysqli_fetch_assoc($q_range);

$tgl_mulai   = (!empty($_GET['tgl_mulai'])) ? $_GET['tgl_mulai'] : ($dt_range['tgl_awal'] ?? date('Y-m-01'));
$tgl_selesai = (!empty($_GET['tgl_selesai'])) ? $_GET['tgl_selesai'] : ($dt_range['tgl_akhir'] ?? date('Y-m-d'));

// 3. Ambil Profil Pelajar, Instansi, Unit Kerja, & Pembimbing
$query_profil = "SELECT 
                    p.nama,
                    p.nim,
                    ins.nama_instansi AS sekolah_kampus,
                    uk.unit_kerja,
                    uk.unit_kerja_es_iii,
                    uk.unit_kerja_es_iv,
                    pem.nama AS nama_pembimbing,
                    pem.nip AS nip_pembimbing
                 FROM pelajar p
                 LEFT JOIN instansi ins ON ins.id = p.instansi_id
                 LEFT JOIN pendaftaran pdf ON pdf.pelajar_id = p.id AND pdf.status = 'diterima'
                 LEFT JOIN unit_kerja uk ON uk.id = pdf.unit_kerja_id
                 LEFT JOIN pembimbing pem ON pem.id = pdf.pembimbing_id
                 WHERE p.id = '$pelajar_id'
                 LIMIT 1";

$res_profil = mysqli_query($conn, $query_profil);
$dt_pelajar = mysqli_fetch_assoc($res_profil);

// Format Nama Unit Kerja
$unit_kerja_tampil = $dt_pelajar['unit_kerja_es_iii'] ?? $dt_pelajar['unit_kerja'] ?? 'Belum Ditentukan';
if (!empty($dt_pelajar['unit_kerja_es_iv'])) {
    $unit_kerja_tampil .= " (" . $dt_pelajar['unit_kerja_es_iv'] . ")";
}

// Pembersihan Spasi NIP
$nip_raw = $dt_pelajar['nip_pembimbing'] ?? '';
$nip_pembimbing_bersih = !empty($nip_raw) ? str_replace(' ', '', $nip_raw) : '-';

// Format Tanggal Cetak Indonesia
$bulan_indo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl_cetak = date('d') . ' ' . $bulan_indo[(int)date('n')] . ' ' . date('Y');

// 4. Ambil Riwayat Absensi
$query_absen = "SELECT * FROM absensi 
                WHERE pelajar_id = '$pelajar_id' 
                  AND tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai' 
                ORDER BY tanggal ASC";
$res_absen = mysqli_query($conn, $query_absen);

$total_hadir = 0; $total_izin = 0; $total_sakit = 0; $total_alpha = 0;
$data_rows = [];
if ($res_absen) {
    while ($row = mysqli_fetch_assoc($res_absen)) {
        $data_rows[] = $row;
        $st = strtolower($row['status'] ?? '');
        if (in_array($st, ['hadir', 'pulang_cepat', 'tanpa_absen_pulang'])) { $total_hadir++; }
        elseif ($st === 'izin') { $total_izin++; }
        elseif ($st === 'sakit') { $total_sakit++; }
        elseif ($st === 'alpha') { $total_alpha++; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap_Absensi_<?= htmlspecialchars($dt_pelajar['nama'] ?? 'Pelajar'); ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; margin: 0; padding: 1.5cm; }
        .kop-official {position: relative;display: flex;align-items: center;border-bottom: 3px double #000;padding-bottom: 6px;margin-bottom: 18px;}
        .kop-official .kop-logo {position: absolute;left: 5px;top: 50%;transform: translateY(-50%);width: 95px;height: 125px;object-fit: contain;}
        .kop-official .kop-text {width: 100%;text-align: center;padding-left: 100px; /* Menjaga posisi teks tetap tepat di tengah halaman */}
        .kop-official .kop-text .baris1 {font-family: 'Times New Roman', Times, serif;font-size: 13pt;font-weight: bold;margin: 0;letter-spacing: 0.5px;text-transform: uppercase;}
        .kop-official .kop-text .baris2 {font-family: 'Times New Roman', Times, serif;font-size: 14pt;font-weight: bold;margin: 2px 0;text-transform: uppercase;}
        .kop-official .kop-text .baris3 {font-family: 'Times New Roman', Times, serif;font-size: 9.5pt;margin: 2px 0;line-height: 1.25;}
        .kop-official .kop-text .baris4 {font-family: 'Times New Roman', Times, serif;font-size: 11pt;font-weight: bold;text-decoration: underline;margin-top: 3px;letter-spacing: 1px;}

        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 15pt; text-transform: uppercase; font-weight: bold; }
        .header p { margin: 3px 0 0; font-size: 10pt; }
        
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; table-layout: fixed; }
        .info-table td { padding: 4px 2px; vertical-align: top; font-size: 10pt; line-height: 1.35; word-wrap: break-word; }
        .info-table td.col-val-left { padding-right: 25px; }

        .rekap-box { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .rekap-box td { border: 1px solid #000; padding: 6px; text-align: center; font-size: 10pt; font-weight: bold; background-color: #f9f9f9; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px 8px; font-size: 10pt; text-align: center; }
        .data-table th { background-color: #f2f2f2; text-transform: uppercase; font-size: 9.5pt; }

        .ttd-container { width: 100%; margin-top: 25px; page-break-inside: avoid; }
        .ttd-box { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .ttd-box td { text-align: center; vertical-align: bottom; height: 90px; font-size: 10.5pt; }

        @media print {
            @page { size: A4 portrait; margin: 0; }
            body { padding: 1.5cm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 18px; background: #2563eb; color: #fff; border: none; border-radius: 20px; cursor: pointer; font-weight: bold;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- KOP LAPORAN RESMI -->
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

    <div class="header" style="border-bottom:none;margin-bottom:8px;padding-bottom:0;">
        <h2>SISTEM INFORMASI BINA MARGA INTERNSHIP JAWA TIMUR</h2>
        <p>Laporan Rekapitulasi Presensi Kehadiran Magang / PKL</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="18%"><strong>Nama Pelajar</strong></td>
            <td width="32%" class="col-val-left">: <?= htmlspecialchars($dt_pelajar['nama'] ?? '-'); ?></td>
            <td width="15%"><strong>Periode Absen</strong></td>
            <td width="35%">: <?= date('d M Y', strtotime($tgl_mulai)); ?> - <?= date('d M Y', strtotime($tgl_selesai)); ?></td>
        </tr>
        <tr>
            <td><strong>NIM / NISN</strong></td>
            <td class="col-val-left">: <?= htmlspecialchars($dt_pelajar['nim'] ?? '-'); ?></td>
            <td><strong>Instansi</strong></td>
            <td>: Dinas Pekerjaan Umum Bina Marga Provinsi Jawa Timur</td>
        </tr>
        <tr>
            <td><strong>Sekolah/Kampus</strong></td>
            <td class="col-val-left">: <?= htmlspecialchars($dt_pelajar['sekolah_kampus'] ?? '-'); ?></td>
            <td><strong>Unit Kerja</strong></td>
            <td>: <?= htmlspecialchars($unit_kerja_tampil); ?></td>
        </tr>
    </table>

    <table class="rekap-box">
        <tr>
            <td>Total Hadir: <?= $total_hadir; ?> Hari</td>
            <td>Total Izin: <?= $total_izin; ?> Hari</td>
            <td>Total Sakit: <?= $total_sakit; ?> Hari</td>
            <td>Total Alpha: <?= $total_alpha; ?> Hari</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="15%">Jam Masuk</th>
                <th width="15%">Jam Pulang</th>
                <th width="15%">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data_rows)): $no = 1; foreach($data_rows as $r): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= date('d/m/Y', strtotime($r['tanggal'])); ?></td>
                <td><?= $r['jam_masuk'] ?: '-'; ?></td>
                <td><?= $r['jam_pulang'] ?: '-'; ?></td>
                <td><strong><?= ucfirst($r['status']); ?></strong></td>
                <td style="text-align: left;"><?= htmlspecialchars($r['keterangan'] ?? '-'); ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
                <td colspan="6" style="padding: 15px;">Data presensi tidak ditemukan pada rentang tanggal ini.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="ttd-container">
        <div style="text-align: right; font-size: 10.5pt; margin-bottom: 12px;">
            Surabaya, <?= $tgl_cetak; ?>
        </div>
        <div style="text-align: center; font-size: 10.5pt; margin-bottom: 10px;">
            Mengetahui,
        </div>
        <table class="ttd-box">
            <tr>
                <td width="50%">
                    <strong>Pembimbing Lapangan / Instansi</strong>,
                    <br><br><br><br><br>
                    ( <strong><?= htmlspecialchars($dt_pelajar['nama_pembimbing'] ?? '_________________________'); ?></strong> )<br>
                    <span style="font-size: 9pt;">NIP. <?= htmlspecialchars($nip_pembimbing_bersih); ?></span>
                </td>
                <td width="50%">
                    <strong>Pelajar / Mahasiswa Magang</strong>,
                    <br><br><br><br><br>
                    ( <strong><?= htmlspecialchars($dt_pelajar['nama'] ?? 'Pelajar'); ?></strong> )<br>
                    <span style="font-size: 9pt;">NIM. <?= htmlspecialchars($dt_pelajar['nim'] ?? '-'); ?></span>
                </td>
            </tr>
        </table>
    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
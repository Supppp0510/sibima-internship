<?php
session_start();
include "../config/config.php";

// 1. Deteksi ID Pelajar dari Session
$pelajar_id = $_SESSION['pelajar_id'] ?? null;

if (!$pelajar_id) {
    if (isset($_SESSION['id_user']) || isset($_SESSION['user_id']) || isset($_SESSION['id'])) {
        $u_id = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'];
        $q_mhs = mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '$u_id' OR id = '$u_id'");
        $dt_mhs = mysqli_fetch_assoc($q_mhs);
        $pelajar_id = $dt_mhs['id'] ?? null;
    }
}

if (empty($pelajar_id)) {
    exit("<script>alert('Akses Ditolak. Silakan login terlebih dahulu.'); window.close();</script>");
}

// 2. Ambil Tanggal Terawal dan Terakhir Absensi dari Database (Untuk Keseluruhan Data)
$q_range = mysqli_query($conn, "SELECT MIN(tanggal) AS tgl_awal, MAX(tanggal) AS tgl_akhir FROM absensi WHERE pelajar_id = '$pelajar_id'");
$dt_range = mysqli_fetch_assoc($q_range);

// Jika URL berisi filter tanggal maka gunakan filter, jika kosong ambil seluruh tanggal dari database
$tgl_mulai   = (!empty($_GET['tgl_mulai'])) ? $_GET['tgl_mulai'] : ($dt_range['tgl_awal'] ?? date('Y-m-01'));
$tgl_selesai = (!empty($_GET['tgl_selesai'])) ? $_GET['tgl_selesai'] : ($dt_range['tgl_akhir'] ?? date('Y-m-d'));

// 3. Ambil Profil Pelajar, Instansi Asal, Unit Kerja, & Nama Pembimbing
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

// FORMAT NIP PEMBIMBING (Pembersihan Spasi Otomatis)
$nip_raw = $dt_pelajar['nip_pembimbing'] ?? '';
$nip_pembimbing_bersih = !empty($nip_raw) ? str_replace(' ', '', $nip_raw) : '-';

// FORMAT TANGGAL CETAK BAHASA INDONESIA
$bulan_indo = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$tgl_cetak = date('d') . ' ' . $bulan_indo[(int)date('n')] . ' ' . date('Y');

// 4. Ambil Data Absensi pada Rentang Tanggal
$query_absen = "SELECT * FROM absensi 
                WHERE pelajar_id = '$pelajar_id' 
                  AND tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai' 
                ORDER BY tanggal ASC";
$res_absen = mysqli_query($conn, $query_absen);

// Hitung Rekapitulasi Status Kehadiran
$total_hadir = 0;
$total_izin  = 0;
$total_sakit = 0;
$total_alpha = 0;

$data_rows = [];
if ($res_absen) {
    while ($row = mysqli_fetch_assoc($res_absen)) {
        $st = strtolower(trim($row['status'] ?? ''));

        // Jika kolom status di DB NULL / kosong tetapi ada jam masuk, atur default status ke 'Hadir'
        if (empty($st) && !empty($row['jam_masuk'])) {
            $st = 'hadir';
            $row['status'] = 'Hadir';
        }

        if ($st === 'izin') {
            $total_izin++;
        } elseif ($st === 'sakit') {
            $total_sakit++;
        } elseif ($st === 'alpha') {
            $total_alpha++;
        } else {
            // Selagi pelajar melakukan absensi (selain Izin, Sakit, Alpha), dihitung Hadir
            $total_hadir++;
        }

        $data_rows[] = $row;
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
        .kop-official .kop-text {width: 100%;text-align: center;padding-left: 100px;}
        .kop-official .kop-text .baris1 {font-family: 'Times New Roman', Times, serif;font-size: 13pt;font-weight: bold;margin: 0;letter-spacing: 0.5px;text-transform: uppercase;}
        .kop-official .kop-text .baris2 {font-family: 'Times New Roman', Times, serif;font-size: 14pt;font-weight: bold;margin: 2px 0;text-transform: uppercase;}
        .kop-official .kop-text .baris3 {font-family: 'Times New Roman', Times, serif;font-size: 9.5pt;margin: 2px 0;line-height: 1.25;}
        .kop-official .kop-text .baris4 {font-family: 'Times New Roman', Times, serif;font-size: 11pt;font-weight: bold;text-decoration: underline;margin-top: 3px;letter-spacing: 1px;}
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 15pt; text-transform: uppercase; font-weight: bold; }
        .header p { margin: 3px 0 0; font-size: 10pt; }
        
        .info-table { 
            width: 100%; 
            margin-bottom: 20px; 
            border-collapse: collapse; 
            table-layout: fixed;
        }
        .info-table td { 
            padding: 4px 2px; 
            vertical-align: top; 
            font-size: 10pt; 
            line-height: 1.35;
            word-wrap: break-word;
        }
        .info-table td.col-val-left {
            padding-right: 25px; 
        }

        .rekap-box { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .rekap-box td { border: 1px solid #000; padding: 6px; text-align: center; font-size: 10pt; font-weight: bold; background-color: #f9f9f9; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px 8px; font-size: 10pt; text-align: center; }
        .data-table th { background-color: #f2f2f2; text-transform: uppercase; font-size: 9.5pt; }

        .ttd-container { width: 100%; margin-top: 25px; page-break-inside: avoid; }
        .ttd-box { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .ttd-box td { text-align: center; vertical-align: bottom; height: 90px; font-size: 10.5pt; }

        @media print {
            @page { 
                size: A4 portrait; 
                margin: 0; 
            }
            body { 
                padding: 1.5cm; 
            }
            .no-print { 
                display: none !important; 
            }
        }
    </style>
</head>
<body>

    <!-- TOMBOL CETAK MANUAL -->
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 18px; background: #2563eb; color: #fff; border: none; border-radius: 20px; cursor: pointer; font-weight: bold;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- KOP LAPORAN -->
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

    <!-- INFORMASI PELAJAR & UNIT KERJA -->
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

    <!-- RINGKASAN KEHADIRAN -->
    <table class="rekap-box">
        <tr>
            <td>Total Hadir: <?= $total_hadir; ?> Hari</td>
            <td>Total Izin: <?= $total_izin; ?> Hari</td>
            <td>Total Sakit: <?= $total_sakit; ?> Hari</td>
            <td>Total Alpha: <?= $total_alpha; ?> Hari</td>
        </tr>
    </table>

    <!-- TABEL DETAIL RIWAYAT ABSENSI -->
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
            <?php 
            if (!empty($data_rows)):
                $no = 1;
                foreach($data_rows as $r): 
                    // Penanganan teks status tampilan tabel
                    $status_text = !empty($r['status']) ? ucfirst($r['status']) : (!empty($r['jam_masuk']) ? 'Hadir' : '-');
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= date('d/m/Y', strtotime($r['tanggal'])); ?></td>
                <td><?= $r['jam_masuk'] ?: '-'; ?></td>
                <td><?= $r['jam_pulang'] ?: '-'; ?></td>
                <td><strong><?= htmlspecialchars($status_text); ?></strong></td>
                <td style="text-align: left;"><?= htmlspecialchars($r['keterangan'] ?? '-'); ?></td>
            </tr>
            <?php 
                endforeach;
            else:
            ?>
            <tr>
                <td colspan="6" style="padding: 15px;">Data presensi tidak ditemukan pada rentang tanggal ini.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- KOLOM TANDA TANGAN -->
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

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
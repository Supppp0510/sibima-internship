<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";
include "../../helper/activity_log.php";
require '../../library/fpdf/fpdf.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';
if (empty($id)) {
    echo "<script>alert('ID tidak valid.'); window.location='index.php';</script>";
    exit;
}

// ======================================
// Cek Apakah Sertifikat Sudah Pernah Dibuat
// ======================================
$cek = mysqli_query($conn, "SELECT id FROM sertifikat WHERE pelajar_id='$id'") or die(mysqli_error($conn));
if (mysqli_num_rows($cek) > 0) {
    echo "<script>alert('Sertifikat sudah dibuat.'); window.location='index.php';</script>";
    exit;
}

// ======================================
// Query Data Lengkap
// ======================================
$query = mysqli_query($conn, "
SELECT m.id AS pelajar_id, m.nama, m.nim, u.nama_instansi, ps.nama_jurusan,
       b.unit_kerja, b.unit_kerja_es_ii, b.unit_kerja_es_iii, b.unit_kerja_es_iv,
       pb.nama AS pembimbing, pb.nip AS nip_pembimbing,
       pd.tanggal_daftar,
       pn.disiplin, pn.komunikasi, pn.kerjasama, pn.tanggung_jawab, pn.inisiatif,
       pn.nilai_akhir AS nilai_pembimbing_db, pn.tugas_akhir, la.status AS status_laporan, la.file_laporan,
       (SELECT COUNT(*) FROM absensi a WHERE a.pelajar_id = m.id) AS total_hari_absen,
       (SELECT COUNT(*) FROM absensi a WHERE a.pelajar_id = m.id AND a.status IN ('hadir', 'izin', 'sakit')) AS total_masuk_absen,
       (SELECT COUNT(*) FROM logbook lb WHERE lb.pelajar_id = m.id) AS total_logbook_diisi
FROM pelajar m
INNER JOIN pendaftaran pd ON pd.pelajar_id = m.id AND pd.status = 'diterima'
LEFT JOIN unit_kerja b ON b.id = pd.unit_kerja_id
LEFT JOIN penilaian pn ON pn.pelajar_id = m.id
LEFT JOIN laporan_akhir la ON la.pelajar_id = m.id
LEFT JOIN instansi u ON u.id = m.instansi_id
LEFT JOIN jurusan ps ON ps.id = m.jurusan_id
LEFT JOIN pembimbing pb ON pb.id = pd.pembimbing_id
WHERE m.id = '$id'
") or die("Error Query Utama: " . mysqli_error($conn));

if (mysqli_num_rows($query) == 0) {
    echo "<script>alert('Data tidak ditemukan.'); window.location='index.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($query);

// ======================================
// Set Data Eselon & Lokasi Magang
// ======================================
$es2           = ! empty(trim($data['unit_kerja_es_ii'])) ? trim($data['unit_kerja_es_ii']) : (! empty(trim($data['unit_kerja'])) ? trim($data['unit_kerja']) : 'DINAS PEKERJAAN UMUM BINA MARGA');
$es3           = ! empty(trim($data['unit_kerja_es_iii'])) ? trim($data['unit_kerja_es_iii']) : '';
$es4           = ! empty(trim($data['unit_kerja_es_iv'])) ? trim($data['unit_kerja_es_iv']) : '';
$lokasi_magang = ! empty(trim($data['unit_kerja'])) ? trim($data['unit_kerja']) : 'Dinas PU Bina Marga Provinsi Jawa Timur';

// Format Tanggal Mulai & Selesai Magang
$tgl_mulai_raw   = $data['tanggal_mulai'] ?? date('Y-m-d');
$tgl_selesai_raw = $data['tanggal_selesai'] ?? date('Y-m-d');

$formatter_bln = function ($bulan_angka) {
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $bulan[(int) $bulan_angka] ?? '';
};

$tgl_mulai_arr   = explode('-', $tgl_mulai_raw);
$tgl_selesai_arr = explode('-', $tgl_selesai_raw);

$str_tgl_mulai   = $tgl_mulai_arr[2] . ' ' . $formatter_bln($tgl_mulai_arr[1]) . ' ' . $tgl_mulai_arr[0];
$str_tgl_selesai = $tgl_selesai_arr[2] . ' ' . $formatter_bln($tgl_selesai_arr[1]) . ' ' . $tgl_selesai_arr[0];

$teks_durasi_magang = "Terhitung mulai tanggal $str_tgl_mulai hingga $str_tgl_selesai";

// ======================================
// Perhitungan Nilai Rata-rata & Akumulasi Final
// ======================================
$disiplin       = isset($data['disiplin']) ? (float) $data['disiplin'] : 0;
$komunikasi     = isset($data['komunikasi']) ? (float) $data['komunikasi'] : 0;
$kerjasama      = isset($data['kerjasama']) ? (float) $data['kerjasama'] : 0;
$tanggung_jawab = isset($data['tanggung_jawab']) ? (float) $data['tanggung_jawab'] : 0;
$inisiatif      = isset($data['inisiatif']) ? (float) $data['inisiatif'] : 0;

$sub_ada          = ($disiplin > 0 || $komunikasi > 0 || $kerjasama > 0 || $tanggung_jawab > 0 || $inisiatif > 0);
$nilai_pembimbing = $sub_ada ? (($disiplin + $komunikasi + $kerjasama + $tanggung_jawab + $inisiatif) / 5) : (float) $data['nilai_pembimbing_db'];

$total_hari_absen  = (int) $data['total_hari_absen'];
$total_masuk_absen = (int) $data['total_masuk_absen'];
$persentase_hadir  = ($total_hari_absen > 0) ? ($total_masuk_absen / $total_hari_absen) * 100 : 0;

$total_logbook_diisi = (int) $data['total_logbook_diisi'];
$persentase_logbook  = min(($total_logbook_diisi / 30) * 100, 100);

$ada_tugas_akhir   = (! empty($data['file_laporan']) || ! empty($data['tugas_akhir']));
$laporan_disetujui = (isset($data['status_laporan']) && $data['status_laporan'] == 'disetujui');
$nilai_tugas_akhir = ($ada_tugas_akhir && $laporan_disetujui) ? 100 : 0;

$nilai_akhir_final = (
    ($nilai_pembimbing * 0.45) +
    ($persentase_hadir * 0.15) +
    ($persentase_logbook * 0.15) +
    ($nilai_tugas_akhir * 0.25)
);

// Nomor Sertifikat
$q        = mysqli_query($conn, "SELECT COUNT(*) total FROM sertifikat") or die(mysqli_error($conn));
$r        = mysqli_fetch_assoc($q);
$no       = $r['total'] + 1;
$nomor    = sprintf("%03d", $no) . "/SIPKL/BM-JATIM/" . date("Y");
$tanggal  = date("Y-m-d");
$namaFile = "sertifikat_" . $data['nim'] . ".pdf";

// Folder Penyimpanan Aman
$folder = realpath(__DIR__ . '/../../assets/upload/sertifikat/');
if (! $folder) {
    $folder = __DIR__ . '/../../assets/upload/sertifikat';
}
$folder = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR;
if (! is_dir($folder)) {
    mkdir($folder, 0777, true);
}
$pathFile = $folder . $namaFile;

// ======================================
// PEMBUATAN PDF
// ======================================
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);

// *** DAFTARKAN SEMUA FONT CUSTOM DI SINI ***
// Pastikan nama file .php sesuai dengan yang di-generate oleh convert_font.php (huruf kecil semua jika default)
$pdf->AddFont('EdwardianScript', '', 'EdwardianScript.php');
$pdf->AddFont('Cinzel', '', 'cinzel.php');
$pdf->AddFont('Montserrat', '', 'montserrat.php');
$pdf->AddFont('Cinzel', 'B', 'Cinzel-Bold.php');

// --------------------------------------
// HALAMAN 1 : SERTIFIKAT UTAMA
// --------------------------------------
$pdf->AddPage();
$pdf->Image('../../assets/img/template_sertifikat.png', 0, 0, 297, 210);

$pdf->Image('../../assets/img/jatim_logo.png', 20, 11, 22);
$pdf->Image('../../assets/img/logo-bm.png', 37, 7, 30);
$pdf->Image('../../assets/img/logo-gerbang-baru.png', 215, 11, 70);

$pdf->SetY(22);
$pdf->SetFont('Cinzel', 'B', 50); // Menggunakan Cinzel untuk Judul
$pdf->SetTextColor(41, 106, 163); // Warna Biru
$pdf->Cell(0, 20, 'SERTIFIKAT', 0, 1, 'C');

$pdf->SetFont('Montserrat', '', 16); // Menggunakan Montserrat untuk Sub Judul
$pdf->Cell(0, 8, 'MAGANG', 0, 1, 'C');

$pdf->SetTextColor(0, 0, 0); // Kembali ke warna hitam
$pdf->SetFont('Montserrat', '', 11);
$pdf->Cell(0, 8, 'Nomor : ' . $nomor, 0, 1, 'C');
$pdf->Ln(3);

$pdf->Cell(0, 6, 'diberikan kepada :', 0, 1, 'C');
$pdf->Ln(2);

// ============================================================
// LOGIKA UNTUK NAMA PESERTA DAN GARIS BAWAH DINAMIS
// ============================================================
$nama_peserta = ucwords(strtolower($data['nama']));

$pdf->SetFont('EdwardianScript', '', 50);
$pdf->SetTextColor(41, 106, 163); // Warna Biru Elegan

// 1. Dapatkan lebar teks nama berdasarkan font saat ini
$lebar_teks   = $pdf->GetStringWidth($nama_peserta);
$lebar_kertas = 297;

// 2. Hitung titik awal (X) agar garis ada di tengah persis di bawah teks
$start_x = ($lebar_kertas - $lebar_teks) / 2;
$end_x   = $start_x + $lebar_teks;

// 3. Simpan posisi Y saat ini sebelum mencetak teks
$y_awal = $pdf->GetY();

// 4. Cetak teks Nama
$pdf->Cell(0, 14, $nama_peserta, 0, 1, 'C');

// 5. Gambar Garis di bawah teks
// Atur warna garis (sama dengan warna teks)
$pdf->SetDrawColor(0, 0, 0);
// Atur ketebalan garis
$pdf->SetLineWidth(0.4);
// Atur posisi Y untuk garis (y_awal + 11mm, sesuaikan jika kurang turun/naik)
$y_garis = $y_awal + 15;
$pdf->Line($start_x, $y_garis, $end_x, $y_garis);
// ============================================================

// Kembalikan ke warna hitam untuk teks selanjutnya
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(3); // Tambah sedikit jarak setelah garis

// Font untuk detail NIM dll
$pdf->SetFont('Montserrat', '', 11);
$pdf->Cell(0, 10, "NIM/NIS " . $data['nim'], 0, 1, 'C');
$pdf->Cell(0, 6, "Pelajar " . $data['nama_instansi'], 0, 1, 'C');
$pdf->Cell(0, 6, "Program Studi " . $data['nama_jurusan'], 0, 1, 'C');
$pdf->Ln(9);

$pdf->SetFont('Montserrat', '', 11);
$pdf->SetX(20);
$pdf->MultiCell(257, 6, "Telah menyelesaikan Magang di " . $lokasi_magang, 0, 'C');
$pdf->SetX(20);
$pdf->MultiCell(257, 6, "$teks_durasi_magang", 0, 'C');

$box_x = 165;
$box_y = 140;
$box_w = 115;
$box_h = 30;

$pdf->Rect($box_x, $box_y, $box_w, $box_h);

// Memasukkan QR Code ttd
$pdf->Image('../../assets/img/qr-ttd-kep.png', $box_x + 4, $box_y + 2, 26, 26);

// Teks Tanda Tangan (menggunakan Arial bawaan karena biasanya instansi pakai standar)
$pdf->SetXY($box_x + 30, $box_y + 4);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(68, 4, 'Surabaya, ' . date('d F Y', strtotime($tanggal)), 0, 1, 'L');
$pdf->SetX($box_x + 30);
$pdf->SetFont('Times', '', 8);
$pdf->Cell(68, 4, 'Ditandatangani Secara Elektronik oleh', 0, 1, 'L');
$pdf->SetX($box_x + 30);
$pdf->SetFont('Times', '', 8.5);
$pdf->Cell(68, 4, 'Kepala Dinas Pekerjaan Umum Bina Marga Provinsi Jawa Timur', 0, 1, 'L');
$pdf->SetX($box_x + 30);
$pdf->SetFont('Times', '', 8.5);
$pdf->Cell(68, 4, 'Ir. EDY TAMBENG WIDJAJA, S.T., M.Si.', 0, 1, 'L');
$pdf->SetX($box_x + 30);
$pdf->SetFont('Times', '', 8.5);
$pdf->Cell(68, 4, 'Pembina Utama Muda', 0, 1, 'L');
$pdf->SetX($box_x + 30);
$pdf->SetFont('Times', '', 8.5);
$pdf->Cell(68, 4, 'NIP. 19700205 199703 1 004', 0, 1, 'L');

// --------------------------------------
// HALAMAN 2 : TRANSKRIP / RINCIAN NILAI
// --------------------------------------
// --------------------------------------
// HALAMAN 2 : TRANSKRIP / RINCIAN NILAI
// --------------------------------------
$pdf->AddPage();
$pdf->Image('../../assets/img/template_sertifikat_page2.png', 0, 0, 297, 210);
$pdf->Image('../../assets/img/jatim_logo.png', 20, 11, 22);
$pdf->Image('../../assets/img/logo-bm.png', 37, 7, 30);
$pdf->Image('../../assets/img/logo-gerbang-baru.png', 215, 11, 70);

// --- BAGIAN JUDUL ---
$pdf->SetY(27);
$pdf->SetFont('Cinzel', 'B', 40);
$pdf->SetTextColor(41, 106, 163);

// Paksa X ke 0 dan gunakan lebar kertas 297mm agar center sempurna
$pdf->SetX(0);
$pdf->Cell(297, 20, 'TRANSKIP PENILAIAN', 0, 1, 'C');

$pdf->SetFont('Cinzel', 'B', 15);
$pdf->SetTextColor(0, 0, 0);

// Paksa X ke 0 lagi untuk baris kedua
$pdf->SetX(0);
$pdf->Cell(297, 8, 'DINAS PEKERJAAN UMUM BINA MARGA PROVINSI JAWA TIMUR', 0, 1, 'C');
$pdf->Ln(7);

// --- BARU AKTIFKAN MARGIN UNTUK TABEL DI BAWAHNYA ---
$pdf->SetLeftMargin(20);
$pdf->SetRightMargin(20);

$pdf->SetFont('Times', '', 9.5);
// ... (lanjutkan ke baris_info dan tabel A seperti biasa) ...
$baris_info = [
    ['Nama Pelajar', trim($data['nama']), 'Unit Kerja Eselon II', $es2],
    ['NIM/NIS', trim($data['nim']), 'Unit Kerja Eselon III', $es3],
    ['Instansi', trim($data['nama_instansi']), 'Unit Kerja Eselon IV', $es4],
    ['Jurusan', trim($data['nama_jurusan']), 'Pembimbing', $data['pembimbing']],
];

$pdf->SetFont('Times', '', 9);
foreach ($baris_info as $b) {
    $y_sebelum = $pdf->GetY();
    
    // Kolom Kiri
    $pdf->SetXY(20, $y_sebelum);
    $pdf->Cell(28, 5, $b[0], 0, 0);
    $pdf->Cell(4, 5, ':', 0, 0);
    $pdf->SetXY(52, $y_sebelum);
    $pdf->MultiCell(93, 5, $b[1], 0, 'L');
    $y_setelah_kiri = $pdf->GetY();

    // Kolom Kanan
    $pdf->SetXY(149, $y_sebelum);
    $pdf->Cell(38, 5, $b[2], 0, 0);
    $pdf->Cell(4, 5, ':', 0, 0);
    $pdf->SetXY(191, $y_sebelum);
    $pdf->MultiCell(86, 5, $b[3], 0, 'L');
    $y_setelah_kanan = $pdf->GetY();

    // Geser Y ke posisi baris terbawah dari kedua kolom
    $pdf->SetY(max($y_setelah_kiri, $y_setelah_kanan));
}
$pdf->Ln(4);

// Tabel A
$pdf->SetFont('Times', 'B', 9.5);
$pdf->Cell(257, 5.5, 'A. Rincian Sub-Komponen Nilai Pembimbing Lapangan', 0, 1, 'L');

$pdf->SetFillColor(37, 99, 235);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(177, 5.5, 'Sub-Komponen Penilaian', 1, 0, 'C', true);
$pdf->Cell(80, 5.5, 'Skor (Skala 0-100)', 1, 1, 'C', true);

$pdf->SetFont('Times', '', 9.5);
$pdf->SetTextColor(0, 0, 0);
$sub_komponen = [
    ['Disiplin', $disiplin],
    ['Komunikasi', $komunikasi],
    ['Kerjasama', $kerjasama],
    ['Tanggung Jawab', $tanggung_jawab],
    ['Inisiatif', $inisiatif],
];

foreach ($sub_komponen as $item) {
    $pdf->Cell(177, 5, $item[0], 1, 0, 'L');
    $pdf->Cell(80, 5, number_format($item[1], 2), 1, 1, 'C');
}

$pdf->SetFont('Times', 'B', 9.5);
$pdf->Cell(177, 5, 'Rata-rata Nilai Pembimbing Lapangan', 1, 0, 'L');
$pdf->Cell(80, 5, number_format($nilai_pembimbing, 2), 1, 1, 'C');
$pdf->Ln(3);

// Tabel B
$pdf->SetFont('Times', 'B', 9.5);
$pdf->Cell(257, 5.5, 'B. Rekapitulasi Komponen & Bobot Kelulusan', 0, 1, 'L');

$pdf->SetFillColor(37, 99, 235);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(117, 5.5, 'Komponen Penilaian', 1, 0, 'C', true);
$pdf->Cell(30, 5.5, 'Bobot', 1, 0, 'C', true);
$pdf->Cell(55, 5.5, 'Skor Capaian', 1, 0, 'C', true);
$pdf->Cell(55, 5.5, 'Nilai Kontribusi', 1, 1, 'C', true);

$pdf->SetFont('Times', '', 9.5);
$pdf->SetTextColor(0, 0, 0);

$data_b = [
    ['1. Nilai Pembimbing Lapangan', '45%', $nilai_pembimbing, $nilai_pembimbing * 0.45],
    ['2. Kehadiran / Absensi', '15%', number_format($persentase_hadir, 1) . '%', $persentase_hadir * 0.15],
    ['3. Kelengkapan Logbook', '15%', number_format($persentase_logbook, 1) . '%', $persentase_logbook * 0.15],
    ['4. Tugas Akhir / Laporan', '25%', ($ada_tugas_akhir && $laporan_disetujui) ? '100 (Disetujui)' : '0', $nilai_tugas_akhir * 0.25],
];

foreach ($data_b as $row) {
    $pdf->Cell(117, 5, $row[0], 1, 0, 'L');
    $pdf->Cell(30, 5, $row[1], 1, 0, 'C');
    $pdf->Cell(55, 5, is_numeric($row[2]) ? number_format($row[2], 2) : $row[2], 1, 0, 'C');
    $pdf->Cell(55, 5, number_format($row[3], 2), 1, 1, 'C');
}

$pdf->SetFont('Times', 'B', 9.5);
$pdf->Cell(202, 5.5, 'NILAI AKHIR AKUMULASI (FINAL):', 1, 0, 'R');
$pdf->Cell(55, 5.5, number_format($nilai_akhir_final, 2), 1, 1, 'C');

// ======================================
// SIMPAN KE SERVER & DATABASE
// ======================================
$pdf->Output("F", $pathFile);

if (file_exists($pathFile)) {
    $sql = "INSERT INTO sertifikat (pelajar_id, nomor_sertifikat, tanggal_terbit, file_pdf)
            VALUES ('" . $data['pelajar_id'] . "', '$nomor', '$tanggal', '$namaFile')";

    if (mysqli_query($conn, $sql)) {
        activityLog($conn, "Generate Sertifikat 2 Halaman : " . $data['nama']);
        echo "<script>alert('Sertifikat berhasil dibuat.'); window.location='index.php';</script>";
    } else {
        die("Gagal Menyimpan ke Database: " . mysqli_error($conn));
    }
} else {
    die("Gagal menyimpan file fisik PDF di direktori server: " . $pathFile);
}
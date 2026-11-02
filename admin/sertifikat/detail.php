<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    echo "<script>alert('ID tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

$query = mysqli_query($conn, "
SELECT

s.*,

m.id AS pelajar_id,
m.nama,
m.nim,

u.nama_instansi,
ps.nama_jurusan,

pn.disiplin,
pn.komunikasi,
pn.kerjasama,
pn.tanggung_jawab,
pn.inisiatif,
pn.nilai_akhir AS nilai_pembimbing_db,
pn.tugas_akhir,

la.status AS status_laporan,
la.file_laporan,

b.unit_kerja,
pb.nama AS pembimbing,

(SELECT COUNT(*) FROM absensi a WHERE a.pelajar_id = m.id) AS total_hari_absen,
(SELECT COUNT(*) FROM absensi a WHERE a.pelajar_id = m.id AND a.status IN ('hadir', 'izin', 'sakit')) AS total_masuk_absen,
(SELECT COUNT(*) FROM logbook lb WHERE lb.pelajar_id = m.id) AS total_logbook_diisi

FROM sertifikat s

LEFT JOIN pelajar m ON s.pelajar_id = m.id
LEFT JOIN instansi u ON m.instansi_id = u.id
LEFT JOIN jurusan ps ON m.jurusan_id = ps.id
LEFT JOIN penilaian pn ON pn.pelajar_id = m.id
LEFT JOIN laporan_akhir la ON la.pelajar_id = m.id
LEFT JOIN pendaftaran pd ON pd.pelajar_id = m.id AND pd.status = 'diterima'
LEFT JOIN unit_kerja b ON pd.unit_kerja_id = b.id
LEFT JOIN pembimbing pb ON pd.pembimbing_id = pb.id

WHERE s.id='$id'
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($query) == 0) {
    header("Location:index.php");
    exit;
}

$row = mysqli_fetch_assoc($query);

// Mengambil langsung Nilai Akhir Final yang tersimpan dari tabel penilaian (sama persis dengan index)
$nilai_akhir_final = isset($row['nilai_pembimbing_db']) ? (float) $row['nilai_pembimbing_db'] : 0;

if ($nilai_akhir_final >= 85) {
    $nilaiClass = 'nilai-a';
    $predikat = 'A (Sangat Baik)';
} elseif ($nilai_akhir_final >= 75) {
    $nilaiClass = 'nilai-b';
    $predikat = 'B (Baik)';
} elseif ($nilai_akhir_final >= 60) {
    $nilaiClass = 'nilai-c';
    $predikat = 'C (Cukup)';
} else {
    $nilaiClass = 'nilai-d';
    $predikat = 'D / E (Kurang)';
}

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<style>
.nilai-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 55px;
    padding: 0.35rem 0.8rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.85rem;
}
.nilai-a { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.nilai-b { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.nilai-c { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.nilai-d { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>

<div class="container-fluid">

    <div class="card shadow border-0 rounded-4">

        <div class="card-header bg-primary text-white rounded-top-4 py-3">
            <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Detail Sertifikat Pelajar</h4>
        </div>

        <div class="card-body p-4">

            <table class="table table-bordered align-middle">
                <tr>
                    <th width="250" class="bg-light">Nomor Sertifikat</th>
                    <td><span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($row['nomor_sertifikat']); ?></span></td>
                </tr>
                <tr>
                    <th class="bg-light">Nama Pelajar</th>
                    <td><?= htmlspecialchars($row['nama']); ?></td>
                </tr>
                <tr>
                    <th class="bg-light">NIM</th>
                    <td><?= htmlspecialchars($row['nim']); ?></td>
                </tr>
                <tr>
                    <th class="bg-light">Instansi</th>
                    <td><?= !empty($row['nama_instansi']) ? htmlspecialchars($row['nama_instansi']) : '-'; ?></td>
                </tr>
                <tr>
                    <th class="bg-light">Jurusan</th>
                    <td><?= !empty($row['nama_jurusan']) ? htmlspecialchars($row['nama_jurusan']) : '-'; ?></td>
                </tr>
                <tr>
                    <th class="bg-light">Unit Kerja</th>
                    <td><?= !empty($row['unit_kerja']) ? htmlspecialchars($row['unit_kerja']) : '-'; ?></td>
                </tr>
                <tr>
                    <th class="bg-light">Pembimbing Lapangan</th>
                    <td><?= !empty($row['pembimbing']) ? htmlspecialchars($row['pembimbing']) : '-'; ?></td>
                </tr>
                <tr>
                    <th class="bg-light">Nilai Akhir Final (Akumulasi)</th>
                    <td>
                        <span class="nilai-badge <?= $nilaiClass; ?>">
                            <?= number_format($nilai_akhir_final, 2); ?>
                        </span>
                        <span class="ms-2 text-muted fw-semibold">(Predikat: <?= $predikat; ?>)</span>
                    </td>
                </tr>
                <tr>
                    <th class="bg-light">Tanggal Terbit</th>
                    <td><?= !empty($row['tanggal_terbit']) ? date("d-m-Y", strtotime($row['tanggal_terbit'])) : '-'; ?></td>
                </tr>
                <tr>
                    <th class="bg-light">File PDF Sertifikat</th>
                    <td>
                        <?php if (!empty($row['file_pdf'])): ?>
                            <a href="../../assets/upload/sertifikat/<?= htmlspecialchars($row['file_pdf']); ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-file-pdf-fill me-1"></i> Lihat / Download PDF
                            </a>
                        <?php else: ?>
                            <span class="text-muted">File belum tersedia</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <div class="text-end mt-4">
                <a href="index.php" class="btn btn-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

        </div>

    </div>

</div>

<?php

include "../../include/footer.php";

?>

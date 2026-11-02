<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    echo "<script>alert('ID tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// Query mengambil data penilaian secara langsung dari tabel penilaian
$query_penilaian = mysqli_query($conn, "
SELECT

pn.*,
pn.nilai_akhir AS nilai_pembimbing_db,
pn.tugas_akhir,

m.id AS pelajar_id,
m.nama,
m.nim,
m.email,
m.no_hp,

u.nama_instansi,
ps.nama_jurusan,
uk.unit_kerja AS nama_bidang,

pb.nama AS nama_pembimbing,
pb.nip

FROM penilaian pn

LEFT JOIN pelajar m
ON pn.pelajar_id = m.id

LEFT JOIN instansi u
ON m.instansi_id = u.id

LEFT JOIN jurusan ps
ON m.jurusan_id = ps.id

LEFT JOIN pembimbing pb
ON pn.pembimbing_id = pb.id

LEFT JOIN pendaftaran pd
ON pd.pelajar_id = m.id
AND pd.status='diterima'

LEFT JOIN unit_kerja uk
ON pd.unit_kerja_id = uk.id

WHERE pn.id = '$id'
");

if (!$query_penilaian) {
    die("Query Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($query_penilaian) == 0) {
    echo "<script>
    alert('Data tidak ditemukan.');
    window.location='index.php';
    </script>";
    exit;
}

$penilaian = mysqli_fetch_assoc($query_penilaian);

// 1. Ambil nilai sub-komponen yang diinput pembimbing
$disiplin        = isset($penilaian['disiplin']) ? (float) $penilaian['disiplin'] : 0;
$komunikasi      = isset($penilaian['komunikasi']) ? (float) $penilaian['komunikasi'] : 0;
$kerjasama       = isset($penilaian['kerjasama']) ? (float) $penilaian['kerjasama'] : 0;
$tanggung_jawab  = isset($penilaian['tanggung_jawab']) ? (float) $penilaian['tanggung_jawab'] : 0;
$inisiatif       = isset($penilaian['inisiatif']) ? (float) $penilaian['inisiatif'] : 0;

// Hitung Rata-rata Sub-Komponen Pembimbing
$sub_komponen_ada = ($disiplin > 0 || $komunikasi > 0 || $kerjasama > 0 || $tanggung_jawab > 0 || $inisiatif > 0);
$nilai_pembimbing = $sub_komponen_ada ? (($disiplin + $komunikasi + $kerjasama + $tanggung_jawab + $inisiatif) / 5) : (float) $penilaian['nilai_pembimbing_db'];

// 2. Skor Kehadiran yang diinput pembimbing (misal disimpan di kolom absensi atau kita sediakan kolomnya)
// Jika di database kolomnya bernama 'absensi' atau 'kehadiran', sesuaikan di sini:
$skor_kehadiran = isset($penilaian['absensi']) ? (float) $penilaian['absensi'] : 0;

// 3. Skor Logbook yang diinput pembimbing
$skor_logbook = isset($penilaian['logbook']) ? (float) $penilaian['logbook'] : 0;

// 4. Skor Tugas Akhir yang diinput pembimbing
$skor_tugas_akhir = isset($penilaian['tugas_akhir']) ? (float) $penilaian['tugas_akhir'] : 0;

// Rumus Akumulasi Nilai Akhir Final (Pembimbing 45%, Kehadiran 15%, Logbook 15%, Tugas Akhir 25%)
$nilai_akhir_final = (
    ($nilai_pembimbing * 0.45) + 
    ($skor_kehadiran * 0.15) + 
    ($skor_logbook * 0.15) + 
    ($skor_tugas_akhir * 0.25)
);

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

    <div class="card shadow mb-4">

        <div class="card-header bg-info text-white">
            <h4 class="mb-0">
                <i class="bi bi-file-text me-2"></i> Detail Penilaian & Akumulasi Nilai PKL
            </h4>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="fw-bold">Nama Pelajar</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($penilaian['nama']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">NIM/NIS</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($penilaian['nim']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Instansi</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($penilaian['nama_instansi']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Jurusan</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($penilaian['nama_jurusan']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">No HP</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($penilaian['no_hp']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Email</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($penilaian['email']); ?>" readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="fw-bold">Unit Kerja</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($penilaian['nama_bidang']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Pembimbing</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($penilaian['nama_pembimbing']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">NIP Pembimbing</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($penilaian['nip']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-success">Nilai Akhir Final (Akumulasi)</label>
                        <input type="text" class="form-control fw-bold text-success fs-5"
                            value="<?= number_format($nilai_akhir_final, 2); ?>" readonly>
                    </div>
                </div>

            </div>

            <hr>

            <!-- Rincian Sub-Komponen Nilai Pembimbing -->
            <h5 class="mb-3 fw-bold text-primary">
                <i class="bi bi-list-check me-1"></i> Rincian Sub-Komponen Nilai Pembimbing
            </h5>
            <table class="table table-bordered mb-4">
                <tr>
                    <th width="35%">Disiplin</th>
                    <td><?= $penilaian['disiplin']; ?></td>
                </tr>
                <tr>
                    <th>Komunikasi</th>
                    <td><?= $penilaian['komunikasi']; ?></td>
                </tr>
                <tr>
                    <th>Kerjasama</th>
                    <td><?= $penilaian['kerjasama']; ?></td>
                </tr>
                <tr>
                    <th>Tanggung Jawab</th>
                    <td><?= $penilaian['tanggung_jawab']; ?></td>
                </tr>
                <tr>
                    <th>Inisiatif</th>
                    <td><?= $penilaian['inisiatif']; ?></td>
                </tr>
                <tr class="table-active">
                    <th>Rata-rata Sub-Komponen Pembimbing</th>
                    <td><strong><?= number_format($nilai_pembimbing, 2); ?></strong></td>
                </tr>
                <tr>
                    <th>Link Tugas Akhir Google Drive</th>
                    <td>
                        <?php if (!empty($penilaian['link_tugas_akhir'])): ?>
                            <a href="<?= htmlspecialchars($penilaian['link_tugas_akhir']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box-arrow-up-right"></i> Buka Link Drive Tugas Akhir
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Belum ada link yang dilampirkan</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <!-- Rincian Komponen & Bobot Penilaian -->
            <h5 class="mb-3 fw-bold text-primary">
                <i class="bi bi-calculator me-1"></i> Rincian Komponen & Bobot Penilaian (Input Pembimbing)
            </h5>

            <table class="table table-bordered table-striped align-middle mb-4">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Komponen Penilaian</th>
                        <th>Bobot</th>
                        <th>Skor Input Pembimbing</th>
                        <th>Nilai Kontribusi ((Skor × Bobot) / 100)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">1. Nilai Pembimbing Lapangan</td>
                        <td class="text-center">45%</td>
                        <td class="text-center"><?= number_format($nilai_pembimbing, 2); ?></td>
                        <td class="text-center fw-bold"><?= number_format($nilai_pembimbing * 0.45, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">2. Kehadiran / Absensi</td>
                        <td class="text-center">15%</td>
                        <td class="text-center"><?= number_format($skor_kehadiran, 2); ?></td>
                        <td class="text-center fw-bold"><?= number_format($skor_kehadiran * 0.15, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">3. Kelengkapan Logbook</td>
                        <td class="text-center">15%</td>
                        <td class="text-center"><?= number_format($skor_logbook, 2); ?></td>
                        <td class="text-center fw-bold"><?= number_format($skor_logbook * 0.15, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">4. Tugas Akhir / Laporan Google Drive</td>
                        <td class="text-center">25%</td>
                        <td class="text-center"><?= number_format($skor_tugas_akhir, 2); ?></td>
                        <td class="text-center fw-bold"><?= number_format($skor_tugas_akhir * 0.25, 2); ?></td>
                    </tr>
                    <tr class="table-success fw-bold">
                        <td colspan="3" class="text-end">NILAI AKHIR AKUMULASI (FINAL):</td>
                        <td class="text-center fs-5 text-success"><?= number_format($nilai_akhir_final, 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="mb-3 mt-3">
                <label class="fw-bold">Catatan Pembimbing</label>
                <textarea class="form-control" rows="4" readonly><?= htmlspecialchars($penilaian['catatan']); ?></textarea>
            </div>

            <div class="text-end">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

        </div>

    </div>

</div>

<?php

include "../../include/footer.php";

?>
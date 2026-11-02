<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$pelajar_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($pelajar_id)) {
    echo "<script>alert('ID Pelajar tidak valid!'); window.location='index.php';</script>";
    exit;
}

// Ambil data profil pelajar berdasarkan ID Pelajar
$query_mhs = mysqli_query($conn, "
SELECT 
    m.*, 
    u.nama_instansi, 
    ps.nama_jurusan, 
    uk.unit_kerja AS nama_bidang, 
    pb.nama AS nama_pembimbing,
    pb.nip
FROM pelajar m
LEFT JOIN instansi u ON m.instansi_id = u.id
LEFT JOIN jurusan ps ON m.jurusan_id = ps.id
LEFT JOIN pendaftaran p ON p.pelajar_id = m.id AND p.status = 'diterima'
LEFT JOIN unit_kerja uk ON p.unit_kerja_id = uk.id
LEFT JOIN pembimbing pb ON p.pembimbing_id = pb.id
WHERE m.id = '$pelajar_id'
");

if (mysqli_num_rows($query_mhs) == 0) {
    echo "<script>alert('Data pelajar tidak ditemukan.'); window.location='index.php';</script>";
    exit;
}

$mhs = mysqli_fetch_assoc($query_mhs);

// Ambil daftar hari libur nasional untuk pencocokan status
$q_libur = mysqli_query($conn, "SELECT * FROM hari_libur");
$data_libur = [];
if ($q_libur) {
    while ($row_l = mysqli_fetch_assoc($q_libur)) {
        $data_libur[$row_l['tanggal']] = $row_l['keterangan'];
    }
}

// Ambil seluruh riwayat absensi harian milik pelajar tersebut
$query_absen = mysqli_query($conn, "
SELECT * FROM absensi 
WHERE pelajar_id = '$pelajar_id' 
ORDER BY tanggal DESC, id DESC
");

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Detail Absensi Pelajar</h4>
            <!-- Tombol Cetak PDF dari Halaman Detail -->
            <a href="cetak_absensi.php?pelajar_id=<?= $pelajar_id; ?>" target="_blank" class="btn btn-light btn-sm fw-bold rounded-pill text-primary">
                <i class="bi bi-printer-fill me-1"></i> Cetak PDF Absensi
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Pelajar</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($mhs['nama']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">NIM/NIS</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($mhs['nim']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Instansi</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($mhs['nama_instansi'] ?? '-'); ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jurusan</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($mhs['nama_jurusan'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unit Kerja</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($mhs['nama_bidang'] ?? '-'); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pembimbing</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($mhs['nama_pembimbing'] ?? '-'); ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Harian Absensi Pelajar -->
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Riwayat Kehadiran Harian</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Foto Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Foto Pulang</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_absen) > 0) {
                            while ($row = mysqli_fetch_assoc($query_absen)) {
                                $tgl_row = $row['tanggal'];
                                $is_libur_nasional = isset($data_libur[$tgl_row]);
                                $ket_row = $row['keterangan'];
                                
                                if ($is_libur_nasional && (empty($ket_row) || $ket_row == '-')) {
                                    $ket_row = $data_libur[$tgl_row];
                                }
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d-m-Y', strtotime($tgl_row)); ?></td>
                                    <td><span class="text-success fw-bold"><?= $row['jam_masuk'] ?: '-'; ?></span></td>
                                    <td>
                                        <?php
                                        if (!empty($row['foto_masuk'])) {
                                            $is_base64 = (strpos($row['foto_masuk'], 'data:image') === 0);
                                            $path = $is_base64 ? $row['foto_masuk'] : "../../assets/upload/absensi/" . $row['foto_masuk'];
                                            echo '<a href="' . $path . '" target="_blank"><img src="' . $path . '" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;"></a>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><span class="text-danger fw-bold"><?= $row['jam_pulang'] ?: '-'; ?></span></td>
                                    <td>
                                        <?php
                                        if (!empty($row['foto_pulang'])) {
                                            $is_base64 = (strpos($row['foto_pulang'], 'data:image') === 0);
                                            $path = $is_base64 ? $row['foto_pulang'] : "../../assets/upload/absensi/" . $row['foto_pulang'];
                                            echo '<a href="' . $path . '" target="_blank"><img src="' . $path . '" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;"></a>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $st = strtolower($row['status']);
                                        if ($is_libur_nasional || $st == 'libur' || $st == 'libur_nasional') {
                                            echo '<span class="badge bg-danger">Libur Nasional</span>';
                                        } else {
                                            switch ($st) {
                                                case "hadir":
                                                    echo '<span class="badge bg-success">Hadir</span>';
                                                    break;
                                                case "izin":
                                                    echo '<span class="badge bg-warning text-dark">Izin</span>';
                                                    break;
                                                case "sakit":
                                                    echo '<span class="badge bg-info">Sakit</span>';
                                                    break;
                                                case "alpha":
                                                    echo '<span class="badge bg-danger">Alpha</span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge bg-secondary">' . ucfirst($row['status']) . '</span>';
                                                    break;
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td class="text-start"><?= htmlspecialchars($ket_row ?? '-'); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="8" class="text-muted py-4">Belum ada riwayat absensi harian untuk pelajar ini.</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Tombol Aksi Bawah -->
            <div class="text-end mt-3 d-flex justify-content-end gap-2">
                <a href="cetak_absensi.php?pelajar_id=<?= $pelajar_id; ?>" target="_blank" class="btn btn-danger">
                    <i class="bi bi-printer me-1"></i> Cetak PDF
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

</div>

<?php
include "../../include/footer.php";
?>
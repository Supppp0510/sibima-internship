<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    $current_page = basename($_SERVER['PHP_SELF']);

    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

    // =========================================================================
    // PROSES TAMBAH & HAPUS HARI LIBUR NASIONAL / TANGGAL MERAH
    // =========================================================================
    if (isset($_POST['tambah_libur'])) {
        $tanggal    = mysqli_real_escape_string($conn, $_POST['tanggal']);
        $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
        $cek = mysqli_query($conn, "SELECT id FROM hari_libur WHERE tanggal = '$tanggal'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Tanggal libur tersebut sudah tercatat!',
                        confirmButtonColor: '#2563eb'
                    }).then(() => { window.location='$current_page'; });
                }); </script>";
        } else {
            $ins = mysqli_query($conn, "INSERT INTO hari_libur (tanggal, keterangan) VALUES ('$tanggal', '$keterangan')");
            if ($ins) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Tanggal merah berhasil ditambahkan!',
                            confirmButtonColor: '#2563eb'
                        }).then(() => { window.location='$current_page'; });
                    }); </script>";
            }
        }
        exit;
    }

    if (isset($_GET['hapus_libur'])) {
        $id_libur = mysqli_real_escape_string($conn, $_GET['hapus_libur']);
        $del = mysqli_query($conn, "DELETE FROM hari_libur WHERE id = '$id_libur'");
        if ($del) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Tanggal merah berhasil dihapus!',
                        confirmButtonColor: '#2563eb'
                    }).then(() => { window.location='$current_page'; });
                });
            </script>";
        }
        exit;
    }

    // Statistik ringkas untuk widget
    $stat = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT 
            (SELECT COUNT(DISTINCT pelajar_id) FROM pendaftaran WHERE status = 'diterima') AS total_pelajar,
            (SELECT COUNT(*) FROM absensi) AS total_absensi
    "));
    $totalPelajar =$stat['total_pelajar'] ?? 0;
    $totalAbsensi =$stat['total_absensi'] ?? 0;

    // Ambil data pelajar dan urutkan berdasarkan yang paling terakhir absen (last_absen_id DESC)
    $query = mysqli_query($conn, "
        SELECT
            m.id AS pelajar_id,
            m.nama,
            m.nim,
            u.nama_instansi,
            ps.nama_jurusan,
            uk.unit_kerja AS nama_bidang,
            pb.nama AS nama_pembimbing,
            (SELECT COUNT(*) FROM absensi a WHERE a.pelajar_id = m.id) AS total_absen,
            (SELECT MAX(id) FROM absensi a WHERE a.pelajar_id = m.id) AS last_absen_id
        FROM pelajar m
        LEFT JOIN instansi u ON m.instansi_id = u.id
        LEFT JOIN jurusan ps ON m.jurusan_id = ps.id
        LEFT JOIN pendaftaran p ON p.pelajar_id = m.id AND p.status = 'diterima'
        LEFT JOIN unit_kerja uk ON p.unit_kerja_id = uk.id
        LEFT JOIN pembimbing pb ON p.pembimbing_id = pb.id
        WHERE p.status = 'diterima'
        ORDER BY last_absen_id DESC, m.nama ASC
    ");

    if (!$query) {
        die("Query Error: " . mysqli_error($conn));
    }
// Ambil data pembimbing untuk rekomendasi penanggung jawab TTD
    $q_pembimbing_ttd = mysqli_query($conn, "SELECT id, nama, nip, nama_jabatan FROM pembimbing ORDER BY nama ASC");
    include "../../include/header.php";
    include "../../include/navbar.php";
    include "../../include/sidebar.php";

?>

<style>
/* ===== Header ===== */
.page-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    background: linear-gradient(135deg, #0f1f4d, #1d4ed8);
    border-radius: 14px;
    padding: 1.1rem 1.4rem;
    box-shadow: 0 4px 14px rgba(29, 78, 216, 0.25);
}

.page-header-bar h3 {
    font-weight: 700;
    color: #fff;
    margin: 0;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
}

.page-header-bar h3 i {
    font-size: 1.1rem;
    margin-right: 0.6rem !important;
}

.page-header-bar p {
    color: #cbd5e1;
    font-size: 0.85rem;
    margin: 0.25rem 0 0;
}

/* ===== Stat Cards ===== */
.stat-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 1rem 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.9rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
}

.stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #fff;
    flex-shrink: 0;
}

.stat-icon.blue {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

.stat-icon.teal {
    background: linear-gradient(135deg, #0d9488, #0f766e);
}

.stat-card small {
    display: block;
    color: #94a3b8;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.stat-card h4 {
    margin: 0.1rem 0 0;
    font-weight: 700;
    color: #1e293b;
}

/* ===== Table ===== */
.card-table {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
}

table.datatable {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100% !important;
}

table.datatable thead th {
    background: #2563eb;
    color: #fff;
    border: none !important;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
    text-align: center;
    border-right: 1px solid rgba(255, 255, 255, 0.15) !important;
}

table.datatable thead th:last-child {
    border-right: none !important;
}

table.datatable thead th.text-left-col {
    text-align: left !important;
}

table.datatable tbody td {
    vertical-align: middle;
    font-size: 0.88rem;
    border-color: #eef1f5 !important;
    text-align: center;
    border-right: 1px solid #eef1f5;
}

table.datatable tbody td:last-child {
    border-right: none;
}

table.datatable tbody tr:hover {
    background: #f8fafc;
}

.mhs-name { font-weight: 600; color: #1e293b; display: block; text-align: left; }
.mhs-nim { color: #94a3b8; font-size: 0.78rem; font-family: 'Courier New', monospace; display: block; text-align: left; }
table.datatable tbody td.text-left-col { text-align: left !important; }

.badge-absen {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    white-space: nowrap;
}

.action-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.btn-detail {
    border-radius: 8px;
    font-size: 0.85rem;
    padding: 0.35rem 0.5rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}

.btn-detail:hover {
    background: #1d4ed8;
    color: #fff;
}

.btn-print {
    border-radius: 8px;
    font-size: 0.85rem;
    padding: 0.35rem 0.5rem;
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fca5a5;
}

.btn-print:hover {
    background: #dc2626;
    color: #fff;
}

.empty-state {
    padding: 2.5rem 1rem;
    color: #94a3b8;
}

.empty-state i {
    font-size: 2.2rem;
    display: block;
    margin-bottom: 0.6rem;
}

@media (max-width: 768px) {
    .stat-row {
        grid-template-columns: 1fr;
    }

    .page-header-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.9rem;
    }
}
</style>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-person-lines-fill me-2"></i>Rekapitulasi Absensi Pelajar</h3>
            <p>Daftar seluruh pelajar magang beserta ringkasan kehadiran harian mereka.</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Tombol 1: Cetak Rekap Kolektif (Membuka Modal Pilihan Cetak) -->
            <button type="button" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#modalCetakKolektif">
                <i class="bi bi-printer-fill me-1"></i> Cetak Rekap Kolektif
            </button>
            <!-- Tombol 2: Kelola Tanggal Merah (Membuka Modal Hari Libur) -->
            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalHariLibur">
                <i class="bi bi-calendar-event me-1"></i> Kelola Tanggal Merah
            </button>
        </div>
    </div>

    <!-- Widget Statistik -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people"></i></div>
            <div>
                <small>Total Pelajar Aktif</small>
                <h4><?php echo $totalPelajar; ?> Orang</h4>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal"><i class="bi bi-check2-square"></i></div>
            <div>
                <small>Total Riwayat Absensi</small>
                <h4><?php echo $totalAbsensi; ?> Catatan</h4>
            </div>
        </div>
    </div>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                
                <table id="tabelRekapAbsensi" class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th width="3%">No</th>
                            <th class="text-left-col" width="18%">Pelajar</th>
                            <th class="text-left-col" width="20%">Instansi</th>
                            <th class="text-left-col" width="12%">Jurusan</th>
                            <th class="text-left-col" width="20%">Unit Kerja</th>
                            <th class="text-left-col" width="12%">Pembimbing</th>
                            <th style="width: 10%; text-align: center;">Total Hadir</th>
                            <th width="8%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    
                                    <td class="text-left-col">
                                        <span class="mhs-name text-truncate d-inline-block w-100" style="max-width: 150px;" title="<?= htmlspecialchars($row['nama']); ?>"><?= htmlspecialchars($row['nama']); ?></span>
                                        <span class="mhs-nim text-truncate d-inline-block w-100" style="max-width: 150px;"><?= htmlspecialchars($row['nim']); ?></span>
                                    </td>
                                    
                                    <td class="text-left-col text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($row['nama_instansi'] ?? '-'); ?>">
                                        <?= !empty($row['nama_instansi']) ? htmlspecialchars($row['nama_instansi']) : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    
                                    <td class="text-left-col text-truncate" style="max-width: 120px;" title="<?= htmlspecialchars($row['nama_jurusan'] ?? '-'); ?>">
                                        <?= !empty($row['nama_jurusan']) ? htmlspecialchars($row['nama_jurusan']) : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    
                                    <td class="text-left-col text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($row['nama_bidang'] ?? '-'); ?>">
                                        <?= !empty($row['nama_bidang']) ? htmlspecialchars($row['nama_bidang']) : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    
                                    <td class="text-left-col text-truncate" style="max-width: 120px;" title="<?= htmlspecialchars($row['nama_pembimbing'] ?? '-'); ?>">
                                        <?= !empty($row['nama_pembimbing']) ? htmlspecialchars($row['nama_pembimbing']) : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    
                                    <td>
                                        <span class="badge-absen"><?= $row['total_absen']; ?> Hari</span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="action-group">
                                            <!-- Tombol Lihat Detail Absensi -->
                                            <a href="detail.php?id=<?= $row['pelajar_id']; ?>" class="btn btn-detail shadow-sm" data-bs-toggle="tooltip" title="Lihat Detail Absensi">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <!-- Tombol Cetak PDF Absensi Individu -->
                                            <a href="cetak_absensi.php?pelajar_id=<?= $row['pelajar_id']; ?>" target="_blank" class="btn btn-print shadow-sm" data-bs-toggle="tooltip" title="Cetak PDF Absensi Pelajar Ini">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL KELOLA HARI LIBUR / TANGGAL MERAH -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalHariLibur" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-danger text-white rounded-top">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar-x-fill me-2"></i>Kelola Tanggal Merah / Libur Nasional</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="<?= $current_page; ?>" method="POST" class="row g-3 mb-4 bg-light p-3 rounded border">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Tanggal Libur</label>
                        <input type="date" name="tanggal" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-bold">Keterangan / Nama Libur</label>
                        <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Contoh: Hari Kemerdekaan RI" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" name="tambah_libur" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Libur
                        </button>
                    </div>
                </form>

                <h6 class="fw-bold mb-3"><i class="bi bi-list-ul me-1"></i>Daftar Tanggal Merah Terdaftar</h6>
                <div class="table-responsive" style="max-height: 250px;">
                    <table class="table table-hover table-bordered align-middle text-center mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="50">No</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_list_libur = mysqli_query($conn, "SELECT * FROM hari_libur ORDER BY tanggal DESC");
                            if ($q_list_libur && mysqli_num_rows($q_list_libur) > 0) {$no_l = 1;
                                while ($hl = mysqli_fetch_assoc($q_list_libur)) {
                            ?>
                                    <tr>
                                        <td><?= $no_l++; ?></td>
                                        <td class="fw-bold text-danger"><?= date('d M Y', strtotime($hl['tanggal'])); ?></td>
                                        <td><?= htmlspecialchars($hl['keterangan']); ?></td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-hapus-libur" data-id="<?= $hl['id']; ?>" title="Hapus">
                                            <i class="bi bi-trash-fill"></i> </button> </td>
                                    </tr>
                            <?php 
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="4" class="text-muted small py-3">Belum ada tanggal merah yang diinput.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light rounded-bottom">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FILTER PERIODE CETAK REKAP KOLEKTIF -->
<div class="modal fade" id="modalCetakKolektif" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white rounded-top">
                <h5 class="modal-title fw-bold"><i class="bi bi-printer-fill me-2"></i>Pilih Opsi Cetak Rekap Kolektif</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="cetak_rekap_kolektif.php" method="GET" target="_blank">
                <div class="modal-body p-4">
                    <!-- Opsi Tipe Cetak -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tipe Laporan</label>
                        <div class="d-flex gap-4 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_cetak" id="cetakSemua" value="semua" checked>
                                <label class="form-check-label small fw-semibold" for="cetakSemua">Cetak Keseluruhan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_cetak" id="cetakPeriode" value="periode">
                                <label class="form-check-label small fw-semibold" for="cetakPeriode">Cetak Per Periode</label>
                            </div>
                        </div>
                    </div>

                    <!-- Input Filter Tanggal -->
                    <div id="boxFilterTanggal" class="row g-3 d-none pt-3 border-top mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Dari Tanggal</label>
                            <input type="date" name="tgl_mulai" id="tglMulai" class="form-control form-control-sm" value="<?= date('Y-m-01'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" id="tglSelesai" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <!-- Input Penanggung Jawab TTD (Datalist Autocomplete) -->
                    <div class="pt-2 border-top">
                        <label class="form-label small fw-bold">Penanggung Jawab / TTD Laporan</label>
                        <input type="text" name="ttd_nama" list="listPembimbing" class="form-control form-control-sm" placeholder="Ketik nama / NIP penanggung jawab..." autocomplete="off">
                        <datalist id="listPembimbing">
                            <?php 
                            if ($q_pembimbing_ttd && mysqli_num_rows($q_pembimbing_ttd) > 0) {
                                while ($pb_ttd = mysqli_fetch_assoc($q_pembimbing_ttd)) {
                                    $label_nip = !empty($pb_ttd['nip']) ? ' (NIP: ' . $pb_ttd['nip'] . ')' : '';
                                    echo '<option value="' . htmlspecialchars($pb_ttd['nama']) . '">' . htmlspecialchars($pb_ttd['nama']) . $label_nip . '</option>';
                                }
                            }
                            ?>
                        </datalist>
                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Pilih dari daftar rekomendasi atau ketik langsung nama penanggung jawab.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-printer me-1"></i> Cetak Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include "../../include/footer.php"; ?>

<script>
$(document).ready(function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });

    // Toggle Tampilan Filter Tanggal berdasarkan Radio Button Modal Cetak
    $('input[name="jenis_cetak"]').on('change', function() {
        if ($('#cetakPeriode').is(':checked')) {
            $('#boxFilterTanggal').removeClass('d-none');
            $('#tglMulai, #tglSelesai').prop('required', true);
        } else {
            $('#boxFilterTanggal').addClass('d-none');
            $('#tglMulai, #tglSelesai').prop('required', false);
        }
    });

    $('#tabelRekapAbsensi').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: `
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    Belum ada data absensi pelajar
                </div>
            `
        },
        columnDefs: [
            { orderable: false, targets: [7] }
        ]
    });

    $(document).on('click', '.btn-hapus-libur', function(e) {
        e.preventDefault();
        var idLibur = $(this).data('id');

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Tanggal merah ini akan dihapus dari sistem.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#dc2626',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= $current_page; ?>?hapus_libur=' + idLibur;
            }
        });
    });
});
</script>
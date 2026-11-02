<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // Statistik ringkas untuk widget
    $stat = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT 
            (SELECT COUNT(DISTINCT pelajar_id) FROM pendaftaran WHERE status = 'diterima') AS total_pelajar,
            (SELECT COUNT(*) FROM logbook) AS total_logbook_all
    "));
    $totalPelajar = $stat['total_pelajar'] ?? 0;
    $totalLogbookAll = $stat['total_logbook_all'] ?? 0;

    // Ambil data pelajar dan urutkan berdasarkan yang paling terakhir mengisi logbook (last_logbook_id DESC)
    $query = mysqli_query($conn, "
        SELECT
            m.id AS pelajar_id,
            m.nama,
            m.nim,
            u.nama_instansi,
            ps.nama_jurusan,
            uk.unit_kerja AS nama_bidang,
            pb.nama AS nama_pembimbing,
            (SELECT COUNT(*) FROM logbook l WHERE l.pelajar_id = m.id) AS total_logbook,
            (SELECT MAX(id) FROM logbook l WHERE l.pelajar_id = m.id) AS last_logbook_id
        FROM pelajar m
        LEFT JOIN instansi u ON m.instansi_id = u.id
        LEFT JOIN jurusan ps ON m.jurusan_id = ps.id
        LEFT JOIN pendaftaran p ON p.pelajar_id = m.id AND p.status = 'diterima'
        LEFT JOIN unit_kerja uk ON p.unit_kerja_id = uk.id
        LEFT JOIN pembimbing pb ON p.pembimbing_id = pb.id
        WHERE p.status = 'diterima'
        ORDER BY last_logbook_id DESC, m.nama ASC
    ");

    if (!$query) {
        die("Query Error: " . mysqli_error($conn));
    }

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

/* REVISI: Perataan Kiri untuk Header */
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

/* Penyesuaian Kolom Khusus Logbook */
.mhs-name { font-weight: 600; color: #1e293b; display: block; text-align: left; }
.mhs-nim { color: #94a3b8; font-size: 0.78rem; font-family: 'Courier New', monospace; display: block; text-align: left; }
table.datatable tbody td.text-left-col { text-align: left !important; }

/* Menyesuaikan style dengan badge unit kerja & absensi */
.badge-logbook {
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

/* REVISI: Tombol aksi diringkas menjadi ikon saja */
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
            <h3><i class="bi bi-journal-check me-2"></i>Data Logbook PKL</h3>
            <p>Pantau rekapitulasi logbook harian pelajar PKL di sini.</p>
        </div>
    </div>

    <!-- Widget Statistik Persis Seperti Absensi & Unit Kerja -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people"></i></div>
            <div>
                <small>Total Pelajar Aktif</small>
                <h4><?php echo $totalPelajar; ?> Orang</h4>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal"><i class="bi bi-pencil-square"></i></div>
            <div>
                <small>Total Riwayat Logbook</small>
                <h4><?php echo $totalLogbookAll; ?> Entri</h4>
            </div>
        </div>
    </div>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                
                <table id="tabelLogbook" class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th width="3%">No</th>
                            <!-- REVISI: Penyesuaian persentase lebar tabel dan penambahan rata kiri -->
                            <th class="text-left-col" width="18%">Pelajar</th>
                            <th class="text-left-col" width="20%">Instansi</th>
                            <th class="text-left-col" width="12%">Jurusan</th>
                            <th class="text-left-col" width="20%">Unit Kerja</th>
                            <th class="text-left-col" width="12%">Pembimbing</th>
                            <th style="width: 10%; text-align: center;">Total Logbook</th>
                            <th width="5%" class="text-center">Aksi</th>
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
                                    
                                    <!-- REVISI: Pembatasan lebar teks pelajar dengan text-truncate -->
                                    <td class="text-left-col">
                                        <span class="mhs-name text-truncate d-inline-block w-100" style="max-width: 150px;" title="<?= htmlspecialchars($row['nama']); ?>"><?= htmlspecialchars($row['nama']); ?></span>
                                        <span class="mhs-nim text-truncate d-inline-block w-100" style="max-width: 150px;"><?= htmlspecialchars($row['nim']); ?></span>
                                    </td>
                                    
                                    <!-- REVISI: Pemotongan teks yang terlalu panjang -->
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
                                        <span class="badge-logbook"><?= $row['total_logbook']; ?> Entri</span>
                                    </td>
                                    
                                    <!-- REVISI: Tombol aksi diringkas menjadi ikon mata dan ditambahkan tooltip -->
                                    <td class="text-center">
                                        <div class="action-group">
                                            <a href="detail.php?id=<?= $row['pelajar_id']; ?>" class="btn btn-detail shadow-sm" data-bs-toggle="tooltip" title="Lihat Detail Logbook">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        // Tanpa else colspan, empty state diserahkan ke JS DataTables
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<?php include "../../include/footer.php"; ?>

<script>
$(document).ready(function() {
    
    // REVISI: Inisialisasi Tooltip Bootstrap untuk tombol aksi
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });

    // DataTables Config 100% Mengikuti Template Sebelumnya
    $('#tabelLogbook').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: `
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    Belum ada data logbook pelajar
                </div>
            `
        },
        columnDefs: [
            { orderable: false, targets: [7] } // Mematikan urut (sort) pada kolom Aksi
        ]
    });
});
</script>
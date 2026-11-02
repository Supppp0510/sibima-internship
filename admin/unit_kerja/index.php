<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // Helper: pilih ikon berdasarkan nama Unit Kerja
    function iconUnitKerja($nama)
    {
    $nama = strtolower($nama);
    if (str_contains($nama, 'teknologi') || str_contains($nama, 'informat')) {
        return 'bi-cpu';
    }

    if (str_contains($nama, 'sekretar')) {
        return 'bi-folder2-open';
    }

    if (str_contains($nama, 'keuangan')) {
        return 'bi-cash-coin';
    }

    if (str_contains($nama, 'humas')) {
        return 'bi-megaphone';
    }

    return 'bi-briefcase';
    }

    // Ambil semua data unit kerja
    $data = mysqli_query($conn, "
SELECT *
FROM unit_kerja
ORDER BY id DESC
");

    if (! $data) {
    die(mysqli_error($conn));
    }

    // Statistik ringkas
    $stat = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_unitkerja, SUM(kuota) AS total_kuota
    FROM unit_kerja
"));
    $totalUnitKerja = $stat['total_unitkerja'] ?? 0;
    $totalKuota     = $stat['total_kuota'] ?? 0;

    include "../../include/header.php";
    include "../../include/navbar.php";
    include "../../include/sidebar.php";

?>

<style>
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

.page-header-bar .btn-tambah {
    background: #fff;
    color: #1d4ed8;
    box-shadow: none;
}

.page-header-bar .btn-tambah:hover {
    background: #eff6ff;
    color: #1d4ed8;
}

@media (max-width: 768px) {
    .page-header-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.9rem;
    }

    .page-header-bar .btn-tambah {
        align-self: stretch;
        text-align: center;
    }
}

.btn-tambah {
    border-radius: 10px;
    font-weight: 600;
    padding: 0.55rem 1.1rem;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    border: none;
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
    color: #fff;
}

.btn-tambah:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    color: #fff;
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

.badge-kuota {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    white-space: nowrap;
}

table.datatable tbody td.text-left-col {
    text-align: left !important;
}

.unit-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: #eff6ff;
    color: #1d4ed8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.5rem;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.action-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.action-group .btn {
    border-radius: 8px;
    font-size: 0.85rem;
    padding: 0.35rem 0.5rem;
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
}
</style>

<?php
    if (isset($_GET['success'])) {
    ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Data berhasil disimpan.',
        timer: 2000,
        showConfirmButton: false
    });
});
</script>
<?php
    }
?>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-building me-2"></i>Data Unit Kerja</h3>
            <p>Kelola daftar Unit Kerja penempatan PKL di sini.</p>
        </div>

        <a href="tambah.php" class="btn btn-tambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Unit Kerja
        </a>
    </div>

    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-diagram-3"></i></div>
            <div>
                <small>Total Unit Kerja</small>
                <h4><?php echo $totalUnitKerja; ?></h4>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal"><i class="bi bi-people"></i></div>
            <div>
                <small>Total Kuota</small>
                <h4><?php echo $totalKuota; ?> Orang</h4>
            </div>
        </div>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelUnitKerja" class="table table-hover datatable">

                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <!-- REVISI: Pembagian lebar kolom agar proporsional -->
                            <th width="23%">Unit Kerja</th>
                            <th class="text-left-col" width="18%">Eselon II</th>
                            <th class="text-left-col" width="22%">Eselon III</th>
                            <th class="text-left-col" width="18%">Eselon IV</th>
                            <th style="width: 8%; text-align: center;">Kuota</th>
                            <th width="6%">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                            $no = 1;
                            if (mysqli_num_rows($data) > 0) {
                                while ($row = mysqli_fetch_assoc($data)) {
                                    $iconClass = iconUnitKerja($row['unit_kerja']);
                                ?>
                        <tr>
                            <td><?php echo $no++; ?></td>

                            <!-- REVISI: Flexbox sejajar, max-width, dan truncate -->
                            <td style="max-width: 200px;">
                                <a href="detail_penempatan.php?id=<?php echo $row['id']; ?>"
                                    class="text-decoration-none d-flex align-items-center justify-content-center" title="<?php echo htmlspecialchars($row['unit_kerja']); ?>">
                                    <span class="unit-icon"><i class="bi <?php echo $iconClass; ?>"></i></span>
                                    <span class="fw-bold text-dark text-truncate text-start" style="max-width: 140px;"><?php echo htmlspecialchars($row['unit_kerja']); ?></span>
                                </a>
                            </td>

                            <!-- REVISI: Truncate untuk teks panjang Eselon -->
                            <td class="text-left-col text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($row['unit_kerja_es_ii'] ?? ''); ?>">
                                <?php echo ! empty($row['unit_kerja_es_ii']) ? htmlspecialchars($row['unit_kerja_es_ii']) : '<span class="text-muted">-</span>'; ?>
                            </td>
                            <td class="text-left-col text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($row['unit_kerja_es_iii'] ?? ''); ?>">
                                <?php echo ! empty($row['unit_kerja_es_iii']) ? htmlspecialchars($row['unit_kerja_es_iii']) : '<span class="text-muted">-</span>'; ?>
                            </td>
                            <td class="text-left-col text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($row['unit_kerja_es_iv'] ?? ''); ?>">
                                <?php echo ! empty($row['unit_kerja_es_iv']) ? htmlspecialchars($row['unit_kerja_es_iv']) : '<span class="text-muted">-</span>'; ?>
                            </td>
                            
                            <td><span class="badge-kuota"><?php echo $row['kuota']; ?> Orang</span></td>
                            
                            <!-- REVISI: Tombol aksi diringkas menjadi ikon saja -->
                            <td>
                                <div class="action-group">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>"
                                        class="btn btn-warning btn-sm text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm btnHapus"
                                        data-id="<?php echo $row['id']; ?>" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
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

<?php
    include "../../include/footer.php";
?>

<script>
$(document).ready(function() {
    $('#tabelUnitKerja').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: `
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        Belum ada data Unit Kerja
                    </div>
                `
        }
    });

    $(document).on('click', '.btnHapus', function(e) {
        e.preventDefault();
        let id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'Data Unit Kerja ini tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = 'hapus.php?id=' + id;
            }
        });
    });
});
</script>
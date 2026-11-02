<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // Helper: pilih ikon berdasarkan nama Ruangan / Unit Kerja
    function iconRuangan($nama)
    {
    $nama = strtolower($nama);
    if (str_contains($nama, 'aula') || str_contains($nama, 'rapat')) {
        return 'bi-easel';
    }

    if (str_contains($nama, 'lab') || str_contains($nama, 'komputer')) {
        return 'bi-pc-display';
    }

    if (str_contains($nama, 'arsip')) {
        return 'bi-archive';
    }

    return 'bi-door-open';
    }

    // Ambil semua data ruangan beserta relasi ke unit_kerja
    $query_ruangan = mysqli_query($conn, "
        SELECT r.*, u.unit_kerja AS nama_unit_kerja
        FROM ruangan r
        LEFT JOIN unit_kerja u ON r.id_unit_kerja = u.id
        ORDER BY r.id_ruangan DESC
    ");

    if (! $query_ruangan) {
    die(mysqli_error($conn));
    }

    // Statistik ringkas
    $stat = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) AS total_ruangan, SUM(kuota) AS total_kuota_ruangan
        FROM ruangan
    "));
    $totalRuangan      = $stat['total_ruangan'] ?? 0;
    $totalKuotaRuangan = $stat['total_kuota_ruangan'] ?? 0;

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

.btn-tambah {
    border-radius: 10px;
    font-weight: 600;
    padding: 0.55rem 1.1rem;
    border: none;
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

/* REVISI: Perataan kiri pada header kolom */
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

.ruangan-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: #eff6ff;
    color: #1d4ed8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.6rem;
    font-size: 0.9rem;
    vertical-align: middle;
    flex-shrink: 0;
}

.action-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

/* REVISI: Diringkas menjadi hanya ikon */
.action-group .btn {
    border-radius: 8px;
    font-size: 0.85rem;
    padding: 0.35rem 0.5rem;
}

.empty-state {
    padding: 2.5rem 1rem;
    color: #94a3b8;
    text-align: center;
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

    .page-header-bar .btn-tambah {
        align-self: stretch;
        text-align: center;
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
        text: 'Data ruangan berhasil disimpan.',
        timer: 2000,
        showConfirmButton: false
    }).then(() => {
        // Bersihkan parameter ?success=tambah dari URL tanpa reload halaman
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location
            .pathname;
        window.history.replaceState({
            path: cleanUrl
        }, '', cleanUrl);
    });
});
</script>
<?php
    }
?>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-door-open me-2"></i>Data Ruangan</h3>
            <p>Kelola daftar Ruangan dan penempatan lokasinya di sini.</p>
        </div>

        <a href="tambah.php" class="btn btn-tambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Ruangan
        </a>
    </div>

    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-building"></i></div>
            <div>
                <small>Total Ruangan</small>
                <h4><?php echo $totalRuangan; ?></h4>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal"><i class="bi bi-people"></i></div>
            <div>
                <small>Total Kuota Ruangan</small>
                <h4><?php echo $totalKuotaRuangan; ?> Orang</h4>
            </div>
        </div>
    </div>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelRuangan" class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <!-- REVISI: Pembagian lebar kolom yang proporsional -->
                            <th class="text-left-col" width="30%">Unit Kerja</th>
                            <th class="text-left-col" width="25%">Nama Ruangan</th>
                            <th class="text-left-col" width="20%">Lokasi</th>
                            <th style="width: 10%; text-align: center;">Kuota</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no = 1;
                            if (mysqli_num_rows($query_ruangan) > 0) {
                                while ($ruangan = mysqli_fetch_assoc($query_ruangan)) {
                                    $iconClass = iconRuangan($ruangan['nama_ruangan']);
                                ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            
                            <!-- REVISI: Memotong teks Unit Kerja yang terlalu panjang -->
                            <td class="text-left-col text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($ruangan['nama_unit_kerja'] ?? '-'); ?>">
                                <?php echo ! empty($ruangan['nama_unit_kerja']) ? htmlspecialchars($ruangan['nama_unit_kerja']) : '<span class="text-muted">- Tanpa Unit Kerja -</span>'; ?>
                            </td>
                            
                            <!-- REVISI: Flex layout dan rata kiri untuk Nama Ruangan -->
                            <td class="text-left-col">
                                <a href="daftar_pelajar.php?id=<?php echo $ruangan['id_ruangan']; ?>"
                                    class="text-decoration-none d-flex align-items-center justify-content-start text-start" title="Lihat daftar pelajar di <?php echo htmlspecialchars($ruangan['nama_ruangan']); ?>">
                                    <span class="ruangan-icon"><i class="bi <?php echo $iconClass; ?>"></i></span>
                                    <span class="fw-bold text-dark text-truncate" style="max-width: 160px;"><?php echo htmlspecialchars($ruangan['nama_ruangan']); ?></span>
                                </a>
                            </td>
                            
                            <!-- REVISI: Memotong teks Lokasi jika terlalu panjang -->
                            <td class="text-left-col text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($ruangan['lokasi'] ?? '-'); ?>">
                                <?php echo ! empty($ruangan['lokasi']) ? htmlspecialchars($ruangan['lokasi']) : '<span class="text-muted">-</span>'; ?>
                            </td>
                            
                            <td><span class="badge-kuota"><?php echo $ruangan['kuota']; ?> Orang</span></td>
                            
                            <!-- REVISI: Tombol aksi diringkas menjadi ikon saja -->
                            <td>
                                <div class="action-group">
                                    <a href="edit.php?id=<?php echo $ruangan['id_ruangan']; ?>"
                                        class="btn btn-warning btn-sm text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm btnHapus"
                                        data-id="<?php echo $ruangan['id_ruangan']; ?>" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php
                            }
                            } // Tidak pakai else untuk DataTables empty state
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
    $('#tabelRuangan').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: `
                <div class="empty-state" style="padding: 2.5rem 1rem; color: #94a3b8; text-align: center;">
                    <i class="bi bi-inbox" style="font-size: 2.5rem; display: block; margin-bottom: 0.8rem; color: #cbd5e1;"></i>
                    Belum ada data Ruangan
                </div>
            `
        },
        columnDefs: [
            { orderable: false, targets: [5] } // Mematikan fitur urut (sort) pada kolom Aksi
        ]
    });

    $(document).on('click', '.btnHapus', function(e) {
        e.preventDefault();
        let id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'Data Ruangan ini tidak dapat dikembalikan.',
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
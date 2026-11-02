<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    $data = mysqli_query($conn, "
SELECT *
FROM instansi
ORDER BY id DESC
");

    // Statistik ringkas
    $statInstansi = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_instansi,
           COUNT(DISTINCT kota) AS total_kota
    FROM instansi
"));
    $totalInstansi = $statInstansi['total_instansi'] ?? 0;
    $totalKota     = $statInstansi['total_kota'] ?? 0;

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

.card-table {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
}

table.table-modern-instansi {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100% !important;
}

table.table-modern-instansi thead th {
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

table.table-modern-instansi thead th:last-child {
    border-right: none !important;
}

table.table-modern-instansi thead th.text-left-col {
    text-align: left;
}

table.table-modern-instansi tbody td {
    vertical-align: middle;
    font-size: 0.88rem;
    border-color: #eef1f5 !important;
    text-align: center;
    border-right: 1px solid #eef1f5;
}

table.table-modern-instansi tbody td.text-left-col {
    text-align: left !important;
}

table.table-modern-instansi tbody td:last-child {
    border-right: none;
}

table.table-modern-instansi tbody tr:hover {
    background: #f8fafc;
}

.instansi-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: #eff6ff;
    color: #1d4ed8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.7rem;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.instansi-name {
    font-weight: 600;
    color: #1e293b;
}

.instansi-alamat {
    color: #64748b;
    font-size: 0.85rem;
    max-width: 320px;
}

.badge-kota {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.action-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

/* REVISI: Tombol aksi diringkas menjadi ikon saja seperti di Unit Kerja */
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

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-mortarboard me-2"></i>Data Instansi</h3>
            <p>Kelola daftar instansi asal Pelajar magang di sini.</p>
        </div>

        <a href="tambah.php" class="btn btn-tambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Instansi
        </a>
    </div>

    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-mortarboard"></i></div>
            <div>
                <small>Total Instansi</small>
                <h4><?php echo $totalInstansi; ?></h4>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal"><i class="bi bi-geo-alt"></i></div>
            <div>
                <small>Total Kota</small>
                <h4><?php echo $totalKota; ?></h4>
            </div>
        </div>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelInstansi" class="table table-hover table-modern-instansi">

                    <thead>

                        <tr>

                            <th width="5%">No</th>
                            <!-- REVISI: Kolom nama instansi diratakan kiri -->
                            <th class="text-left-col" width="30%">Nama Instansi</th>
                            <th class="text-left-col">Alamat</th>
                            <th width="15%">Kota</th>
                            <th width="10%">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                            $no = 1;

                            if (mysqli_num_rows($data) > 0) {

                                while ($row = mysqli_fetch_assoc($data)) {

                                ?>

                        <tr>

                            <td><?php echo $no++; ?></td>

                            <!-- REVISI: Layout diubah menjadi flex & text-start agar sejajar rapi -->
                            <td class="text-left-col">
                                <a href="daftar_pelajar.php?id=<?php echo $row['id']; ?>"
                                    class="text-decoration-none d-flex align-items-center justify-content-start text-start" title="Lihat daftar pelajar dari <?php echo htmlspecialchars($row['nama_instansi']); ?>">
                                    <span class="instansi-icon"><i class="bi bi-bank"></i></span>
                                    <span class="instansi-name"><?php echo htmlspecialchars($row['nama_instansi']); ?></span>
                                </a>
                            </td>

                            <td class="text-left-col">
                                <span
                                    class="instansi-alamat"><?php echo ! empty($row['alamat']) ? htmlspecialchars($row['alamat']) : '<span class="text-muted">-</span>'; ?></span>
                            </td>

                            <td>
                                <!-- REVISI: Ikon kota disembunyikan jika datanya kosong -->
                                <?php if (!empty($row['kota'])): ?>
                                    <span class="badge-kota">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <?php echo htmlspecialchars($row['kota']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <!-- REVISI: Tombol diringkas hanya ikon -->
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

                            } else {

                            ?>

                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    Belum ada data instansi
                                </div>
                            </td>
                        </tr>

                        <?php

                            }

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
$(function() {

    $("#tabelInstansi").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: `
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        Belum ada data instansi
                    </div>
                ` // Memastikan empty state DataTables seragam
        },
        columnDefs: [
            { orderable: false, targets: [4] } // Mematikan fitur urut (sort) pada kolom Aksi
        ]
    });

    $(document).on('click', '.btnHapus', function(e) {

        e.preventDefault();

        let id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'Data Instansi ini tidak dapat dikembalikan.',
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
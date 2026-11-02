<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    $data = mysqli_query($conn, "
SELECT *
FROM periode_pkl
ORDER BY id DESC
");

    // Statistik ringkas
    $statPeriode = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_periode,
           SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) AS total_aktif,
           SUM(CASE WHEN status != 'aktif' THEN 1 ELSE 0 END) AS total_selesai
    FROM periode_pkl
"));
    $totalPeriode = $statPeriode['total_periode'] ?? 0;
    $totalAktif   = $statPeriode['total_aktif'] ?? 0;
    $totalSelesai = $statPeriode['total_selesai'] ?? 0;

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
    grid-template-columns: repeat(3, 1fr);
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

.stat-icon.green {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

.stat-icon.gray {
    background: linear-gradient(135deg, #64748b, #475569);
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

table.table-modern-periode {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100% !important;
}

table.table-modern-periode thead th {
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

table.table-modern-periode thead th:last-child {
    border-right: none !important;
}

/* REVISI: Class text-left-col untuk menyelaraskan ke kiri */
table.table-modern-periode thead th.text-left-col {
    text-align: left;
}

table.table-modern-periode tbody td {
    vertical-align: middle;
    font-size: 0.88rem;
    border-color: #eef1f5 !important;
    text-align: center;
    border-right: 1px solid #eef1f5;
}

table.table-modern-periode tbody td.text-left-col {
    text-align: left !important;
}

table.table-modern-periode tbody td:last-child {
    border-right: none;
}

table.table-modern-periode tbody tr:hover {
    background: #f8fafc;
}

.periode-icon {
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

.periode-name {
    font-weight: 600;
    color: #1e293b;
}

.tanggal-cell {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: #64748b;
    font-size: 0.85rem;
}

.badge-soft {
    padding: 0.45rem 0.9rem;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.78rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
}

.badge-soft-aktif {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.badge-soft-selesai {
    background: #e2e8f0;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.action-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

/* REVISI: Tombol aksi diringkas menjadi ikon saja */
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
            <h3><i class="bi bi-calendar-range me-2"></i>Data Periode PKL</h3>
            <p>Kelola periode pelaksanaan PKL di sini.</p>
        </div>

        <a href="tambah.php" class="btn btn-tambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Periode
        </a>
    </div>

    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-calendar-range"></i></div>
            <div>
                <small>Total Periode</small>
                <h4><?php echo $totalPeriode; ?></h4>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-play-circle-fill"></i></div>
            <div>
                <small>Periode Aktif</small>
                <h4><?php echo $totalAktif; ?></h4>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gray"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <small>Periode Selesai</small>
                <h4><?php echo $totalSelesai; ?></h4>
            </div>
        </div>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelPeriode" class="table table-hover table-modern-periode">

                    <thead>

                        <tr>

                            <th width="5%">No</th>
                            <!-- REVISI: Kolom nama periode diratakan kiri -->
                            <th class="text-left-col" width="35%">Nama Periode</th>
                            <th width="20%">Tanggal Mulai</th>
                            <th width="20%">Tanggal Selesai</th>
                            <th width="10%">Status</th>
                            <th width="10%">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        $no = 1;

                        if (mysqli_num_rows($data) > 0) {

                            while ($row = mysqli_fetch_assoc($data)) {
                                // REVISI: Kapitalisasi huruf awal setiap kata
                                $nama_periode_rapi = ucwords(strtolower($row['nama_periode']));

                            ?>

                        <tr>

                            <td><?php echo $no++; ?></td>

                            <!-- REVISI: Layout diubah menjadi flex & text-start agar sejajar rapi dari kiri -->
                            <td class="text-left-col">
                                <a href="daftar_pendaftaran.php?id=<?php echo $row['id']; ?>"
                                    class="text-decoration-none d-flex align-items-center justify-content-start text-start" title="Lihat pendaftaran periode <?php echo htmlspecialchars($nama_periode_rapi); ?>">
                                    <span class="periode-icon"><i class="bi bi-calendar3"></i></span>
                                    <span class="periode-name"><?php echo htmlspecialchars($nama_periode_rapi); ?></span>
                                </a>
                            </td>

                            <td>
                                <span class="tanggal-cell">
                                    <i class="bi bi-calendar-event"></i>
                                    <?php echo date('d-m-Y', strtotime($row['tanggal_mulai'])); ?>
                                </span>
                            </td>

                            <td>
                                <span class="tanggal-cell">
                                    <i class="bi bi-calendar-check"></i>
                                    <?php echo date('d-m-Y', strtotime($row['tanggal_selesai'])); ?>
                                </span>
                            </td>

                            <td>
                                <?php if ($row['status'] == "aktif") {?>
                                <span class="badge-soft badge-soft-aktif">
                                    <i class="bi bi-play-circle-fill"></i> Aktif
                                </span>
                                <?php } else {?>
                                <span class="badge-soft badge-soft-selesai">
                                    <i class="bi bi-check-circle-fill"></i> Selesai
                                </span>
                                <?php }?>
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
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    Belum ada data periode PKL
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

    $("#tabelPeriode").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: `
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        Belum ada data periode PKL
                    </div>
                ` // Memastikan empty state DataTables seragam
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
            text: 'Data periode PKL ini tidak dapat dikembalikan.',
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
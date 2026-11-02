<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    $data = mysqli_query($conn, "
SELECT *
FROM jurusan
ORDER BY id DESC
");

    // Statistik ringkas
    $statJurusan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_jurusan
    FROM jurusan
"));
    $totalJurusan = $statJurusan['total_jurusan'] ?? 0;

    $jenjangTerbanyakRow = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT jenjang, COUNT(*) AS jumlah
    FROM jurusan
    WHERE jenjang IS NOT NULL AND jenjang != ''
    GROUP BY jenjang
    ORDER BY jumlah DESC
    LIMIT 1
"));
    $jenjangTerbanyak       = $jenjangTerbanyakRow['jenjang'] ?? '-';
    $jenjangTerbanyakJumlah = $jenjangTerbanyakRow['jumlah'] ?? 0;

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

.stat-icon.amber {
    background: linear-gradient(135deg, #f59e0b, #d97706);
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

table.table-modern-jurusan {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100% !important;
}

table.table-modern-jurusan thead th {
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

table.table-modern-jurusan thead th:last-child {
    border-right: none !important;
}

/* REVISI: Tambahan class text-left-col untuk Thead */
table.table-modern-jurusan thead th.text-left-col {
    text-align: left;
}

table.table-modern-jurusan tbody td {
    vertical-align: middle;
    font-size: 0.88rem;
    border-color: #eef1f5 !important;
    text-align: center;
    border-right: 1px solid #eef1f5;
}

/* REVISI: Tambahan class text-left-col untuk Tbody */
table.table-modern-jurusan tbody td.text-left-col {
    text-align: left !important;
}

table.table-modern-jurusan tbody td:last-child {
    border-right: none;
}

table.table-modern-jurusan tbody tr:hover {
    background: #f8fafc;
}

.jurusan-icon {
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

.jurusan-name {
    font-weight: 600;
    color: #1e293b;
}

.badge-jenjang {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

/* SMA / SMK / MA (Nuansa Merah / Rose) */
.jenjang-sma {
    background: #ffe4e6;
    color: #be123c;
    border: 1px solid #fecdd3;
}

.jenjang-smk {
    background: #ffedd5;
    color: #c2410c;
    border: 1px solid #fed7aa;
}

/* Nuansa Oranye */
.jenjang-ma {
    background: #fef08a;
    color: #854d0e;
    border: 1px solid #fde047;
}

/* Nuansa Kuning */

/* D3 / D4 (Nuansa Biru / Cyan / Teal) */
.jenjang-d3 {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

/* Nuansa Kuning Emas */
.jenjang-d4 {
    background: #ccfbf1;
    color: #115e59;
    border: 1px solid #99f6e4;
}

/* Nuansa Teal */

/* S1 / S2 / S3 (Nuansa Hijau, Ungu, & Pink/Indigo) */
.jenjang-s1 {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

/* Nuansa Hijau */
.jenjang-s2 {
    background: #ede9fe;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
}

/* Nuansa Ungu */
.jenjang-s3 {
    background: #fce7f3;
    color: #9d174d;
    border: 1px solid #fbcfe8;
}

/* Nuansa Pink / Magenta */

/* Default (Abu-abu) */
.jenjang-default {
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
            <h3><i class="bi bi-book-half me-2"></i>Data Jurusan</h3>
            <p>Kelola daftar jurusan Pelajar magang di sini.</p>
        </div>

        <a href="tambah.php" class="btn btn-tambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Jurusan
        </a>
    </div>

    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-book-half"></i></div>
            <div>
                <small>Total Jurusan</small>
                <h4><?php echo $totalJurusan; ?></h4>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="bi bi-mortarboard-fill"></i></div>
            <div>
                <small>Jenjang Terbanyak</small>
                <h4><?php echo $jenjangTerbanyak; ?> <span
                        style="font-size:0.75rem; color:#94a3b8; font-weight:500;">(<?php echo $jenjangTerbanyakJumlah; ?>)</span>
                </h4>
            </div>
        </div>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelJurusan" class="table table-hover table-modern-jurusan">

                    <thead>

                        <tr>

                            <th width="5%">No</th>
                            <!-- REVISI: Kolom jurusan dibuat text-left-col agar rata kiri -->
                            <th class="text-left-col" width="60%">Jurusan</th>
                            <th width="20%">Jenjang</th>
                            <th width="15%">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                            $no = 1;

                            if (mysqli_num_rows($data) > 0) {

                                while ($row = mysqli_fetch_assoc($data)) {

                                    $jenjang      = strtoupper(trim($row['jenjang']));
                                    $jenjangClass = match ($jenjang) {
                                        'SMA'   => 'jenjang-sma',
                                        'SMK'   => 'jenjang-smk',
                                        'MA'    => 'jenjang-ma',
                                        'D3'    => 'jenjang-d3',
                                        'D4'    => 'jenjang-d4',
                                        'S1'    => 'jenjang-s1',
                                        'S2'    => 'jenjang-s2',
                                        'S3'    => 'jenjang-s3',
                                        default => 'jenjang-default',
                                    };
                                    
                                    // REVISI: Kapitalisasi huruf awal setiap kata
                                    $nama_jurusan_rapi = ucwords(strtolower($row['nama_jurusan']));

                                ?>

                        <tr>

                            <td><?php echo $no++; ?></td>

                            <!-- REVISI: Layout diubah menjadi flex & text-start agar sejajar rapi dari kiri -->
                            <td class="text-left-col">
                                <a href="daftar_pelajar.php?id=<?php echo $row['id']; ?>"
                                    class="text-decoration-none d-flex align-items-center justify-content-start text-start" title="Lihat daftar pelajar jurusan <?php echo $nama_jurusan_rapi; ?>">
                                    <span class="jurusan-icon"><i class="bi bi-journal-bookmark"></i></span>
                                    <span class="jurusan-name"><?php echo htmlspecialchars($nama_jurusan_rapi); ?></span>
                                </a>
                            </td>

                            <td>
                                <!-- REVISI: Sembunyikan badge jika jenjang kosong di database -->
                                <?php if (!empty($jenjang)): ?>
                                    <span class="badge-jenjang <?php echo $jenjangClass; ?>">
                                        <i class="bi bi-mortarboard-fill"></i>
                                        <?php echo htmlspecialchars($jenjang); ?>
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
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    Belum ada data jurusan
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

    $("#tabelJurusan").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: `
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        Belum ada data jurusan
                    </div>
                ` // Memastikan empty state DataTables seragam
        },
        columnDefs: [
            { orderable: false, targets: [3] } // Mematikan fitur urut (sort) pada kolom Aksi
        ]
    });

    $(document).on('click', '.btnHapus', function(e) {

        e.preventDefault();

        let id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'Data jurusan ini tidak dapat dikembalikan.',
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
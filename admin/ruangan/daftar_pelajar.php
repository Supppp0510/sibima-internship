<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // Ambil ID Ruangan dari URL
    $id_ruangan = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

    if (empty($id_ruangan)) {
    echo "<script>alert('ID Ruangan tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }

    // Ambil data ruangan
    $query_ruangan = mysqli_query($conn, "SELECT * FROM ruangan WHERE id_ruangan = '$id_ruangan'");
    if (mysqli_num_rows($query_ruangan) == 0) {
    echo "<script>alert('Data ruangan tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }
    $ruangan = mysqli_fetch_assoc($query_ruangan);

    // Ambil data pelajar yang ditempatkan di ruangan ini
    $query_pelajar = mysqli_query($conn, "
    SELECT p.*, i.nama_instansi
    FROM pelajar p
    LEFT JOIN instansi i ON p.instansi_id = i.id
    WHERE p.id_ruangan = '$id_ruangan'
    ORDER BY p.id DESC
");

    $total_ditempatkan = mysqli_num_rows($query_pelajar);

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
}

.page-header-bar h3 {
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.page-header-bar p {
    color: #64748b;
    font-size: 0.85rem;
    margin: 0.2rem 0 0;
}

.card-table {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
}

table.table-modern-pelajar {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100% !important;
}

table.table-modern-pelajar thead th {
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

table.table-modern-pelajar thead th:last-child {
    border-right: none !important;
}

table.table-modern-pelajar thead th.text-left-col {
    text-align: left;
}

table.table-modern-pelajar tbody td {
    vertical-align: middle;
    font-size: 0.88rem;
    border-color: #eef1f5 !important;
    text-align: center;
    border-right: 1px solid #eef1f5;
}

table.table-modern-pelajar tbody td.text-left-col {
    text-align: left !important;
}

table.table-modern-pelajar tbody td:last-child {
    border-right: none;
}

table.table-modern-pelajar tbody tr:hover {
    background: #f8fafc;
}

/* Pindahkan styling empty-state agar bisa dirender oleh DataTables */
.dataTables_empty {
    padding: 3rem 1rem !important;
    color: #94a3b8 !important;
    background-color: transparent !important;
    border-bottom: none !important;
}

.dataTables_empty i {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 0.8rem;
    color: #cbd5e1;
}

.ruangan-info-box {
    background: #fff;
    border-radius: 14px;
    padding: 1.2rem 1.5rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.badge-kuota {
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
</style>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-people me-2"></i>Daftar Pelajar Magang</h3>
            <p>Menampilkan pelajar yang ditempatkan di <b><?php echo htmlspecialchars($ruangan['nama_ruangan']); ?></b>.
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm px-3 py-2" style="border-radius: 10px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Informasi Singkat Ruangan -->
    <div class="ruangan-info-box">
        <div>
            <h5 class="fw-bold text-primary mb-2"><i
                    class="bi bi-door-open me-2"></i><?php echo htmlspecialchars($ruangan['nama_ruangan']); ?></h5>
            <div class="text-muted small">
                <i class="bi bi-geo-alt-fill me-1"></i> Lokasi:
                <?php echo ! empty($ruangan['lokasi']) ? htmlspecialchars($ruangan['lokasi']) : '-'; ?>
            </div>
        </div>
        <div>
            <span class="badge-kuota">
                Terisi: <?php echo $total_ditempatkan; ?> / <?php echo $ruangan['kuota']; ?> Orang
            </span>
        </div>
    </div>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelPelajarRuangan" class="table table-hover table-modern-pelajar">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>NIM</th>
                            <th class="text-left-col">Nama Pelajar</th>
                            <th class="text-left-col">Asal Instansi</th>
                            <th>No HP / WhatsApp</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_pelajar) > 0) {
                            while ($row = mysqli_fetch_assoc($query_pelajar)) {
                            ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><span class="fw-semibold"><?php echo htmlspecialchars($row['nim']); ?></span></td>
                            <td class="text-left-col fw-bold text-dark"><?php echo htmlspecialchars($row['nama']); ?>
                            </td>
                            <td class="text-left-col">
                                <?php echo ! empty($row['nama_instansi']) ? htmlspecialchars($row['nama_instansi']) : '<span class="text-muted">-</span>'; ?>
                            </td>
                            <td><?php echo ! empty($row['no_hp']) ? htmlspecialchars($row['no_hp']) : '-'; ?></td>
                            <td><?php echo ! empty($row['email']) ? htmlspecialchars($row['email']) : '-'; ?></td>
                        </tr>
                        <?php
                        }
                        }
                        // Tidak ada tag <tr> tag khusus empty state di sini agar DataTables tidak error.
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include "../../include/footer.php"; ?>

<script>
$(function() {
    $("#tabelPelajarRuangan").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: '<i class="bi bi-inbox"></i> Belum ada pelajar yang ditempatkan di ruangan ini.'
        }
    });
});
</script>
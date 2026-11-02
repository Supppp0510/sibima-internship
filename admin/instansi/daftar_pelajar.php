<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // Ambil ID Instansi dari URL
    $id_instansi = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

    if (empty($id_instansi)) {
    echo "<script>alert('ID Instansi tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }

    // Ambil data instansi
    $query_instansi = mysqli_query($conn, "SELECT * FROM instansi WHERE id = '$id_instansi'");
    if (mysqli_num_rows($query_instansi) == 0) {
    echo "<script>alert('Data instansi tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }
    $instansi = mysqli_fetch_assoc($query_instansi);

    // Ambil data pelajar/mahasiswa yang berelasi dengan instansi ini
    $query_pelajar = mysqli_query($conn, "
    SELECT p.*, j.nama_jurusan
    FROM pelajar p
    LEFT JOIN jurusan j ON p.jurusan_id = j.id
    WHERE p.instansi_id = '$id_instansi'
    ORDER BY p.id DESC
");

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

.instansi-info-box {
    background: #fff;
    border-radius: 14px;
    padding: 1.2rem 1.5rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-people me-2"></i>Daftar Pelajar PKL</h3>
            <p>Menampilkan seluruh pelajar asal instansi
                <b><?php echo htmlspecialchars($instansi['nama_instansi']); ?></b>.
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm px-3 py-2" style="border-radius: 10px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Informasi Singkat Instansi -->
    <div class="instansi-info-box">
        <div>
            <h5 class="fw-bold text-primary mb-1"><i
                    class="bi bi-bank me-2"></i><?php echo htmlspecialchars($instansi['nama_instansi']); ?></h5>
            <p class="text-muted mb-0 small"><i class="bi bi-geo-alt-fill me-1"></i>
                <?php echo ! empty($instansi['alamat']) ? htmlspecialchars($instansi['alamat']) : '-'; ?> (Kota:
                <b><?php echo ! empty($instansi['kota']) ? htmlspecialchars($instansi['kota']) : '-'; ?></b>)
            </p>
        </div>
        <div>
            <span class="badge bg-primary px-3 py-2" style="font-size: 0.85rem; border-radius: 8px;">
                Total Pelajar: <?php echo mysqli_num_rows($query_pelajar); ?> Orang
            </span>
        </div>
    </div>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelPelajarInstansi" class="table table-hover table-modern-pelajar">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>NIM/NIS</th>
                            <th class="text-left-col">Nama Pelajar</th>
                            <th class="text-left-col">Jurusan</th>
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
                                <?php echo ! empty($row['nama_jurusan']) ? htmlspecialchars($row['nama_jurusan']) : '<span class="text-muted">-</span>'; ?>
                            </td>
                            <td><?php echo ! empty($row['no_hp']) ? htmlspecialchars($row['no_hp']) : '-'; ?></td>
                            <td><?php echo ! empty($row['email']) ? htmlspecialchars($row['email']) : '-'; ?></td>
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

<?php include "../../include/footer.php"; ?>

<script>
$(function() {
    $("#tabelPelajarInstansi").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"
        }
    });
});
</script>
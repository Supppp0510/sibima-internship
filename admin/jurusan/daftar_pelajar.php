<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // Ambil ID Jurusan dari URL
    $id_jurusan = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

    if (empty($id_jurusan)) {
    echo "<script>alert('ID Jurusan tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }

    // Ambil data jurusan
    $query_jurusan = mysqli_query($conn, "SELECT * FROM jurusan WHERE id = '$id_jurusan'");
    if (mysqli_num_rows($query_jurusan) == 0) {
    echo "<script>alert('Data jurusan tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }
    $jurusan = mysqli_fetch_assoc($query_jurusan);

    // Ambil data pelajar yang berelasi dengan jurusan ini (tambahkan JOIN instansi)
    $query_pelajar = mysqli_query($conn, "
    SELECT p.*, i.nama_instansi
    FROM pelajar p
    LEFT JOIN instansi i ON p.instansi_id = i.id
    WHERE p.jurusan_id = '$id_jurusan'
    ORDER BY p.id DESC
");

    // Tentukan warna badge jenjang agar senada dengan index.php
    $jenjang      = strtoupper(trim($jurusan['jenjang']));
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

.jurusan-info-box {
    background: #fff;
    border-radius: 14px;
    padding: 1.2rem 1.5rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Badge Jenjang CSS dari index */
.badge-jenjang {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

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

.jenjang-ma {
    background: #fef08a;
    color: #854d0e;
    border: 1px solid #fde047;
}

.jenjang-d3 {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.jenjang-d4 {
    background: #ccfbf1;
    color: #115e59;
    border: 1px solid #99f6e4;
}

.jenjang-s1 {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.jenjang-s2 {
    background: #ede9fe;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
}

.jenjang-s3 {
    background: #fce7f3;
    color: #9d174d;
    border: 1px solid #fbcfe8;
}

.jenjang-default {
    background: #e2e8f0;
    color: #475569;
    border: 1px solid #cbd5e1;
}
</style>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-people me-2"></i>Daftar Pelajar PKL</h3>
            <p>Menampilkan seluruh pelajar dari jurusan
                <b><?php echo htmlspecialchars($jurusan['nama_jurusan']); ?></b>.
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm px-3 py-2" style="border-radius: 10px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Informasi Singkat Jurusan -->
    <div class="jurusan-info-box">
        <div>
            <h5 class="fw-bold text-primary mb-2"><i
                    class="bi bi-journal-bookmark me-2"></i><?php echo htmlspecialchars($jurusan['nama_jurusan']); ?>
            </h5>
            <span class="badge-jenjang <?php echo $jenjangClass; ?>">
                <i class="bi bi-mortarboard-fill"></i> Jenjang:
                <?php echo ! empty($jurusan['jenjang']) ? htmlspecialchars($jurusan['jenjang']) : '-'; ?>
            </span>
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
                <table id="tabelPelajarJurusan" class="table table-hover table-modern-pelajar">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>NIM/NIS</th>
                            <th class="text-left-col">Nama Pelajar</th>
                            <th class="text-left-col">Instansi</th>
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
                            // BAGIAN ELSE DIHAPUS. Biarkan DataTables yang mengambil alih jika tidak ada <tr> di sini.
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
    $("#tabelPelajarJurusan").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            // Ini menggantikan tampilan default tabel kosong DataTables dengan UI cantik milikmu
            emptyTable: '<i class="bi bi-inbox"></i> Belum ada pelajar yang terdaftar dari jurusan ini.'
        }
    });
});
</script>
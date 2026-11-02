<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // Ambil ID Periode dari URL
    $id_periode = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

    if (empty($id_periode)) {
    echo "<script>alert('ID Periode tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }

    // Ambil data periode
    $query_periode = mysqli_query($conn, "SELECT * FROM periode_pkl WHERE id = '$id_periode'");
    if (mysqli_num_rows($query_periode) == 0) {
    echo "<script>alert('Data periode tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }
    $periode = mysqli_fetch_assoc($query_periode);

    // Ambil data pelajar dari tabel pendaftaran berdasarkan periode ini
    $query_pendaftaran = mysqli_query($conn, "
    SELECT pd.*, pl.nim, pl.nama, pl.no_hp
    FROM pendaftaran pd
    JOIN pelajar pl ON pd.pelajar_id = pl.id
    WHERE pd.periode_id = '$id_periode'
    ORDER BY pd.id DESC
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

table.table-modern-pendaftaran {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100% !important;
}

table.table-modern-pendaftaran thead th {
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

table.table-modern-pendaftaran thead th:last-child {
    border-right: none !important;
}

table.table-modern-pendaftaran thead th.text-left-col {
    text-align: left;
}

table.table-modern-pendaftaran tbody td {
    vertical-align: middle;
    font-size: 0.88rem;
    border-color: #eef1f5 !important;
    text-align: center;
    border-right: 1px solid #eef1f5;
}

table.table-modern-pendaftaran tbody td.text-left-col {
    text-align: left !important;
}

table.table-modern-pendaftaran tbody td:last-child {
    border-right: none;
}

table.table-modern-pendaftaran tbody tr:hover {
    background: #f8fafc;
}

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

.periode-info-box {
    background: #fff;
    border-radius: 14px;
    padding: 1.2rem 1.5rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.badge-status {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.status-aktif {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.status-selesai {
    background: #e2e8f0;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.badge-pendaftaran {
    padding: 0.3rem 0.6rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
}

.daftar-menunggu {
    background: #fef3c7;
    color: #d97706;
}

.daftar-diterima {
    background: #dcfce7;
    color: #16a34a;
}

.daftar-ditolak {
    background: #fee2e2;
    color: #dc2626;
}
</style>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-card-checklist me-2"></i>Daftar Pendaftaran Pelajar</h3>
            <p>Menampilkan pelajar yang mendaftar pada periode
                <b><?php echo htmlspecialchars($periode['nama_periode']); ?></b>.</p>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm px-3 py-2" style="border-radius: 10px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Informasi Singkat Periode -->
    <div class="periode-info-box">
        <div>
            <h5 class="fw-bold text-primary mb-2"><i
                    class="bi bi-calendar3 me-2"></i><?php echo htmlspecialchars($periode['nama_periode']); ?></h5>
            <div class="text-muted small mb-1">
                <i class="bi bi-calendar-event me-1"></i>
                <?php echo date('d-m-Y', strtotime($periode['tanggal_mulai'])); ?>
                s/d
                <i class="bi bi-calendar-check me-1"></i>
                <?php echo date('d-m-Y', strtotime($periode['tanggal_selesai'])); ?>
            </div>
            <?php if ($periode['status'] == 'aktif') {?>
            <span class="badge-status status-aktif"><i class="bi bi-play-circle-fill"></i> Periode Aktif</span>
            <?php } else {?>
            <span class="badge-status status-selesai"><i class="bi bi-check-circle-fill"></i> Periode Selesai</span>
            <?php }?>
        </div>
        <div>
            <span class="badge bg-primary px-3 py-2" style="font-size: 0.85rem; border-radius: 8px;">
                Total Pendaftar: <?php echo mysqli_num_rows($query_pendaftaran); ?> Pelajar
            </span>
        </div>
    </div>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelPendaftaranPeriode" class="table table-hover table-modern-pendaftaran">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>NIM</th>
                            <th class="text-left-col">Nama Pelajar</th>
                            <th>No HP / WhatsApp</th>
                            <th>Tanggal Daftar</th>
                            <th>Status Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_pendaftaran) > 0) {
                            while ($row = mysqli_fetch_assoc($query_pendaftaran)) {
                                $status      = strtolower(trim($row['status']));
                                $statusClass = match ($status) {
                                    'menunggu' => 'daftar-menunggu',
                                    'diterima' => 'daftar-diterima',
                                    'ditolak'  => 'daftar-ditolak',
                                    default    => 'bg-secondary text-white'
                                };
                            ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><span class="fw-semibold"><?php echo htmlspecialchars($row['nim']); ?></span></td>
                            <td class="text-left-col fw-bold text-dark"><?php echo htmlspecialchars($row['nama']); ?>
                            </td>
                            <td><?php echo ! empty($row['no_hp']) ? htmlspecialchars($row['no_hp']) : '-'; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['tanggal_daftar'])); ?></td>
                            <td>
                                <span class="badge-pendaftaran <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
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

<?php include "../../include/footer.php"; ?>

<script>
$(function() {
    $("#tabelPendaftaranPeriode").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: '<i class="bi bi-inbox"></i> Belum ada pelajar yang mendaftar di periode ini.'
        }
    });
});
</script>
<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // Ambil ID Unit Kerja dari URL
    $id_unit_kerja = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

    if (empty($id_unit_kerja)) {
    echo "<script>alert('ID Unit Kerja tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }

    // Ambil data Unit Kerja
    $query_unit = mysqli_query($conn, "SELECT * FROM unit_kerja WHERE id = '$id_unit_kerja'");
    if (mysqli_num_rows($query_unit) == 0) {
    echo "<script>alert('Data Unit Kerja tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }
    $unit_kerja = mysqli_fetch_assoc($query_unit);

    // 1. Ambil data Pembimbing yang bertugas di Unit Kerja ini
    $query_pembimbing = mysqli_query($conn, "
    SELECT *
    FROM pembimbing
    WHERE unit_kerja_id = '$id_unit_kerja'
    ORDER BY id DESC
");

    // 2. Ambil data Pelajar yang ditempatkan di Unit Kerja ini (melalui tabel pendaftaran)
    // Hanya mengambil yang status pendaftarannya 'diterima' atau bisa disesuaikan
    $query_pelajar = mysqli_query($conn, "
    SELECT pl.*, pd.status, i.nama_instansi
    FROM pendaftaran pd
    JOIN pelajar pl ON pd.pelajar_id = pl.id
    LEFT JOIN instansi i ON pl.instansi_id = i.id
    WHERE pd.unit_kerja_id = '$id_unit_kerja'
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

.unit-info-box {
    background: #fff;
    border-radius: 14px;
    padding: 1.2rem 1.5rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.unit-info-text h5 {
    font-weight: 700;
    color: #1d4ed8;
    margin-bottom: 0.4rem;
}

.unit-info-text p {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
}

.badge-kuota {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    white-space: nowrap;
}

/* Card Table Styling */
.card-table {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
    margin-bottom: 2rem;
}

.card-table-header {
    background: #f8fafc;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    border-radius: 16px 16px 0 0;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* DataTable Modern Styling */
table.datatable-modern {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100% !important;
    margin: 0 !important;
}

table.datatable-modern thead th {
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

table.datatable-modern thead th:last-child {
    border-right: none !important;
}

table.datatable-modern thead th.text-left-col {
    text-align: left;
}

table.datatable-modern tbody td {
    vertical-align: middle;
    font-size: 0.88rem;
    border-color: #eef1f5 !important;
    text-align: center;
    border-right: 1px solid #eef1f5;
}

table.datatable-modern tbody td.text-left-col {
    text-align: left !important;
}

table.datatable-modern tbody td:last-child {
    border-right: none;
}

table.datatable-modern tbody tr:hover {
    background: #f8fafc;
}

/* Empty State for DataTables */
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

.badge-status {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
}

.status-diterima {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.status-menunggu {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}
</style>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-diagram-3 me-2"></i>Detail Penempatan Unit Kerja</h3>
            <p>Daftar pembimbing dan pelajar magang di Unit Kerja terpilih.</p>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm px-3 py-2" style="border-radius: 10px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Info Unit Kerja -->
    <div class="unit-info-box">
        <div class="unit-info-text">
            <h5><i class="bi bi-building me-2"></i><?php echo htmlspecialchars($unit_kerja['unit_kerja']); ?></h5>
            <p>
                <?php if (! empty($unit_kerja['unit_kerja_es_ii'])) {
                        echo "Es. II: " . htmlspecialchars($unit_kerja['unit_kerja_es_ii']) . " | ";
                    }
                ?>
                <?php if (! empty($unit_kerja['unit_kerja_es_iii'])) {
                        echo "Es. III: " . htmlspecialchars($unit_kerja['unit_kerja_es_iii']) . " | ";
                    }
                ?>
                <?php if (! empty($unit_kerja['unit_kerja_es_iv'])) {
                        echo "Es. IV: " . htmlspecialchars($unit_kerja['unit_kerja_es_iv']);
                    }
                ?>
            </p>
        </div>
        <div>
            <span class="badge-kuota">
                <i class="bi bi-people-fill me-1"></i> Kuota Maksimal: <?php echo $unit_kerja['kuota']; ?> Orang
            </span>
        </div>
    </div>

    <!-- TABEL 1: PEMBIMBING -->
    <div class="card card-table">
        <div class="card-table-header">
            <i class="bi bi-person-workspace text-primary"></i> Daftar Pembimbing
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table id="tabelPembimbing" class="table table-hover datatable-modern">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>NIP</th>
                            <th class="text-left-col">Nama Pembimbing</th>
                            <th>No HP / WhatsApp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no_pem = 1;
                        if (mysqli_num_rows($query_pembimbing) > 0) {
                            while ($pem = mysqli_fetch_assoc($query_pembimbing)) {
                            ?>
                        <tr>
                            <td><?php echo $no_pem++; ?></td>
                            <td><span class="fw-semibold"><?php echo htmlspecialchars($pem['nip']); ?></span></td>
                            <td class="text-left-col fw-bold text-dark"><?php echo htmlspecialchars($pem['nama']); ?>
                            </td>
                            <td><?php echo ! empty($pem['no_hp']) ? htmlspecialchars($pem['no_hp']) : '-'; ?></td>
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

    <!-- TABEL 2: PELAJAR MAGANG -->
    <div class="card card-table">
        <div class="card-table-header">
            <i class="bi bi-mortarboard-fill text-success"></i> Daftar Pelajar Magang
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table id="tabelPelajar" class="table table-hover datatable-modern">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>NIM / NIS</th>
                            <th class="text-left-col">Nama Pelajar</th>
                            <th class="text-left-col">Instansi Asal</th>
                            <th>No HP</th>
                            <th>Status Penempatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no_pel = 1;
                        if (mysqli_num_rows($query_pelajar) > 0) {
                            while ($pel = mysqli_fetch_assoc($query_pelajar)) {
                                $status      = strtolower($pel['status']);
                                $badgeStatus = ($status == 'diterima') ? 'status-diterima' : 'status-menunggu';
                            ?>
                        <tr>
                            <td><?php echo $no_pel++; ?></td>
                            <td><span class="fw-semibold"><?php echo htmlspecialchars($pel['nim']); ?></span></td>
                            <td class="text-left-col fw-bold text-dark"><?php echo htmlspecialchars($pel['nama']); ?>
                            </td>
                            <td class="text-left-col">
                                <?php echo ! empty($pel['nama_instansi']) ? htmlspecialchars($pel['nama_instansi']) : '-'; ?>
                            </td>
                            <td><?php echo ! empty($pel['no_hp']) ? htmlspecialchars($pel['no_hp']) : '-'; ?></td>
                            <td>
                                <span class="badge-status <?php echo $badgeStatus; ?>">
                                    <?php echo ucfirst($pel['status']); ?>
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
    // Inisialisasi DataTable untuk Pembimbing
    $("#tabelPembimbing").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 5,
        lengthMenu: [
            [5, 10, 25, -1],
            [5, 10, 25, "Semua"]
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: '<i class="bi bi-inbox"></i> Belum ada pembimbing yang ditugaskan di unit kerja ini.'
        }
    });

    // Inisialisasi DataTable untuk Pelajar
    $("#tabelPelajar").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: '<i class="bi bi-inbox"></i> Belum ada pelajar magang yang ditempatkan di unit kerja ini.'
        }
    });
});
</script>
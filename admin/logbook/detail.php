<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$pelajar_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($pelajar_id)) {
    echo "<script>alert('ID Pelajar tidak valid!'); window.location='index.php';</script>";
    exit;
}

// Ambil data profil pelajar
$query_mhs = mysqli_query($conn, "
SELECT 
    m.*, 
    u.nama_instansi, 
    ps.nama_jurusan, 
    uk.unit_kerja AS nama_bidang, 
    pb.nama AS nama_pembimbing,
    pb.nip
FROM pelajar m
LEFT JOIN instansi u ON m.instansi_id = u.id
LEFT JOIN jurusan ps ON m.jurusan_id = ps.id
LEFT JOIN pendaftaran p ON p.pelajar_id = m.id AND p.status = 'diterima'
LEFT JOIN unit_kerja uk ON p.unit_kerja_id = uk.id
LEFT JOIN pembimbing pb ON p.pembimbing_id = pb.id
WHERE m.id = '$pelajar_id'
");

if (mysqli_num_rows($query_mhs) == 0) {
    echo "<script>alert('Data pelajar tidak ditemukan.'); window.location='index.php';</script>";
    exit;
}

$mhs = mysqli_fetch_assoc($query_mhs);

// Ambil seluruh daftar logbook harian milik pelajar tersebut (diurutkan dari yang terbaru)
$query_logbook = mysqli_query($conn, "
SELECT * FROM logbook 
WHERE pelajar_id = '$pelajar_id' 
ORDER BY tanggal DESC, id DESC
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
    .page-header-bar h3 { font-weight: 700; color: #1e293b; margin: 0; }
    .page-header-bar p { color: #64748b; font-size: 0.85rem; margin: 0.2rem 0 0; }
    
    .card-profile { 
        border: none; 
        border-radius: 14px; 
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05); 
        margin-bottom: 1.5rem; 
        background: #fff;
    }
    
    .card-table { 
        border: none; 
        border-radius: 16px; 
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06); 
    }
    
    table.table-modern-detail { 
        border-collapse: separate !important; 
        border-spacing: 0; 
        width: 100% !important; 
    }
    
    table.table-modern-detail thead th {
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
    
    table.table-modern-detail thead th:last-child { 
        border-right: none !important; 
    }
    
    table.table-modern-detail tbody td {
        vertical-align: middle; 
        font-size: 0.88rem; 
        border-color: #eef1f5 !important; 
        text-align: center;
        border-right: 1px solid #eef1f5;
    }
    
    table.table-modern-detail tbody td:last-child { 
        border-right: none; 
    }
    
    table.table-modern-detail tbody tr:hover { 
        background: #f8fafc; 
    }
    
    .img-thumbnail-logbook {
        width: 45px; 
        height: 45px; 
        object-fit: cover; 
        border-radius: 8px;
        border: 1px solid #cbd5e1; 
        transition: transform 0.2s;
    }
    .img-thumbnail-logbook:hover { transform: scale(1.1); }
    
    .badge-soft { 
        padding: 0.4rem 0.75rem; 
        border-radius: 20px; 
        font-weight: 600; 
        font-size: 0.78rem; 
        display: inline-flex; 
        align-items: center; 
        gap: 0.3rem; 
    }
    .badge-soft-menunggu { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-soft-disetujui { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-soft-revisi { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .empty-state { padding: 2.5rem 1rem; color: #94a3b8; }
    .empty-state i { font-size: 2.2rem; display: block; margin-bottom: 0.6rem; }
</style>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-journal-text me-2"></i>Rincian Logbook Harian Pelajar</h3>
            <p>Daftar seluruh catatan logbook harian atas nama <b><?= htmlspecialchars($mhs['nama']); ?></b>.</p>
        </div>
        <div>
            <a href="index.php" class="btn btn-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Informasi Profil Singkat Pelajar -->
    <div class="card card-profile">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <span class="text-muted small d-block">Nama Lengkap (NIM/NIS)</span>
                    <strong class="text-dark fs-6"><?= htmlspecialchars($mhs['nama']); ?> (<?= htmlspecialchars($mhs['nim']); ?>)</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small d-block">Instansi / Jurusan</span>
                    <span class="text-dark fw-medium"><?= !empty($mhs['nama_instansi']) ? htmlspecialchars($mhs['nama_instansi']) : '-'; ?> / <?= !empty($mhs['nama_jurusan']) ? htmlspecialchars($mhs['nama_jurusan']) : '-'; ?></span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small d-block">Unit Kerja / Pembimbing</span>
                    <span class="text-dark fw-medium"><?= !empty($mhs['nama_bidang']) ? htmlspecialchars($mhs['nama_bidang']) : '-'; ?> (<?= !empty($mhs['nama_pembimbing']) ? htmlspecialchars($mhs['nama_pembimbing']) : '-'; ?>)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Logbook Harian Pelajar -->
    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelDetailLogbook" class="table table-hover table-modern-detail">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th width="150">Tanggal</th>
                            <th>Judul Kegiatan</th>
                            <th width="140">Dokumentasi</th>
                            <th width="150">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_logbook) > 0) {
                            while ($row = mysqli_fetch_assoc($query_logbook)) {
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <span class="fw-semibold text-secondary">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            <?= date('d-m-Y', strtotime($row['tanggal'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-start">
                                        <strong class="text-dark"><?= htmlspecialchars($row['judul']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['dokumentasi'])) { ?>
                                            <a href="../../assets/upload/pelajar/dokumentasi/<?= htmlspecialchars($row['dokumentasi']); ?>" target="_blank">
                                                <img src="../../assets/upload/pelajar/dokumentasi/<?= htmlspecialchars($row['dokumentasi']); ?>" class="img-thumbnail-logbook" alt="Dokumentasi">
                                            </a>
                                        <?php } else { ?>
                                            <span class="text-muted small">-</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['status'] == "menunggu") {
                                            echo '<span class="badge-soft badge-soft-menunggu"><i class="bi bi-hourglass-split"></i> Menunggu</span>';
                                        } elseif ($row['status'] == "disetujui") {
                                            echo '<span class="badge-soft badge-soft-disetujui"><i class="bi bi-check-circle-fill"></i> Disetujui</span>';
                                        } else {
                                            echo '<span class="badge-soft badge-soft-revisi"><i class="bi bi-arrow-repeat"></i> Revisi</span>';
                                        }
                                        ?>
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
                                        Belum ada data logbook harian untuk pelajar ini.
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

<script>
$(document).ready(function(){
    $("#tabelDetailLogbook").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language:{
            url: "//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            search: "Cari:"
        }
    });
});
</script>

<?php
include "../../include/footer.php";
?>
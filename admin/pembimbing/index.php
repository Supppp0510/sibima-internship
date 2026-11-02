<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";

$data = mysqli_query($conn,"
    SELECT 
        p.*, 
        u.unit_kerja AS nama_unit_kerja
    FROM pembimbing p
    LEFT JOIN unit_kerja u 
        ON p.unit_kerja_id = u.id
    ORDER BY p.id DESC
");

$statPembimbing = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_pembimbing
    FROM pembimbing
"));
$totalPembimbing = $statPembimbing['total_pembimbing'] ?? 0;

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";
?>

<style>
    .page-header-bar{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, #0f1f4d, #1d4ed8);
        border-radius: 14px;
        padding: 1.1rem 1.4rem;
        box-shadow: 0 4px 14px rgba(29, 78, 216, 0.25);
    }
    .page-header-bar h3{
        font-weight: 700;
        color: #fff;
        margin: 0;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
    }
    .page-header-bar h3 i{
        font-size: 1.1rem;
        margin-right: 0.6rem !important;
    }
    .page-header-bar p{
        color: #cbd5e1;
        font-size: 0.85rem;
        margin: 0.25rem 0 0;
    }
    .page-header-bar .btn-tambah{
        background: #fff;
        color: #1d4ed8;
        box-shadow: none;
    }
    .page-header-bar .btn-tambah:hover{
        background: #eff6ff;
        color: #1d4ed8;
    }
    .btn-tambah{
        border-radius: 10px;
        font-weight: 600;
        padding: 0.55rem 1.1rem;
        border: none;
    }
    .stat-row{
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-card{
        background: #fff;
        border-radius: 14px;
        padding: 1rem 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    }
    .stat-icon{
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
    .stat-icon.blue{ background: linear-gradient(135deg,#2563eb,#1d4ed8); }
    .stat-card small{
        display: block;
        color: #94a3b8;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .stat-card h4{
        margin: 0.1rem 0 0;
        font-weight: 700;
        color: #1e293b;
    }
    .card-table{
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    }
    table.table-modern-pembimbing{
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100% !important;
    }
    table.table-modern-pembimbing thead th{
        background: #2563eb;
        color: #fff;
        border: none !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
        text-align: center;
        border-right: 1px solid rgba(255,255,255,0.15) !important;
    }
    table.table-modern-pembimbing thead th:last-child{
        border-right: none !important;
    }
    table.table-modern-pembimbing thead th.text-left-col {
        text-align: left !important;
    }
    table.table-modern-pembimbing tbody td{
        vertical-align: middle;
        font-size: 0.82rem;
        border-color: #eef1f5 !important;
        text-align: center;
        border-right: 1px solid #eef1f5;
    }
    table.table-modern-pembimbing tbody td.text-left-col {
        text-align: left !important;
    }
    table.table-modern-pembimbing tbody td:last-child{
        border-right: none;
    }
    table.table-modern-pembimbing tbody tr:hover{
        background: #f8fafc;
    }
    .nip-cell{
        color: #64748b;
        font-size: 0.78rem;
        font-family: 'Courier New', monospace;
    }
    .pembimbing-name{
        font-weight: 600;
        color: #1e293b;
    }
    .unit-kerja-text{
        color: #334155;
        font-weight: 500;
        font-size: 0.78rem;
    }
    .action-group{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
    }
    .action-group .btn{
        border-radius: 8px;
        font-size: 0.8rem;
        padding: 0.3rem 0.45rem;
    }
</style>

<div class="container-fluid">
    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-person-badge-fill me-2"></i>Data Pembimbing</h3>
            <p>Kelola daftar pembimbing PKL beserta unit kerjanya di sini.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="export.php" class="btn btn-success btn-tambah shadow-sm d-flex align-items: center">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <button type="button" class="btn btn-success btn-tambah shadow-sm d-flex align-items: center" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel
            </button>
            <a href="tambah.php" class="btn btn-tambah">
                <i class="bi bi-plus-circle me-1"></i> Tambah Pembimbing
            </a>
        </div>
    </div>

    <!-- Modal Form Import Excel -->
    <div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="import.php" method="POST" enctype="multipart/form-data">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header bg-info text-white" style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                        <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up me-1"></i> Import Data Pembimbing Excel</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Pilih File CSV (.csv)</label>
                            <input type="file" name="file_excel" class="form-control" accept=".csv" required>
                        </div>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Pastikan struktur file CSV berformat UTF-8 dengan delimiter `;`.</small>
                    </div>
                    <div class="modal-footer bg-light" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="import" class="btn btn-info text-white btn-sm px-3 fw-bold">Upload & Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-person-badge-fill"></i></div>
            <div>
                <small>Total Pembimbing</small>
                <h4><?= $totalPembimbing; ?></h4>
            </div>
        </div>
    </div>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelPembimbing" class="table table-hover table-modern-pembimbing">
                    <thead>
                        <tr>
                            <th width="3%">No</th>
                            <th class="text-left-col" width="16%">Nama</th>
                            <th width="12%">NIP</th>
                            <th class="text-left-col" width="18%">Unit Kerja</th>
                            <th width="9%">Gol. Ruang</th>
                            <th width="11%">Pangkat</th>
                            <th class="text-left-col" width="14%">Nama Jabatan</th>
                            <th width="9%">Jenis Jabatan</th>
                            <th width="8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    if(mysqli_num_rows($data) > 0){
                        while($row = mysqli_fetch_assoc($data)){
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="text-left-col"><span class="pembimbing-name"><?= htmlspecialchars($row['nama']); ?></span></td>
                            <td><span class="nip-cell"><?= !empty($row['nip']) ? htmlspecialchars($row['nip']) : '-'; ?></span></td>
                            <td class="text-left-col text-truncate" style="max-width: 160px;" title="<?= htmlspecialchars($row['nama_unit_kerja'] ?? '-'); ?>">
                                <span class="unit-kerja-text">
                                    <?= !empty($row['nama_unit_kerja']) ? htmlspecialchars($row['nama_unit_kerja']) : '-'; ?>
                                </span>
                            </td>
                            <td><?= !empty($row['golongan_ruang']) ? htmlspecialchars($row['golongan_ruang']) : '-'; ?></td>
                            <td><?= !empty($row['pangkat']) ? htmlspecialchars($row['pangkat']) : '-'; ?></td>
                            <td class="text-left-col"><?= !empty($row['nama_jabatan']) ? htmlspecialchars($row['nama_jabatan']) : '-'; ?></td>
                            <td><?= !empty($row['jenis_jabatan']) ? htmlspecialchars($row['jenis_jabatan']) : '-'; ?></td>
                            <td>
                                <div class="action-group">
                                    <a href="detail.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm text-white" title="Detail">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm btnHapus" data-id="<?= $row['id']; ?>" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
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
$(function(){
    $("#tabelPembimbing").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language:{
            url:"https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: `
                <div class="empty-state" style="padding: 2.5rem 1rem; color: #94a3b8; text-align: center;">
                    <i class="bi bi-inbox" style="font-size: 2.5rem; display: block; margin-bottom: 0.8rem; color: #cbd5e1;"></i>
                    Belum ada data pembimbing
                </div>
            `
        },
        columnDefs: [
            { orderable: false, targets: [8] }
        ]
    });

    $(document).on('click', '.btnHapus', function(e){
        e.preventDefault();
        let id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'Data pembimbing ini tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626'
        }).then((result) => {
            if(result.isConfirmed){
                window.location = 'hapus.php?id=' + id;
            }
        });
    });
});
</script>
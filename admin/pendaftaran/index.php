<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // Kueri SQL diperbarui: Menambahkan JOIN ke tabel ruangan
    $data = mysqli_query($conn, "
SELECT
    p.*,
    m.nama,
    m.nim,
    u.nama_instansi,
    ps.nama_jurusan,
    pr.nama_periode,
    uk.unit_kerja,
    pb.nama AS nama_pembimbing,
    r.nama_ruangan

FROM pendaftaran p

LEFT JOIN pelajar m
ON p.pelajar_id = m.id

LEFT JOIN instansi u
ON m.instansi_id = u.id

LEFT JOIN jurusan ps
ON m.jurusan_id = ps.id

LEFT JOIN periode_pkl pr
ON p.periode_id = pr.id

LEFT JOIN unit_kerja uk
ON p.unit_kerja_id = uk.id

LEFT JOIN pembimbing pb
ON p.pembimbing_id = pb.id

LEFT JOIN ruangan r
ON m.id_ruangan = r.id_ruangan

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

.card-table {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
}

.dataTables_filter {
    text-align: right;
    margin-bottom: 1rem;
}

.dataTables_filter label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #475569;
}

.dataTables_filter input {
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    padding: 0.35rem 0.75rem;
    outline: none;
    width: 240px;
}

.dataTables_filter input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

table.datatable {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100% !important;
}

table.datatable thead th {
    background: #1e3a8a;
    color: #fff;
    border: none !important;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
    text-align: center;
    border-right: 1px solid rgba(255, 255, 255, 0.15) !important;
}

table.datatable thead th:last-child {
    border-right: none !important;
}

table.datatable tbody td {
    vertical-align: middle;
    font-size: 0.88rem;
    border-color: #eef1f5 !important;
    text-align: center;
    border-right: 1px solid #eef1f5;
}

table.datatable tbody td:last-child {
    border-right: none;
}

table.datatable tbody tr:hover {
    background: #f8fafc;
}

.mhs-name {
    font-weight: 600;
    color: #1e293b;
    display: block;
    text-align: center;
}

.mhs-nim {
    color: #94a3b8;
    font-size: 0.78rem;
}

.status-line {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    white-space: nowrap;
    font-size: 0.82rem;
    font-weight: 500;
}

.status-line .divider {
    color: #cbd5e1;
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}

.dot-warning {
    background: #f59e0b;
}

.dot-success {
    background: #16a34a;
}

.dot-danger {
    background: #dc2626;
}

.dot-neutral {
    background: #94a3b8;
}

.status-text-warning {
    color: #b45309;
}

.status-text-success {
    color: #15803d;
}

.status-text-danger {
    color: #b91c1c;
}

.status-text-neutral {
    color: #475569;
}

.file-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.file-icon-btn {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    text-decoration: none;
    border: 1px solid transparent;
    transition: all 0.15s ease;
    position: relative;
}

.file-icon-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
}

.file-surat {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
}

.file-cv {
    background: #f0fdf4;
    color: #15803d;
    border-color: #bbf7d0;
}

.file-transkrip {
    background: #fffbeb;
    color: #b45309;
    border-color: #fde68a;
}

.file-proposal {
    background: #fef2f2;
    color: #b91c1c;
    border-color: #fecaca;
}

/* REVISI: Styling untuk grup tombol aksi yang baru */
.action-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.action-group .btn {
    border-radius: 8px;
    font-size: 0.85rem;
    padding: 0.35rem 0.5rem;
}

@media (max-width: 768px) {
    .page-header-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.9rem;
    }
}
</style>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-journal-text me-2"></i>Data Pendaftaran PKL</h3>
            <p>Kelola seluruh data pendaftaran pelajar PKL di sini.</p>
        </div>
    </div>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th width="3%">No</th>
                            <th width="12%">Pelajar</th>
                            <th width="15%">Instansi</th>
                            <th width="10%">Jurusan</th>
                            <th width="8%">Periode</th>
                            <th width="15%">Unit Kerja</th>
                            <th width="10%">Ruangan</th>
                            <th width="10%">Pembimbing</th>
                            <th width="8%">Tanggal Daftar</th>
                            <th width="10%">Status</th>
                            <th width="10%">Berkas</th>
                            <!-- REVISI: Kolom aksi diperlebar sedikit untuk memuat 3 ikon -->
                            <th width="8%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($data)) {
                            ?>

                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <span class="mhs-name text-truncate d-inline-block w-100" style="max-width: 120px;" title="<?php echo htmlspecialchars($row['nama']); ?>">
                                    <?php echo $row['nama']; ?>
                                </span>
                                <span class="mhs-nim text-truncate d-inline-block w-100" style="max-width: 120px;"><?php echo $row['nim']; ?></span>
                            </td>
                            
                            <td class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($row['nama_instansi']); ?>">
                                <?php echo $row['nama_instansi']; ?>
                            </td>
                            
                            <td class="text-truncate" style="max-width: 100px;" title="<?php echo htmlspecialchars($row['nama_jurusan']); ?>">
                                <?php echo $row['nama_jurusan']; ?>
                            </td>
                            
                            <td class="text-truncate" style="max-width: 80px;" title="<?php echo htmlspecialchars($row['nama_periode']); ?>">
                                <?php echo $row['nama_periode']; ?>
                            </td>

                            <td class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($row['unit_kerja'] ?? '-'); ?>">
                                <?php echo ! empty($row['unit_kerja']) ? $row['unit_kerja'] : '<span class="text-muted">-</span>'; ?>
                            </td>

                            <td class="text-truncate" style="max-width: 100px;" title="<?php echo htmlspecialchars($row['nama_ruangan'] ?? '-'); ?>">
                                <?php echo ! empty($row['nama_ruangan']) ? $row['nama_ruangan'] : '<span class="text-muted">-</span>'; ?>
                            </td>

                            <td class="text-truncate" style="max-width: 120px;" title="<?php echo htmlspecialchars($row['nama_pembimbing'] ?? '-'); ?>">
                                <?php echo ! empty($row['nama_pembimbing']) ? $row['nama_pembimbing'] : '<span class="text-muted">-</span>'; ?>
                            </td>
                            
                            <td style="white-space: nowrap;">
                                <?php echo date('d-m-Y', strtotime($row['tanggal_daftar'])); ?>
                            </td>
                            
                            <td>
                                <div class="status-line">
                                    <?php if ($row['status'] == "menunggu") {?>
                                    <span class="status-dot dot-neutral"></span>
                                    <span class="status-text-neutral">Belum Diverifikasi</span>
                                    <?php } else {?>
                                    <span class="status-dot dot-neutral"></span>
                                    <span class="status-text-neutral">Diverifikasi</span>
                                    <?php }?>

                                    <span class="divider">&bull;</span>

                                    <?php
                                        if ($row['status'] == "menunggu") {
                                                echo '<span class="status-dot dot-warning"></span><span class="status-text-warning">Menunggu</span>';
                                            } elseif ($row['status'] == "diterima") {
                                                echo '<span class="status-dot dot-success"></span><span class="status-text-success">Diterima</span>';
                                            } else {
                                                echo '<span class="status-dot dot-danger"></span><span class="status-text-danger">Ditolak</span>';
                                            }
                                        ?>
                                </div>
                            </td>
                            <td>
                                <div class="file-group">
                                    <a target="_blank"
                                        href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $row['surat_pengantar']; ?>"
                                        class="file-icon-btn file-surat" data-bs-toggle="tooltip"
                                        title="Surat Pengantar">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                    <a target="_blank"
                                        href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $row['cv']; ?>"
                                        class="file-icon-btn file-cv" data-bs-toggle="tooltip" title="CV">
                                        <i class="bi bi-file-earmark-person"></i>
                                    </a>
                                    <a target="_blank"
                                        href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $row['transkrip']; ?>"
                                        class="file-icon-btn file-transkrip" data-bs-toggle="tooltip" title="Transkrip">
                                        <i class="bi bi-file-earmark-spreadsheet"></i>
                                    </a>
                                    <a target="_blank"
                                        href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $row['proposal']; ?>"
                                        class="file-icon-btn file-proposal" data-bs-toggle="tooltip" title="Proposal">
                                        <i class="bi bi-file-earmark-richtext"></i>
                                    </a>
                                </div>
                            </td>

                            <!-- REVISI: Menu Dropdown diganti jadi Tombol Ikon Sejajar -->
                            <td class="text-center">
                                <div class="action-group">
                                    <a href="detail.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm text-white" data-bs-toggle="tooltip" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    <?php if ($row['status'] == "menunggu") {?>
                                    <a href="verifikasi.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm text-white" data-bs-toggle="tooltip" title="Verifikasi Pendaftaran">
                                        <i class="bi bi-check2-square"></i>
                                    </a>
                                    <?php } else {?>
                                    <a href="verifikasi.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm text-white" data-bs-toggle="tooltip" title="Edit Penempatan">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <?php }?>
                                    
                                    <button type="button" class="btn btn-danger btn-sm btnHapus" data-id="<?php echo $row['id']; ?>" data-bs-toggle="tooltip" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php }?>
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
$(document).ready(function() {

    // REVISI: Re-inisialisasi Tooltip agar tombol aksi baru juga memiliki tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });

    $('.datatable').DataTable({
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: {
                previous: "Sebelumnya",
                next: "Berikutnya"
            },
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data pendaftaran"
        },
        columnDefs: [
            { orderable: false, targets: [10, 11] } // Mematikan urut di kolom Berkas dan Aksi
        ]
    });

    // REVISI: Script penghapusan menggunakan SweetAlert diaktifkan
    $(document).on('click', '.btnHapus', function(e) {
        e.preventDefault();
        let id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'Data pendaftaran ini tidak dapat dikembalikan.',
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

<?php if (isset($_GET['success'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '<?php echo $_GET['success']; ?>',
    confirmButtonColor: '#1e3a8a',
    timer: 2500
});
<?php endif; ?>
</script>
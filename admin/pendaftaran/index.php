<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$data = mysqli_query($conn, "
SELECT
    p.*,
    m.nama,
    m.nim,
    u.nama_universitas,
    ps.nama_prodi,
    pr.nama_periode,
    b.nama_bidang,
    pb.nama AS nama_pembimbing

FROM pendaftaran p

LEFT JOIN mahasiswa m
ON p.mahasiswa_id = m.id

LEFT JOIN universitas u
ON m.universitas_id = u.id

LEFT JOIN program_studi ps
ON m.program_studi_id = ps.id

LEFT JOIN periode_pkl pr
ON p.periode_id = pr.id

LEFT JOIN bidang b
ON p.bidang_id = b.id

LEFT JOIN pembimbing pb
ON p.pembimbing_id = pb.id

ORDER BY p.id DESC
");

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
    }
    .page-header-bar h3{
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .page-header-bar p{
        color: #64748b;
        font-size: 0.85rem;
        margin: 0.2rem 0 0;
    }

    .btn-tambah{
        border-radius: 10px;
        font-weight: 600;
        padding: 0.55rem 1.1rem;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border: none;
        box-shadow: 0 4px 10px rgba(37,99,235,0.3);
    }
    .btn-tambah:hover{
        background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    }

    .card-table{
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    }

    table.datatable{
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100% !important;
    }
    table.datatable thead th{
        background: #1e3a8a;
        color: #fff;
        border: none !important;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
        text-align: center;
        border-right: 1px solid rgba(255,255,255,0.15) !important;
    }
    table.datatable thead th:last-child{
        border-right: none !important;
    }
    table.datatable tbody td{
        vertical-align: middle;
        font-size: 0.88rem;
        border-color: #eef1f5 !important;
        text-align: center;
        border-right: 1px solid #eef1f5;
    }
    table.datatable tbody td:last-child{
        border-right: none;
    }
    table.datatable tbody tr:hover{
        background: #f8fafc;
    }

    .mhs-name{
        font-weight: 600;
        color: #1e293b;
        display: block;
        text-align: center;
    }
    .mhs-nim{
        color: #94a3b8;
        font-size: 0.78rem;
    }

    .status-line{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        white-space: nowrap;
        font-size: 0.82rem;
        font-weight: 500;
    }
    .status-line .divider{
        color: #cbd5e1;
    }
    .status-dot{
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .dot-warning{ background: #f59e0b; }
    .dot-success{ background: #16a34a; }
    .dot-danger{ background: #dc2626; }
    .dot-neutral{ background: #94a3b8; }

    .status-text-warning{ color: #b45309; }
    .status-text-success{ color: #15803d; }
    .status-text-danger{ color: #b91c1c; }
    .status-text-neutral{ color: #475569; }

    .file-group{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }
    .file-icon-btn{
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
    .file-icon-btn:hover{
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.12);
    }
    .file-surat{ background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .file-cv{ background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .file-transkrip{ background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .file-proposal{ background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

    .action-group .btn{
        border-radius: 8px;
        font-size: 0.78rem;
        padding: 0.32rem 0.6rem;
        margin: 0 0.1rem 0.25rem 0;
    }

    .dropdown-aksi .btn{
        border-radius: 8px;
    }
</style>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-journal-text me-2"></i>Data Pendaftaran PKL</h3>
            <p>Kelola seluruh data pendaftaran mahasiswa PKL di sini.</p>
        </div>

        <a href="tambah.php" class="btn btn-tambah text-white">
            <i class="bi bi-plus-circle me-1"></i>
            Tambah Pendaftaran
        </a>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover datatable">

                    <thead>

                        <tr>

                            <th>No</th>
                            <th>Mahasiswa</th>
                            <th>Universitas</th>
                            <th>Program Studi</th>
                            <th>Periode</th>
                            <th>Bidang</th>
                            <th>Pembimbing</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                            <th>Berkas</th>
                            <th width="80" class="text-center">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $no = 1;

                    while($row = mysqli_fetch_assoc($data)){

                    ?>

                    <tr>

                        <td><?= $no++; ?></td>

                        <td>
                            <span class="mhs-name"><?= $row['nama']; ?></span>
                            <span class="mhs-nim"><?= $row['nim']; ?></span>
                        </td>

                        <td><?= $row['nama_universitas']; ?></td>

                        <td><?= $row['nama_prodi']; ?></td>

                        <td><?= $row['nama_periode']; ?></td>

                        <td><?= $row['nama_bidang']; ?></td>

                        <td><?= !empty($row['nama_pembimbing']) ? $row['nama_pembimbing'] : '<span class="text-muted">-</span>'; ?></td>

                        <td><?= date('d-m-Y', strtotime($row['tanggal_daftar'])); ?></td>

                        <td>
                            <div class="status-line">
                                <?php if($row['status'] == "menunggu"){ ?>
                                    <span class="status-dot dot-neutral"></span>
                                    <span class="status-text-neutral">Belum Diverifikasi</span>
                                <?php }else{ ?>
                                    <span class="status-dot dot-neutral"></span>
                                    <span class="status-text-neutral">Diverifikasi</span>
                                <?php } ?>

                                <span class="divider">&bull;</span>

                                <?php

                                if($row['status'] == "menunggu"){
                                    echo '<span class="status-dot dot-warning"></span><span class="status-text-warning">Menunggu</span>';
                                }elseif($row['status'] == "diterima"){
                                    echo '<span class="status-dot dot-success"></span><span class="status-text-success">Diterima</span>';
                                }else{
                                    echo '<span class="status-dot dot-danger"></span><span class="status-text-danger">Ditolak</span>';
                                }

                                ?>
                            </div>
                        </td>

                        <td>
                            <div class="file-group">
                                <a target="_blank"
                                   href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['surat_pengantar']; ?>"
                                   class="file-icon-btn file-surat"
                                   data-bs-toggle="tooltip"
                                   title="Surat Pengantar">
                                    <i class="bi bi-file-earmark-text"></i>
                                </a>
                                <a target="_blank"
                                   href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['cv']; ?>"
                                   class="file-icon-btn file-cv"
                                   data-bs-toggle="tooltip"
                                   title="CV">
                                    <i class="bi bi-file-earmark-person"></i>
                                </a>
                                <a target="_blank"
                                   href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['transkrip']; ?>"
                                   class="file-icon-btn file-transkrip"
                                   data-bs-toggle="tooltip"
                                   title="Transkrip">
                                    <i class="bi bi-file-earmark-spreadsheet"></i>
                                </a>
                                <a target="_blank"
                                   href="<?= BASE_URL ?>assets/upload/pendaftaran/<?= $row['proposal']; ?>"
                                   class="file-icon-btn file-proposal"
                                   data-bs-toggle="tooltip"
                                   title="Proposal">
                                    <i class="bi bi-file-earmark-richtext"></i>
                                </a>
                            </div>
                        </td>

                        <td class="text-center">
                            <div class="dropdown dropdown-aksi">
                                <button class="btn btn-light btn-sm border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item" href="detail.php?id=<?= $row['id']; ?>">
                                            <i class="bi bi-eye me-2 text-info"></i>Detail
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="edit.php?id=<?= $row['id']; ?>">
                                            <i class="bi bi-pencil-square me-2 text-warning"></i>Edit
                                        </a>
                                    </li>
                                    <?php if($row['status'] == "menunggu"){ ?>
                                    <li>
                                        <a class="dropdown-item" href="verifikasi.php?id=<?= $row['id']; ?>">
                                            <i class="bi bi-check2-square me-2 text-success"></i>Verifikasi
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger"
                                           href="hapus.php?id=<?= $row['id']; ?>"
                                           onclick="return confirm('Yakin hapus data?')">
                                            <i class="bi bi-trash me-2"></i>Hapus
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>

                    </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<script>

$(document).ready(function(){

    // Aktifkan tooltip untuk ikon berkas
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (el) {
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
        }
    });

});

</script>

<?php

include "../../include/footer.php";

?>
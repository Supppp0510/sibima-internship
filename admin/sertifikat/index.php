<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

$query = mysqli_query($conn, "

SELECT

m.id AS mahasiswa_id,

m.nama,
m.nim,

u.nama_universitas,

ps.nama_prodi,

b.nama_bidang,

pb.nama AS nama_pembimbing,

pn.nilai_akhir,

s.id AS sertifikat_id,
s.nomor_sertifikat,
s.tanggal_terbit,
s.file_pdf

FROM mahasiswa m

INNER JOIN pendaftaran pd
ON pd.mahasiswa_id = m.id
AND pd.status='diterima'

INNER JOIN penilaian pn
ON pn.mahasiswa_id = m.id

LEFT JOIN pembimbing pb
ON pb.id = pn.pembimbing_id

LEFT JOIN bidang b
ON b.id = pd.bidang_id

LEFT JOIN universitas u
ON u.id = m.universitas_id

LEFT JOIN program_studi ps
ON ps.id = m.program_studi_id

LEFT JOIN sertifikat s
ON s.mahasiswa_id = m.id

ORDER BY

m.nama ASC

");

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

    .card-table{
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    }

    table.table-modern-sertif{
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100% !important;
    }
    table.table-modern-sertif thead th{
        background: #2563eb;
        color: #fff;
        border: none !important;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
        text-align: center;
        border-right: 1px solid rgba(255,255,255,0.15) !important;
    }
    table.table-modern-sertif thead th:last-child{
        border-right: none !important;
    }
    table.table-modern-sertif tbody td{
        vertical-align: middle;
        font-size: 0.88rem;
        border-color: #eef1f5 !important;
        text-align: center;
        border-right: 1px solid #eef1f5;
    }
    table.table-modern-sertif tbody td:last-child{
        border-right: none;
    }
    table.table-modern-sertif tbody tr:hover{
        background: #f8fafc;
    }

    .mhs-name{
        font-weight: 600;
        color: #1e293b;
        display: block;
    }
    .mhs-nim{
        color: #94a3b8;
        font-size: 0.78rem;
        font-family: 'Courier New', monospace;
    }

    .plain-text{
        color: #334155;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .nilai-badge{
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 55px;
        padding: 0.35rem 0.8rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .nilai-a{ background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .nilai-b{ background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .nilai-c{ background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .nilai-d{ background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .no-sertif{
        font-family: 'Courier New', monospace;
        color: #334155;
        font-size: 0.82rem;
    }

    .tanggal-cell{
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #475569;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .badge-soft{
        padding: 0.4rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        white-space: nowrap;
    }
    .badge-soft-belum{ background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-soft-sudah{ background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

    .action-group{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        flex-wrap: wrap;
    }
    .action-group .btn{
        border-radius: 8px;
        font-size: 0.78rem;
        padding: 0.35rem 0.7rem;
    }
    .btn-detail{
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .btn-detail:hover{
        background: #1d4ed8;
        color: #fff;
    }
    .btn-generate{
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border: none;
        color: #fff;
    }
    .btn-generate:hover{
        color: #fff;
        background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    }

    .empty-state{
        padding: 2.5rem 1rem;
        color: #94a3b8;
    }
    .empty-state i{
        font-size: 2.2rem;
        display: block;
        margin-bottom: 0.6rem;
    }
</style>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-award-fill me-2"></i>Data Sertifikat PKL</h3>
            <p>Kelola dan generate sertifikat mahasiswa yang sudah dinilai di sini.</p>
        </div>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelSertifikat" class="table table-hover table-modern-sertif">

                    <thead>

                        <tr>

                            <th width="60">No</th>
                            <th>Mahasiswa</th>
                            <th width="170">Universitas</th>
                            <th width="150">Program Studi</th>
                            <th width="150">Bidang</th>
                            <th width="140">Pembimbing</th>
                            <th width="90">Nilai</th>
                            <th width="150">No Sertifikat</th>
                            <th width="120">Tanggal</th>
                            <th width="140">Status</th>
                            <th width="260">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $no = 1;

                    if(mysqli_num_rows($query) > 0){

                        while($row = mysqli_fetch_assoc($query)){

                            $nilai = (float) $row['nilai_akhir'];

                            if($nilai >= 85){
                                $nilaiClass = 'nilai-a';
                            }elseif($nilai >= 75){
                                $nilaiClass = 'nilai-b';
                            }elseif($nilai >= 60){
                                $nilaiClass = 'nilai-c';
                            }else{
                                $nilaiClass = 'nilai-d';
                            }

                    ?>

                    <tr>

                        <td><?= $no++; ?></td>

                        <td>
                            <span class="mhs-name"><?= $row['nama']; ?></span>
                            <span class="mhs-nim"><?= $row['nim']; ?></span>
                        </td>

                        <td><span class="plain-text"><?= !empty($row['nama_universitas']) ? $row['nama_universitas'] : '-'; ?></span></td>

                        <td><span class="plain-text"><?= !empty($row['nama_prodi']) ? $row['nama_prodi'] : '-'; ?></span></td>

                        <td><span class="plain-text"><?= !empty($row['nama_bidang']) ? $row['nama_bidang'] : '-'; ?></span></td>

                        <td><span class="plain-text"><?= !empty($row['nama_pembimbing']) ? $row['nama_pembimbing'] : '-'; ?></span></td>

                        <td>
                            <span class="nilai-badge <?= $nilaiClass; ?>">
                                <?= number_format($nilai, 2); ?>
                            </span>
                        </td>

                        <td>
                            <span class="no-sertif"><?= !empty($row['nomor_sertifikat']) ? $row['nomor_sertifikat'] : '-'; ?></span>
                        </td>

                        <td>
                            <?php if(!empty($row['tanggal_terbit'])){ ?>
                                <span class="tanggal-cell">
                                    <i class="bi bi-calendar-event"></i>
                                    <?= date('d-m-Y', strtotime($row['tanggal_terbit'])); ?>
                                </span>
                            <?php }else{ ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
                        </td>

                        <td>
                            <?php if(empty($row['sertifikat_id'])){ ?>
                                <span class="badge-soft badge-soft-belum">
                                    <i class="bi bi-hourglass-split"></i> Belum Dibuat
                                </span>
                            <?php }else{ ?>
                                <span class="badge-soft badge-soft-sudah">
                                    <i class="bi bi-check-circle-fill"></i> Sudah Dibuat
                                </span>
                            <?php } ?>
                        </td>

                        <td>
                            <div class="action-group">
                                <?php if(empty($row['sertifikat_id'])){ ?>

                                    <a href="generate.php?id=<?= $row['mahasiswa_id']; ?>" class="btn btn-generate btn-sm">
                                        <i class="bi bi-file-earmark-pdf"></i> Generate
                                    </a>

                                <?php }else{ ?>

                                    <a href="detail.php?id=<?= $row['sertifikat_id']; ?>" class="btn btn-detail btn-sm">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>

                                    <a href="download.php?id=<?= $row['sertifikat_id']; ?>" class="btn btn-success btn-sm">
                                        <i class="bi bi-download"></i> Download
                                    </a>

                                    <button type="button" class="btn btn-danger btn-sm btnHapus" data-id="<?= $row['sertifikat_id']; ?>">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>

                                <?php } ?>
                            </div>
                        </td>

                    </tr>

                    <?php

                        }

                    }else{

                    ?>

                    <tr>
                        <td colspan="11">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                Belum ada mahasiswa yang siap dibuatkan sertifikat
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

$(function(){

    $("#tabelSertifikat").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language:{
            url:"//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"
        }
    });

    $(document).on('click', '.btnHapus', function(e){

        e.preventDefault();

        let id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Sertifikat?',
            text: 'Sertifikat ini tidak dapat dikembalikan.',
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

<?php

include "../../include/footer.php";

?>
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

$query = mysqli_query($conn,"
SELECT

pn.*,

m.nama,
m.nim,

u.nama_universitas,

ps.nama_prodi,

b.nama_bidang,

pb.nama AS nama_pembimbing

FROM penilaian pn

LEFT JOIN mahasiswa m
ON pn.mahasiswa_id=m.id

LEFT JOIN universitas u
ON m.universitas_id=u.id

LEFT JOIN program_studi ps
ON m.program_studi_id=ps.id

LEFT JOIN pembimbing pb
ON pn.pembimbing_id=pb.id

LEFT JOIN pendaftaran pd
ON pd.mahasiswa_id=m.id
AND pd.status='diterima'

LEFT JOIN bidang b
ON pd.bidang_id=b.id

ORDER BY

pn.created_at DESC

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

    .btn-tambah{
        border-radius: 10px;
        font-weight: 600;
        padding: 0.55rem 1.1rem;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border: none;
        box-shadow: 0 4px 10px rgba(37,99,235,0.3);
        color: #fff;
    }
    .btn-tambah:hover{
        background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        color: #fff;
    }

    .card-table{
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    }

    table.table-modern-nilai{
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100% !important;
    }
    table.table-modern-nilai thead th{
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
    table.table-modern-nilai thead th:last-child{
        border-right: none !important;
    }
    table.table-modern-nilai tbody td{
        vertical-align: middle;
        font-size: 0.88rem;
        border-color: #eef1f5 !important;
        text-align: center;
        border-right: 1px solid #eef1f5;
    }
    table.table-modern-nilai tbody td:last-child{
        border-right: none;
    }
    table.table-modern-nilai tbody tr:hover{
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
        min-width: 60px;
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .nilai-a{ background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .nilai-b{ background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .nilai-c{ background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .nilai-d{ background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .action-group{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
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
            <h3><i class="bi bi-clipboard-data me-2"></i>Data Penilaian PKL</h3>
            <p>Kelola nilai akhir mahasiswa PKL di sini.</p>
        </div>

        <a href="tambah.php" class="btn btn-tambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Penilaian
        </a>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelPenilaian" class="table table-hover table-modern-nilai">

                    <thead>

                        <tr>

                            <th width="60">No</th>
                            <th>Mahasiswa</th>
                            <th width="170">Universitas</th>
                            <th width="150">Program Studi</th>
                            <th width="150">Bidang</th>
                            <th width="140">Pembimbing</th>
                            <th width="120">Nilai Akhir</th>
                            <th width="220">Aksi</th>

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
                            <div class="action-group">
                                <a href="detail.php?id=<?= $row['id']; ?>" class="btn btn-detail btn-sm">
                                    <i class="bi bi-eye"></i> Detail
                                </a>

                                <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm text-white">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>

                                <button type="button" class="btn btn-danger btn-sm btnHapus" data-id="<?= $row['id']; ?>">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </td>

                    </tr>

                    <?php

                        }

                    }else{

                    ?>

                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                Belum ada data penilaian
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

    $("#tabelPenilaian").DataTable({
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
            title: 'Hapus Data?',
            text: 'Data penilaian ini tidak dapat dikembalikan.',
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
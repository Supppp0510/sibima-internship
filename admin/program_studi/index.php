<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$data = mysqli_query($conn,"
SELECT *
FROM program_studi
ORDER BY id DESC
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

    table.table-modern-prodi{
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100% !important;
    }
    table.table-modern-prodi thead th{
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
    table.table-modern-prodi thead th:last-child{
        border-right: none !important;
    }
    table.table-modern-prodi tbody td{
        vertical-align: middle;
        font-size: 0.88rem;
        border-color: #eef1f5 !important;
        text-align: center;
        border-right: 1px solid #eef1f5;
    }
    table.table-modern-prodi tbody td:last-child{
        border-right: none;
    }
    table.table-modern-prodi tbody tr:hover{
        background: #f8fafc;
    }

    .prodi-name{
        font-weight: 600;
        color: #1e293b;
    }

    .badge-jenjang{
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .jenjang-d3{ background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .jenjang-d4{ background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .jenjang-s1{ background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .jenjang-s2{ background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
    .jenjang-s3{ background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .jenjang-default{ background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; }

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
            <h3><i class="bi bi-book-half me-2"></i>Data Program Studi</h3>
            <p>Kelola daftar program studi mahasiswa PKL di sini.</p>
        </div>

        <a href="tambah.php" class="btn btn-tambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Program Studi
        </a>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelProdi" class="table table-hover table-modern-prodi">

                    <thead>

                        <tr>

                            <th width="60">No</th>
                            <th>Program Studi</th>
                            <th width="140">Jenjang</th>
                            <th width="180">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $no = 1;

                    if(mysqli_num_rows($data) > 0){

                        while($row = mysqli_fetch_assoc($data)){

                            $jenjang = strtoupper(trim($row['jenjang']));
                            $jenjangClass = match($jenjang){
                                'D3' => 'jenjang-d3',
                                'D4' => 'jenjang-d4',
                                'S1' => 'jenjang-s1',
                                'S2' => 'jenjang-s2',
                                'S3' => 'jenjang-s3',
                                default => 'jenjang-default',
                            };

                    ?>

                    <tr>

                        <td><?= $no++; ?></td>

                        <td><span class="prodi-name"><?= $row['nama_prodi']; ?></span></td>

                        <td>
                            <span class="badge-jenjang <?= $jenjangClass; ?>">
                                <i class="bi bi-mortarboard-fill"></i>
                                <?= $row['jenjang']; ?>
                            </span>
                        </td>

                        <td>
                            <div class="action-group">
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
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                Belum ada data program studi
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

    $("#tabelProdi").DataTable({
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
            text: 'Data program studi ini tidak dapat dikembalikan.',
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
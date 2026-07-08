<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$data = mysqli_query($conn,"
SELECT *
FROM universitas
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

    table.table-modern-univ{
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100% !important;
    }
    table.table-modern-univ thead th{
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
    table.table-modern-univ thead th:last-child{
        border-right: none !important;
    }
    table.table-modern-univ thead th.text-left-col{
        text-align: left;
    }
    table.table-modern-univ tbody td{
        vertical-align: middle;
        font-size: 0.88rem;
        border-color: #eef1f5 !important;
        text-align: center;
        border-right: 1px solid #eef1f5;
    }
    table.table-modern-univ tbody td.text-left-col{
        text-align: left !important;
    }
    table.table-modern-univ tbody td:last-child{
        border-right: none;
    }
    table.table-modern-univ tbody tr:hover{
        background: #f8fafc;
    }

    .univ-name{
        font-weight: 600;
        color: #1e293b;
    }

    .univ-alamat{
        color: #64748b;
        font-size: 0.85rem;
        max-width: 320px;
    }

    .badge-kota{
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

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
            <h3><i class="bi bi-mortarboard me-2"></i>Data Universitas</h3>
            <p>Kelola daftar universitas asal mahasiswa PKL di sini.</p>
        </div>

        <a href="tambah.php" class="btn btn-tambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Universitas
        </a>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelUniversitas" class="table table-hover table-modern-univ">

                    <thead>

                        <tr>

                            <th width="60">No</th>
                            <th>Nama Universitas</th>
                            <th class="text-left-col">Alamat</th>
                            <th width="160">Kota</th>
                            <th width="180">Aksi</th>

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

                        <td><span class="univ-name"><?= $row['nama_universitas']; ?></span></td>

                        <td class="text-left-col">
                            <span class="univ-alamat"><?= !empty($row['alamat']) ? $row['alamat'] : '<span class="text-muted">-</span>'; ?></span>
                        </td>

                        <td>
                            <span class="badge-kota">
                                <i class="bi bi-geo-alt-fill"></i>
                                <?= !empty($row['kota']) ? $row['kota'] : '-'; ?>
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
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                Belum ada data universitas
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

    $("#tabelUniversitas").DataTable({
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
            text: 'Data universitas ini tidak dapat dikembalikan.',
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
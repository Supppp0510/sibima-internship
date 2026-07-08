<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$data = mysqli_query($conn,"
SELECT
m.*,
u.nama_universitas,
p.nama_prodi
FROM mahasiswa m
LEFT JOIN universitas u
ON m.universitas_id=u.id
LEFT JOIN program_studi p
ON m.program_studi_id=p.id
ORDER BY m.id DESC
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

    table.table-modern-mhs{
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100% !important;
    }
    table.table-modern-mhs thead th{
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
    table.table-modern-mhs thead th:last-child{
        border-right: none !important;
    }
    table.table-modern-mhs tbody td{
        vertical-align: middle;
        font-size: 0.88rem;
        border-color: #eef1f5 !important;
        text-align: center;
        border-right: 1px solid #eef1f5;
    }
    table.table-modern-mhs tbody td:last-child{
        border-right: none;
    }
    table.table-modern-mhs tbody tr:hover{
        background: #f8fafc;
    }

    .foto-mhs{
        width: 46px;
        height: 46px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }
    .foto-placeholder{
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
    }

    .nim-cell{
        color: #94a3b8;
        font-size: 0.82rem;
        font-family: 'Courier New', monospace;
    }

    .mhs-name{
        font-weight: 600;
        color: #1e293b;
    }

    .univ-text{
        color: #1e293b;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .prodi-text{
        color: #334155;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .contact-link{
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 500;
        text-decoration: none;
        font-size: 0.85rem;
    }
    .contact-wa{
        color: #15803d;
    }
    .contact-wa:hover{
        text-decoration: underline;
        color: #166534;
    }
    .contact-email{
        color: #2563eb;
    }
    .contact-email:hover{
        text-decoration: underline;
        color: #1d4ed8;
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
            <h3><i class="bi bi-people-fill me-2"></i>Data Mahasiswa</h3>
            <p>Kelola daftar mahasiswa peserta PKL di sini.</p>
        </div>

        <a href="tambah.php" class="btn btn-tambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Mahasiswa
        </a>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelMahasiswa" class="table table-hover table-modern-mhs">

                    <thead>

                        <tr>

                            <th width="60">No</th>
                            <th width="80">Foto</th>
                            <th width="120">NIM</th>
                            <th>Nama</th>
                            <th width="200">Universitas</th>
                            <th width="150">Program Studi</th>
                            <th width="150">No HP</th>
                            <th width="180">Email</th>
                            <th width="170">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $no = 1;

                    if(mysqli_num_rows($data) > 0){

                        while($row = mysqli_fetch_assoc($data)){

                            $inisial = strtoupper(substr($row['nama'], 0, 1));

                            $hpBersih = preg_replace('/[^0-9]/', '', $row['no_hp']);
                            if(substr($hpBersih, 0, 1) == '0'){
                                $hpBersih = '62' . substr($hpBersih, 1);
                            }

                    ?>

                    <tr>

                        <td><?= $no++; ?></td>

                        <td>
                            <?php if(empty($row['foto'])){ ?>
                                <span class="foto-placeholder"><?= $inisial; ?></span>
                            <?php }else{ ?>
                                <img src="../../assets/upload/mahasiswa/foto/<?= $row['foto']; ?>"
                                     class="foto-mhs"
                                     alt="Foto <?= $row['nama']; ?>">
                            <?php } ?>
                        </td>

                        <td><span class="nim-cell"><?= $row['nim']; ?></span></td>

                        <td><span class="mhs-name"><?= $row['nama']; ?></span></td>

                        <td>
                            <span class="univ-text"><?= !empty($row['nama_universitas']) ? $row['nama_universitas'] : '-'; ?></span>
                        </td>

                        <td>
                            <span class="prodi-text"><?= !empty($row['nama_prodi']) ? $row['nama_prodi'] : '-'; ?></span>
                        </td>

                        <td>
                            <?php if(!empty($row['no_hp'])){ ?>
                                <a href="https://wa.me/<?= $hpBersih; ?>" target="_blank" class="contact-link contact-wa">
                                    <i class="bi bi-whatsapp"></i> <?= $row['no_hp']; ?>
                                </a>
                            <?php }else{ ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
                        </td>

                        <td>
                            <?php if(!empty($row['email'])){ ?>
                                <a href="mailto:<?= $row['email']; ?>" class="contact-link contact-email">
                                    <i class="bi bi-envelope-fill"></i> <?= $row['email']; ?>
                                </a>
                            <?php }else{ ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
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
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                Belum ada data mahasiswa
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

    $("#tabelMahasiswa").DataTable({
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
            text: 'Data mahasiswa ini tidak dapat dikembalikan.',
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
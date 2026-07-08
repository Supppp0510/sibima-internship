<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

$query = mysqli_query($conn,"
SELECT

a.*,

m.nama,
m.nim,

u.nama_universitas,

ps.nama_prodi,

b.nama_bidang,

pb.nama AS nama_pembimbing

FROM absensi a

LEFT JOIN mahasiswa m
ON a.mahasiswa_id=m.id

LEFT JOIN universitas u
ON m.universitas_id=u.id

LEFT JOIN program_studi ps
ON m.program_studi_id=ps.id

LEFT JOIN pendaftaran p
ON p.mahasiswa_id=m.id
AND p.status='diterima'

LEFT JOIN bidang b
ON p.bidang_id=b.id

LEFT JOIN pembimbing pb
ON p.pembimbing_id=pb.id

ORDER BY
a.tanggal DESC,
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

    table.table-modern-absensi{
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100% !important;
    }
    table.table-modern-absensi thead th{
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
    table.table-modern-absensi thead th:last-child{
        border-right: none !important;
    }
    table.table-modern-absensi tbody td{
        vertical-align: middle;
        font-size: 0.88rem;
        border-color: #eef1f5 !important;
        text-align: center;
        border-right: 1px solid #eef1f5;
    }
    table.table-modern-absensi tbody td:last-child{
        border-right: none;
    }
    table.table-modern-absensi tbody tr:hover{
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

    .tanggal-cell{
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .jam-cell{
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        color: #334155;
    }

    .badge-soft{
        padding: 0.4rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .badge-soft-hadir{ background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-soft-izin{ background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-soft-sakit{ background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-soft-alpha{ background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .btn-detail{
        border-radius: 8px;
        font-size: 0.78rem;
        padding: 0.35rem 0.7rem;
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
            <h3><i class="bi bi-geo-alt-fill me-2"></i>Data Absensi PKL</h3>
            <p>Riwayat kehadiran mahasiswa PKL di sini.</p>
        </div>
    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelAbsensi" class="table table-hover table-modern-absensi">

                    <thead>

                        <tr>

                            <th width="60">No</th>
                            <th>Mahasiswa</th>
                            <th width="170">Universitas</th>
                            <th width="150">Program Studi</th>
                            <th width="150">Bidang</th>
                            <th width="140">Pembimbing</th>
                            <th width="120">Tanggal</th>
                            <th width="110">Jam Masuk</th>
                            <th width="110">Jam Pulang</th>
                            <th width="110">Status</th>
                            <th width="110">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $no = 1;

                    if(mysqli_num_rows($query) > 0){

                        while($row = mysqli_fetch_assoc($query)){

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
                            <span class="tanggal-cell">
                                <i class="bi bi-calendar-event"></i>
                                <?= date('d-m-Y', strtotime($row['tanggal'])); ?>
                            </span>
                        </td>

                        <td>
                            <span class="jam-cell">
                                <i class="bi bi-box-arrow-in-right text-success"></i>
                                <?= $row['jam_masuk'] ? $row['jam_masuk'] : "-"; ?>
                            </span>
                        </td>

                        <td>
                            <span class="jam-cell">
                                <i class="bi bi-box-arrow-right text-danger"></i>
                                <?= $row['jam_pulang'] ? $row['jam_pulang'] : "-"; ?>
                            </span>
                        </td>

                        <td>
                            <?php

                            switch($row['status']){

                                case "hadir":
                                    echo '<span class="badge-soft badge-soft-hadir"><i class="bi bi-check-circle-fill"></i> Hadir</span>';
                                    break;

                                case "izin":
                                    echo '<span class="badge-soft badge-soft-izin"><i class="bi bi-envelope-fill"></i> Izin</span>';
                                    break;

                                case "sakit":
                                    echo '<span class="badge-soft badge-soft-sakit"><i class="bi bi-thermometer-half"></i> Sakit</span>';
                                    break;

                                case "alpha":
                                    echo '<span class="badge-soft badge-soft-alpha"><i class="bi bi-x-circle-fill"></i> Alpha</span>';
                                    break;

                            }

                            ?>
                        </td>

                        <td>
                            <a href="detail.php?id=<?= $row['id']; ?>" class="btn btn-detail btn-sm">
                                <i class="bi bi-eye"></i> Detail
                            </a>
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
                                Belum ada data absensi
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

    $("#tabelAbsensi").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language:{
            url:"//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"
        }
    });

});

</script>

<?php

include "../../include/footer.php";

?>
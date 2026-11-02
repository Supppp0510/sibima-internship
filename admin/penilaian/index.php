<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";


/* =========================================================
   QUERY DATA PENILAIAN
   ========================================================= */

$query = mysqli_query($conn,"
    SELECT

    pn.*,
    pn.nilai_akhir AS nilai_pembimbing_db,
    pn.tugas_akhir,

    m.id AS pelajar_id,
    m.nama,
    m.nim,

    u.nama_instansi,

    ps.nama_jurusan,

    uk.unit_kerja AS nama_bidang,

    pb.nama AS nama_pembimbing,

    la.status AS status_laporan,
    la.file_laporan

    FROM penilaian pn

    LEFT JOIN pelajar m
    ON pn.pelajar_id = m.id

    LEFT JOIN instansi u
    ON m.instansi_id = u.id

    LEFT JOIN jurusan ps
    ON m.jurusan_id = ps.id

    LEFT JOIN pembimbing pb
    ON pn.pembimbing_id = pb.id

    LEFT JOIN laporan_akhir la
    ON la.pelajar_id = m.id

    LEFT JOIN pendaftaran pd
    ON pd.pelajar_id = m.id
    AND pd.status = 'diterima'

    LEFT JOIN unit_kerja uk
    ON pd.unit_kerja_id = uk.id

    ORDER BY pn.created_at DESC

");


/* =========================================================
   CEK QUERY
   ========================================================= */

if (!$query) {

    die("Query Error: " . mysqli_error($conn));

}

?>

<style>

/* =========================================================
   PANEL HEADER DATA PENILAIAN
   ========================================================= */

.page-header-card {

    display: flex;

    justify-content: space-between;

    align-items: center;

    background: linear-gradient(
        135deg,
        #172554 0%,
        #2563eb 100%
    );

    color: #ffffff;

    border-radius: 16px;

    padding: 1.25rem 1.4rem;

    margin-bottom: 1.5rem;

    box-shadow:
        0 6px 18px rgba(37, 99, 235, 0.20);

}


/* Judul */

.page-header-card h3 {

    font-weight: 700;

    color: #ffffff;

    margin: 0;

    font-size: 1.25rem;

}


/* Deskripsi */

.page-header-card p {

    color: rgba(255, 255, 255, 0.85);

    font-size: 0.85rem;

    margin: 0.25rem 0 0;

}


/* =========================================================
   CARD TABLE
   ========================================================= */

.card-table {

    border: none;

    border-radius: 16px;

    box-shadow:
        0 4px 18px rgba(0, 0, 0, 0.06);

}


/* =========================================================
   TABLE
   ========================================================= */

table.table-modern-nilai {

    border-collapse: separate !important;

    border-spacing: 0;

    width: 100% !important;

}


/* Header tabel */

table.table-modern-nilai thead th {

    background: #2563eb;

    color: #ffffff;

    border: none !important;

    font-size: 0.8rem;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    white-space: nowrap;

    text-align: center;

    border-right:
        1px solid rgba(255, 255, 255, 0.15) !important;

}


table.table-modern-nilai thead th:last-child {

    border-right: none !important;

}

/* REVISI: Perataan kiri pada thead */
table.table-modern-nilai thead th.text-left-col {
    text-align: left !important;
}


/* Isi tabel */

table.table-modern-nilai tbody td {

    vertical-align: middle;

    font-size: 0.88rem;

    border-color: #eef1f5 !important;

    text-align: center;

    border-right:
        1px solid #eef1f5;

}

/* REVISI: Perataan kiri pada tbody */
table.table-modern-nilai tbody td.text-left-col {
    text-align: left !important;
}

table.table-modern-nilai tbody td:last-child {

    border-right: none;

}


/* Hover */

table.table-modern-nilai tbody tr:hover {

    background: #f8fafc;

}


/* =========================================================
   DATA PELAJAR
   ========================================================= */

.mhs-name {

    font-weight: 600;

    color: #1e293b;

    display: block;

}


.mhs-nim {

    color: #94a3b8;

    font-size: 0.78rem;

    font-family: 'Courier New', monospace;

}


/* Instansi */

.plain-text {

    color: #334155;

    font-weight: 500;

    font-size: 0.85rem;

}


/* =========================================================
   BADGE NILAI AKHIR
   ========================================================= */

.nilai-badge {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 60px;

    padding: 0.4rem 0.9rem;

    border-radius: 20px;

    font-weight: 700;

    font-size: 0.9rem;

}


/* Nilai A */

.nilai-a {

    background: #dcfce7;

    color: #166534;

    border: 1px solid #bbf7d0;

}


/* Nilai B */

.nilai-b {

    background: #e0f2fe;

    color: #0369a1;

    border: 1px solid #bae6fd;

}


/* Nilai C */

.nilai-c {

    background: #fef3c7;

    color: #92400e;

    border: 1px solid #fde68a;

}


/* Nilai D */

.nilai-d {

    background: #fee2e2;

    color: #991b1b;

    border: 1px solid #fecaca;

}


/* =========================================================
   ACTION
   ========================================================= */

.action-group {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 0.4rem;

}

/* REVISI: Diringkas menjadi hanya ikon */
.action-group .btn {

    border-radius: 8px;

    font-size: 0.85rem;

    padding: 0.35rem 0.5rem;

}


/* Tombol detail */

.btn-detail {

    background: #eff6ff;

    color: #1d4ed8;

    border: 1px solid #bfdbfe;

}


.btn-detail:hover {

    background: #1d4ed8;

    color: #ffffff;

}


/* =========================================================
   DATATABLES EMPTY STATE
   ========================================================= */

.dataTables_empty {

    padding: 2.5rem 1rem !important;

    color: #94a3b8 !important;

    text-align: center !important;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 768px) {

    .page-header-card {

        padding: 1rem 1.1rem;

        border-radius: 14px;

    }


    .page-header-card h3 {

        font-size: 1.1rem;

    }


    .page-header-card p {

        font-size: 0.8rem;

    }

}

</style>


<div class="container-fluid">


    <!-- =====================================================
         PANEL HEADER
         ===================================================== -->

    <div class="page-header-card">

        <div>

            <h3>

                <i class="bi bi-clipboard-data me-2"></i>

                Data Penilaian PKL

            </h3>


            <p>

                Kelola dan pantau rekap nilai akhir pelajar PKL
                berdasarkan input nilai dari pembimbing lapangan.

            </p>

        </div>

    </div>



    <!-- =====================================================
         CARD TABLE
         ===================================================== -->

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">


                <table
                    id="tabelPenilaian"
                    class="table table-hover table-modern-nilai"
                >


                    <!-- =================================================
                         HEADER TABLE
                         ================================================= -->

                    <thead>

                        <tr>

                            <th width="3%">
                                No
                            </th>

                            <!-- REVISI: Pembagian persentase kolom disesuaikan & rata kiri -->
                            <th class="text-left-col" width="22%">
                                Pelajar
                            </th>

                            <th class="text-left-col" width="25%">
                                Instansi
                            </th>

                            <th width="10%">
                                Nilai Pembimbing
                            </th>

                            <th width="8%">
                                Kehadiran
                            </th>

                            <th width="8%">
                                Logbook
                            </th>

                            <th width="10%">
                                Tugas Akhir
                            </th>

                            <th width="9%">
                                Nilai Akhir Final
                            </th>

                            <th width="5%">
                                Aksi
                            </th>

                        </tr>

                    </thead>



                    <!-- =================================================
                         BODY TABLE
                         ================================================= -->

                    <tbody>


                    <?php

                    $no = 1;


                    if(mysqli_num_rows($query) > 0){


                        while($row = mysqli_fetch_assoc($query)){


                            /* =========================================
                               1. SUB KOMPONEN PEMBIMBING
                               ========================================= */

                            $disiplin = isset($row['disiplin'])
                                ? (float) $row['disiplin']
                                : 0;


                            $komunikasi = isset($row['komunikasi'])
                                ? (float) $row['komunikasi']
                                : 0;


                            $kerjasama = isset($row['kerjasama'])
                                ? (float) $row['kerjasama']
                                : 0;


                            $tanggung_jawab = isset(
                                $row['tanggung_jawab']
                            )
                                ? (float) $row['tanggung_jawab']
                                : 0;


                            $inisiatif = isset($row['inisiatif'])
                                ? (float) $row['inisiatif']
                                : 0;



                            /* =========================================
                               CEK SUB KOMPONEN
                               ========================================= */

                            $sub_komponen_ada = (

                                $disiplin > 0 ||

                                $komunikasi > 0 ||

                                $kerjasama > 0 ||

                                $tanggung_jawab > 0 ||

                                $inisiatif > 0

                            );



                            /* =========================================
                               NILAI PEMBIMBING
                               ========================================= */

                            $nilai_pembimbing =

                                $sub_komponen_ada

                                ? (

                                    (

                                        $disiplin +

                                        $komunikasi +

                                        $kerjasama +

                                        $tanggung_jawab +

                                        $inisiatif

                                    ) / 5

                                )

                                : (float)
                                    $row['nilai_pembimbing_db'];



                            /* =========================================
                               2. KEHADIRAN
                               ========================================= */

                            $skor_kehadiran = isset(
                                $row['absensi']
                            )

                                ? (float) $row['absensi']

                                : 0;



                            /* =========================================
                               3. LOGBOOK
                               ========================================= */

                            $skor_logbook = isset(
                                $row['logbook']
                            )

                                ? (float) $row['logbook']

                                : 0;



                            /* =========================================
                               4. TUGAS AKHIR
                               ========================================= */

                            $skor_tugas_akhir = isset(
                                $row['tugas_akhir']
                            )

                                ? (float) $row['tugas_akhir']

                                : 0;



                            /* =========================================
                               NILAI AKHIR FINAL
                               
                               Pembimbing  = 45%
                               Kehadiran   = 15%
                               Logbook     = 15%
                               Tugas Akhir = 25%
                               ========================================= */

                            $nilai_akhir_final = (

                                ($nilai_pembimbing * 0.45) +

                                ($skor_kehadiran * 0.15) +

                                ($skor_logbook * 0.15) +

                                ($skor_tugas_akhir * 0.25)

                            );



                            /* =========================================
                               CLASS NILAI
                               ========================================= */

                            if($nilai_akhir_final >= 85){

                                $nilaiClass = 'nilai-a';

                            }

                            elseif($nilai_akhir_final >= 75){

                                $nilaiClass = 'nilai-b';

                            }

                            elseif($nilai_akhir_final >= 60){

                                $nilaiClass = 'nilai-c';

                            }

                            else{

                                $nilaiClass = 'nilai-d';

                            }


                    ?>


                        <!-- =================================================
                             DATA ROW
                             ================================================= -->

                        <tr>


                            <!-- NO -->

                            <td>

                                <?= $no++; ?>

                            </td>



                            <!-- PELAJAR -->

                            <!-- REVISI: Pembatasan lebar teks pelajar dengan text-truncate & rata kiri -->
                            <td class="text-left-col">

                                <span class="mhs-name text-truncate d-inline-block w-100" style="max-width: 180px;" title="<?= htmlspecialchars($row['nama']); ?>">

                                    <?= htmlspecialchars(
                                        $row['nama']
                                    ); ?>

                                </span>


                                <span class="mhs-nim text-truncate d-inline-block w-100" style="max-width: 180px;">

                                    <?= htmlspecialchars(
                                        $row['nim']
                                    ); ?>

                                </span>

                            </td>



                            <!-- INSTANSI -->

                            <!-- REVISI: Pemotongan teks yang terlalu panjang & rata kiri -->
                            <td class="text-left-col text-truncate" style="max-width: 220px;" title="<?= !empty($row['nama_instansi']) ? htmlspecialchars($row['nama_instansi']) : '-'; ?>">

                                <span class="plain-text">

                                    <?= !empty(
                                        $row['nama_instansi']
                                    )

                                        ? htmlspecialchars(
                                            $row['nama_instansi']
                                        )

                                        : '-';

                                    ?>

                                </span>

                            </td>



                            <!-- NILAI PEMBIMBING -->

                            <td>

                                <span
                                    class="badge bg-primary text-white"
                                    title="Nilai Pembimbing"
                                >

                                    <?= number_format(
                                        $nilai_pembimbing,
                                        2
                                    ); ?>

                                </span>

                            </td>



                            <!-- KEHADIRAN -->

                            <td>

                                <span
                                    class="badge bg-info text-dark"
                                    title="Skor Kehadiran"
                                >

                                    <?= number_format(
                                        $skor_kehadiran,
                                        1
                                    ); ?>

                                </span>

                            </td>



                            <!-- LOGBOOK -->

                            <td>

                                <span
                                    class="badge bg-secondary text-white"
                                    title="Skor Logbook"
                                >

                                    <?= number_format(
                                        $skor_logbook,
                                        1
                                    ); ?>

                                </span>

                            </td>



                            <!-- TUGAS AKHIR -->

                            <td>

                                <span
                                    class="badge bg-success"
                                    title="Skor Tugas Akhir"
                                >

                                    <?= number_format(
                                        $skor_tugas_akhir,
                                        1
                                    ); ?>

                                </span>

                            </td>



                            <!-- NILAI AKHIR -->

                            <td>

                                <span
                                    class="nilai-badge <?= $nilaiClass; ?>"
                                >

                                    <?= number_format(
                                        $nilai_akhir_final,
                                        2
                                    ); ?>

                                </span>

                            </td>



                            <!-- AKSI -->

                            <td>

                                <div class="action-group">


                                    <!-- REVISI: Teks 'Detail' dihapus & diganti tooltip -->
                                    <a
                                        href="detail.php?id=<?= $row['id']; ?>"
                                        class="btn btn-detail btn-sm"
                                        data-bs-toggle="tooltip" 
                                        title="Lihat Detail Penilaian"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </a>


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



<?php

include "../../include/footer.php";

?>



<script>


/* =========================================================
   DATATABLES
   ========================================================= */

$(function() {

    // REVISI: Inisialisasi Tooltip Bootstrap untuk tombol aksi
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });

    $("#tabelPenilaian").DataTable({


        responsive: true,


        autoWidth: false,


        pageLength: 10,


        language: {


            url:
                "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",


            emptyTable:
                "Belum ada data penilaian",


            zeroRecords:
                "Data penilaian tidak ditemukan",


            info:
                "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",


            infoEmpty:
                "Menampilkan 0 sampai 0 dari 0 entri",


            search:
                "Cari:",


            lengthMenu:
                "Tampilkan _MENU_ entri",


            paginate: {

                first:
                    "Pertama",

                last:
                    "Terakhir",

                next:
                    "Selanjutnya",

                previous:
                    "Sebelumnya"

            }

        },
        columnDefs: [
            { orderable: false, targets: [8] } // Mematikan urut di kolom Aksi
        ]

    });


});



/* =========================================================
   SWEETALERT SUCCESS
   ========================================================= */

<?php if(isset($_GET['success'])): ?>


Swal.fire({

    icon: 'success',

    title: 'Berhasil!',

    text: '<?= $_GET['success']; ?>',

    confirmButtonColor: '#1e3a8a',

    timer: 2500

});


<?php endif; ?>


</script>
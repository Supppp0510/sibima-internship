<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    include "../../include/header.php";
    include "../../include/navbar.php";
    include "../../include/sidebar.php";

    // Query untuk mengambil data pelajar, status pendaftaran, penilaian, laporan akhir, rekap absensi, rekap logbook, dan sertifikat
    $query = mysqli_query($conn, "
        SELECT
            m.id AS pelajar_id,
            m.nama,
            m.nim,
            u.nama_instansi,
            ps.nama_jurusan,
            uk.unit_kerja AS nama_bidang,
            pb.nama AS nama_pembimbing,
            pn.disiplin,
            pn.komunikasi,
            pn.kerjasama,
            pn.tanggung_jawab,
            pn.inisiatif,
            pn.nilai_akhir AS nilai_pembimbing_db,
            pn.tugas_akhir,
            la.status AS status_laporan,
            la.file_laporan,
            s.id AS sertifikat_id,
            s.nomor_sertifikat,
            s.tanggal_terbit,
            s.file_pdf,
            (SELECT COUNT(*) FROM absensi a WHERE a.pelajar_id = m.id) AS total_hari_absen,
            (SELECT COUNT(*) FROM absensi a WHERE a.pelajar_id = m.id AND a.status IN ('hadir', 'izin', 'sakit')) AS total_masuk_absen,
            (SELECT COUNT(*) FROM logbook lb WHERE lb.pelajar_id = m.id) AS total_logbook_diisi
        FROM pelajar m
        INNER JOIN pendaftaran pd ON pd.pelajar_id = m.id AND pd.status = 'diterima'
        LEFT JOIN penilaian pn ON pn.pelajar_id = m.id
        LEFT JOIN laporan_akhir la ON la.pelajar_id = m.id
        LEFT JOIN pembimbing pb ON pb.id = pn.pembimbing_id
        LEFT JOIN unit_kerja uk ON uk.id = pd.unit_kerja_id
        LEFT JOIN instansi u ON u.id = m.instansi_id
        LEFT JOIN jurusan ps ON ps.id = m.jurusan_id
        LEFT JOIN sertifikat s ON s.pelajar_id = m.id
        ORDER BY m.nama ASC
    ");

    if (! $query) {
    die("Query Error: " . mysqli_error($conn));
    }
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

table.table-modern-sertif {
    border-collapse: separate !important;
    border-spacing: 0;
    width: 100% !important;
}

table.table-modern-sertif thead th {
    background: #2563eb;
    color: #fff;
    border: none !important;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
    text-align: center;
    border-right: 1px solid rgba(255, 255, 255, 0.15) !important;
}

table.table-modern-sertif thead th:last-child {
    border-right: none !important;
}

/* REVISI: Perataan kiri pada Thead */
table.table-modern-sertif thead th.text-left-col {
    text-align: left !important;
}

table.table-modern-sertif tbody td {
    vertical-align: middle;
    font-size: 0.88rem;
    border-color: #eef1f5 !important;
    text-align: center;
    border-right: 1px solid #eef1f5;
}

table.table-modern-sertif tbody td:last-child {
    border-right: none;
}

table.table-modern-sertif tbody tr:hover {
    background: #f8fafc;
}

/* REVISI: Perataan kiri pada Tbody */
table.table-modern-sertif tbody td.text-left-col {
    text-align: left !important;
}

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

.plain-text {
    color: #334155;
    font-weight: 500;
    font-size: 0.85rem;
}

.nilai-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 55px;
    padding: 0.35rem 0.8rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.85rem;
}

.nilai-a {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.nilai-b {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.nilai-c {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.nilai-d {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.no-sertif {
    font-family: 'Courier New', monospace;
    color: #334155;
    font-size: 0.82rem;
}

.badge-soft {
    padding: 0.4rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.78rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
}

.badge-soft-belum {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.badge-soft-sudah {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.badge-soft-syarat {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.action-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    flex-wrap: wrap;
}

/* REVISI: Mengatur padding tombol agar lebih ringkas khusus ikon */
.action-group .btn {
    border-radius: 8px;
    font-size: 0.85rem;
    padding: 0.35rem 0.5rem;
}

.btn-detail {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}

.btn-detail:hover {
    background: #1d4ed8;
    color: #fff;
}

.btn-generate {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    border: none;
    color: #fff;
}

.btn-generate:hover {
    color: #fff;
    background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
}

.empty-state {
    padding: 2.5rem 1rem;
    color: #94a3b8;
}

.empty-state i {
    font-size: 2.2rem;
    display: block;
    margin-bottom: 0.6rem;
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
            <h3><i class="bi bi-award-fill me-2"></i>Data Sertifikat PKL</h3>
            <p>Kelola penerbitan sertifikat kelulusan berdasarkan Nilai Akhir Final (Passing Grade &ge; 75).</p>
        </div>
    </div>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelSertifikat" class="table table-hover table-modern-sertif">
                    <thead>
                        <tr>
                            <th width="3%">No</th>
                            <!-- REVISI: Pembagian lebar kolom & Rata Kiri -->
                            <th class="text-left-col" width="22%">Pelajar</th>
                            <th class="text-left-col" width="28%">Instansi</th>
                            <th width="10%">Nilai Akhir</th>
                            <th width="15%">No Sertifikat</th>
                            <th width="12%">Status Syarat</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no = 1;
                            if (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    // Hitung ulang komponen persis seperti file generate.php agar konsisten
                                    $disiplin       = isset($row['disiplin']) ? (float) $row['disiplin'] : 0;
                                    $komunikasi     = isset($row['komunikasi']) ? (float) $row['komunikasi'] : 0;
                                    $kerjasama      = isset($row['kerjasama']) ? (float) $row['kerjasama'] : 0;
                                    $tanggung_jawab = isset($row['tanggung_jawab']) ? (float) $row['tanggung_jawab'] : 0;
                                    $inisiatif      = isset($row['inisiatif']) ? (float) $row['inisiatif'] : 0;

                                    $sub_ada          = ($disiplin > 0 || $komunikasi > 0 || $kerjasama > 0 || $tanggung_jawab > 0 || $inisiatif > 0);
                                    $nilai_pembimbing = $sub_ada ? (($disiplin + $komunikasi + $kerjasama + $tanggung_jawab + $inisiatif) / 5) : (float) $row['nilai_pembimbing_db'];

                                    $total_hari_absen  = (int) $row['total_hari_absen'];
                                    $total_masuk_absen = (int) $row['total_masuk_absen'];
                                    $persentase_hadir  = ($total_hari_absen > 0) ? ($total_masuk_absen / $total_hari_absen) * 100 : 0;

                                    $total_logbook_diisi = (int) $row['total_logbook_diisi'];
                                    $persentase_logbook  = min(($total_logbook_diisi / 30) * 100, 100);

                                    $ada_tugas_akhir   = (! empty($row['file_laporan']) || ! empty($row['tugas_akhir']));
                                    $laporan_disetujui = (isset($row['status_laporan']) && $row['status_laporan'] == 'disetujui');
                                    $nilai_tugas_akhir = ($ada_tugas_akhir && $laporan_disetujui) ? 100 : 0;

                                    $nilai_akhir_final = (
                                        ($nilai_pembimbing * 0.45) +
                                        ($persentase_hadir * 0.15) +
                                        ($persentase_logbook * 0.15) +
                                        ($nilai_tugas_akhir * 0.25)
                                    );

                                    if ($nilai_akhir_final >= 85) {
                                        $nilaiClass = 'nilai-a';
                                    } elseif ($nilai_akhir_final >= 75) {
                                        $nilaiClass = 'nilai-b';
                                    } elseif ($nilai_akhir_final >= 60) {
                                        $nilaiClass = 'nilai-c';
                                    } else {
                                        $nilaiClass = 'nilai-d';
                                    }

                                    // Syarat kelulusan mutlak
                                    $syarat_mutlak = ($ada_tugas_akhir && $laporan_disetujui);
                                    $lulus_magang  = ($syarat_mutlak && $nilai_akhir_final >= 75);
                                ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            
                            <!-- REVISI: Perataan Kiri dan Pembatasan Lebar Teks Pelajar -->
                            <td class="text-left-col">
                                <span class="mhs-name text-truncate d-inline-block w-100" style="max-width: 180px;" title="<?php echo htmlspecialchars($row['nama']); ?>">
                                    <?php echo htmlspecialchars($row['nama']); ?>
                                </span>
                                <span class="mhs-nim text-truncate d-inline-block w-100" style="max-width: 180px;">
                                    <?php echo htmlspecialchars($row['nim']); ?>
                                </span>
                            </td>
                            
                            <!-- REVISI: Perataan Kiri dan Pembatasan Lebar Teks Instansi -->
                            <td class="text-left-col text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($row['nama_instansi'] ?? '-'); ?>">
                                <span class="plain-text">
                                    <?php echo ! empty($row['nama_instansi']) ? htmlspecialchars($row['nama_instansi']) : '-'; ?>
                                </span>
                            </td>
                            
                            <td>
                                <span class="nilai-badge <?php echo $nilaiClass; ?>"
                                    title="Nilai Akhir Akumulasi Final">
                                    <?php echo number_format($nilai_akhir_final, 2); ?>
                                </span>
                            </td>
                            <td>
                                <span class="no-sertif">
                                    <?php echo ! empty($row['nomor_sertifikat']) ? htmlspecialchars($row['nomor_sertifikat']) : '-'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (! $lulus_magang) {?>
                                <span class="badge-soft badge-soft-syarat"
                                    title="Belum memenuhi syarat kelulusan atau passing grade &lt; 75">
                                    <i class="bi bi-x-circle-fill"></i> Belum Lulus
                                </span>
                                <?php } elseif (empty($row['sertifikat_id'])) {?>
                                <span class="badge-soft badge-soft-belum">
                                    <i class="bi bi-hourglass-split"></i> Siap Generate
                                </span>
                                <?php } else {?>
                                <span class="badge-soft badge-soft-sudah">
                                    <i class="bi bi-check-circle-fill"></i> Selesai
                                </span>
                                <?php }?>
                            </td>
                            
                            <!-- REVISI: Teks pada tombol Aksi dihilangkan dan ditambahkan Tooltip -->
                            <td>
                                <div class="action-group">
                                    <?php if ($lulus_magang && empty($row['sertifikat_id'])) {?>
                                    <a href="generate.php?id=<?php echo $row['pelajar_id']; ?>"
                                        class="btn btn-generate btn-sm" data-bs-toggle="tooltip" title="Generate 2 Halaman">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    <?php } elseif (! empty($row['sertifikat_id'])) {?>
                                    <a href="detail.php?id=<?php echo $row['sertifikat_id']; ?>"
                                        class="btn btn-detail btn-sm" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="download.php?id=<?php echo $row['sertifikat_id']; ?>"
                                        class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Download Sertifikat">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm btnHapus"
                                        data-id="<?php echo $row['sertifikat_id']; ?>" data-bs-toggle="tooltip" title="Hapus Sertifikat">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php } else {?>
                                    <button type="button" class="btn btn-secondary btn-sm" disabled
                                        data-bs-toggle="tooltip" title="Belum memenuhi passing grade 75 atau syarat mutlak laporan">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                    <?php }?>
                                </div>
                            </td>
                        </tr>
                        <?php
                            }
                            } else {
                            ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    Belum ada data pelajar magang yang terdaftar.
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
$(function() {
    
    // REVISI: Inisialisasi Tooltip Bootstrap untuk tombol aksi
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });

    $("#tabelSertifikat").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json",
            emptyTable: `
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    Belum ada data pelajar magang yang terdaftar.
                </div>
            `
        },
        columnDefs: [
            { orderable: false, targets: [6] } // Mematikan fitur urut (sort) pada kolom Aksi
        ]
    });

    $(document).on('click', '.btnHapus', function(e) {
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
            if (result.isConfirmed) {
                window.location = 'hapus.php?id=' + id;
            }
        });
    });
});
</script>
<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    $data = mysqli_query($conn, "
SELECT
m.*,
u.nama_instansi,
p.nama_jurusan
FROM pelajar m
LEFT JOIN instansi u
ON m.instansi_id=u.id
LEFT JOIN jurusan p
ON m.jurusan_id=p.id
ORDER BY m.id DESC
");

    include "../../include/header.php";
    include "../../include/navbar.php";
    include "../../include/sidebar.php";

?>

<style>
/* 1. Pengaturan Utama: Paksa seluruh isi tabel ke tengah secara mutlak */
table#tabelPelajar.table-modern-pelajar th,
table#tabelPelajar.table-modern-pelajar td {
    text-align: center !important;
    vertical-align: middle !important;
}

/* 2. Styling Header Tabel */
table.table-modern-pelajar thead th {
    background: #2563eb;
    color: #fff;
    border: none !important;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
    border-right: 1px solid rgba(255, 255, 255, 0.15) !important;
}

table.table-modern-pelajar thead th:last-child {
    border-right: none !important;
}

/* 3. Styling Baris Isi Tabel */
table.table-modern-pelajar tbody td {
    font-size: 0.88rem;
    border-color: #eef1f5 !important;
    border-right: 1px solid #eef1f5;
}

table.table-modern-pelajar tbody td:last-child {
    border-right: none;
}

table.table-modern-pelajar tbody tr:hover {
    background: #f8fafc;
}

/* 4. Komponen Pendukung (Foto, Teks, Badge) */
.foto-pelajar {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
    display: block;
    margin: 0 auto;
}

.foto-placeholder {
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
    margin: 0 auto;
}

.nim-cell {
    color: #94a3b8;
    font-size: 0.82rem;
    font-family: 'Courier New', monospace;
}

.pelajar-name {
    font-weight: 600;
    color: #1e293b;
}

.instansi-text,
.jurusan-text {
    color: #1e293b;
    font-weight: 500;
    font-size: 0.85rem;
}

.contact-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-weight: 500;
    text-decoration: none;
    font-size: 0.85rem;
}

.contact-wa {
    color: #15803d;
}

.contact-wa:hover {
    text-decoration: underline;
    color: #166534;
}

.contact-email {
    color: #2563eb;
}

.contact-email:hover {
    text-decoration: underline;
    color: #1d4ed8;
}

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
            <h3><i class="bi bi-people-fill me-2"></i>Data Pelajar</h3>
            <p>Kelola daftar pelajar peserta PKL di sini.</p>
        </div>

    </div>

    <div class="card card-table">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelPelajar" class="table table-hover table-modern-pelajar">

                    <thead>

                        <tr>

                            <th width="60">No</th>
                            <th width="80">Foto</th>
                            <th width="120">NIM/NIS</th>
                            <!-- REVISI: Pembagian lebar kolom (width) disesuaikan -->
                            <th width="200">Nama</th>
                            <th width="250">Instansi</th>
                            <th width="150">Jurusan</th>
                            <th width="150">No HP</th>
                            <th width="180">Email</th>
                            <th width="150">Alamat</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                            $no = 1;

                            if (mysqli_num_rows($data) > 0) {

                                while ($row = mysqli_fetch_assoc($data)) {

                                    $inisial = strtoupper(substr($row['nama'], 0, 1));

                                    $hpBersih = preg_replace('/[^0-9]/', '', $row['no_hp']);
                                    if (substr($hpBersih, 0, 1) == '0') {
                                        $hpBersih = '62' . substr($hpBersih, 1);
                                    }

                                ?>

                        <tr>

                            <td><?php echo $no++; ?></td>

                            <td>
                                <!-- REVISI: Logika file_exists() ditambahkan agar foto yang rusak langsung diganti inisial -->
                                <?php 
                                    $foto_path = "../../assets/upload/pelajar/foto/" . $row['foto'];
                                    if (empty($row['foto']) || !file_exists($foto_path)) { 
                                ?>
                                    <span class="foto-placeholder"><?php echo $inisial; ?></span>
                                <?php } else { ?>
                                    <img src="<?php echo $foto_path; ?>"
                                        class="foto-pelajar" alt="Foto <?php echo $row['nama']; ?>">
                                <?php } ?>
                            </td>

                            <td><span class="nim-cell"><?php echo $row['nim']; ?></span></td>

                            <td><span class="pelajar-name"><?php echo $row['nama']; ?></span></td>

                            <!-- REVISI: Tambahan class text-truncate dan pengaturan style max-width pada tabel teks panjang -->
                            <td class="text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($row['nama_instansi']); ?>">
                                <span class="instansi-text"><?php echo ! empty($row['nama_instansi']) ? $row['nama_instansi'] : '-'; ?></span>
                            </td>

                            <td class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($row['nama_jurusan']); ?>">
                                <span class="jurusan-text"><?php echo ! empty($row['nama_jurusan']) ? $row['nama_jurusan'] : '-'; ?></span>
                            </td>

                            <td>
                                <?php if (! empty($row['no_hp'])) {?>
                                <a href="https://wa.me/<?php echo $hpBersih; ?>" target="_blank"
                                    class="contact-link contact-wa">
                                    <i class="bi bi-whatsapp"></i> <?php echo $row['no_hp']; ?>
                                </a>
                                <?php } else {?>
                                <span class="text-muted">-</span>
                                <?php }?>
                            </td>

                            <td>
                                <?php if (! empty($row['email'])) {?>
                                <a href="mailto:<?php echo $row['email']; ?>" class="contact-link contact-email">
                                    <i class="bi bi-envelope-fill"></i> <?php echo $row['email']; ?>
                                </a>
                                <?php } else {?>
                                <span class="text-muted">-</span>
                                <?php }?>
                            </td>

                            <!-- REVISI: Text truncate alamat agar tidak membuat baris tinggi -->
                            <td class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($row['alamat']); ?>">
                                <span class="text-muted" style="font-size: 0.85rem;">
                                    <?php echo ! empty($row['alamat']) ? htmlspecialchars($row['alamat']) : '-'; ?>
                                </span>
                            </td>

                        </tr>

                        <?php

                                }

                            } else {

                            ?>

                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    Belum ada data pelajar
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

<?php

    include "../../include/footer.php";

?>

<script>
$(function() {

    $("#tabelPelajar").DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"
        }
    });

    $(document).on('click', '.btnHapus', function(e) {

        e.preventDefault();

        let id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'Data pelajar ini tidak dapat dikembalikan.',
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
    confirmButtonColor: '#1e3a8a', // Sesuai dengan tema warna admin Anda
    timer: 2500
});
<?php endif; ?>
</script>
<?php

include "../../helper/auth_admin.php";
include "../../config/config.php";

$mahasiswa = mysqli_query($conn, "
SELECT
    m.id,
    m.nim,
    m.nama,
    u.nama_universitas,
    pr.nama_prodi
FROM mahasiswa m
LEFT JOIN universitas u
    ON m.universitas_id = u.id
LEFT JOIN program_studi pr
    ON m.program_studi_id = pr.id
WHERE m.id NOT IN (
    SELECT mahasiswa_id
    FROM pendaftaran
    WHERE status IN ('menunggu', 'diterima')
)
ORDER BY m.nama ASC
");

$periode = mysqli_query($conn, "
SELECT *
FROM periode_pkl
WHERE status = 'aktif'
ORDER BY tanggal_mulai DESC
");

$bidang = mysqli_query($conn, "
SELECT *
FROM bidang
WHERE status = 'aktif'
ORDER BY nama_bidang ASC
");

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";

?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Tambah Pendaftaran PKL</h4>
        </div>

        <div class="card-body">

            <div class="alert alert-info">
                Pendaftaran baru akan otomatis berstatus <b>Menunggu</b>.
                Pembimbing akan ditentukan saat verifikasi oleh admin.
            </div>

            <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mahasiswa</label>
                        <select name="mahasiswa_id" class="form-select" required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            <?php while ($m = mysqli_fetch_assoc($mahasiswa)) { ?>
                                <option value="<?= $m['id']; ?>">
                                    <?= $m['nim']; ?> - <?= $m['nama']; ?> 
                                    (<?= $m['nama_universitas']; ?> / <?= $m['nama_prodi']; ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Periode PKL</label>
                        <select name="periode_id" class="form-select" required>
                            <option value="">-- Pilih Periode --</option>
                            <?php while ($p = mysqli_fetch_assoc($periode)) { ?>
                                <option value="<?= $p['id']; ?>">
                                    <?= $p['nama_periode']; ?> 
                                    (<?= date('d-m-Y', strtotime($p['tanggal_mulai'])); ?> s/d <?= date('d-m-Y', strtotime($p['tanggal_selesai'])); ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bidang</label>
                        <select name="bidang_id" class="form-select" required>
                            <option value="">-- Pilih Bidang --</option>
                            <?php while ($b = mysqli_fetch_assoc($bidang)) { ?>
                                <option value="<?= $b['id']; ?>">
                                    <?= $b['nama_bidang']; ?> (Kuota: <?= $b['kuota']; ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Daftar</label>
                        <input type="date" name="tanggal_daftar" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Surat Pengantar (PDF)</label>
                        <input type="file" name="surat_pengantar" class="form-control" accept=".pdf" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">CV (PDF)</label>
                        <input type="file" name="cv" class="form-control" accept=".pdf" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Transkrip (PDF)</label>
                        <input type="file" name="transkrip" class="form-control" accept=".pdf" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Proposal (PDF)</label>
                        <input type="file" name="proposal" class="form-control" accept=".pdf" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3"
                              placeholder="Opsional, misalnya catatan awal dari admin/mahasiswa"></textarea>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Kembali
                </a>
            </form>

        </div>
    </div>
</div>

<?php include "../../include/footer.php"; ?>
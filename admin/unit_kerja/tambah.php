<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";
?>

<div class="container-fluid">

    <h3 class="mb-4">Tambah Unit Kerja</h3>

    <div class="card shadow">

        <div class="card-body">

            <form action="proses_tambah.php" method="POST">

                <div class="mb-3">
                    <label>Unit Kerja</label>
                    <input type="text" name="unit_kerja" class="form-control" placeholder="Contoh: DINAS PEKERJAAN UMUM BINA MARGA..." required>
                </div>

                <!-- Kolom Eselon II Otomatis -->
                <div class="mb-3">
                    <label>Unit Kerja Eselon II</label>
                    <input type="text" class="form-control bg-light" value="DINAS PEKERJAAN UMUM BINA MARGA" disabled>
                    <input type="hidden" name="unit_kerja_es_ii" value="DINAS PEKERJAAN UMUM BINA MARGA">
                </div>

                <div class="mb-3">
                    <label>Unit Kerja Eselon III</label>
                    <input type="text" name="unit_kerja_es_iii" class="form-control" placeholder="Contoh: BIDANG BINA TEKNIK (Opsional)">
                </div>

                <div class="mb-3">
                    <label>Unit Kerja Eselon IV</label>
                    <input type="text" name="unit_kerja_es_iv" class="form-control" placeholder="Contoh: SUB BAGIAN TATA USAHA (Opsional)">
                </div>

                <div class="mb-3">
                    <label>Kuota</label>
                    <input type="number" name="kuota" class="form-control" value="0" required>
                </div>

                <button type="submit" name="simpan" class="btn btn-primary">
                    Simpan
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>

    </div>

</div>

<?php
include "../../include/footer.php";
?>  
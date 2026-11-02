<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    $query_es3 = mysqli_query($conn, "
        SELECT DISTINCT unit_kerja_es_iii 
        FROM unit_kerja 
        WHERE unit_kerja_es_iii != '' AND unit_kerja_es_iii IS NOT NULL
    ");

    $query_uk = mysqli_query($conn, "
        SELECT id, unit_kerja, unit_kerja_es_iii, unit_kerja_es_iv 
        FROM unit_kerja 
    ");
    
    $data_uk = [];
    while ($row = mysqli_fetch_assoc($query_uk)) {
        $data_uk[] = $row;
    }

    include "../../include/header.php";
    include "../../include/navbar.php";
    include "../../include/sidebar.php";
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container .select2-selection--single {
    height: 38px !important;
    border: 1px solid #ced4da !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}
</style>

<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4><i class="fa-solid fa-user-plus"></i> Tambah Pembimbing Baru</h4>
        </div>
        <div class="card-body">
            <form action="proses_tambah.php" method="POST">

                <h5 class="text-primary mb-3">Informasi Akun Login</h5>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label>Email Akun</label>
                        <input type="email" name="email" class="form-control" placeholder="contoh@email.com" required>
                    </div>
                    <div class="col-md-4">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="col-md-4">
                        <label>Status Akun</label>
                        <select name="status" class="form-control" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Non-Aktif">Non-Aktif</option>
                        </select>
                    </div>
                </div>

                <h5 class="text-primary mb-3">Informasi Data Profil Pembimbing</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nama Lengkap & Gelar</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="col-md-6">
                        <label>NIP</label>
                        <input type="text" name="nip" class="form-control" placeholder="Nomor Induk Pegawai" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Golongan Ruang</label>
                        <input type="text" name="golongan_ruang" class="form-control" placeholder="Contoh: IV/c - Pembina Utama Muda" required>
                    </div>
                    <div class="col-md-4">
                        <label>Pangkat</label>
                        <input type="text" name="pangkat" class="form-control" placeholder="Contoh: Pembina Utama Muda" required>
                    </div>
                    <div class="col-md-4">
                        <label>Jenis Jabatan</label>
                        <input type="text" name="jenis_jabatan" class="form-control" placeholder="Contoh: Jabatan Struktural / Fungsional" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Nama Jabatan</label>
                        <input type="text" name="nama_jabatan" class="form-control" placeholder="Contoh: KEPALA DINAS / KEPALA BIDANG" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label>Unit Kerja Eselon II</label>
                        <input type="text" class="form-control bg-light" value="DINAS PEKERJAAN UMUM BINA MARGA" disabled>
                        <input type="hidden" name="unit_kerja_es_ii" value="DINAS PEKERJAAN UMUM BINA MARGA">
                    </div>
                    <div class="col-md-4">
                        <label>Unit Kerja Eselon III</label>
                        <select id="eselon_iii" class="form-control select2-enable">
                            <option value="">-- Pilih Eselon III --</option>
                            <?php foreach($query_es3 as $row): ?>
                            <option value="<?= htmlspecialchars($row['unit_kerja_es_iii']) ?>">
                                <?= htmlspecialchars($row['unit_kerja_es_iii']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Unit Kerja Eselon IV (Bidang)</label>
                        <select name="unit_kerja_id" id="eselon_iv" class="form-control select2-enable" required>
                            <option value="">-- Pilih Eselon IV / Unit Kerja --</option>
                        </select>
                    </div>
                </div>

                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Simpan Pembimbing</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const dataUk = <?php echo json_encode($data_uk); ?>;

jQuery(document).ready(function($) {
    $('.select2-enable').select2({
        width: '100%',
        placeholder: "-- Ketik untuk mencari unit kerja --",
        allowClear: true
    });

    $('#eselon_iii').on('change', function() {
        const selectedEs3 = $(this).val();
        const $es4Select = $('#eselon_iv');

        $es4Select.empty().append('<option value="">-- Pilih Eselon IV / Unit Kerja --</option>');

        if (selectedEs3) {
            const filteredUk = dataUk.filter(item => item.unit_kerja_es_iii === selectedEs3);

            filteredUk.forEach(item => {
                if(item.unit_kerja_es_iv) {
                    $es4Select.append(new Option(item.unit_kerja_es_iv, item.id));
                } else {
                    $es4Select.append(new Option(item.unit_kerja, item.id));
                }
            });
        }
        $es4Select.trigger('change');
    });
});
</script>

<?php include "../../include/footer.php"; ?>
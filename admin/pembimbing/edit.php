<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";
    include "../../config/koneksi.php";

    $id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

    if (empty($id)) {
        echo "<script>alert('ID pembimbing tidak ditemukan!'); window.location='index.php';</script>";
        exit;
    }

    $query_pembimbing = mysqli_query($conn, "
        SELECT p.*,
               uk.unit_kerja_es_iii,
               uk.unit_kerja_es_iv,
               us.email,
               us.status
        FROM pembimbing p
        LEFT JOIN unit_kerja uk ON p.unit_kerja_id = uk.id
        LEFT JOIN users us ON p.user_id = us.id
        WHERE p.id = '$id'
    ");

    if (mysqli_num_rows($query_pembimbing) == 0) {
        echo "<script>alert('Data pembimbing tidak ditemukan!'); window.location='index.php';</script>";
        exit;
    }

    $p = mysqli_fetch_assoc($query_pembimbing);

    $query_es3 = mysqli_query($conn, "
        SELECT DISTINCT unit_kerja_es_iii
        FROM unit_kerja
        WHERE unit_kerja_es_iii != '' AND unit_kerja_es_iii IS NOT NULL
    ");

    $query_es4 = mysqli_query($conn, "
        SELECT id, unit_kerja_es_iii, unit_kerja_es_iv
        FROM unit_kerja
        WHERE unit_kerja_es_iv != '' AND unit_kerja_es_iv IS NOT NULL
    ");

    $data_es4 = [];
    while ($row = mysqli_fetch_assoc($query_es4)) {
        $data_es4[] = $row;
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
            <h4><i class="fa-solid fa-user-pen"></i> Edit Data Pembimbing</h4>
        </div>
        <div class="card-body">
            <form action="proses_edit.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($p['id']); ?>">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($p['user_id']); ?>">

                <h5 class="text-primary mb-3">Informasi Akun Login</h5>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label>Email Akun</label>
                        <input type="email" name="email" class="form-control"
                            value="<?php echo htmlspecialchars($p['email'] ?? ''); ?>" placeholder="contoh@email.com"
                            required>
                    </div>
                    <div class="col-md-4">
                        <label>Password Baru <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter">
                    </div>
                    <div class="col-md-4">
                        <label>Status Akun</label>
                        <select name="status" class="form-control" required>
                            <option value="Aktif" <?php echo (isset($p['status']) && strtolower($p['status']) == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Non-Aktif" <?php echo (isset($p['status']) && strtolower($p['status']) == 'non-aktif') ? 'selected' : ''; ?>>Non-Aktif</option>
                        </select>
                    </div>
                </div>

                <h5 class="text-primary mb-3">Informasi Data Profil Pembimbing</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nama Lengkap & Gelar</label>
                        <input type="text" name="nama" class="form-control"
                            value="<?php echo htmlspecialchars($p['nama'] ?? ''); ?>"
                            placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="col-md-6">
                        <label>NIP</label>
                        <input type="text" name="nip" class="form-control"
                            value="<?php echo htmlspecialchars($p['nip'] ?? ''); ?>" placeholder="Nomor Induk Pegawai"
                            required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Golongan Ruang</label>
                        <input type="text" name="golongan_ruang" class="form-control"
                            value="<?php echo htmlspecialchars($p['golongan_ruang'] ?? ''); ?>"
                            placeholder="Contoh: IV/c - Pembina Utama Muda" required>
                    </div>
                    <div class="col-md-4">
                        <label>Pangkat</label>
                        <input type="text" name="pangkat" class="form-control"
                            value="<?php echo htmlspecialchars($p['pangkat'] ?? ''); ?>"
                            placeholder="Contoh: Pembina Utama Muda" required>
                    </div>
                    <div class="col-md-4">
                        <label>Jenis Jabatan</label>
                        <input type="text" name="jenis_jabatan" class="form-control"
                            value="<?php echo htmlspecialchars($p['jenis_jabatan'] ?? ''); ?>"
                            placeholder="Contoh: Jabatan Struktural / Fungsional" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Nama Jabatan</label>
                        <input type="text" name="nama_jabatan" class="form-control"
                            value="<?php echo htmlspecialchars($p['nama_jabatan'] ?? ''); ?>"
                            placeholder="Contoh: KEPALA DINAS / KEPALA BIDANG" required>
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
                        <select name="unit_kerja_es_iii" id="eselon_iii" class="form-control select2-enable">
                            <option value="">-- Pilih Unit Kerja Eselon III --</option>
                            <?php foreach ($query_es3 as $row): ?>
                            <option value="<?php echo htmlspecialchars($row['unit_kerja_es_iii']) ?>"
                                <?php echo (isset($p['unit_kerja_es_iii']) && $p['unit_kerja_es_iii'] == $row['unit_kerja_es_iii']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['unit_kerja_es_iii']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Unit Kerja Eselon IV (Bidang)</label>
                        <select name="unit_kerja_id" id="eselon_iv" class="form-control select2-enable" required>
                            <option value="">-- Pilih Unit Kerja Eselon IV --</option>
                        </select>
                    </div>
                </div>

                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const dataUkLengkap = <?php echo json_encode($data_es4); ?>;
const oldUnitKerjaId = "<?php echo htmlspecialchars($p['unit_kerja_id'] ?? ''); ?>";
const oldEs3 = "<?php echo htmlspecialchars($p['unit_kerja_es_iii'] ?? ''); ?>";

jQuery(document).ready(function($) {
    $('.select2-enable').select2({
        width: '100%',
        placeholder: "-- Ketik untuk mencari unit kerja --",
        allowClear: true
    });

    function updateEs4Options(selectedEs3, targetUkId = '') {
        const $es4Select = $('#eselon_iv');
        $es4Select.empty().append('<option value="">-- Pilih Unit Kerja Eselon IV --</option>');

        if (selectedEs3) {
            const filtered = dataUkLengkap.filter(item => item.unit_kerja_es_iii === selectedEs3);

            filtered.forEach(item => {
                if (item.unit_kerja_es_iv) {
                    const isSelected = (String(item.id) === String(targetUkId)) ? true : false;
                    const optionText = item.unit_kerja_es_iv;

                    $es4Select.append(new Option(optionText, item.id, isSelected, isSelected));
                }
            });
        }
        $es4Select.trigger('change.select2');
    }

    if (oldEs3) {
        updateEs4Options(oldEs3, oldUnitKerjaId);
    }

    $('#eselon_iii').on('change', function() {
        const selectedEs3 = $(this).val();
        updateEs4Options(selectedEs3, '');
    });
});
</script>

<?php include "../../include/footer.php"; ?>
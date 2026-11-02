<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // 1. Ambil data lengkap unit kerja untuk Opsi A (Pilih Langsung)
    $query_semua_uk = mysqli_query($conn, "
        SELECT id, unit_kerja
        FROM unit_kerja
        WHERE unit_kerja != '' AND unit_kerja IS NOT NULL
    ");
    $data_uk_lengkap = [];
    while ($row = mysqli_fetch_assoc($query_semua_uk)) {
    $data_uk_lengkap[] = $row;
    }

    // 2. Mengambil data unik Eselon III untuk Opsi B
    $query_es3 = mysqli_query($conn, "
        SELECT DISTINCT unit_kerja_es_iii
        FROM unit_kerja
        WHERE unit_kerja_es_iii != '' AND unit_kerja_es_iii IS NOT NULL
    ");

    // 3. Mengambil data pemetaan Eselon III, Eselon IV, dan ID Unit Kerja untuk JavaScript Opsi B
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

<!-- Load CSS Select2 agar bisa diketik/dicari -->
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
            <h4 class="mb-0"><i class="fa-solid fa-door-open"></i> Tambah Ruangan Baru</h4>
        </div>
        <div class="card-body">
            <form action="proses_tambah.php" method="POST">

                <!-- INPUT HIDDEN UTAMA YANG AKAN DIKIRIM KE PROSES -->
                <input type="hidden" name="id_unit_kerja" id="final_id_unit_kerja" value="">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nama_ruangan" class="form-label">Nama Ruangan</label>
                        <input type="text" name="nama_ruangan" id="nama_ruangan" class="form-control"
                            placeholder="Contoh: Ruang Rapat Utama / Lab Komputer" required>
                    </div>
                    <div class="col-md-6">
                        <label for="lokasi" class="form-label">Lokasi Ruangan</label>
                        <input type="text" name="lokasi" id="lokasi" class="form-control"
                            placeholder="Contoh: Gedung A Lantai 2">
                    </div>
                </div>

                <!-- KOTAK PEMILIHAN UNIT KERJA (2 OPSI) -->
                <div class="p-3 border rounded bg-light mb-4">
                    <h6 class="text-primary mb-3"><i class="fa fa-sitemap"></i> Tentukan Unit Kerja</h6>

                    <!-- OPSI A: PILIH UNIT KERJA LANGSUNG -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Opsi A: Pilih Unit Kerja Langsung</label>
                        <select id="unit_kerja_langsung" class="form-control select2-enable">
                            <option value="">-- Ketik/Pilih Unit Kerja Lengkap --</option>
                            <?php foreach ($data_uk_lengkap as $uk): ?>
                            <option value="<?php echo $uk['id']; ?>">
                                <?php echo htmlspecialchars($uk['unit_kerja']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="text-center text-muted my-2">
                        <small>--- ATAU ---</small>
                    </div>

                    <!-- OPSI B: PILIH VIA ESELON -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Opsi B: Pilih via Eselon</label>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit Kerja Eselon II</label>
                            <input type="text" class="form-control border-secondary bg-white"
                                value="DINAS PEKERJAAN UMUM BINA MARGA" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit Kerja Eselon III</label>
                            <select id="pilih_eselon_iii" class="form-control select2-enable">
                                <option value="">-- Pilih Eselon III --</option>
                                <?php while ($row = mysqli_fetch_assoc($query_es3)): ?>
                                <option value="<?php echo htmlspecialchars($row['unit_kerja_es_iii']) ?>">
                                    <?php echo htmlspecialchars($row['unit_kerja_es_iii']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit Kerja Eselon IV (Bidang)</label>
                            <select id="pilih_eselon_iv" class="form-control select2-enable">
                                <option value="">-- Tunggu Eselon III Dipilih --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="kuota" class="form-label">Kuota</label>
                        <input type="number" name="kuota" id="kuota" class="form-control" value="0" min="0" required>
                    </div>
                </div>

                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" name="simpan" class="btn btn-success">Simpan Ruangan</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Load jQuery & Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- SCRIPT LOGIKA PILIHAN & FILTER -->
<script>
const dataEs4 = <?php echo json_encode($data_es4); ?>;

jQuery(document).ready(function($) {
    // Inisialisasi Select2 agar semua dropdown bisa diketik/dicari
    $('.select2-enable').select2({
        width: '100%',
        allowClear: true,
        placeholder: "-- Pilih --"
    });

    const $ukLangsung = $('#unit_kerja_langsung');
    const $es3 = $('#pilih_eselon_iii');
    const $es4 = $('#pilih_eselon_iv');
    const $finalId = $('#final_id_unit_kerja');

    // 1. Jika Opsi A (Langsung) dipilih
    $ukLangsung.on('change', function() {
        const val = $(this).val();
        if (val) {
            // Reset Opsi B agar tidak bentrok
            $es3.val(null).trigger('change.select2');
            $es4.empty().append('<option value="">-- Tunggu Eselon III Dipilih --</option>').trigger(
                'change.select2');

            // Masukkan ID ke input utama
            $finalId.val(val);
        } else {
            $finalId.val('');
        }
    });

    // 2. Jika Eselon III (Opsi B) dipilih -> Isi Eselon IV
    $es3.on('change', function() {
        const selectedEs3 = $(this).val();
        $es4.empty().append('<option value="">-- Pilih Eselon IV --</option>');

        if (selectedEs3) {
            // Reset Opsi A agar tidak bentrok
            $ukLangsung.val(null).trigger('change.select2');

            // Filter data Eselon IV berdasarkan Eselon III
            const filteredEs4 = dataEs4.filter(item => item.unit_kerja_es_iii === selectedEs3);

            // MENGHAPUS DUPLIKAT: Pastikan nama Eselon IV hanya muncul 1x
            const seen = new Set();

            filteredEs4.forEach(item => {
                if (item.unit_kerja_es_iv && !seen.has(item.unit_kerja_es_iv)) {
                    seen.add(item.unit_kerja_es_iv); // Ingat nama ini agar tidak dicetak lagi

                    // CUKUP TAMPILKAN ESELON IV SAJA
                    const textFormat = item.unit_kerja_es_iv;
                    $es4.append(new Option(textFormat, item.id));
                }
            });
        }
        $es4.trigger('change.select2');
        $finalId.val(''); // Reset final ID sampai Eselon IV dipilih
    });

    // 3. Jika Eselon IV (Opsi B) dipilih
    $es4.on('change', function() {
        const val = $(this).val();
        if (val) {
            $finalId.val(val);
        } else {
            $finalId.val('');
        }
    });

    // Validasi sebelum form disubmit (wajib pilih salah satu unit kerja)
    $('form').on('submit', function(e) {
        if (!$finalId.val()) {
            e.preventDefault();
            alert('Silakan tentukan Unit Kerja (melalui Opsi A atau Opsi B terlebih dahulu)!');
        }
    });
});
</script>

<?php
include "../../include/footer.php";
?>
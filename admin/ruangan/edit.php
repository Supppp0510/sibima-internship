<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";

    // 1. Ambil ID Ruangan dari URL
    $id_ruangan = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // 2. Query data lama ruangan dengan join ke tabel unit_kerja
    $query_ruangan = mysqli_query($conn, "
        SELECT r.*, uk.unit_kerja, uk.unit_kerja_es_iii, uk.unit_kerja_es_iv, uk.id as id_unit_kerja_aktif
        FROM ruangan r
        LEFT JOIN unit_kerja uk ON r.id_unit_kerja = uk.id
        WHERE r.id_ruangan = '$id_ruangan'
    ");

    if (mysqli_num_rows($query_ruangan) == 0) {
        header("Location: index.php");
        exit;
    }
    $ruangan = mysqli_fetch_assoc($query_ruangan);

    // Variabel untuk data yang sedang aktif (tersimpan di DB)
    $current_uk_id = $ruangan['id_unit_kerja_aktif'];
    $current_es3   = $ruangan['unit_kerja_es_iii'];
    $current_es4   = $ruangan['unit_kerja_es_iv'];

    // 3. Ambil data lengkap unit kerja untuk Opsi A (Pilih Langsung)
    $query_semua_uk = mysqli_query($conn, "
        SELECT id, unit_kerja 
        FROM unit_kerja 
        WHERE unit_kerja != '' AND unit_kerja IS NOT NULL
    ");
    $data_uk_lengkap = [];
    while ($row = mysqli_fetch_assoc($query_semua_uk)) {
        $data_uk_lengkap[] = $row;
    }

    // 4. Mengambil data unik Eselon III untuk Opsi B
    // Kita gunakan value nama string agar sinkron dengan filter JavaScript
    $query_es3 = mysqli_query($conn, "
        SELECT DISTINCT unit_kerja_es_iii
        FROM unit_kerja
        WHERE unit_kerja_es_iii != '' AND unit_kerja_es_iii IS NOT NULL
    ");

    // 5. Query inisialisasi Eselon IV (Hanya untuk opsi B saat form pertama kali dimuat)
    // GROUP BY digunakan untuk menghilangkan duplikat nama subbagian yang sama
    $query_es4_init = mysqli_query($conn, "
        SELECT MIN(id) as id, unit_kerja_es_iv
        FROM unit_kerja
        WHERE unit_kerja_es_iii = '" . mysqli_real_escape_string($conn, $current_es3) . "'
        AND unit_kerja_es_iv != '' AND unit_kerja_es_iv IS NOT NULL
        GROUP BY unit_kerja_es_iv
        ORDER BY unit_kerja_es_iv ASC
    ");

    // 6. Mengambil data pemetaan lengkap untuk JavaScript (Filter dinamis Opsi B)
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
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0"><i class="fa-solid fa-pen-to-square"></i> Edit Ruangan</h4>
        </div>
        <div class="card-body">
            <form action="proses_edit.php" method="POST">

                <!-- ID Ruangan untuk proses update -->
                <input type="hidden" name="id_ruangan" value="<?php echo $ruangan['id_ruangan']; ?>">

                <!-- INPUT HIDDEN UTAMA UNIT KERJA YANG AKAN DIKIRIM KE PROSES (Sudah diisi ID dari database) -->
                <input type="hidden" name="id_unit_kerja" id="final_id_unit_kerja"
                    value="<?php echo $current_uk_id; ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nama_ruangan" class="form-label">Nama Ruangan</label>
                        <input type="text" name="nama_ruangan" id="nama_ruangan" class="form-control"
                            value="<?php echo htmlspecialchars($ruangan['nama_ruangan']); ?>"
                            placeholder="Contoh: Ruang Rapat Utama / Lab Komputer" required>
                    </div>
                    <div class="col-md-6">
                        <label for="lokasi" class="form-label">Lokasi Ruangan</label>
                        <input type="text" name="lokasi" id="lokasi" class="form-control"
                            value="<?php echo htmlspecialchars($ruangan['lokasi']); ?>"
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
                            <option value="<?php echo $uk['id']; ?>"
                                <?php echo ($current_uk_id == $uk['id']) ? 'selected' : ''; ?>>
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
                                <option value="<?php echo htmlspecialchars($row['unit_kerja_es_iii']); ?>"
                                    <?php echo ($current_es3 == $row['unit_kerja_es_iii']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['unit_kerja_es_iii']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit Kerja Eselon IV (Bidang)</label>
                            <select id="pilih_eselon_iv" class="form-control select2-enable">
                                <option value="">-- Pilih Eselon IV --</option>
                                <?php while ($row_iv = mysqli_fetch_assoc($query_es4_init)): ?>
                                <option value="<?php echo $row_iv['id']; ?>"
                                    <?php echo ($current_es4 == $row_iv['unit_kerja_es_iv']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row_iv['unit_kerja_es_iv']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="kuota" class="form-label">Kuota</label>
                        <input type="number" name="kuota" id="kuota" class="form-control"
                            value="<?php echo $ruangan['kuota']; ?>" min="0" required>
                    </div>
                </div>

                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" name="update" class="btn btn-warning">
                        <i class="fa-solid fa-pen"></i> Update Ruangan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const dataEs4 = <?php echo json_encode($data_es4); ?>;

jQuery(document).ready(function($) {
    $('.select2-enable').select2({
        width: '100%',
        allowClear: true,
        placeholder: "-- Pilih --"
    });

    const $ukLangsung = $('#unit_kerja_langsung');
    const $es3 = $('#pilih_eselon_iii');
    const $es4 = $('#pilih_eselon_iv');
    const $finalId = $('#final_id_unit_kerja');

    let isUpdating = false; // Mencegah looping saat reset otomatis form sebelahnya

    // 1. Jika Opsi A (Langsung) diubah oleh Admin
    $ukLangsung.on('change', function() {
        if (isUpdating) return;

        const val = $(this).val();
        if (val) {
            isUpdating = true;
            // Reset Opsi B karena Opsi A dipakai
            $es3.val(null).trigger('change.select2');
            $es4.empty().append('<option value="">-- Tunggu Eselon III Dipilih --</option>').trigger(
                'change.select2');

            $finalId.val(val); // Masukkan ID baru ke input utama
            isUpdating = false;
        } else if (!$es4.val()) {
            $finalId.val(''); // Kosongkan jika dua-duanya kosong
        }
    });

    // 2. Jika Eselon III (Opsi B) diubah oleh Admin -> Isi Eselon IV (Tanpa Sekretariat & Duplikat)
    $es3.on('change', function() {
        if (isUpdating) return;

        const selectedEs3 = $(this).val();
        $es4.empty().append('<option value="">-- Pilih Eselon IV --</option>');

        if (selectedEs3) {
            isUpdating = true;
            // Reset Opsi A karena Opsi B dipakai
            $ukLangsung.val(null).trigger('change.select2');

            // Filter data dan Hapus Duplikat
            const filteredEs4 = dataEs4.filter(item => item.unit_kerja_es_iii === selectedEs3);
            const seen = new Set();

            filteredEs4.forEach(item => {
                if (item.unit_kerja_es_iv && !seen.has(item.unit_kerja_es_iv)) {
                    seen.add(item.unit_kerja_es_iv);
                    // Langsung tampilkan nama subbagian tanpa prefix
                    $es4.append(new Option(item.unit_kerja_es_iv, item.id));
                }
            });
            isUpdating = false;
        }
        $es4.trigger('change.select2');
        $finalId.val(''); // Reset ID sampai Eselon IV dipilih
    });

    // 3. Jika Eselon IV (Opsi B) dipilih
    $es4.on('change', function() {
        if (isUpdating) return;

        const val = $(this).val();
        if (val) {
            $finalId.val(val);
        } else if (!$ukLangsung.val()) {
            $finalId.val('');
        }
    });

    // Validasi submit form
    $('form').on('submit', function(e) {
        if (!$finalId.val()) {
            e.preventDefault();
            alert('Silakan tentukan Unit Kerja (melalui Opsi A atau Opsi B terlebih dahulu)!');
        }
    });
});
</script>

<?php include "../../include/footer.php"; ?>
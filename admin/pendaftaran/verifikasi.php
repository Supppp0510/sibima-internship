<?php
    include "../../helper/auth_admin.php";
    include "../../config/config.php";
    include "../../config/koneksi.php";

    $id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

    if (empty($id)) {
        echo "<script>alert('ID tidak ditemukan!'); window.location='index.php';</script>";
        exit;
    }

    // ===============================
    // 1. Ambil Data Pendaftaran & Pelajar
    // ===============================
    $query = "SELECT
        p.*,
        m.id AS id_pelajar,
        m.nama,
        m.nim,
        m.id_ruangan,
        u.nama_instansi,
        ps.nama_jurusan,
        pr.nama_periode
    FROM pendaftaran p
    LEFT JOIN pelajar m ON p.pelajar_id=m.id
    LEFT JOIN instansi u ON m.instansi_id=u.id
    LEFT JOIN jurusan ps ON m.jurusan_id=ps.id
    LEFT JOIN periode_pkl pr ON p.periode_id=pr.id
    WHERE p.id='$id'";

    $data = mysqli_query($conn, $query);

    if (!$data) {
        die("Query Error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($data) == 0) {
        header("Location:index.php");
        exit;
    }

    $data_verifikasi = mysqli_fetch_assoc($data);
    $current_uk_id   = $data_verifikasi['unit_kerja_id'];

    // Ambil data detail unit kerja saat ini untuk mendeteksi Eselon III & IV
    $current_es_iii = '';
    $current_es_iv  = '';
    if (!empty($current_uk_id)) {
        $q_uk_current = mysqli_query($conn, "SELECT unit_kerja_es_iii, unit_kerja_es_iv FROM unit_kerja WHERE id = '$current_uk_id'");
        if ($row_uk_curr = mysqli_fetch_assoc($q_uk_current)) {
            $current_es_iii = $row_uk_curr['unit_kerja_es_iii'];
            $current_es_iv  = $row_uk_curr['unit_kerja_es_iv'];
        }
    }

    // ===============================
    // 2. Query Data Master untuk JS
    // ===============================
    $query_semua_uk  = mysqli_query($conn, "SELECT id, unit_kerja, unit_kerja_es_iii, unit_kerja_es_iv FROM unit_kerja");
    $data_uk_lengkap = [];
    while ($row = mysqli_fetch_assoc($query_semua_uk)) {
        $data_uk_lengkap[] = $row;
    }

    $query_pb = mysqli_query($conn, "SELECT id, nama, nip, unit_kerja_id FROM pembimbing");
    $data_pb  = [];
    while ($row = mysqli_fetch_assoc($query_pb)) {
        $data_pb[] = $row;
    }

    // Ambil data ruangan beserta id_unit_kerja-nya untuk filter dinamis
    $query_ruangan = mysqli_query($conn, "SELECT id_ruangan, id_unit_kerja, nama_ruangan, lokasi, kuota FROM ruangan ORDER BY nama_ruangan ASC");
    $data_ruangan  = [];
    while ($row = mysqli_fetch_assoc($query_ruangan)) {
        $data_ruangan[] = $row;
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
            <h4 class="mb-0">Verifikasi Pendaftaran PKL</h4>
        </div>
        <div class="card-body">

            <form method="POST" action="proses_verifikasi.php">
                <input type="hidden" name="id" value="<?php echo $data_verifikasi['id']; ?>">

                <div class="row">
                    <!-- KIRI: Data Mahasiswa -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Mahasiswa</label>
                            <input type="text" class="form-control bg-light"
                                value="<?php echo htmlspecialchars($data_verifikasi['nama']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">NIM/NIS</label>
                            <input type="text" class="form-control bg-light"
                                value="<?php echo htmlspecialchars($data_verifikasi['nim']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instansi</label>
                            <input type="text" class="form-control bg-light"
                                value="<?php echo htmlspecialchars($data_verifikasi['nama_instansi']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jurusan</label>
                            <input type="text" class="form-control bg-light"
                                value="<?php echo htmlspecialchars($data_verifikasi['nama_jurusan']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Daftar</label>
                            <input type="text" class="form-control bg-light"
                                value="<?php echo date('d-m-Y', strtotime($data_verifikasi['tanggal_daftar'])); ?>"
                                readonly>
                        </div>
                    </div>

                    <!-- KANAN: Penempatan -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Periode</label>
                            <input type="text" class="form-control bg-light"
                                value="<?php echo htmlspecialchars($data_verifikasi['nama_periode']); ?>" readonly>
                        </div>

                        <div class="p-3 border rounded bg-light mb-3">
                            <h6 class="text-primary mb-3"><i class="fa fa-sitemap"></i> Tentukan Unit Kerja</h6>

                            <!-- OPSI 1: PILIH UNIT KERJA LANGSUNG -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Opsi A: Pilih Unit Kerja Langsung</label>
                                <select id="unit_kerja_langsung" class="form-control select2-enable">
                                    <option value="">-- Ketik/Pilih Unit Kerja --</option>
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

                            <!-- OPSI 2: PILIH VIA ESELON -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Opsi B: Pilih via Eselon</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Eselon II</label>
                                <input type="text" class="form-control border-secondary bg-white"
                                    value="DINAS PEKERJAAN UMUM BINA MARGA" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Eselon III</label>
                                <select id="eselon_iii" class="form-control select2-enable">
                                    <option value="">-- Pilih Eselon III --</option>
                                    <?php
                                        $query_es3 = mysqli_query($conn, "SELECT DISTINCT unit_kerja_es_iii FROM unit_kerja WHERE unit_kerja_es_iii != '' AND unit_kerja_es_iii IS NOT NULL");
                                        while ($row = mysqli_fetch_assoc($query_es3)):
                                    ?>
                                    <option value="<?php echo htmlspecialchars($row['unit_kerja_es_iii']); ?>"
                                        <?php echo ($current_es_iii == $row['unit_kerja_es_iii']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['unit_kerja_es_iii']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Eselon IV (Bidang)</label>
                                <select id="eselon_iv" class="form-control select2-enable">
                                    <option value="">-- Pilih Eselon IV --</option>
                                </select>
                            </div>

                            <!-- INPUT HIDDEN UTAMA YANG DIKIRIM KE PROSES -->
                            <input type="hidden" name="unit_kerja_id" id="final_uk_id"
                                value="<?php echo $current_uk_id; ?>">
                        </div>
                    </div>
                </div>

                <hr>

                <h5>Berkas</h5>
                <div class="mb-4">
                    <a target="_blank" class="btn btn-outline-primary btn-sm"
                        href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $data_verifikasi['surat_pengantar']; ?>">Surat Pengantar</a>
                    <a target="_blank" class="btn btn-outline-success btn-sm"
                        href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $data_verifikasi['cv']; ?>">CV</a>
                    <a target="_blank" class="btn btn-outline-warning btn-sm"
                        href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $data_verifikasi['transkrip']; ?>">Transkrip</a>
                    <a target="_blank" class="btn btn-outline-danger btn-sm"
                        href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $data_verifikasi['proposal']; ?>">Proposal</a>
                </div>

                <hr>

                <!-- STATUS KEPUTUSAN -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Status Verifikasi</label>
                    <select name="status" id="pilihStatus" class="form-select border-primary" required>
                        <option value="menunggu" <?php echo $data_verifikasi['status'] == "menunggu" ? "selected" : ""; ?>>Menunggu</option>
                        <option value="diterima" <?php echo $data_verifikasi['status'] == "diterima" ? "selected" : ""; ?>>Diterima</option>
                        <option value="ditolak" <?php echo $data_verifikasi['status'] == "ditolak" ? "selected" : ""; ?>>Ditolak</option>
                    </select>
                </div>

                <!-- PEMBIMBING DIBAWAH STATUS -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Pilih Pembimbing</label>
                    <select name="pembimbing_id" id="pilihPembimbing" class="form-control select2-enable" required>
                        <option value="">-- Pilih Pembimbing --</option>
                    </select>
                    <small class="text-muted"><i class="fa fa-info-circle"></i> Pembimbing akan otomatis muncul sesuai Unit Kerja yang dipilih dari Opsi A atau Opsi B.</small>
                </div>

                <!-- RUANGAN DIBAWAH PEMBIMBING (TERFILTER BERDASARKAN UNIT KERJA) -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Pilih Ruangan (Lokasi Pelajar)</label>
                    <select name="id_ruangan" id="pilihRuangan" class="form-control select2-enable" required>
                        <option value="">-- Pilih Unit Kerja Terlebih Dahulu --</option>
                    </select>
                    <small class="text-muted"><i class="fa fa-info-circle"></i> Ruangan akan menyesuaikan dengan Unit Kerja yang dipilih di atas.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Catatan Admin</label>
                    <textarea name="catatan_admin" rows="4" class="form-control"
                        placeholder="Tulis catatan untuk mahasiswa di sini..."><?php echo htmlspecialchars($data_verifikasi['catatan_admin'] ?? ''); ?></textarea>
                </div>

                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Verifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const dataUk = <?php echo json_encode($data_uk_lengkap); ?>;
const dataPb = <?php echo json_encode($data_pb); ?>;
const dataRuangan = <?php echo json_encode($data_ruangan); ?>;
const dbPbId = "<?php echo $data_verifikasi['pembimbing_id']; ?>";
const dbRuanganId = "<?php echo $data_verifikasi['id_ruangan']; ?>";
const currentUkId = "<?php echo $current_uk_id; ?>";
const currentEsIII = "<?php echo $current_es_iii; ?>";
const currentEsIV = "<?php echo $current_es_iv; ?>";

jQuery(document).ready(function($) {
    $('.select2-enable').select2({
        width: '100%'
    });

    const $ukLangsung = $('#unit_kerja_langsung');
    const $es3 = $('#eselon_iii');
    const $es4 = $('#eselon_iv');
    const $pb = $('#pilihPembimbing');
    const $ruangan = $('#pilihRuangan');
    const $status = $('#pilihStatus');
    const $finalUk = $('#final_uk_id');

    // Fungsi populate Eselon IV
    function populateEselonIV(es3Val, selectedUkId = null) {
        $es4.empty().append('<option value="">-- Pilih Eselon IV --</option>');
        if (es3Val) {
            const filtered = dataUk.filter(u => u.unit_kerja_es_iii === es3Val);
            const seen = new Set();

            filtered.forEach(u => {
                if (u.unit_kerja_es_iv && !seen.has(u.unit_kerja_es_iv)) {
                    seen.add(u.unit_kerja_es_iv);
                    const isSelected = (selectedUkId && String(u.id) === String(selectedUkId));
                    $es4.append(new Option(u.unit_kerja_es_iv, u.id, isSelected, isSelected));
                }
            });
        }
        $es4.trigger('change.select2');
    }

    // Fungsi Memuat Pembimbing & Ruangan Berdasarkan Unit Kerja ID
    function loadDataByUnitKerja(ukId) {
        $finalUk.val(ukId);
        
        // Load Pembimbing
        $pb.empty().append('<option value="">-- Pilih Pembimbing --</option>');
        if (ukId) {
            const filteredPb = dataPb.filter(p => String(p.unit_kerja_id) === String(ukId));
            filteredPb.forEach(p => {
                const isSelected = (String(p.id) === String(dbPbId));
                $pb.append(new Option(p.nama + " (" + p.nip + ")", p.id, isSelected, isSelected));
            });
        }
        $pb.trigger('change.select2');

        // Load Ruangan (Hanya ruangan yang memiliki id_unit_kerja sama)
        $ruangan.empty().append('<option value="">-- Pilih Ruangan --</option>');
        if (ukId) {
            const filteredRuangan = dataRuangan.filter(r => String(r.id_unit_kerja) === String(ukId));
            if (filteredRuangan.length > 0) {
                filteredRuangan.forEach(r => {
                    const isSelected = (String(r.id_ruangan) === String(dbRuanganId));
                    let label = r.nama_ruangan + (r.lokasi ? ' - (' + r.lokasi + ')' : '') + ' [Kuota: ' + r.kuota + ']';
                    $ruangan.append(new Option(label, r.id_ruangan, isSelected, isSelected));
                });
            } else {
                $ruangan.append(new Option('-- Tidak ada ruangan untuk unit kerja ini --', ''));
            }
        } else {
            $ruangan.empty().append('<option value="">-- Pilih Unit Kerja Terlebih Dahulu --</option>');
        }
        $ruangan.trigger('change.select2');
        cekStatus();
    }

    // INISIALISASI SAAT HALAMAN DIBUKA
    if (currentUkId) {
        if (currentEsIII) {
            $ukLangsung.val(null).trigger('change.select2');
            $es3.val(currentEsIII).trigger('change.select2');
            populateEselonIV(currentEsIII, currentUkId);
        } else {
            $ukLangsung.val(currentUkId).trigger('change.select2');
        }

        setTimeout(function() {
            loadDataByUnitKerja(currentUkId);
        }, 150);
    }

    // 1. Jika Opsi A (Langsung) Dipilih
    $ukLangsung.on('change', function() {
        const val = $(this).val();
        if (val) {
            $es3.val(null).trigger('change.select2');
            $es4.empty().append('<option value="">-- Pilih Eselon IV --</option>').trigger('change.select2');
            loadDataByUnitKerja(val);
        } else {
            loadDataByUnitKerja('');
        }
    });

    // 2. Jika Eselon III Dipilih -> Isi Eselon IV
    $es3.on('change', function() {
        const val = $(this).val();
        if (val) {
            $ukLangsung.val(null).trigger('change.select2');
            populateEselonIV(val, null);
            loadDataByUnitKerja('');
        } else {
            $es4.empty().append('<option value="">-- Pilih Eselon IV --</option>').trigger('change.select2');
            loadDataByUnitKerja('');
        }
    });

    // 3. Jika Eselon IV Dipilih -> Muat Pembimbing & Ruangan
    $es4.on('change', function() {
        const val = $(this).val();
        if (val) {
            loadDataByUnitKerja(val);
        } else {
            loadDataByUnitKerja('');
        }
    });

    // 4. Kunci Form saat status Ditolak
    function cekStatus() {
        if ($status.val() === "ditolak") {
            $pb.val(null).trigger('change.select2');
            $ruangan.val(null).trigger('change.select2');

            $pb.prop("disabled", true);
            $ruangan.prop("disabled", true);
            $ukLangsung.prop("disabled", true);
            $es3.prop("disabled", true);
            $es4.prop("disabled", true);
        } else {
            $pb.prop("disabled", false);
            $ruangan.prop("disabled", false);
            $ukLangsung.prop("disabled", false);
            $es3.prop("disabled", false);
            $es4.prop("disabled", false);
        }
    }
    $status.on("change", cekStatus);
});
</script>

<?php include "../../include/footer.php"; ?>
<?php
session_start();
include '../config/koneksi.php';

// Role yang benar untuk pelajar adalah 3 (Admin=1, Pembimbing=2, Pelajar=3)
if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header('Location: ../login.php');
    exit();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}

$id_user = $_SESSION['id_user'];
// 🟢 FIX: Tabel yang benar bernama "pelajar" (bukan "pelajar")
$pelajar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pelajar WHERE user_id = '$id_user'"));
$id_pelajar = $pelajar['id'] ?? 0;

if (!$id_pelajar) {
    header('Location: ../login.php');
    exit();
}

// Pastikan pelajar berstatus 'diterima' sebelum bisa mengisi logbook
// 🟢 FIX: Kolom yang benar "pelajar_id" (bukan "pelajar_id")
$status_pkl = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT status FROM pendaftaran WHERE pelajar_id = '$id_pelajar' ORDER BY id DESC LIMIT 1
"));
$boleh_isi = $status_pkl && $status_pkl['status'] === 'diterima';

$status_label = [
    'menunggu'  => 'Menunggu Review',
    'disetujui' => 'Disetujui',
    'revisi'    => 'Perlu Revisi',
    'ditolak'   => 'Ditolak',
];
$status_badge = [
    'menunggu'  => 'bg-warning text-dark',
    'disetujui' => 'bg-success text-white',
    'revisi'    => 'bg-danger text-white',
    'ditolak'   => 'bg-secondary text-white',
];

// 🟢 FIX: Kolom yang benar "pelajar_id" (bukan "pelajar_id")
// ⚠️ ASUMSI: kolom di tabel logbook juga "pelajar_id" — mohon verifikasi
// Struktur tabel logbook di phpMyAdmin.
$query_log = mysqli_query($conn, "
    SELECT * FROM logbook WHERE pelajar_id = '$id_pelajar' ORDER BY tanggal DESC, id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logbook Harian - SIBIMA JATIM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
html, body { width: 100%; margin: 0; padding: 0; }
body { background: #f4f6f9; overflow-x: hidden; }
*, *::before, *::after { box-sizing: border-box; }
.main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
.content-body { flex: 1; }
.section-card { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,.06); }
.page-header-bar {
    background: linear-gradient(135deg, #0f172a, #1e40af);
    border-radius: 20px;
    padding: 1.8rem;
    color: #fff;
    box-shadow: 0 10px 25px rgba(30,64,175,.20);
}
.page-header-bar h3 { color: #fff; }
.page-header-bar p { color: rgba(255,255,255,0.85); }
.table-modern thead th { background-color: #1e3a8a; color: #fff; border-bottom: none; }
.table-modern th, .table-modern td { vertical-align: middle; padding: 1rem; }
.badge-status { padding: .4rem .85rem; border-radius: 30px; font-size: .76rem; font-weight: 500; }
.bukti-thumb { width: 46px; height: 46px; border-radius: 8px; object-fit: cover; }
@media(max-width:768px){ .main-content { margin-left: 0; } }
</style>
</head>
<body>
<div class="wrapper">
    <?php include '../include/sidebar_pelajar.php'; ?>

    <div class="main-content">
        <?php include '../include/navbar_pelajar.php'; ?>

        <div class="content-body container-fluid px-4 py-3">

            <div class="page-header-bar d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div>
                    <h3 class="fw-bold m-0"><i class="bi bi-journal-text me-2"></i>Logbook Harian</h3>
                    <p class="mb-0">Catat kegiatan harian PKL kamu di sini</p>
                </div>
                <?php if ($boleh_isi): ?>
                <button class="btn btn-light rounded-pill px-4 fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Logbook
                </button>
                <?php endif; ?>
            </div>

            <?php if (isset($_GET['pesan'])): ?>
                <div class="alert alert-<?= $_GET['pesan']=='sukses' ? 'success' : 'danger' ?> alert-dismissible fade show">
                    <?php
                    $pesan_map = [
                        'sukses' => 'Logbook berhasil disimpan.',
                        'gagal'  => 'Gagal menyimpan logbook. Silakan coba lagi.',
                        'terkunci' => 'Logbook yang sudah Disetujui tidak bisa diedit lagi.',
                    ];
                    echo $pesan_map[$_GET['pesan']] ?? 'Aksi berhasil diproses.';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!$boleh_isi): ?>
                <div class="alert alert-warning">
                    Kamu belum bisa mengisi logbook. Status pendaftaran PKL kamu belum <b>Diterima</b>.
                </div>
            <?php endif; ?>

            <div class="card section-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Tanggal</th>
                                    <th>Judul</th>
                                    <th>Kegiatan</th>
                                    <th>Bukti</th>
                                    <th class="text-center">Status</th>
                                    <th>Catatan Pembimbing</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($query_log) > 0):
                                    $no = 1;
                                    while ($log = mysqli_fetch_assoc($query_log)):
                                        $st = strtolower($log['status']);
                                        $label = $status_label[$st] ?? $log['status'];
                                        $badge = $status_badge[$st] ?? 'bg-secondary text-white';
                                        $bisa_edit = $st !== 'disetujui';
                                        $bukti = !empty($log['dokumentasi']) ? BASE_URL.'assets/upload/pelajar/dokumentasi/'.$log['dokumentasi'] : '';
                                ?>
                                <tr>
                                    <td class="ps-4"><?= $no++ ?></td>
                                    <td><?= date('d M Y', strtotime($log['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($log['judul'] ?? '-') ?></td>
                                    <td style="max-width:220px;" class="text-truncate"><?= htmlspecialchars($log['kegiatan']) ?></td>
                                    <td>
                                        <?php if($bukti): ?>
                                            <a href="<?= $bukti ?>" target="_blank"><img src="<?= $bukti ?>" class="bukti-thumb" onerror="this.style.display='none'"></a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><span class="badge-status <?= $badge ?>"><?= $label ?></span></td>
                                    <td class="small text-muted"><?= htmlspecialchars($log['komentar'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <?php if ($bisa_edit): ?>
                                        <button class="btn btn-sm btn-outline-warning rounded-circle btn-edit-log"
                                            data-id="<?= $log['id'] ?>"
                                            data-tanggal="<?= $log['tanggal'] ?>"
                                            data-judul="<?= htmlspecialchars($log['judul'] ?? '', ENT_QUOTES) ?>"
                                            data-kegiatan="<?= htmlspecialchars($log['kegiatan'], ENT_QUOTES) ?>"
                                            title="Edit"><i class="bi bi-pencil"></i></button>
                                        <?php else: ?>
                                            <span class="text-muted small">Terkunci</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada logbook. Klik "Tambah Logbook" untuk mulai mencatat kegiatan harianmu.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

<!-- MODAL TAMBAH LOGBOOK -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="proses_tambah_logbook.php" enctype="multipart/form-data" style="border-radius:18px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Logbook Harian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small text-muted">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Judul Kegiatan</label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: Setup lingkungan development" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Deskripsi Kegiatan</label>
                    <textarea name="kegiatan" class="form-control" rows="4" placeholder="Jelaskan kegiatan yang dilakukan hari ini..." required></textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label small text-muted">Bukti Kegiatan (opsional, JPG/PNG)</label>
                    <input type="file" name="dokumentasi" class="form-control" accept=".jpg,.jpeg,.png">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Logbook</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT LOGBOOK -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="proses_edit_logbook.php" enctype="multipart/form-data" style="border-radius:18px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Logbook</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="editId">
                <div class="mb-3">
                    <label class="form-label small text-muted">Tanggal</label>
                    <input type="date" name="tanggal" id="editTanggal" class="form-control" max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Judul Kegiatan</label>
                    <input type="text" name="judul" id="editJudul" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Deskripsi Kegiatan</label>
                    <textarea name="kegiatan" id="editKegiatan" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label small text-muted">Ganti Bukti Kegiatan (opsional)</label>
                    <input type="file" name="dokumentasi" class="form-control" accept=".jpg,.jpeg,.png">
                    <small class="text-muted">Kosongkan kalau tidak ingin mengganti foto.</small>
                </div>
                <div class="alert alert-info small mb-0">Mengedit logbook akan mengembalikan statusnya ke "Menunggu Review" untuk diperiksa ulang pembimbing.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.btn-edit-log').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.getElementById('editId').value = this.dataset.id;
        document.getElementById('editTanggal').value = this.dataset.tanggal;
        document.getElementById('editJudul').value = this.dataset.judul;
        document.getElementById('editKegiatan').value = this.dataset.kegiatan;
        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    });
});
</script>

        <?php include '../include/footer_pelajar.php'; ?>
    </div>
</div>
</body>
</html>
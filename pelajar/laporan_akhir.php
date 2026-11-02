<?php
session_start();

// GATEKEEPER: Kunci halaman jika belum login atau bukan pelajar (Role ID = 3)
if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php?pesan=belum_login");
    exit();
}

include "../config/koneksi.php";

if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}

$id_user = $_SESSION['id_user']; 

// Cari ID asli pelajar dan ID pendaftaran yang diterima
$query_mhs = mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '$id_user'");
$data_mhs  = mysqli_fetch_assoc($query_mhs);
$pelajar_id = $data_mhs['id'] ?? 0;

$query_pendaftaran = mysqli_query($conn, "SELECT id FROM pendaftaran WHERE pelajar_id = '$pelajar_id' AND status = 'diterima' LIMIT 1");
$data_pendaftaran  = mysqli_fetch_assoc($query_pendaftaran);
$pendaftaran_id     = $data_pendaftaran['id'] ?? 0;

// PROSES UPLOAD LAPORAN AKHIR
$pesan_sukses = false;
$pesan_gagal  = "";
if (isset($_POST['upload_laporan'])) {
    $judul       = mysqli_real_escape_string($conn, trim($_POST['judul']));
    $link_gdrive = mysqli_real_escape_string($conn, trim($_POST['link_gdrive']));

    // 🟢 1. Validasi Link GDrive
    if (empty($link_gdrive)) {
        $pesan_gagal = "Link Google Drive wajib diisi!";
    } else {
        $new_filename = NULL; 
        $upload_ok    = true;

        // 🟢 2. Cek apakah siswa memilih file ZIP/RAR untuk diunggah (Opsional)
        if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == UPLOAD_ERR_OK) {
            $allowed  = ['zip', 'rar'];
            $filename = $_FILES['file_laporan']['name'];
            $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $filesize = $_FILES['file_laporan']['size'];

            if (!in_array($ext, $allowed)) {
                $pesan_gagal = "Format file harus berupa archive .ZIP atau .RAR!";
                $upload_ok   = false;
            } elseif ($filesize > 20 * 1024 * 1024) { // 20 MB
                $pesan_gagal = "Ukuran file maksimal 20MB!";
                $upload_ok   = false;
            } else {
                $new_filename = "Laporan_Akhir_" . $pelajar_id . "_" . time() . "." . $ext;
                $target_dir   = "../uploads/laporan_akhir/";

                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (!move_uploaded_file($_FILES['file_laporan']['tmp_name'], $target_dir . $new_filename)) {
                    $pesan_gagal = "Gagal mengunggah file ke server.";
                    $upload_ok   = false;
                }
            }
        }

        // 🟢 3. Simpan ke Database
        if ($upload_ok) {
            $tanggal_upload = date('Y-m-d H:i:s');
            
            $file_val = ($new_filename !== NULL) ? "'$new_filename'" : "NULL";

            $query_insert = "INSERT INTO laporan_akhir (pelajar_id, pendaftaran_id, judul, link_gdrive, file_laporan, tanggal_upload) 
                             VALUES ('$pelajar_id', '$pendaftaran_id', '$judul', '$link_gdrive', $file_val, '$tanggal_upload')";

            $insert = mysqli_query($conn, $query_insert);

            if ($insert) {
                $pesan_sukses = true;
            } else {
                $pesan_gagal = "Gagal menyimpan data ke database: " . mysqli_error($conn);
            }
        }
    }
}

// AMBIL DATA LAPORAN AKHIR PELAJAR DENGAN JOIN KE TABEL PENILAIAN
$query_laporan = mysqli_query($conn, "
    SELECT la.*, p.nilai_akhir, p.catatan AS catatan_pembimbing 
    FROM laporan_akhir la 
    LEFT JOIN penilaian p ON la.pelajar_id = p.pelajar_id 
    WHERE la.pelajar_id = '$pelajar_id'
    ORDER BY la.id DESC
");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Akhir - SIBIMA JATIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        html, body { width: 100%; margin: 0; padding: 0; }
        body { background-color: #f4f6f9; overflow-x: hidden; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        *, *::before, *::after { box-sizing: border-box; }

        .sidebar { transition: all 0.3s ease; }
        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; transition: all 0.3s ease; }
        .content-body { flex: 1; }

        body.sidebar-hidden .sidebar { transform: translateX(-260px); }
        body.sidebar-hidden .main-content { margin-left: 0; width: 100%; }

        .footer { background-color: #ffffff; border-top: 1px solid #eef2f6; padding: 20px; text-align: center; color: #777777; font-size: 14px; margin-top: auto; margin-left: 260px; transition: all 0.3s ease; }
        body.sidebar-hidden .footer { margin-left: 0; }

        .welcome-banner { background: linear-gradient(135deg, #0f172a, #1e40af); border-radius: 20px; padding: 1.8rem; color: #fff; margin-bottom: 1.8rem; box-shadow: 0 10px 25px rgba(30,64,175,.20); }

        .section-card { border: none; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,.06); overflow: hidden; }
        .table-modern thead th { --bs-table-bg: #1e3a8a; --bs-table-color: #fff; background-color: #1e3a8a; color: #fff; border-bottom: none; }
        .table-modern th, .table-modern td { vertical-align: middle; padding: 1rem; text-align: center; }
        .table-modern tbody tr:hover { --bs-table-bg: #f8fafc; background: #f8fafc; }

        .badge-soft { padding: 0.45rem 0.9rem; border-radius: 30px; font-weight: 600; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.3rem; }
        .badge-soft-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-soft-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-soft-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        @media(max-width:768px){
            .main-content { margin-left: 0; }
            .footer { margin-left: 0; } 
            .sidebar { transform: translateX(-260px); }
            body.sidebar-hidden .sidebar { transform: translateX(0); }
            /* Memastikan flex direction menjadi kolom di layar kecil agar teks & tombol tidak bertumpuk */
            .welcome-banner { flex-direction: column !important; align-items: flex-start !important; gap: 1rem; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <?php include "../include/sidebar_pelajar.php"; ?>

    <div class="main-content">
        <?php include "../include/navbar_pelajar.php"; ?>

        <div class="content-body container-fluid px-4">

            <!-- REVISI: welcome-banner disesuaikan layoutnya untuk menampung tombol -->
            <div class="welcome-banner d-flex justify-content-between align-items-center">
                <div>
                    <h3><i class="bi bi-file-earmark-text me-2"></i>Laporan Akhir</h3>
                    <p class="mb-0">Unggah dan pantau status kelulusan laporan tugas akhir magang Anda.</p>
                </div>
                <div>
                    <!-- REVISI: Tombol diubah warnanya (btn-light) agar cocok di atas background biru -->
                    <button type="button" class="btn btn-light text-primary fw-bold rounded-pill px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUpload">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Laporan
                    </button>
                </div>
            </div>

            <div class="card section-card mb-4">
                <div class="card-body p-0">
                    <div class="p-4 pb-0">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-journal-check me-2 text-primary"></i>Riwayat Upload Laporan Akhir
                        </h5>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="12%">Tanggal</th>
                                    <th>Judul Tugas Akhir</th>
                                    <th width="20%">Upload File & Link</th>
                                    <th width="13%">Status</th>
                                    <th width="12%">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($query_laporan && mysqli_num_rows($query_laporan) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($query_laporan)) {
                                        
                                        $badge_color = 'badge-soft-warning';
                                        $icon_status = 'bi-hourglass-split';
                                        $status_text = 'Pending';

                                        if (isset($row['status']) && $row['status'] == 'disetujui') {
                                            $badge_color = 'badge-soft-success';
                                            $icon_status = 'bi-check-circle';
                                            $status_text = 'Disetujui';
                                        } elseif (isset($row['status']) && $row['status'] == 'revisi') {
                                            $badge_color = 'badge-soft-danger';
                                            $icon_status = 'bi-exclamation-circle';
                                            $status_text = 'Revisi';
                                        }

                                        $judul_clean   = htmlspecialchars($row['judul'], ENT_QUOTES);
                                        $catatan_clean = htmlspecialchars($row['catatan_pembimbing'] ?? 'Belum ada catatan dari pembimbing.', ENT_QUOTES);
                                        $nilai_tampil  = $row['nilai_akhir'] ?? 'Belum Dinilai';
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td><?= !empty($row['tanggal_upload']) ? date('d M Y', strtotime($row['tanggal_upload'])) : '-'; ?></td>
                                        <td class="fw-bold text-dark text-center"><?= htmlspecialchars($row['judul']); ?></td>
                                        
                                        <td class="text-center">
                                            <div class="d-flex flex-wrap justify-content-center gap-1">
                                                <?php if (!empty($row['file_laporan'])): ?>
                                                    <a href="../uploads/laporan_akhir/<?= $row['file_laporan']; ?>" download class="btn btn-sm btn-outline-primary rounded-pill">
                                                        <i class="bi bi-file-earmark-zip me-1"></i>ZIP
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($row['link_gdrive'])): ?>
                                                    <a href="<?= htmlspecialchars($row['link_gdrive']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success rounded-pill">
                                                        <i class="bi bi-google me-1"></i>GDrive
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-soft <?= $badge_color; ?>">
                                                <i class="<?= $icon_status; ?>"></i> <?= $status_text; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3" 
                                                    onclick="lihatDetailNilai('<?= $judul_clean; ?>', '<?= $catatan_clean; ?>', '<?= $nilai_tampil; ?>')">
                                                <i class="bi bi-eye me-1"></i>Detail
                                            </button>
                                        </td>
                                    </tr>
                                <?php 
                                    }
                                } else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                            Belum ada laporan akhir yang diunggah.
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL UPLOAD LAPORAN -->
<div class="modal fade" id="modalUpload" tabindex="-1" aria-labelledby="modalUploadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="modal-title fw-bold" id="modalUploadLabel"><i class="bi bi-cloud-arrow-up me-2"></i>Upload Laporan Akhir</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul Tugas Akhir / Laporan <span class="text-danger">*</span></label>
                        <textarea name="judul" class="form-control" rows="3" placeholder="Masukkan judul laporan/tugas akhir Anda..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Link Google Drive <span class="text-danger">*</span></label>
                        <input type="url" name="link_gdrive" class="form-control" placeholder="https://drive.google.com/..." required>
                        <small class="text-muted fs-7">* Pastikan akses link sudah diubah ke <b>"Siapa saja yang memiliki link"</b>.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">File Project dan Laporan Akhir (zip) <span class="text-danger">*</span></label>
                        <input type="file" name="file_laporan" class="form-control" accept=".zip,.rar" required>
                        <small class="text-muted fs-7">* Format harus .ZIP atau .RAR</small>
                    </div>          
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="upload_laporan" class="btn btn-primary rounded-pill px-4">Unggah Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "../include/footer_pelajar.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
<?php if ($pesan_sukses): ?>
    Swal.fire({
        title: 'Berhasil!',
        text: 'Laporan Akhir Berhasil Diunggah!',
        icon: 'success',
        confirmButtonColor: '#2563eb'
    }).then(() => {
        window.location = 'laporan_akhir.php';
    });
<?php endif; ?>

<?php if (!empty($pesan_gagal)): ?>
    Swal.fire({
        title: 'Gagal!',
        text: '<?= addslashes($pesan_gagal); ?>',
        icon: 'error',
        confirmButtonColor: '#ef4444'
    });
<?php endif; ?>

function lihatDetailNilai(judul, catatan, nilai) {
    Swal.fire({
        title: '<strong>Detail Penilaian Laporan</strong>',
        icon: 'info',
        html:
            '<div class="text-start mt-3">' +
            '<p><strong>Judul Tugas Akhir:</strong><br>' + judul + '</p>' +
            '<p><strong>Nilai Akhir:</strong> <span class="badge bg-success fs-6">' + nilai + '</span></p>' +
            '<p><strong>Catatan Pembimbing:</strong><br><em class="text-secondary">' + catatan + '</em></p>' +
            '</div>',
        showCloseButton: true,
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#2563eb'
    });
}
</script>

</body>
</html>
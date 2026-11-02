<?php
session_start();

// GATEKEEPER: Kunci halaman jika belum login atau bukan pelajar (Role ID = 3)
if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php?pesan=belum_login");
    exit();
}

include "../config/koneksi.php";

// Definisikan BASE_URL jika belum didefinisikan
if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}

$id_user = $_SESSION['id_user'];

// ========================================================================
// FITUR PENGINGAT: CEK PROFIL PELAJAR SEBELUM BISA MENDAFTAR
// ========================================================================
$cek_profil = mysqli_query($conn, "SELECT * FROM pelajar WHERE user_id = '$id_user' LIMIT 1");
$data_profil = mysqli_fetch_assoc($cek_profil);

// Jika nim, instansi_id, atau no_hp kosong, munculkan alert pop-up!
if (!$data_profil || empty($data_profil['nim']) || empty($data_profil['instansi_id']) || empty($data_profil['no_hp'])) {
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Akses Ditolak</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>
            body { background-color: #f4f6f9; font-family: 'Segoe UI', Roboto, sans-serif; }
        </style>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Akses Ditolak!',
                text: 'Silahkan lengkapi data profil terlebih dahulu sebelum mendaftar magang.',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'OK, Lengkapi Profil',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'profil.php';
                }
            });
        </script>
    </body>
    </html>";
    exit();
}
// ========================================================================

// Ambil data periode pkl untuk form pendaftaran
$periode = mysqli_query($conn, "SELECT * FROM periode_pkl WHERE status = 'aktif' LIMIT 1");
$data_periode = mysqli_fetch_assoc($periode);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Magang - SIBIMA JATIM</title>
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
        .content-body { flex: 1; padding-bottom: 2rem; }

        body.sidebar-hidden .sidebar { transform: translateX(-260px); }
        body.sidebar-hidden .main-content { margin-left: 0; width: 100%; }

        .navbar-custom { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 10px 20px; background-color: #ffffff; height: 70px; 
            border-bottom: 1px solid #eef2f6; margin-bottom: 24px;
        }

        .page-header-bar {
            background: linear-gradient(135deg, #0f172a, #1e40af);
            border-radius: 20px;
            padding: 1.8rem;
            color: #fff;
            margin-bottom: 1.8rem;
            box-shadow: 0 10px 25px rgba(30,64,175,.20);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-header-bar h3 { font-weight: 700; color: #fff; margin: 0; }
        .page-header-bar p { color: rgba(255,255,255,0.85); font-size: 0.85rem; margin: 0.2rem 0 0; }

        .card-form { 
            border: none; border-radius: 16px; 
            box-shadow: 0 4px 18px rgba(0,0,0,0.06); 
            overflow: hidden; 
        }
        .card-header-custom {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: #fff;
            padding: 1.2rem 1.5rem;
            border-bottom: none;
        }
        
        .form-label { font-size: 0.9rem; color: #334155; font-weight: 600; }
        .form-control, .form-select { 
            border-radius: 8px; 
            padding: 0.6rem 1rem; 
            border: 1px solid #cbd5e1; 
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus { 
            border-color: #3b82f6; 
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.15); 
        }

        .upload-box {
            border: 2px dashed #cbd5e1;
            padding: 1rem;
            border-radius: 10px;
            background-color: #fafafa;
            transition: all 0.2s;
        }
        .upload-box:hover {
            border-color: #2563eb;
            background-color: #f8fafc;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            color: white;
        }

        @media(max-width:768px){
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-260px); }
            body.sidebar-hidden .sidebar { transform: translateX(0); }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Load Sidebar Pelajar -->
    <?php include "../include/sidebar_pelajar.php"; ?>

    <div class="main-content">
        <!-- Load Navbar Pelajar -->
        <?php include "../include/navbar_pelajar.php"; ?>

        <div class="content-body container-fluid px-4">

            <div class="page-header-bar">
                <div>
                    <h3><i class="fa-solid fa-file-signature me-2"></i>Form Pendaftaran Magang</h3>
                    <p>Silakan isi data dan unggah berkas pendaftaran Anda dengan benar sesuai persyaratan.</p>
                </div>
            </div>

            <div class="card card-form mb-4">
                <div class="card-header-custom">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-ui-checks me-2"></i>Lengkapi Data Pengajuan</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <form action="proses_pendaftaran.php" method="POST" enctype="multipart/form-data">
                        
                        <input type="hidden" name="periode_id" value="<?= isset($data_periode['id']) ? $data_periode['id'] : ''; ?>">

                        <div class="row mb-4">
                            <!-- 1. Pilihan Semester -->
                            <div class="col-md-12 mb-4">
                                <label class="form-label">Semester Pelaksanaan Magang <span class="text-danger">*</span></label>
                                <select class="form-select" name="semester" required>
                                    <option value="" selected disabled>-- Pilih Semester --</option>
                                    <option value="Ganjil">Semester Ganjil</option>
                                    <option value="Genap">Semester Genap</option>
                                </select>
                            </div>

                            <!-- 2. Pilihan Rentang Tanggal Magang -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Tanggal Mulai Magang <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_mulai" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Tanggal Selesai Magang <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_selesai" required>
                            </div>
                        </div>

                        <hr class="my-4 text-muted">
                        <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-folder-fill me-2"></i>Dokumen Persyaratan (Format PDF, Maks 2MB)</h6>

                        <div class="row">
                            <!-- Surat Pengantar -->
                            <div class="col-md-6 mb-4">
                                <div class="upload-box">
                                    <label class="form-label mb-2"><i class="bi bi-file-earmark-text text-primary me-1"></i> Surat Pengantar <span class="text-danger">*</span></label>
                                    <input type="file" name="surat_pengantar" class="form-control form-control-sm" accept=".pdf" required>
                                    <small class="text-muted d-block mt-1">Resmi dari Universitas / Sekolah.</small>
                                </div>
                            </div>

                            <!-- Proposal Magang -->
                            <div class="col-md-6 mb-4">
                                <div class="upload-box">
                                    <label class="form-label mb-2"><i class="bi bi-file-earmark-richtext text-danger me-1"></i> Proposal Magang <span class="text-danger">*</span></label>
                                    <input type="file" name="proposal" class="form-control form-control-sm" accept=".pdf" required>
                                    <small class="text-muted d-block mt-1">Proposal rencana kegiatan magang.</small>
                                </div>
                            </div>

                            <!-- Curriculum Vitae (CV) -->
                            <div class="col-md-6 mb-4">
                                <div class="upload-box">
                                    <label class="form-label mb-2"><i class="bi bi-file-earmark-person text-success me-1"></i> Curriculum Vitae (CV) <span class="text-danger">*</span></label>
                                    <input type="file" name="cv" class="form-control form-control-sm" accept=".pdf" required>
                                    <small class="text-muted d-block mt-1">Daftar riwayat hidup terbaru.</small>
                                </div>
                            </div>

                            <!-- Transkrip Nilai -->
                            <div class="col-md-6 mb-4">
                                <div class="upload-box">
                                    <label class="form-label mb-2"><i class="bi bi-file-earmark-spreadsheet text-warning me-1"></i> Transkrip Nilai <span class="text-danger">*</span></label>
                                    <input type="file" name="transkrip" class="form-control form-control-sm" accept=".pdf" required>
                                    <small class="text-muted d-block mt-1">Nilai akademik terakhir.</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 shadow-sm mt-3 d-flex align-items-center">
                            <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                            <div class="small">
                                Pastikan seluruh berkas sudah benar dan lengkap sebelum menekan tombol kirim pendaftaran.
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" name="submit" class="btn btn-submit">
                                <i class="bi bi-send me-2"></i>Kirim Pendaftaran
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div> <!-- End Main Content -->
</div> <!-- End Wrapper -->

<!-- Load Footer Pelajar -->
<?php include "../include/footer_pelajar.php"; ?>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'sudah_daftar'): ?>
<script>
    Swal.fire({
        title: 'Perhatian!',
        text: 'Anda sudah memiliki pengajuan pendaftaran PKL yang sedang aktif atau diterima.',
        icon: 'warning',
        confirmButtonText: 'Kembali ke Dashboard',
        confirmButtonColor: '#2563eb'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'dashboard_pelajar.php';
        }
    });
</script>
<?php endif; ?>
</body>
</html>
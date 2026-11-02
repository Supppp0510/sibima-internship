<?php
// Mencegah error "session is already active"
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php?pesan=belum_login");
    exit();
}

include "../config/koneksi.php";

$id_user = $_SESSION['id_user'];

// Ambil data profil pelajar beserta data instansi dan jurusannya
$query_profil = mysqli_query($conn, "SELECT p.*, i.nama_instansi, i.alamat as alamat_instansi, j.nama_jurusan, j.jenjang as jenjang_jurusan FROM pelajar p LEFT JOIN instansi i ON p.instansi_id = i.id LEFT JOIN jurusan j ON p.jurusan_id = j.id WHERE p.user_id = '$id_user' LIMIT 1");
$profil = mysqli_fetch_assoc($query_profil);

// Fallback nama & email dari session jika data profil DB masih kosong
$nama_user  = $profil['nama'] ?? $_SESSION['nama'] ?? '';
$email_user = $profil['email'] ?? $_SESSION['email'] ?? '';

// Ambil data untuk datalist instansi dan datalist jurusan
$universitas = mysqli_query($conn, "SELECT * FROM instansi ORDER BY nama_instansi ASC");
$prodi       = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");

// Cek status magang aktif
$magang_aktif = false;
$qPelajar = mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '" . mysqli_real_escape_string($conn, $id_user) . "' LIMIT 1");
$dataPelajar = $qPelajar ? mysqli_fetch_assoc($qPelajar) : null;
$pelajar_id = $dataPelajar['id'] ?? 0;

if ($pelajar_id > 0) {
    $qCekStatus = mysqli_query($conn, "
        SELECT p.status AS status_pendaftaran, pr.tanggal_selesai 
        FROM pendaftaran p 
        LEFT JOIN periode_pkl pr ON p.periode_id = pr.id 
        WHERE p.pelajar_id = '$pelajar_id' 
        ORDER BY p.id DESC LIMIT 1
    ");
    $status_data = $qCekStatus ? mysqli_fetch_assoc($qCekStatus) : null;

    if ($status_data && $status_data['status_pendaftaran'] === 'diterima') {
        $tgl_selesai = $status_data['tanggal_selesai'];
        if (empty($tgl_selesai) || strtotime($tgl_selesai) >= strtotime(date('Y-m-d'))) {
            $magang_aktif = true;
        }
    }
}
?> 
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - SIBIMA JATIM</title>
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

        .page-header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header-bar h3 { font-weight: 700; color: #1e293b; margin: 0; }
        .page-header-bar p { color: #64748b; font-size: 0.85rem; margin: 0.2rem 0 0; }

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

        .foto-preview { 
            width: 130px; height: 130px; 
            object-fit: cover; border-radius: 50%; 
            border: 4px solid #fff; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        
        .form-label { font-size: 0.9rem; color: #334155; }
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
        
        .btn-submit {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
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
    <!-- Load Sidebar pelajar -->
    <?php include "../include/sidebar_pelajar.php"; ?>

    <div class="main-content">
        <!-- Load Navbar pelajar -->
        <?php include "../include/navbar_pelajar.php"; ?>

        <div class="content-body container-fluid px-4">

            <div class="page-header-bar">
                <div>
                    <h3><i class="bi bi-person-lines-fill me-2 text-primary"></i>Profil Pelajar</h3>
                    <p>Lengkapi identitas diri dan asal instansi Anda di bawah ini.</p>
                </div>
            </div>

            <?php if (!empty($profil['nim']) && !empty($profil['instansi_id']) && !$magang_aktif) : ?>
                <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                        <div>
                            <strong>Profil Lengkap!</strong> Data Anda sudah tersimpan. Silakan lanjutkan ke proses pendaftaran magang.
                        </div>
                    </div>
                    <a href="pendaftaran.php" class="btn btn-success fw-bold px-4 py-2">
                        <i class="bi bi-arrow-right-circle me-2"></i>Daftar Magang Sekarang
                    </a>
                </div>
            <?php elseif (empty($profil['nim']) || empty($profil['instansi_id'])) : ?>
                <div class="alert alert-warning border-0 shadow-sm rounded-3 d-flex align-items-center mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                    <div>
                        <strong>Perhatian!</strong> Anda belum bisa mendaftar magang. Silakan lengkapi form profil di bawah ini lalu klik Simpan.
                    </div>
                </div>
            <?php endif; ?>

            <div class="card card-form">
                <div class="card-header-custom">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Data Pribadi & Akademik</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <form action="proses_profil.php" method="POST" enctype="multipart/form-data">
                        
                        <input type="hidden" name="id_pelajar" value="<?= $profil['id'] ?? ''; ?>">
                        <input type="hidden" name="foto_lama" value="<?= $profil['foto'] ?? ''; ?>">

                        <div class="row mb-5 text-center">
                            <div class="col-12">
                                <div id="preview-container" class="mb-3 mx-auto text-center">
                                    <?php 
                                    $nama_display = $nama_user ?: 'Pelajar';
                                    $foto_db      = trim($profil['foto'] ?? $_SESSION['foto'] ?? '');
                                    $path_fisik   = __DIR__ . "/../assets/upload/pelajar/profile/" . $foto_db;

                                    if (!empty($foto_db) && file_exists($path_fisik)) {
                                        $url_foto = "../assets/upload/pelajar/profile/" . rawurlencode($foto_db) . "?v=" . time();
                                    } else {
                                        $url_foto = "https://ui-avatars.com/api/?name=" . urlencode($nama_display) . "&background=0d6efd&color=fff&size=128";
                                    }
                                    ?>
                                    
                                    <img id="img-preview" src="<?= $url_foto; ?>" class="foto-preview rounded-circle shadow-sm" alt="Foto Profil">
                                </div>
                                
                                <div class="mt-2">
                                    <label class="form-label fw-bold">Unggah Foto Profil Baru</label>
                                    <input type="file" name="foto" class="form-control mx-auto shadow-sm" style="max-width: 350px;" accept=".jpg,.jpeg,.png">
                                    <small class="text-muted d-block mt-2">Format: JPG, JPEG, PNG. Kosongkan jika tidak ingin mengubah foto.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">NIM / NISN <span class="text-danger">*</span></label>
                                <input type="text" name="nim" class="form-control" value="<?= htmlspecialchars($profil['nim'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($nama_user); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Input Instansi dengan Datalist -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Asal Instansi / Universitas <span class="text-danger">*</span></label>
                                <input class="form-control" list="datalistInstansi" id="inputInstansi" name="nama_instansi" value="<?= htmlspecialchars($profil['nama_instansi'] ?? ''); ?>" placeholder="Pilih atau ketik instansi baru..." required autocomplete="off">
                                <datalist id="datalistInstansi">
                                    <?php while($u = mysqli_fetch_assoc($universitas)): ?>
                                        <option value="<?= htmlspecialchars($u['nama_instansi']); ?>" data-alamat="<?= htmlspecialchars($u['alamat']); ?>">
                                    <?php endwhile; ?>
                                </datalist>
                                <small class="text-muted">Pilih dari daftar atau ketik nama instansi baru jika belum ada.</small>
                            </div>

                            <!-- Input Alamat Instansi -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Alamat Instansi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="inputAlamat" name="alamat_instansi" value="<?= htmlspecialchars($profil['alamat_instansi'] ?? ''); ?>" placeholder="Contoh: Jl. Ahmad Yani No. 152, Surabaya" required>
                                <small class="text-muted">Tulis lengkap hingga nama Kota (misal: Surabaya, Malang).</small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Dropdown Jenjang -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Jenjang Pendidikan <span class="text-danger">*</span></label>
                                <select name="jenjang" class="form-select" required>
                                    <option value="" disabled <?= empty($profil['jenjang_jurusan']) ? 'selected' : ''; ?>>-- Pilih Jenjang --</option>
                                    <option value="SMA" <?= (($profil['jenjang_jurusan'] ?? '') == 'SMA') ? 'selected' : ''; ?>>SMA</option>
                                    <option value="SMK" <?= (($profil['jenjang_jurusan'] ?? '') == 'SMK') ? 'selected' : ''; ?>>SMK</option>
                                    <option value="MA" <?= (($profil['jenjang_jurusan'] ?? '') == 'MA') ? 'selected' : ''; ?>>MA</option>
                                    <option value="D3" <?= (($profil['jenjang_jurusan'] ?? '') == 'D3') ? 'selected' : ''; ?>>D3</option>
                                    <option value="D4" <?= (($profil['jenjang_jurusan'] ?? '') == 'D4') ? 'selected' : ''; ?>>D4</option>
                                    <option value="S1" <?= (($profil['jenjang_jurusan'] ?? '') == 'S1') ? 'selected' : ''; ?>>S1</option>
                                    <option value="S2" <?= (($profil['jenjang_jurusan'] ?? '') == 'S2') ? 'selected' : ''; ?>>S2</option>
                                </select>
                            </div>

                            <!-- Input Jurusan dengan Datalist -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Jurusan <span class="text-danger">*</span></label>
                                <input class="form-control" list="datalistJurusan" id="inputJurusan" name="nama_jurusan" value="<?= htmlspecialchars($profil['nama_jurusan'] ?? ''); ?>" placeholder="Pilih atau ketik jurusan baru..." required autocomplete="off">
                                <datalist id="datalistJurusan">
                                    <?php while($p = mysqli_fetch_assoc($prodi)): ?>
                                        <option value="<?= htmlspecialchars($p['nama_jurusan']); ?>">
                                    <?php endwhile; ?>
                                </datalist>
                                <small class="text-muted">Pilih dari daftar atau ketik jurusan baru jika belum ada.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($profil['no_hp'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Alamat Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email_user); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Alamat Domisili Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($profil['alamat'] ?? ''); ?></textarea>
                        </div>

                        <hr class="my-4 text-muted">
                        
                        <div class="text-end mt-2">
                            <button type="submit" name="simpan_profil" class="btn btn-submit">
                                <i class="bi bi-save me-2"></i>Simpan Profil
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>

        <!-- Load Footer di dalam main-content -->
        <?php include "../include/footer_pelajar.php"; ?>

    </div> <!-- End Main Content -->
</div> <!-- End Wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi Preview Foto Real-time (Sintaks diperbaiki)
    const inputFoto = document.querySelector('input[name="foto"]');
    const imgPreview = document.getElementById('img-preview');

    if (inputFoto && imgPreview) {
        inputFoto.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Auto-fill Alamat Instansi dari Datalist
    const inputInstansi = document.getElementById('inputInstansi');
    const inputAlamat   = document.getElementById('inputAlamat');
    const options       = document.querySelectorAll('#datalistInstansi option');

    if (inputInstansi && inputAlamat) {
        inputInstansi.addEventListener('input', function() {
            let val = this.value;
            options.forEach(function(opt) {
                if (opt.value === val) {
                    inputAlamat.value = opt.getAttribute('data-alamat');
                }
            });
        });
    }
});
</script>

</body>
</html>
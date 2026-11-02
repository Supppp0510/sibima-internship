<?php
include "../../helper/auth_admin.php";
include "../../config/config.php";

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($id)) {
    echo "<script>alert('ID pembimbing tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

$query = mysqli_query($conn, "
    SELECT p.*, 
           uk.unit_kerja AS nama_unit_kerja,
           uk.unit_kerja_es_iii,
           uk.unit_kerja_es_iv,
           us.email AS user_email,
           us.status AS user_status
    FROM pembimbing p
    LEFT JOIN unit_kerja uk ON p.unit_kerja_id = uk.id
    LEFT JOIN users us ON p.user_id = us.id
    WHERE p.id = '$id'
");

if (mysqli_num_rows($query) == 0) {
    echo "<script>alert('Data pembimbing tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

$p = mysqli_fetch_assoc($query);

$email_login    = $p['email'] ?? $p['user_email'] ?? '-';
$password_login = '123456'; // Password default standar sistem untuk login pembimbing

include "../../include/header.php";
include "../../include/navbar.php";
include "../../include/sidebar.php";
?>

<div class="container-fluid">
    <div class="card shadow border-0" style="border-radius: 14px;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items: center" style="border-top-left-radius: 14px; border-top-right-radius: 14px; padding: 1.1rem 1.4rem;">
            <h4 class="mb-0 fw-bold fs-5"><i class="bi bi-person-lines-fill me-2"></i> Detail Data Pembimbing</h4>
            <a href="index.php" class="btn btn-light btn-sm fw-bold text-primary px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
        <div class="card-body p-4">
            
            <h5 class="text-primary fw-bold mb-3"><i class="bi bi-shield-lock-fill me-2"></i>Informasi Akun Login</h5>
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label class="text-muted small fw-bold">Username / Email Login</label>
                    <div class="p-2 bg-light rounded border fw-semibold text-dark">
                        <?= htmlspecialchars($email_login); ?>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small fw-bold">Password Login (Teks Asli)</label>
                    <div class="p-2 bg-light rounded border fw-bold text-success font-monospace">
                        <?= htmlspecialchars($password_login); ?>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small fw-bold">Status Akun</label>
                    <div>
                        <?php 
                        $status = ucfirst($p['user_status'] ?? 'Aktif');
                        $badgeBg = ($status == 'Aktif') ? 'bg-success' : 'bg-danger';
                        ?>
                        <span class="badge <?= $badgeBg; ?> px-3 py-2 mt-1"><?= $status; ?></span>
                    </div>
                </div>
            </div>

            <h5 class="text-primary fw-bold mb-3"><i class="bi bi-person-badge-fill me-2"></i>Informasi Data Profil Pembimbing</h5>
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label class="text-muted small fw-bold">Nama Lengkap & Gelar</label>
                    <div class="p-2 bg-light rounded border fw-semibold text-dark">
                        <?= htmlspecialchars($p['nama']); ?>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small fw-bold">Nomor Induk Pegawai (NIP)</label>
                    <div class="p-2 bg-light rounded border font-monospace text-secondary">
                        <?= !empty($p['nip']) ? htmlspecialchars($p['nip']) : '-'; ?>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 mb-3">
                    <label class="text-muted small fw-bold">Golongan Ruang</label>
                    <div class="p-2 bg-light rounded border text-dark">
                        <?= !empty($p['golongan_ruang']) ? htmlspecialchars($p['golongan_ruang']) : '-'; ?>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small fw-bold">Pangkat</label>
                    <div class="p-2 bg-light rounded border text-dark">
                        <?= !empty($p['pangkat']) ? htmlspecialchars($p['pangkat']) : '-'; ?>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small fw-bold">Jenis Jabatan</label>
                    <div class="p-2 bg-light rounded border text-dark">
                        <?= !empty($p['jenis_jabatan']) ? htmlspecialchars($p['jenis_jabatan']) : '-'; ?>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label class="text-muted small fw-bold">Nama Jabatan</label>
                    <div class="p-2 bg-light rounded border text-dark fw-semibold">
                        <?= !empty($p['nama_jabatan']) ? htmlspecialchars($p['nama_jabatan']) : '-'; ?>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label class="text-muted small fw-bold">Unit Kerja Eselon II</label>
                    <div class="p-2 bg-light rounded border text-dark">
                        DINAS PEKERJAAN UMUM BINA MARGA
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small fw-bold">Unit Kerja Eselon III</label>
                    <div class="p-2 bg-light rounded border text-dark">
                        <?= !empty($p['unit_kerja_es_iii']) ? htmlspecialchars($p['unit_kerja_es_iii']) : '-'; ?>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small fw-bold">Unit Kerja Eselon IV (Bidang)</label>
                    <div class="p-2 bg-light rounded border text-dark">
                        <?= !empty($p['nama_unit_kerja']) ? htmlspecialchars($p['nama_unit_kerja']) : '-'; ?>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <a href="edit.php?id=<?= $p['id']; ?>" class="btn btn-warning text-white fw-bold px-4">
                    <i class="bi bi-pencil-square me-1"></i> Edit Data
                </a>
            </div>

        </div>
    </div>
</div>

<?php include "../../include/footer.php"; ?>
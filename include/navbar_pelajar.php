<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_user = $_SESSION['id_user'] ?? 0;
$role_id = $_SESSION['role_id'] ?? 0;
$totalNotifikasi = 0;
$notifPelajar = [];

// 1. Ambil ID Pelajar & Waktu Terakhir Notifikasi Dibaca dari Database
$pelajar_id = 0;
$foto_db = '';
$last_notif_read = '2000-01-01 00:00:00';
if ($role_id == 3 && $id_user > 0 && isset($conn)) {
    $q_pelajar = mysqli_query($conn, "SELECT id, foto, last_notif_read FROM pelajar WHERE user_id = '$id_user' LIMIT 1");
    if ($q_pelajar && mysqli_num_rows($q_pelajar) > 0) {
        $dt_p = mysqli_fetch_assoc($q_pelajar);
        $pelajar_id = $dt_p['id'];
        $foto_db    = $dt_p['foto'] ?? '';
        if (!empty($dt_p['last_notif_read'])) {
            $last_notif_read = $dt_p['last_notif_read'];
        }
    }
}

// 2. KUMPULKAN NOTIFIKASI SEMUA FITUR PELAJAR
if ($role_id == 3 && $pelajar_id > 0) {

    // A. Notifikasi Pendaftaran PKL
    $qPendaftaran = mysqli_query($conn, "SELECT id, status, tanggal_daftar FROM pendaftaran WHERE pelajar_id = '$pelajar_id' AND status IN ('diterima', 'ditolak') ORDER BY id DESC LIMIT 1");
    if ($qPendaftaran && mysqli_num_rows($qPendaftaran) > 0) {
        $data = mysqli_fetch_assoc($qPendaftaran);
        $tgl = !empty($data['tanggal_daftar']) ? $data['tanggal_daftar'] : '2026-01-01 00:00:00';
        $notifPelajar[] = [
            'id'      => 'pendaftar_' . $data['id'] . '_' . $data['status'],
            'icon'    => $data['status'] == 'diterima' ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger',
            'teks'    => 'Pendaftaran PKL Anda telah <strong>' . ucfirst($data['status']) . '</strong>',
            'waktu'   => date('d-m-Y', strtotime($tgl)),
            'created' => $tgl,
            'link'    => 'dashboard_pelajar.php'
        ];
    }

    // B. Notifikasi Logbook
    $qLogbook = mysqli_query($conn, "SELECT id, tanggal, status FROM logbook WHERE pelajar_id = '$pelajar_id' AND status IN ('disetujui', 'ditolak') ORDER BY id DESC LIMIT 3");
    if ($qLogbook) {
        while ($row = mysqli_fetch_assoc($qLogbook)) {
            $tgl = $row['tanggal'] . ' 23:59:59';
            $notifPelajar[] = [
                'id'      => 'logbook_' . $row['id'] . '_' . $row['status'],
                'icon'    => $row['status'] == 'disetujui' ? 'fa-book-open text-primary' : 'fa-triangle-exclamation text-warning',
                'teks'    => 'Logbook tanggal <strong>' . date('d/m/Y', strtotime($row['tanggal'])) . '</strong> telah <strong>' . $row['status'] . '</strong>',
                'waktu'   => date('d-m-Y', strtotime($row['tanggal'])),
                'created' => $tgl,
                'link'    => 'logbook.php'
            ];
        }
    }

    // C. Notifikasi Laporan Akhir
    $qLaporan = mysqli_query($conn, "SELECT * FROM laporan_akhir WHERE pelajar_id = '$pelajar_id' AND status IN ('disetujui', 'revisi', 'ditolak') ORDER BY id DESC LIMIT 1");
    if ($qLaporan && mysqli_num_rows($qLaporan) > 0) {
        $data = mysqli_fetch_assoc($qLaporan);
        $tgl = $data['updated_at'] ?? $data['tanggal'] ?? $data['created_at'] ?? '2026-01-01 00:00:00';
        $notifPelajar[] = [
            'id'      => 'laporan_' . $data['id'] . '_' . $data['status'],
            'icon'    => 'fa-file-lines text-info',
            'teks'    => 'Laporan Akhir Anda berstatus: <strong>' . ucfirst($data['status']) . '</strong>',
            'waktu'   => date('d-m-Y', strtotime($tgl)),
            'created' => $tgl,
            'link'    => 'laporan_akhir.php'
        ];
    }

    // D. Notifikasi Nilai Akhir
    $qNilai = mysqli_query($conn, "SELECT * FROM penilaian WHERE pelajar_id = '$pelajar_id' AND nilai_akhir IS NOT NULL LIMIT 1");
    if ($qNilai && mysqli_num_rows($qNilai) > 0) {
        $data = mysqli_fetch_assoc($qNilai);
        $tgl = $data['created_at'] ?? $data['tanggal'] ?? '2026-01-01 00:00:00';
        $notifPelajar[] = [
            'id'      => 'nilai_' . $data['id'],
            'icon'    => 'fa-award text-warning',
            'teks'    => 'Nilai Akhir magang Anda telah <strong>Dirilis</strong>',
            'waktu'   => date('d-m-Y', strtotime($tgl)),
            'created' => $tgl,
            'link'    => 'dashboard_pelajar.php'
        ];
    }

    // E. Notifikasi Sertifikat
    $qSertifikat = mysqli_query($conn, "SELECT * FROM sertifikat WHERE pelajar_id = '$pelajar_id' LIMIT 1");
    if ($qSertifikat && mysqli_num_rows($qSertifikat) > 0) {
        $data = mysqli_fetch_assoc($qSertifikat);
        $tgl = $data['created_at'] ?? $data['tanggal'] ?? '2026-01-01 00:00:00';
        $notifPelajar[] = [
            'id'      => 'sertifikat_' . $data['id'],
            'icon'    => 'fa-certificate text-success',
            'teks'    => 'Sertifikat PKL Anda sudah <strong>Siap Diunduh</strong>',
            'waktu'   => date('d-m-Y', strtotime($tgl)),
            'created' => $tgl,
            'link'    => 'sertifikat.php'
        ];
    }

    // 3. HITUNG JUMLAH NOTIFIKASI YANG BELUM DIBACA
    foreach ($notifPelajar as $n) {
        if (strtotime($n['created']) > strtotime($last_notif_read)) {
            $totalNotifikasi++;
        }
    }
}

// Foto Profil
$nama_user  = $_SESSION['nama'] ?? 'User';
$foto_user  = !empty($foto_db) ? trim($foto_db) : trim($_SESSION['foto'] ?? '');
$file_fisik = __DIR__ . "/../assets/upload/pelajar/profile/" . $foto_user;
$foto_path  = (!empty($foto_user) && file_exists($file_fisik)) 
    ? (defined('BASE_URL') ? BASE_URL : '../') . "assets/upload/pelajar/profile/" . rawurlencode($foto_user) . "?v=" . time()
    : "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=0d6efd&color=fff";
?>

<div class="navbar-custom d-flex justify-content-between align-items-center shadow-sm">
    <div>
        <button type="button" id="btnToggleSidebar" class="btn-hamburger" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <div>
        <h5 class="navbar-title m-0">
            <span class="brand-main">SIBIMA</span>
            <span class="brand-sub">INTERNSHIP</span>
        </h5>
    </div>

    <div class="d-flex align-items-center">
        <!-- Lonceng Notifikasi Pelajar -->
        <div class="dropdown me-3">
            <button type="button" class="btn-notif" id="btnNotifikasi" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-bell"></i>
                <?php if($totalNotifikasi > 0){ ?>
                    <span class="notif-badge" id="notifBadge"><?= $totalNotifikasi > 9 ? '9+' : $totalNotifikasi; ?></span>
                <?php } ?>
            </button>

            <div class="dropdown-menu dropdown-menu-end notif-dropdown shadow" aria-labelledby="btnNotifikasi">
                <div class="notif-header">
                    <span><i class="fa-solid fa-bell me-2"></i> Notifikasi</span>
                    <?php if($totalNotifikasi > 0){ ?>
                        <span class="notif-count" id="notifCountHeader"><?= $totalNotifikasi; ?> baru</span>
                    <?php } ?>
                </div>

                <div class="notif-body">
                    <?php if(empty($notifPelajar)){ ?>
                        <div class="notif-empty">
                            <i class="fa-solid fa-circle-check"></i>
                            <p>Tidak ada notifikasi</p>
                        </div>
                    <?php } else { ?>
                        <?php foreach($notifPelajar as $notif): ?>
                            <a href="<?= $notif['link']; ?>" class="notif-item">
                                <div class="notif-icon">
                                    <i class="fa-solid <?= $notif['icon']; ?>"></i>
                                </div>
                                <div class="notif-text">
                                    <?= $notif['teks']; ?>
                                    <div class="notif-time"><?= $notif['waktu']; ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Profil User -->
        <div class="dropdown">
            <button type="button" class="btn-profile" id="btnProfile" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= $foto_path; ?>" class="rounded-circle shadow-sm" width="40" height="40" style="object-fit:cover;">
                <b class="ms-2 d-none d-md-inline"><?= htmlspecialchars($nama_user); ?></b>
                <i class="fa-solid fa-chevron-down ms-2 profile-chevron"></i>
            </button>   

            <div class="dropdown-menu dropdown-menu-end profile-dropdown shadow" aria-labelledby="btnProfile">
                <div class="profile-header">
                    <img src="<?= $foto_path; ?>" class="rounded-circle" width="48" height="48" style="object-fit:cover;">
                    <div>
                        <strong><?= htmlspecialchars($nama_user); ?></strong>
                        <div class="profile-role">Pelajar</div>
                    </div>
                </div>

                <a href="profil.php" class="profile-item"><i class="fa-solid fa-user"></i> Profil Saya</a>
                <div class="profile-divider"></div>
                <a href="#" id="btnLogout" class="profile-item profile-item-danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </div>
</div>

<style>
    .navbar-custom { position: relative; padding: 10px 20px; background-color: #ffffff; height: 70px; border-bottom: 1px solid #eef2f6; margin-bottom: 24px; }
    
    .navbar-title {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin: 0;
    }
    .brand-main{ font-weight: 800; font-size: 1.5rem; color: #1e3a8a; letter-spacing: 0.5px; }
    .brand-sub{ font-weight: 500; font-size: 0.9rem; color: #94a3b8; letter-spacing: 1.5px; }
    
    .btn-hamburger { background: none; border: none; font-size: 1.3rem; padding: 0.4rem 0.6rem; cursor: pointer; border-radius: 8px; color: #1e293b; }
    .btn-hamburger:hover { background: #f1f5f9; }
    
    .btn-notif { background: none; border: none; font-size: 1.25rem; padding: 0.4rem 0.6rem; position: relative; border-radius: 8px; cursor: pointer; color: #1e293b; }
    .btn-notif:hover { background: #f1f5f9; }
    .notif-badge { position: absolute; top: 2px; right: 2px; background: #dc2626; color: #fff; font-size: 0.65rem; font-weight: 700; min-width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 0 3px; border: 2px solid #fff; }
    
    .notif-dropdown { width: 340px; padding: 0; border: none; border-radius: 14px; overflow: hidden; margin-top: 10px; }
    .notif-header { background: #2563eb; color: #fff; padding: 0.9rem 1.1rem; font-weight: 600; display: flex; align-items: center; justify-content: space-between; }
    .notif-count { background: rgba(255,255,255,.2); font-size: 0.72rem; font-weight: 500; padding: 0.2rem 0.6rem; border-radius: 20px; }
    .notif-body { max-height: 340px; overflow-y: auto; }
    .notif-item { display: flex; gap: 0.7rem; padding: 0.8rem 1.1rem; text-decoration: none; color: #1e293b; border-bottom: 1px solid #f1f5f9; transition: background .15s ease; }
    .notif-item:hover { background: #f8fafc; }
    .notif-icon { width: 38px; height: 38px; min-width: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; background: #f8fafc; }
    .notif-text { font-size: 0.85rem; line-height: 1.4; }
    .notif-time { color: #94a3b8; font-size: 0.75rem; margin-top: 2px; }
    .notif-empty { text-align: center; padding: 2.2rem 1rem; color: #94a3b8; }
    .notif-empty i { font-size: 1.8rem; color: #16a34a; display: block; margin-bottom: 0.5rem; }
    
    .btn-profile { background: none; border: none; display: flex; align-items: center; padding: 0.3rem 0.6rem; cursor: pointer; border-radius: 10px; }
    .btn-profile:hover { background: #f1f5f9; }
    .profile-chevron{ font-size: 0.7rem; color: #94a3b8; transition: transform .2s ease; }
    .btn-profile[aria-expanded="true"] .profile-chevron{ transform: rotate(180deg); }
    
    .profile-dropdown { width: 260px; padding: 0; border: none; border-radius: 14px; overflow: hidden; margin-top: 10px; }
    .profile-header { background: #2563eb; color: #fff; padding: 1rem 1.1rem; display: flex; gap: 0.7rem; align-items:center; }
    .profile-header strong{ display: block; font-size: 0.95rem; }
    .profile-role{ font-size: 0.75rem; opacity: 0.85; text-transform: capitalize; }
    
    .profile-item { display: flex; align-items: center; gap: 0.7rem; padding: 0.75rem 1.1rem; text-decoration: none; color: #334155; font-size: 0.88rem; transition: background .15s ease; }
    .profile-item:hover { background: #f8fafc; color: #1e293b; }
    .profile-item-danger { color: #dc2626; }
    .profile-item-danger i { color: #dc2626; }
    .profile-item-danger:hover { background: #fef2f2; color: #b91c1c; }
    .profile-divider { border-top: 1px solid #f1f5f9; margin: 0.2rem 0; }

    /* PENYESUAIAN RESPONSIF KHUSUS HP (Sama persis seperti Admin) */
    @media(max-width: 768px) {
        .navbar-title {
            position: static !important;
            transform: none !important;
        }
        .navbar-title .brand-main {
            font-size: 1.1rem !important;
        }
        .navbar-title .brand-sub {
            font-size: 0.7rem !important;
            letter-spacing: 0.5px;
        }
        .btn-profile b { 
            display: none !important; 
        }
        .navbar-custom {
            padding: 0 10px !important;
            gap: 4px !important;
        }

        /* Sidebar & Overlay Mode HP */
        .sidebar {
            transform: translateX(-260px);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 1050;
            transition: transform 0.3s ease-in-out;
        }
        body.sidebar-show .sidebar {
            transform: translateX(0);
            box-shadow: 5px 0 15px rgba(0,0,0,0.5);
        }
        body.sidebar-show::after {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .main-content, .footer {
            margin-left: 0 !important;
            width: 100% !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var btnNotifikasi = document.getElementById('btnNotifikasi');
    var notifBadge = document.getElementById('notifBadge');
    var notifCountHeader = document.getElementById('notifCountHeader');
    var body = document.body;
    var btnToggle = document.getElementById('btnToggleSidebar');

    // Saat tombol lonceng diklik, simpan waktu baca ke database via AJAX (baca_notif.php)
    if (btnNotifikasi) {
        btnNotifikasi.addEventListener('click', function(){
            if (notifBadge && notifBadge.style.display !== 'none') {
                fetch('baca_notif.php')
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success'){
                        if (notifBadge) notifBadge.style.display = 'none';
                        if (notifCountHeader) notifCountHeader.style.display = 'none';
                    }
                });
            }
        });
    }

    // Toggle Sidebar responsif HP & Desktop
    if(btnToggle){
        btnToggle.addEventListener('click', function(){
            if(window.innerWidth <= 768) {
                body.classList.toggle('sidebar-show');
            } else {
                body.classList.toggle('sidebar-hidden');
            }
        });
    }

    // Menutup sidebar di HP saat mengklik area gelap (overlay)
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 && body.classList.contains('sidebar-show')) {
            if (!event.target.closest('.sidebar') && !event.target.closest('#btnToggleSidebar')) {
                body.classList.remove('sidebar-show');
            }
        }
    });

    var btnLogout = document.getElementById('btnLogout');
    if(btnLogout){
        btnLogout.addEventListener('click', function(e){
            e.preventDefault();
            Swal.fire({ 
                title: 'Keluar?', 
                text: 'Kamu akan logout dari sistem SIBIMA.',
                icon: 'question', 
                showCancelButton: true, 
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if(result.isConfirmed) window.location = '../logout.php';
            });
        });
    }
});
</script>
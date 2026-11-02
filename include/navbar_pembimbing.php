<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================
// Ambil data notifikasi (khusus siswa bimbingan pembimbing yang login)
// =========================
$id_user_navbar = $_SESSION['id_user'] ?? 0;
$pembimbing_navbar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM pembimbing WHERE user_id='$id_user_navbar'"));
$id_pembimbing_navbar = $pembimbing_navbar['id'] ?? 0;

// Pendaftaran (siswa bimbingan pembimbing ini) yang masih menunggu verifikasi admin
$qNotifPendaftaran = mysqli_query($conn, "
    SELECT p.id, m.nama, p.tanggal_daftar
    FROM pendaftaran p
    LEFT JOIN pelajar m ON p.pelajar_id = m.id
    WHERE p.status = 'menunggu' AND p.pembimbing_id = '$id_pembimbing_navbar'
    ORDER BY p.id DESC
    LIMIT 5
");
$totalNotifPendaftaran = mysqli_num_rows($qNotifPendaftaran);

// Logbook (siswa bimbingan pembimbing ini) yang masih menunggu verifikasi
$qNotifLogbook = mysqli_query($conn, "
    SELECT l.id, m.nama, l.tanggal
    FROM logbook l
    LEFT JOIN pelajar m ON l.pelajar_id = m.id
    INNER JOIN pendaftaran p ON p.pelajar_id = l.pelajar_id AND p.pembimbing_id = '$id_pembimbing_navbar' AND p.status = 'diterima'
    WHERE l.status = 'menunggu'
    ORDER BY l.id DESC
    LIMIT 5
");
$totalNotifLogbook = mysqli_num_rows($qNotifLogbook);

$totalNotifikasi = $totalNotifPendaftaran + $totalNotifLogbook;
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

        <!-- Notifikasi -->
        <div class="dropdown me-3">
            <button type="button"
                    class="btn-notif"
                    id="btnNotifikasi"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                <i class="fa-solid fa-bell"></i>
                <?php if($totalNotifikasi > 0){ ?>
                <span class="notif-badge"><?= $totalNotifikasi > 9 ? '9+' : $totalNotifikasi; ?></span>
                <?php } ?>
            </button>

            <div class="dropdown-menu dropdown-menu-end notif-dropdown shadow" aria-labelledby="btnNotifikasi">
                <div class="notif-header">
                    <span><i class="fa-solid fa-bell me-2"></i> Notifikasi</span>
                    <?php if($totalNotifikasi > 0){ ?>
                        <span class="notif-count"><?= $totalNotifikasi; ?> baru</span>
                    <?php } ?>
                </div>

                <div class="notif-body">
                    <?php if($totalNotifikasi == 0){ ?>
                        <div class="notif-empty">
                            <i class="fa-solid fa-circle-check"></i>
                            <p>Tidak ada notifikasi baru</p>
                        </div>
                    <?php }else{ ?>
                        <?php while($row = mysqli_fetch_assoc($qNotifPendaftaran)){ ?>
                        <a href="<?= BASE_URL ?>dospem/dashboard_pembimbing.php" class="notif-item">
                            <div class="notif-icon notif-icon-primary">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                            <div class="notif-text">
                                <strong><?= $row['nama']; ?></strong> mengajukan pendaftaran PKL baru
                                <div class="notif-time"><?= date('d-m-Y', strtotime($row['tanggal_daftar'])); ?></div>
                            </div>
                        </a>
                        <?php } ?>

                        <?php while($row = mysqli_fetch_assoc($qNotifLogbook)){ ?>
                        <a href="<?= BASE_URL ?>dospem/logbook.php" class="notif-item">
                            <div class="notif-icon notif-icon-warning">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <div class="notif-text">
                                <strong><?= $row['nama']; ?></strong> mengirim logbook, menunggu verifikasi
                                <div class="notif-time"><?= date('d-m-Y', strtotime($row['tanggal'])); ?></div>
                            </div>
                        </a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="dropdown">
            <button type="button" class="btn-profile" id="btnProfile" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=0d6efd&color=fff"
                     class="rounded-circle shadow-sm"
                     width="40" height="40">
                <b class="ms-2 d-none d-md-inline"><?= $_SESSION['nama']; ?></b>
                <i class="fa-solid fa-chevron-down ms-2 profile-chevron"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-end profile-dropdown shadow" aria-labelledby="btnProfile">
                <div class="profile-header">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=ffffff&color=2563eb"
                         class="rounded-circle"
                         width="48" height="48">
                    <div>
                        <strong><?= $_SESSION['nama']; ?></strong>
                        <div class="profile-role"><?= isset($_SESSION['role']) ? $_SESSION['role'] : 'Pembimbing'; ?></div>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>admin/ganti_password.php" class="profile-item">
                    <i class="fa-solid fa-key"></i> Ganti Password
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .navbar-custom {
        position: relative;
        padding: 10px 20px;
        background-color: #ffffff;
        height: 70px;
        border-bottom: 1px solid #eef2f6;
        margin-bottom: 24px;
        flex-wrap: nowrap !important;
        gap: 8px;
    }

    .navbar-title {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        margin: 0;
        display: flex;
        align-items: baseline;
        gap: 8px;
    }
    .navbar-title .brand-main {
        font-weight: 800;
        font-size: 1.5rem;
        color: #1e3a8a;
        letter-spacing: 0.5px;
    }
    .navbar-title .brand-sub {
        font-weight: 500;
        font-size: 0.9rem;
        color: #94a3b8;
        letter-spacing: 1.5px;
    }

    .btn-hamburger {
        background: none;
        border: none;
        color: #1e293b;
        font-size: 1.3rem;
        padding: 0.4rem 0.6rem;
        border-radius: 8px;
        cursor: pointer;
        transition: background .2s ease;
    }
    .btn-hamburger:hover {
        background: #f1f5f9;
    }

    .btn-notif {
        position: relative;
        background: none;
        border: none;
        font-size: 1.25rem;
        color: #1e293b;
        padding: 0.4rem 0.6rem;
        border-radius: 8px;
        cursor: pointer;
        transition: background .2s ease;
    }
    .btn-notif:hover {
        background: #f1f5f9;
    }
    .notif-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        background: #dc2626;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 3px;
        border: 2px solid #fff;
    }

    .notif-dropdown {
        width: 340px;
        padding: 0;
        border: none;
        border-radius: 14px;
        overflow: hidden;
        margin-top: 10px;
    }
    .notif-header {
        background: #2563eb;
        color: #fff;
        padding: 0.9rem 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .notif-count {
        background: rgba(255,255,255,.2);
        font-size: 0.72rem;
        font-weight: 500;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
    }
    .notif-body {
        max-height: 340px;
        overflow-y: auto;
    }
    .notif-item {
        display: flex;
        gap: 0.7rem;
        padding: 0.8rem 1.1rem;
        text-decoration: none;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s ease;
    }
    .notif-item:hover {
        background: #f8fafc;
    }
    .notif-item:last-child {
        border-bottom: none;
    }
    .notif-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }
    .notif-icon-primary { background: #eff6ff; color: #1d4ed8; }
    .notif-icon-warning { background: #fef3c7; color: #92400e; }
    .notif-text {
        font-size: 0.85rem;
        line-height: 1.4;
    }
    .notif-time {
        color: #94a3b8;
        font-size: 0.75rem;
        margin-top: 2px;
    }
    .notif-empty {
        text-align: center;
        padding: 2.2rem 1rem;
        color: #94a3b8;
    }
    .notif-empty i {
        font-size: 1.8rem;
        color: #16a34a;
        display: block;
        margin-bottom: 0.5rem;
    }
    .notif-empty p {
        margin: 0;
        font-size: 0.85rem;
    }

    .btn-profile {
        background: none;
        border: none;
        display: flex;
        align-items: center;
        padding: 0.3rem 0.6rem;
        border-radius: 10px;
        cursor: pointer;
        transition: background .2s ease;
    }
    .btn-profile:hover {
        background: #f1f5f9;
    }
    .profile-chevron {
        font-size: 0.7rem;
        color: #94a3b8;
        transition: transform .2s ease;
    }
    .btn-profile[aria-expanded="true"] .profile-chevron {
        transform: rotate(180deg);
    }
    .profile-dropdown {
        width: 260px;
        padding: 0;
        border: none;
        border-radius: 14px;
        overflow: hidden;
        margin-top: 10px;
    }
    .profile-header {
        background: #2563eb;
        color: #fff;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    .profile-header strong {
        display: block;
        font-size: 0.95rem;
    }
    .profile-role {
        font-size: 0.75rem;
        opacity: 0.85;
        text-transform: capitalize;
    }
    .profile-item {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.75rem 1.1rem;
        color: #334155;
        text-decoration: none;
        font-size: 0.88rem;
        transition: background .15s ease;
    }
    .profile-item:hover {
        background: #f8fafc;
        color: #1e293b;
    }
    .profile-item i {
        width: 18px;
        color: #64748b;
    }

    /* Penyesuaian Responsif Khusus HP (Layar <= 768px) */
    @media (max-width: 768px) {
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
    var body = document.body;
    var btnToggle = document.getElementById('btnToggleSidebar');
    var storageKey = 'sipklbm_sidebar_hidden';

    // State awal desktop
    var isHidden = sessionStorage.getItem(storageKey) === '1';
    if(window.innerWidth > 768 && isHidden){
        body.classList.add('sidebar-hidden');
    }

    // Toggle Sidebar
    if(btnToggle){
        btnToggle.addEventListener('click', function(){
            if(window.innerWidth <= 768) {
                body.classList.toggle('sidebar-show');
            } else {
                body.classList.toggle('sidebar-hidden');
                var nowHidden = body.classList.contains('sidebar-hidden');
                sessionStorage.setItem(storageKey, nowHidden ? '1' : '0');
            }
        });
    }

    // Menutup sidebar di HP saat menekan area luar/gelap
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 && body.classList.contains('sidebar-show')) {
            if (!event.target.closest('.sidebar') && !event.target.closest('#btnToggleSidebar')) {
                body.classList.remove('sidebar-show');
            }
        }
    });

    // Konfirmasi Logout
    var btnLogout = document.getElementById('btnLogout');
    if(btnLogout){
        btnLogout.addEventListener('click', function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Keluar dari sistem?',
                text: 'Kamu akan logout dari SIPKL BM.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626'
            }).then((result) => {
                if(result.isConfirmed){
                    window.location = '<?= BASE_URL ?>logout.php';
                }
            });
        });
    }
});
</script>
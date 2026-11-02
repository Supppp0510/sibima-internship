<?php

// Guard: kalau halaman yang meng-include file ini belum define BASE_URL,
// definisikan default-nya di sini supaya tidak fatal error "Undefined constant BASE_URL".
if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}

// Deteksi file halaman saat ini untuk penentuan menu aktif
$currentFile = basename($_SERVER['PHP_SELF']);

if (!function_exists('isActivePelajar')) {
    function isActivePelajar($file, $currentFile){
        return ($file === $currentFile) ? 'active' : '';
    }
}

?>

<!-- SweetAlert2 (untuk pop up konfirmasi logout) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="sidebar" id="sidebarMenu">

    <div class="logo">
        <img src="<?= BASE_URL ?>assets/img/sibimaa.png" alt="Logo SIBIMA">
    </div>

    <div class="sidebar-menu">

        <a href="dashboard_pelajar.php" class="<?= isActivePelajar('dashboard_pelajar.php', $currentFile); ?>">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>

        <hr>

        <?php
        // 1. Ambil User ID dari session login (users.id)
        $user_id_sess = $_SESSION['id_user'] ?? $_SESSION['id'] ?? $_SESSION['user_id'] ?? 0;
        $sidebar_pelajar_id = 0;

        // 2. Cari ID Pelajar dari tabel `pelajar` berdasarkan `user_id`
        if ($user_id_sess > 0 && isset($conn)) {
            $qSidebarPelajar = mysqli_query($conn, "SELECT id FROM pelajar WHERE user_id = '$user_id_sess' LIMIT 1");
            if ($qSidebarPelajar && mysqli_num_rows($qSidebarPelajar) > 0) {
                $dataSidebarPelajar = mysqli_fetch_assoc($qSidebarPelajar);
                $sidebar_pelajar_id = $dataSidebarPelajar['id'];
            }
        }

        $sudah_mendaftar    = false;
        $status_pendaftaran = '';
        $magang_aktif       = false;

        // 3. Cek keberadaan data pendaftaran dari tabel `pendaftaran` berdasarkan `pelajar_id`
        if ($sidebar_pelajar_id > 0 && isset($conn)) {
            $qCekStatus = mysqli_query($conn, "
                SELECT 
                    p.*, 
                    pr.tanggal_selesai, 
                    pr.status AS status_periode 
                FROM pendaftaran p 
                LEFT JOIN periode_pkl pr ON p.periode_id = pr.id 
                WHERE p.pelajar_id = '$sidebar_pelajar_id' 
                ORDER BY p.id DESC LIMIT 1
            ");

            if ($qCekStatus && mysqli_num_rows($qCekStatus) > 0) {
                $status_data     = mysqli_fetch_assoc($qCekStatus);
                $sudah_mendaftar = true; // Tandai bahwa pelajar sudah mendaftar

                // Ambil status pendaftaran (default ke 'diterima' jika kolom status belum diisi)
                $status_pendaftaran = strtolower(trim($status_data['status'] ?? 'diterima'));

                if ($status_pendaftaran === 'diterima') {
                    $tgl_selesai = $status_data['tanggal_selesai'] ?? null;
                    $hari_ini    = date('Y-m-d');
                    
                    // Magang dianggap AKTIF jika tanggal_selesai belum lewat (>= hari ini) atau belum diatur
                    if (empty($tgl_selesai) || strtotime($tgl_selesai) >= strtotime($hari_ini)) {
                        $magang_aktif = true;
                    }
                }
            }
        }
        ?>

        <!-- 🔴 Menu Pendaftaran: Disembunyikan jika pelajar SUDAH pernah mendaftar -->
        <?php if (!$sudah_mendaftar): ?>
            <a href="pendaftaran.php" class="<?= isActivePelajar('pendaftaran.php', $currentFile); ?>">
                <i class="fa-solid fa-file-signature"></i>
                Pendaftaran
            </a>
        <?php endif; ?>

        <!-- 🟢 Menu Absensi, Logbook, Laporan, & Sertifikat: Muncul jika status 'diterima' -->
        <?php if ($status_pendaftaran === 'diterima'): ?>
            <a href="absensi.php" class="<?= isActivePelajar('absensi.php', $currentFile); ?>">
                <i class="fa-solid fa-location-dot"></i>
                Absensi
            </a>

            <a href="logbook.php" class="<?= isActivePelajar('logbook.php', $currentFile); ?>">
                <i class="fa-solid fa-book-open"></i>
                Logbook
            </a>

            <a href="laporan_akhir.php" class="<?= isActivePelajar('laporan_akhir.php', $currentFile); ?>">
                <i class="fa-solid fa-file-pdf"></i>
                Laporan Akhir
            </a>

            <a href="sertifikat.php" class="<?= isActivePelajar('sertifikat.php', $currentFile); ?>">
                <i class="fa-solid fa-award"></i>
                Sertifikat
            </a>
        <?php endif; ?>

        <hr>

        <a href="#" onclick="konfirmasiLogout(event)">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>

    </div>

</div>

<style>
    /* ==========================
       STRUKTUR DASAR SIDEBAR
    ========================== */
    .sidebar{
        width: 260px;
        background:
            linear-gradient(rgba(15, 23, 42, 0.80), rgba(15, 23, 42, 0.90)),
            url('<?= BASE_URL ?>assets/img/binamarga.jpeg') no-repeat center center;
        background-size: cover;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        color: #fff;
        z-index: 100;
        overflow-y: auto;
        border-right: 1px solid rgba(255,255,255,.05);
    }

    .sidebar hr{
        border-color: rgba(255,255,255,.1);
        margin: .8rem 1.2rem;
    }

    .logo{
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem 0 1rem 0;
    }
    
    .logo img{
        max-width: 130px;
        height: auto;
        filter: drop-shadow(0 4px 6px rgba(0,0,0,.3));
    }

    .sidebar-menu{ 
        padding: 0 1rem; 
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .sidebar-menu a{
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        font-size: 0.95rem;
        font-weight: 500;
        border-radius: 10px;
        color: rgba(255,255,255,.75);
        text-decoration: none;
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }

    .sidebar-menu a i{
        width: 26px;
        height: 26px;
        min-width: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.08);
        border-radius: 7px;
        font-size: 0.8rem;
        color: rgba(255,255,255,.6);
        transition: background .2s ease, color .2s ease;
    }

    .sidebar-menu a:hover{
        background: rgba(255,255,255,.08);
        color: #fff;
        transform: translateX(3px);
    }
    .sidebar-menu a:hover i{
        background: rgba(37, 99, 235, .35);
        color: #93c5fd;
    }

    .sidebar-menu a.active{
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(37, 99, 235, .4);
        position: relative;
    }
    
    .sidebar-menu a.active::before{
        content: "";
        position: absolute;
        left: -8px;
        top: 15%;
        bottom: 15%;
        width: 4px;
        background: #fff;
        border-radius: 4px;
    }

    .sidebar-menu a.active i{
        background: rgba(255,255,255,.25);
        color: #fff;
    }
</style>

<script>
(function(){

    var sidebar = document.getElementById('sidebarMenu');
    var storageKey = 'sipklbm_sidebar_scroll_pelajar';

    var savedScroll = sessionStorage.getItem(storageKey);
    if(savedScroll !== null){
        sidebar.scrollTop = parseInt(savedScroll, 10);
    }

    sidebar.addEventListener('scroll', function(){
        sessionStorage.setItem(storageKey, sidebar.scrollTop);
    });

    var allLinks = sidebar.querySelectorAll('a');
    allLinks.forEach(function(link){
        link.addEventListener('click', function(){
            sessionStorage.setItem(storageKey, sidebar.scrollTop);
        });
    });

})();

function konfirmasiLogout(e){
    e.preventDefault();

    Swal.fire({
        title: 'Yakin ingin logout?',
        text: "Anda akan keluar dari sesi ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if(result.isConfirmed){
            window.location.href = "<?= BASE_URL ?>logout.php";
        }
    });
}
</script>
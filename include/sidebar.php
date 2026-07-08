<?php

// Deteksi path URL saat ini untuk penentuan menu & submenu aktif
$currentPath = $_SERVER['REQUEST_URI'];

function isActivePath($needle, $currentPath){
    return (strpos($currentPath, $needle) !== false);
}

// Cek grup submenu mana yang sedang aktif, supaya otomatis terbuka saat halaman dimuat
$activeMasterData = isActivePath('admin/bidang/', $currentPath)
    || isActivePath('admin/universitas/', $currentPath)
    || isActivePath('admin/program_studi/', $currentPath)
    || isActivePath('admin/periode_pkl/', $currentPath);

$activePkl = isActivePath('admin/mahasiswa/', $currentPath)
    || isActivePath('admin/pembimbing/', $currentPath)
    || isActivePath('admin/pendaftaran/', $currentPath);

$activeMonitoring = isActivePath('admin/absensi/', $currentPath)
    || isActivePath('admin/logbook/', $currentPath);

$activeLaporan = isActivePath('admin/penilaian/', $currentPath)
    || isActivePath('admin/sertifikat/', $currentPath);

?>

<div class="sidebar" id="sidebarMenu">

    <div class="logo">
        <img src="<?= BASE_URL ?>assets/img/LOGO (3).png" alt="Logo SIPKL BM">
    </div>

    <div class="sidebar-menu">

        <!-- Dashboard -->
        <a href="<?= BASE_URL ?>admin/dashboard.php"
           class="<?= isActivePath('admin/dashboard.php', $currentPath) ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>

        <hr>

        <!-- MASTER DATA -->
        <div class="menu-group">

            <button type="button" class="menu-toggle <?= $activeMasterData ? 'open' : ''; ?>" data-target="submenuMasterData">
                <span><i class="fa-solid fa-database"></i> Master Data</span>
                <i class="fa-solid fa-chevron-down chevron"></i>
            </button>

            <div class="submenu <?= $activeMasterData ? 'show' : ''; ?>" id="submenuMasterData">

                <a href="<?= BASE_URL ?>admin/bidang/index.php"
                   class="<?= isActivePath('admin/bidang/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-building"></i> Bidang
                </a>

                <a href="<?= BASE_URL ?>admin/universitas/index.php"
                   class="<?= isActivePath('admin/universitas/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-school"></i> Universitas
                </a>

                <a href="<?= BASE_URL ?>admin/program_studi/index.php"
                   class="<?= isActivePath('admin/program_studi/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-book"></i> Program Studi
                </a>

                <a href="<?= BASE_URL ?>admin/periode_pkl/index.php"
                   class="<?= isActivePath('admin/periode_pkl/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-calendar"></i> Periode PKL
                </a>

            </div>

        </div>

        <hr>

        <!-- PKL -->
        <div class="menu-group">

            <button type="button" class="menu-toggle <?= $activePkl ? 'open' : ''; ?>" data-target="submenuPkl">
                <span><i class="fa-solid fa-graduation-cap"></i> PKL</span>
                <i class="fa-solid fa-chevron-down chevron"></i>
            </button>

            <div class="submenu <?= $activePkl ? 'show' : ''; ?>" id="submenuPkl">

                <a href="<?= BASE_URL ?>admin/mahasiswa/index.php"
                   class="<?= isActivePath('admin/mahasiswa/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-user-graduate"></i> Mahasiswa
                </a>

                <a href="<?= BASE_URL ?>admin/pembimbing/index.php"
                   class="<?= isActivePath('admin/pembimbing/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-user-tie"></i> Pembimbing
                </a>

                <a href="<?= BASE_URL ?>admin/pendaftaran/index.php"
                   class="<?= isActivePath('admin/pendaftaran/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-file-signature"></i> Pendaftaran
                </a>

            </div>

        </div>

        <hr>

        <!-- MONITORING -->
        <div class="menu-group">

            <button type="button" class="menu-toggle <?= $activeMonitoring ? 'open' : ''; ?>" data-target="submenuMonitoring">
                <span><i class="fa-solid fa-eye"></i> Monitoring</span>
                <i class="fa-solid fa-chevron-down chevron"></i>
            </button>

            <div class="submenu <?= $activeMonitoring ? 'show' : ''; ?>" id="submenuMonitoring">

                <a href="<?= BASE_URL ?>admin/absensi/index.php"
                   class="<?= isActivePath('admin/absensi/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-location-dot"></i> Absensi
                </a>

                <a href="<?= BASE_URL ?>admin/logbook/index.php"
                   class="<?= isActivePath('admin/logbook/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-book-open"></i> Logbook
                </a>

            </div>

        </div>

        <hr>

        <!-- LAPORAN -->
        <div class="menu-group">

            <button type="button" class="menu-toggle <?= $activeLaporan ? 'open' : ''; ?>" data-target="submenuLaporan">
                <span><i class="fa-solid fa-chart-line"></i> Laporan</span>
                <i class="fa-solid fa-chevron-down chevron"></i>
            </button>

            <div class="submenu <?= $activeLaporan ? 'show' : ''; ?>" id="submenuLaporan">

                <a href="<?= BASE_URL ?>admin/penilaian/index.php"
                   class="<?= isActivePath('admin/penilaian/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-simple"></i> Penilaian
                </a>

                <a href="<?= BASE_URL ?>admin/sertifikat/index.php"
                   class="<?= isActivePath('admin/sertifikat/', $currentPath) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-award"></i> Sertifikat
                </a>

            </div>

        </div>

        <hr>

        <a href="<?= BASE_URL ?>logout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>

    </div>

</div>

<style>
    .logo{
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.2rem 0;
    }
    .logo img{
        max-width: 140px;
        height: auto;
    }

    /* Menu utama biasa (Dashboard, Logout) */
    .sidebar-menu a.active{
        background: rgba(255,255,255,.25);
        font-weight: 600;
        position: relative;
    }
    .sidebar-menu a.active::before{
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #fff;
        border-radius: 0 4px 4px 0;
    }
    .sidebar-menu a.active i{
        color: #fbbf24;
    }

    /* Tombol menu utama yang punya submenu */
    .menu-toggle{
        width: 100%;
        background: none;
        border: none;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background .3s;
    }
    .menu-toggle:hover{
        background: rgba(255,255,255,.2);
    }
    .menu-toggle span{
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .menu-toggle .chevron{
        font-size: .75rem;
        transition: transform .25s ease;
    }
    .menu-toggle.open .chevron{
        transform: rotate(180deg);
    }
    .menu-toggle.open{
        background: rgba(255,255,255,.12);
        font-weight: 600;
    }

    /* Submenu (collapsible) */
    .submenu{
        max-height: 0;
        overflow: hidden;
        transition: max-height .3s ease;
        padding-left: 8px;
    }
    .submenu.show{
        max-height: 500px;
    }
    .submenu a{
        padding: 10px 12px 10px 34px;
        font-size: 0.92rem;
        position: relative;
    }
    .submenu a.active{
        background: rgba(255,255,255,.25);
        font-weight: 600;
    }
    .submenu a.active::before{
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #fff;
        border-radius: 0 4px 4px 0;
    }
    .submenu a.active i{
        color: #fbbf24;
    }
</style>

<script>
(function(){

    var sidebar = document.getElementById('sidebarMenu');
    var storageKey = 'sipklbm_sidebar_scroll';

    // Kembalikan posisi scroll sidebar setelah halaman baru dimuat
    var savedScroll = sessionStorage.getItem(storageKey);
    if(savedScroll !== null){
        sidebar.scrollTop = parseInt(savedScroll, 10);
    }

    // Simpan posisi scroll setiap kali sidebar di-scroll manual
    sidebar.addEventListener('scroll', function(){
        sessionStorage.setItem(storageKey, sidebar.scrollTop);
    });

    // Simpan posisi scroll tepat sebelum pindah halaman (klik link menu/submenu)
    var allLinks = sidebar.querySelectorAll('a');
    allLinks.forEach(function(link){
        link.addEventListener('click', function(){
            sessionStorage.setItem(storageKey, sidebar.scrollTop);
        });
    });

    // Toggle buka/tutup submenu
    var toggles = document.querySelectorAll('.menu-toggle');
    toggles.forEach(function(btn){
        btn.addEventListener('click', function(){
            var targetId = btn.getAttribute('data-target');
            var target = document.getElementById(targetId);

            var isOpen = btn.classList.contains('open');

            if(isOpen){
                btn.classList.remove('open');
                target.classList.remove('show');
            }else{
                btn.classList.add('open');
                target.classList.add('show');
            }

            sessionStorage.setItem(storageKey, sidebar.scrollTop);
        });
    });

})();
</script>

<div class="content">
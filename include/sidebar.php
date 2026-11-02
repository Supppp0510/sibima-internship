<?php

// =========================================================
// DETEKSI PATH URL SAAT INI
// =========================================================

$currentPath = $_SERVER['REQUEST_URI'];


function isActivePath($needle, $currentPath)
{
    return (strpos($currentPath, $needle) !== false);
}


// =========================================================
// CEK GROUP MENU YANG AKTIF
// =========================================================

// MASTER DATA
// PERBAIKAN:
// admin/unit kerja/  -> admin/unit_kerja/

$activeMasterData =
    isActivePath('admin/unit_kerja/', $currentPath)
    || isActivePath('admin/instansi/', $currentPath)
    || isActivePath('admin/jurusan/', $currentPath)
    || isActivePath('admin/periode_pkl/', $currentPath)
    || isActivePath('admin/ruangan/', $currentPath);


// PKL

$activePkl =
    isActivePath('admin/pelajar/', $currentPath)
    || isActivePath('admin/pembimbing/', $currentPath)
    || isActivePath('admin/pendaftaran/', $currentPath);


// MONITORING

$activeMonitoring =
    isActivePath('admin/absensi/', $currentPath)
    || isActivePath('admin/logbook/', $currentPath);


// LAPORAN

$activeLaporan =
    isActivePath('admin/penilaian/', $currentPath)
    || isActivePath('admin/sertifikat/', $currentPath);

?>



<!-- =====================================================
     SWEETALERT2
     ===================================================== -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<!-- =====================================================
     SIDEBAR
     ===================================================== -->

<div class="sidebar" id="sidebarMenu">


    <!-- =================================================
         LOGO
         ================================================= -->

    <div class="logo">

        <img
            src="<?php echo BASE_URL ?>assets/img/sibimaa.png"
            alt="Logo SIBIMA"
        >

    </div>



    <!-- =================================================
         SIDEBAR MENU
         ================================================= -->

    <div class="sidebar-menu">


        <!-- =================================================
             DASHBOARD
             ================================================= -->

        <a
            href="<?php echo BASE_URL ?>admin/dashboard.php"
            class="<?php echo isActivePath(
                'admin/dashboard.php',
                $currentPath
            ) ? 'active' : ''; ?>"
        >

            <i class="fa-solid fa-house"></i>

            Dashboard

        </a>



        <hr>



        <!-- =================================================
             MASTER DATA
             ================================================= -->

        <div class="menu-group">


            <button
                type="button"
                class="menu-toggle <?php echo $activeMasterData ? 'open' : ''; ?>"
                data-target="submenuMasterData"
            >

                <span>

                    <i class="fa-solid fa-database"></i>

                    Master Data

                </span>


                <i class="fa-solid fa-chevron-down chevron"></i>

            </button>



            <div
                class="submenu <?php echo $activeMasterData ? 'show' : ''; ?>"
                id="submenuMasterData"
            >


                <!-- UNIT KERJA -->

                <a
                    href="<?php echo BASE_URL ?>admin/unit_kerja/index.php"
                    class="<?php echo isActivePath(
                        'admin/unit_kerja/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-building"></i>

                    Unit Kerja

                </a>



                <!-- INSTANSI -->

                <a
                    href="<?php echo BASE_URL ?>admin/instansi/index.php"
                    class="<?php echo isActivePath(
                        'admin/instansi/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-school"></i>

                    Instansi

                </a>



                <!-- JURUSAN -->

                <a
                    href="<?php echo BASE_URL ?>admin/jurusan/index.php"
                    class="<?php echo isActivePath(
                        'admin/jurusan/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-book"></i>

                    Jurusan

                </a>



                <!-- PERIODE PKL -->

                <a
                    href="<?php echo BASE_URL ?>admin/periode_pkl/index.php"
                    class="<?php echo isActivePath(
                        'admin/periode_pkl/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-calendar"></i>

                    Periode PKL

                </a>



                <!-- RUANGAN -->

                <a
                    href="<?php echo BASE_URL ?>admin/ruangan/index.php"
                    class="<?php echo isActivePath(
                        'admin/ruangan/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-calendar"></i>

                    Ruangan

                </a>


            </div>

        </div>



        <hr>



        <!-- =================================================
             PKL
             ================================================= -->

        <div class="menu-group">


            <button
                type="button"
                class="menu-toggle <?php echo $activePkl ? 'open' : ''; ?>"
                data-target="submenuPkl"
            >

                <span>

                    <i class="fa-solid fa-graduation-cap"></i>

                    PKL

                </span>


                <i class="fa-solid fa-chevron-down chevron"></i>

            </button>



            <div
                class="submenu <?php echo $activePkl ? 'show' : ''; ?>"
                id="submenuPkl"
            >


                <!-- PELAJAR -->

                <a
                    href="<?php echo BASE_URL ?>admin/pelajar/index.php"
                    class="<?php echo isActivePath(
                        'admin/pelajar/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-user-graduate"></i>

                    Pelajar

                </a>



                <!-- PEMBIMBING -->

                <a
                    href="<?php echo BASE_URL ?>admin/pembimbing/index.php"
                    class="<?php echo isActivePath(
                        'admin/pembimbing/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-user-tie"></i>

                    Pembimbing

                </a>



                <!-- PENDAFTARAN -->

                <a
                    href="<?php echo BASE_URL ?>admin/pendaftaran/index.php"
                    class="<?php echo isActivePath(
                        'admin/pendaftaran/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-file-signature"></i>

                    Pendaftaran

                </a>


            </div>

        </div>



        <hr>



        <!-- =================================================
             MONITORING
             ================================================= -->

        <div class="menu-group">


            <button
                type="button"
                class="menu-toggle <?php echo $activeMonitoring ? 'open' : ''; ?>"
                data-target="submenuMonitoring"
            >

                <span>

                    <i class="fa-solid fa-eye"></i>

                    Monitoring

                </span>


                <i class="fa-solid fa-chevron-down chevron"></i>

            </button>



            <div
                class="submenu <?php echo $activeMonitoring ? 'show' : ''; ?>"
                id="submenuMonitoring"
            >


                <!-- ABSENSI -->

                <a
                    href="<?php echo BASE_URL ?>admin/absensi/index.php"
                    class="<?php echo isActivePath(
                        'admin/absensi/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-location-dot"></i>

                    Absensi

                </a>



                <!-- LOGBOOK -->

                <a
                    href="<?php echo BASE_URL ?>admin/logbook/index.php"
                    class="<?php echo isActivePath(
                        'admin/logbook/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-book-open"></i>

                    Logbook

                </a>


            </div>

        </div>



        <hr>



        <!-- =================================================
             LAPORAN
             ================================================= -->

        <div class="menu-group">


            <button
                type="button"
                class="menu-toggle <?php echo $activeLaporan ? 'open' : ''; ?>"
                data-target="submenuLaporan"
            >

                <span>

                    <i class="fa-solid fa-chart-line"></i>

                    Laporan

                </span>


                <i class="fa-solid fa-chevron-down chevron"></i>

            </button>



            <div
                class="submenu <?php echo $activeLaporan ? 'show' : ''; ?>"
                id="submenuLaporan"
            >


                <!-- PENILAIAN -->

                <a
                    href="<?php echo BASE_URL ?>admin/penilaian/index.php"
                    class="<?php echo isActivePath(
                        'admin/penilaian/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-chart-simple"></i>

                    Penilaian

                </a>



                <!-- SERTIFIKAT -->

                <a
                    href="<?php echo BASE_URL ?>admin/sertifikat/index.php"
                    class="<?php echo isActivePath(
                        'admin/sertifikat/',
                        $currentPath
                    ) ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-award"></i>

                    Sertifikat

                </a>


            </div>

        </div>



        <hr>



        <!-- =================================================
             LOGOUT
             ================================================= -->

        <a
            href="#"
            onclick="konfirmasiLogout(event)"
        >

            <i class="fa-solid fa-right-from-bracket"></i>

            Logout

        </a>


    </div>

</div>



<!-- =========================================================
     CSS SIDEBAR
     ========================================================= -->

<style>


/* =========================================================
   LOGO
   ========================================================= */

.logo {

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 1.2rem 0;

}


.logo img {

    max-width: 140px;

    height: auto;

}



/* =========================================================
   MENU UTAMA BIASA
   ========================================================= */

.sidebar-menu a.active {

    background: rgba(255, 255, 255, .25);

    font-weight: 600;

    position: relative;

}


.sidebar-menu a.active::before {

    content: "";

    position: absolute;

    left: 0;

    top: 0;

    bottom: 0;

    width: 4px;

    background: #fff;

    border-radius: 0 4px 4px 0;

}


.sidebar-menu a.active i {

    color: #fbbf24;

}



/* =========================================================
   MENU TOGGLE
   ========================================================= */

.menu-toggle {

    width: 100%;

    background: rgba(255, 255, 255, .04);

    border: 1px solid rgba(255, 255, 255, .06);

    color: #fff;

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 12px 14px;

    border-radius: 10px;

    margin-bottom: 4px;

    font-size: 0.95rem;

    cursor: pointer;

    transition:
        background .25s ease,
        border-color .25s ease;

}


.menu-toggle:hover {

    background: rgba(255, 255, 255, .1);

    border-color: rgba(255, 255, 255, .12);

}


.menu-toggle span {

    display: flex;

    align-items: center;

    gap: 10px;

}


.menu-toggle i:first-child {

    width: 26px;

    height: 26px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    background: rgba(37, 99, 235, .35);

    border-radius: 7px;

    font-size: 0.8rem;

    color: #93c5fd;

}



/* =========================================================
   CHEVRON
   ========================================================= */

.menu-toggle .chevron {

    font-size: .7rem;

    color: rgba(255, 255, 255, .5);

    transition: transform .25s ease;

}


.menu-toggle.open .chevron {

    transform: rotate(180deg);

    color: #93c5fd;

}


.menu-toggle.open {

    background: rgba(37, 99, 235, .18);

    border-color: rgba(37, 99, 235, .35);

    font-weight: 600;

}



/* =========================================================
   SUBMENU
   ========================================================= */

.submenu {

    max-height: 0;

    overflow: hidden;

    transition: max-height .3s ease;

    margin-left: 13px;

    padding-left: 14px;

    border-left:
        1px dashed rgba(255, 255, 255, .15);

    margin-top: 6px;

}


.submenu.show {

    max-height: 500px;

    margin-bottom: 8px;

}


.submenu a {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 9px 12px;

    font-size: 0.88rem;

    border-radius: 8px;

    color: rgba(255, 255, 255, .75);

    margin-bottom: 3px;

    transition:
        background .2s ease,
        color .2s ease,
        transform .2s ease;

}


.submenu a i {

    width: 22px;

    height: 22px;

    min-width: 22px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    background: rgba(255, 255, 255, .08);

    border-radius: 6px;

    font-size: 0.72rem;

    color: rgba(255, 255, 255, .6);

    transition:
        background .2s ease,
        color .2s ease;

}


.submenu a:hover {

    background: rgba(255, 255, 255, .08);

    color: #fff;

    transform: translateX(2px);

}


.submenu a.active {

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #1d4ed8
        );

    color: #fff;

    font-weight: 600;

    box-shadow:
        0 4px 12px rgba(37, 99, 235, .4);

}


.submenu a.active i {

    background: rgba(255, 255, 255, .25);

    color: #fff;

}

</style>



<!-- =========================================================
     JAVASCRIPT SIDEBAR
     ========================================================= -->

<script>

(function() {


    /* =====================================================
       SIDEBAR
       ===================================================== */

    var sidebar =
        document.getElementById('sidebarMenu');


    var storageKey =
        'sipklbm_sidebar_scroll';



    /* =====================================================
       RESTORE SCROLL SIDEBAR
       ===================================================== */

    var savedScroll =
        sessionStorage.getItem(storageKey);


    if (savedScroll !== null) {

        sidebar.scrollTop =
            parseInt(savedScroll, 10);

    }



    /* =====================================================
       SIMPAN SCROLL SAAT SIDEBAR DI-SCROLL
       ===================================================== */

    sidebar.addEventListener(
        'scroll',
        function() {

            sessionStorage.setItem(
                storageKey,
                sidebar.scrollTop
            );

        }
    );



    /* =====================================================
       SIMPAN SCROLL SEBELUM PINDAH HALAMAN
       ===================================================== */

    var allLinks =
        sidebar.querySelectorAll('a');


    allLinks.forEach(
        function(link) {

            link.addEventListener(
                'click',
                function() {

                    sessionStorage.setItem(
                        storageKey,
                        sidebar.scrollTop
                    );

                }
            );

        }
    );



    /* =====================================================
       TOGGLE SUBMENU
       ===================================================== */

    var toggles =
        document.querySelectorAll('.menu-toggle');


    toggles.forEach(
        function(btn) {


            btn.addEventListener(
                'click',
                function() {


                    var targetId =
                        btn.getAttribute(
                            'data-target'
                        );


                    var target =
                        document.getElementById(
                            targetId
                        );


                    var isOpen =
                        btn.classList.contains(
                            'open'
                        );



                    if (isOpen) {

                        btn.classList.remove(
                            'open'
                        );

                        target.classList.remove(
                            'show'
                        );

                    }

                    else {

                        btn.classList.add(
                            'open'
                        );

                        target.classList.add(
                            'show'
                        );

                    }



                    sessionStorage.setItem(
                        storageKey,
                        sidebar.scrollTop
                    );

                }
            );

        }
    );


})();



/* =========================================================
   KONFIRMASI LOGOUT
   ========================================================= */

function konfirmasiLogout(e) {


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

    }).then(
        (result) => {


            if (result.isConfirmed) {

                window.location.href =
                    "<?php echo BASE_URL ?>logout.php";

            }

        }
    );

}

</script>



<div class="content">
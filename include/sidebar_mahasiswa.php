<?php

// Deteksi file halaman saat ini untuk penentuan menu aktif
$currentFile = basename($_SERVER['PHP_SELF']);

function isActiveMhs($file, $currentFile){
    return ($file === $currentFile) ? 'active' : '';
}

?>

<div class="sidebar" id="sidebarMenu">

    <div class="logo">
        <img src="../assets/img/LOGO (3).png" alt="Logo SIPKL BM">
    </div>

    <div class="sidebar-menu">

        <a href="dashboard_mahasiswa.php" class="<?= isActiveMhs('dashboard_mahasiswa.php', $currentFile); ?>">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>

        <hr>

        <a href="pendaftaran.php" class="<?= isActiveMhs('pendaftaran.php', $currentFile); ?>">
            <i class="bi bi-file-earmark-arrow-up"></i>
            Pendaftaran
        </a>

        <a href="absensi.php" class="<?= isActiveMhs('absensi.php', $currentFile); ?>">
            <i class="bi bi-geo-alt"></i>
            Absensi
        </a>

        <a href="logbook.php" class="<?= isActiveMhs('logbook.php', $currentFile); ?>">
            <i class="bi bi-journal-text"></i>
            Logbook
        </a>

        <a href="laporan_akhir.php" class="<?= isActiveMhs('laporan_akhir.php', $currentFile); ?>">
            <i class="bi bi-file-earmark-check"></i>
            Pengumpulan Laporan Akhir
        </a>

        <a href="sertifikat.php" class="<?= isActiveMhs('sertifikat.php', $currentFile); ?>">
            <i class="bi bi-patch-check"></i>
            Sertifikat
        </a>

        <hr>

        <a href="../logout.php">
            <i class="bi bi-box-arrow-left"></i>
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
</style>

<script>
(function(){

    var sidebar = document.getElementById('sidebarMenu');
    var storageKey = 'sipklbm_sidebar_scroll_mhs';

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
</script>
<?php

    // Guard: kalau halaman yang meng-include file ini belum define BASE_URL,
    // definisikan default-nya di sini supaya tidak fatal error "Undefined constant BASE_URL".
    if (! defined('BASE_URL')) {
    define('BASE_URL', '../');
    }

    // Deteksi path URL saat ini untuk penentuan menu aktif
    $currentPath = $_SERVER['REQUEST_URI'];

    if (! function_exists('isActivePath')) {
    function isActivePath($needle, $currentPath)
    {
        return (strpos($currentPath, $needle) !== false);
    }
    }

?>

<!-- SweetAlert2 (untuk pop up konfirmasi logout) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="sidebar" id="sidebarMenu">

    <div class="logo">
        <img src="<?php echo BASE_URL ?>assets/img/sibimaa.png" alt="Logo SIBIMA">
    </div>

    <div class="sidebar-menu">

        <!-- Dashboard -->
        <a href="<?php echo BASE_URL ?>dospem/dashboard_pembimbing.php"
            class="<?php echo isActivePath('dospem/dashboard_pembimbing.php', $currentPath) ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>

        <hr>

        <!-- Absensi Siswa -->
        <a href="<?php echo BASE_URL ?>dospem/absensi.php"
            class="<?php echo isActivePath('dospem/absensi.php', $currentPath) ? 'active' : ''; ?>">
            <i class="fa-solid fa-location-dot"></i>
            Absensi Siswa
        </a>

        <!-- Logbook Harian -->
        <a href="<?php echo BASE_URL ?>dospem/logbook.php"
            class="<?php echo isActivePath('dospem/logbook.php', $currentPath) ? 'active' : ''; ?>">
            <i class="fa-solid fa-book-open"></i>
            Logbook Harian
        </a>

        <!-- Penilaian Akhir -->
        <a href="<?php echo BASE_URL ?>dospem/penilaian_akhir.php"
            class="<?php echo isActivePath('dospem/penilaian_akhir.php', $currentPath) ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-line"></i>
            Penilaian Akhir
        </a>

        <!-- Sertifikat -->
        <a href="<?php echo BASE_URL ?>dospem/sertifikat.php"
            class="<?php echo isActivePath('dospem/sertifikat.php', $currentPath) ? 'active' : ''; ?>">
            <i class="fa-solid fa-award"></i>
            Sertifikat
        </a>

        <hr>

        <a href="#" onclick="konfirmasiLogout(event)">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>

    </div>

</div>

<style>
/* ==========================
       STRUKTUR DASAR SIDEBAR (disamakan dgn tema admin/mahasiswa: foto
       gedung Bina Marga + overlay gradasi gelap, dibuat self-contained
       di sini supaya semua halaman dospem yang include file ini otomatis konsisten)
    ========================== */
.sidebar {
    width: 260px;
    background:
        linear-gradient(rgba(15, 23, 42, 0.80), rgba(15, 23, 42, 0.90)),
        url('<?php echo BASE_URL ?>assets/img/binamarga.jpeg') no-repeat center center;
    background-size: cover;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    color: #fff;
    z-index: 100;
    overflow-y: auto;
}

.sidebar hr {
    border-color: rgba(255, 255, 255, .12);
    margin: .6rem 1rem;
}

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

.sidebar-menu {
    padding: 0 1rem;
}

.sidebar-menu a {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 5px;
    border-radius: 8px;
    padding: 12px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: bold;
    text-decoration: none;
    transition: all 0.2s ease;
}

.sidebar-menu a:hover {
    background-color: rgba(255, 255, 255, 0.15);
    color: #fff;
}

/* Menu aktif (Dashboard, Absensi, Logbook, Penilaian, Logout) */
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
</style>

<script>
(function() {

    var sidebar = document.getElementById('sidebarMenu');
    var storageKey = 'sibima_sidebar_scroll_dospem';

    // Kembalikan posisi scroll sidebar setelah halaman baru dimuat
    var savedScroll = sessionStorage.getItem(storageKey);
    if (savedScroll !== null) {
        sidebar.scrollTop = parseInt(savedScroll, 10);
    }

    // Simpan posisi scroll setiap kali sidebar di-scroll manual
    sidebar.addEventListener('scroll', function() {
        sessionStorage.setItem(storageKey, sidebar.scrollTop);
    });

    // Simpan posisi scroll tepat sebelum pindah halaman (klik link menu)
    var allLinks = sidebar.querySelectorAll('a');
    allLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            sessionStorage.setItem(storageKey, sidebar.scrollTop);
        });
    });

})();

// Konfirmasi logout menggunakan SweetAlert2
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
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "<?php echo BASE_URL ?>logout.php";
        }
    });
}
</script>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../helper/auth_admin.php";
require_once "../config/config.php";
include "../config/koneksi.php";

// =========================
// Total Mahasiswa
// =========================
$qMahasiswa = mysqli_query($conn, "SELECT COUNT(*) AS total FROM mahasiswa");
$totalMahasiswa = mysqli_fetch_assoc($qMahasiswa);

// =========================
// Total Pembimbing
// =========================
$qPembimbing = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pembimbing");
$totalPembimbing = mysqli_fetch_assoc($qPembimbing);

// =========================
// Total Bidang
// =========================
$qBidang = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bidang");
$totalBidang = mysqli_fetch_assoc($qBidang);

// =========================
// Total Pendaftaran
// =========================
$qPendaftaran = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pendaftaran");
$totalPendaftaran = mysqli_fetch_assoc($qPendaftaran);

// =========================
// Data Pendaftaran Terbaru
// =========================
$pendaftaranTerbaru = mysqli_query($conn, "
    SELECT
        p.id,
        m.nama,
        p.tanggal_daftar,
        p.status
    FROM pendaftaran p
    JOIN mahasiswa m
    ON p.mahasiswa_id = m.id
    ORDER BY p.id DESC
    LIMIT 5
");

include "../include/header.php";
include "../include/navbar.php";
include "../include/sidebar.php";

?>

<style>
    .stat-card{
        border: none;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        color: #fff;
    }
    .stat-card:hover{
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.18) !important;
    }
    .stat-card .card-body{
        position: relative;
        z-index: 2;
        padding: 1.4rem;
    }
    .stat-card .stat-icon{
        position: absolute;
        right: 0.8rem;
        top: 0.8rem;
        font-size: 2.6rem;
        opacity: 0.25;
        z-index: 1;
    }
    .stat-card .stat-label{
        font-size: 0.85rem;
        font-weight: 500;
        opacity: 0.9;
        margin-bottom: 0.3rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-card .stat-value{
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    .stat-mahasiswa{ background: linear-gradient(135deg, #2563eb, #1e3a8a); }
    .stat-pembimbing{ background: linear-gradient(135deg, #16a34a, #14532d); }
    .stat-bidang{ background: linear-gradient(135deg, #f59e0b, #b45309); }
    .stat-pendaftaran{ background: linear-gradient(135deg, #dc2626, #7f1d1d); }

    .welcome-banner{
        background: linear-gradient(135deg, #0f172a, #1e3a8a);
        border-radius: 16px;
        padding: 1.6rem 1.8rem;
        color: #fff;
        margin-bottom: 1.8rem;
    }
    .welcome-banner h3{
        font-weight: 700;
        margin-bottom: 0.2rem;
    }
    .welcome-banner p{
        opacity: 0.85;
        margin: 0;
    }

    #realtime-clock {
        transition: color 0.5s ease;
        letter-spacing: 2px;
    }

    .section-card{
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    }

    .table-modern thead{
        background: #1e3a8a;
        color: #fff;
    }
    .table-modern td, .table-modern th{
        vertical-align: middle;
        padding: 0.9rem 1rem;
    }
    .table-modern tbody tr{
        transition: background 0.15s ease;
    }
    .table-modern tbody tr:hover{
        background: #f1f5f9;
    }

    .avatar-circle{
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #1e3a8a;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.85rem;
        margin-right: 0.6rem;
    }

    .badge-status{
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.78rem;
    }
</style>

<div class="container-fluid">

<div class="welcome-banner d-flex justify-content-between align-items-center">
        <div>
            <h3><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</h3>
            <p>Selamat datang, <b><?= htmlspecialchars($_SESSION['nama']); ?></b> — berikut ringkasan data hari ini.</p>
        </div>
        <div id="realtime-clock" class="fw-bold fs-4 pe-3">
            --:--:--
        </div>
    </div>

    <div class="row">

        <!-- Mahasiswa -->
        <div class="col-md-3 mb-3">
            <div class="card stat-card stat-mahasiswa shadow">
                <i class="bi bi-people-fill stat-icon"></i>
                <div class="card-body">
                    <div class="stat-label">Total Mahasiswa</div>
                    <p class="stat-value"><?= $totalMahasiswa['total']; ?></p>
                </div>
            </div>
        </div>

        <!-- Pembimbing -->
        <div class="col-md-3 mb-3">
            <div class="card stat-card stat-pembimbing shadow">
                <i class="bi bi-person-video3 stat-icon"></i>
                <div class="card-body">
                    <div class="stat-label">Total Pembimbing</div>
                    <p class="stat-value"><?= $totalPembimbing['total']; ?></p>
                </div>
            </div>
        </div>

        <!-- Bidang -->
        <div class="col-md-3 mb-3">
            <div class="card stat-card stat-bidang shadow">
                <i class="bi bi-diagram-3-fill stat-icon"></i>
                <div class="card-body">
                    <div class="stat-label">Total Bidang</div>
                    <p class="stat-value"><?= $totalBidang['total']; ?></p>
                </div>
            </div>
        </div>

        <!-- Pendaftaran -->
        <div class="col-md-3 mb-3">
            <div class="card stat-card stat-pendaftaran shadow">
                <i class="bi bi-file-earmark-text-fill stat-icon"></i>
                <div class="card-body">
                    <div class="stat-label">Total Pendaftaran</div>
                    <p class="stat-value"><?= $totalPendaftaran['total']; ?></p>
                </div>
            </div>
        </div>

    </div>

    <div class="card section-card mt-2">
        <div class="card-body">

            <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Pendaftaran Terbaru</h5>

            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Mahasiswa</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($pendaftaranTerbaru) > 0):
                            while ($row = mysqli_fetch_assoc($pendaftaranTerbaru)):
                                $inisial = strtoupper(substr($row['nama'], 0, 1));
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <span class="avatar-circle"><?= $inisial; ?></span>
                                <?= htmlspecialchars($row['nama']); ?>
                            </td>
                            <td><?= date('d M Y', strtotime($row['tanggal_daftar'])); ?></td>
                            <td>
                                <?php if ($row['status'] == "menunggu"): ?>
                                    <span class="badge badge-status bg-warning text-dark">
                                        <i class="bi bi-hourglass-split me-1"></i>Menunggu
                                    </span>
                                <?php elseif ($row['status'] == "diterima"): ?>
                                    <span class="badge badge-status bg-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>Diterima
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-status bg-danger">
                                        <i class="bi bi-x-circle-fill me-1"></i>Ditolak
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada data pendaftaran.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<script>
    // Flag untuk memastikan sirine hanya bunyi sekali saat jam 15.00
    let isSirenPlayed = false;

    function updateClock() {
        const now = new Date();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const seconds = now.getSeconds();
        
        // Format agar selalu dua digit (contoh: 09, bukan 9)
        const hoursStr = String(hours).padStart(2, '0');
        const minutesStr = String(minutes).padStart(2, '0');
        const secondsStr = String(seconds).padStart(2, '0');
        
        const timeString = `${hoursStr}:${minutesStr}:${secondsStr}`;
        const clockElement = document.getElementById('realtime-clock');
        
        clockElement.textContent = timeString;

        // Mengecek apakah sudah jam 15.00 atau lebih (15:00 WIB)
        if (hours >= 15) {
            // HAPUS pengaturan warna manual, GANTI dengan menambahkan class CSS
            clockElement.classList.add('text-flicker');

            // Bunyikan sirine TEPAT saat jam 15:00:00
            // atau jika baru buka halaman setelah jam 15:00 dan belum bunyi
            if (!isSirenPlayed) {
                playSiren();
                isSirenPlayed = true; 
            }
        } else {
            // Hapus efek jika belum waktunya
            clockElement.classList.remove('text-flicker');
            isSirenPlayed = false; // Reset flag untuk besoknya
        }
    }

function playSiren() {
        // 1. Panggil file audionya
        const sirenAudio = new Audio('../assets/sfx/Alarm Siren Sound Fx - Jigga.mp3'); 
        
        // 2. Buat Audio Context (Mesin Mixernya)
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const track = audioCtx.createMediaElementSource(sirenAudio);
        const gainNode = audioCtx.createGain();

        // 3. Atur peningkatan volumenya di sini!
        // Nilai 1 = 100% (Normal). Nilai 2 = 200%, 3 = 300%, dst.
        gainNode.gain.value = 30.0; // Coba naikkan 3x lipat

        // 4. Sambungkan track -> gain (pengeras) -> speaker tujuan
        track.connect(gainNode).connect(audioCtx.destination);
        
        // 5. Putar suaranya
        sirenAudio.play().catch(function(error) {
            console.log("Browser memblokir autoplay audio. User harus berinteraksi (klik) di halaman dulu.");
        });
    }

    // Jalankan fungsi pertama kali
    updateClock();
    
    // Perbarui fungsi setiap 1000 milidetik (1 detik)
    setInterval(updateClock, 1000);
</script>

<?php

include "../include/footer.php";

?>
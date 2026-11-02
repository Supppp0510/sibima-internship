<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../helper/auth_admin.php";
require_once "../config/config.php";
include "../config/koneksi.php";
include "../config/cek_alpha_otomatis.php";
/** @var mysqli $conn */

// =========================
// Query Statistik
// =========================
$totalPelajar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pelajar"))['total'];
$totalPembimbing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pembimbing"))['total'];
$totalUnitKerja = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM unit_kerja"))['total'];
$totalPendaftaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pendaftaran"))['total'];

// Data Pendaftaran Terbaru
$pendaftaranTerbaru = mysqli_query($conn, "
    SELECT p.id, m.nama, p.tanggal_daftar, p.status
    FROM pendaftaran p
    JOIN pelajar m ON p.pelajar_id = m.id
    ORDER BY p.id DESC LIMIT 5
");

// Query Grafik
$qGrafik = mysqli_query($conn, "
SELECT
    EXTRACT(YEAR_MONTH FROM p.tanggal_daftar) AS ym,
    DATE_FORMAT(MIN(p.tanggal_daftar), '%M') AS nama_bulan,
    COUNT(*) AS total_pendaftaran,
    SUM(
        CASE
            WHEN (
                SELECT AVG(nilai_akhir)
                FROM penilaian
                WHERE pelajar_id = p.pelajar_id
            ) >= 75
            THEN 1
            ELSE 0
        END
    ) AS total_lulus
FROM pendaftaran p
WHERE p.tanggal_daftar IS NOT NULL
GROUP BY EXTRACT(YEAR_MONTH FROM p.tanggal_daftar)
ORDER BY ym;
");

if (!$qGrafik) die("Query Error: " . mysqli_error($conn));

$labelBulan = [];
$dataPendaftaran = [];
$dataLulus = [];

while($row = mysqli_fetch_assoc($qGrafik)){
    $labelBulan[] = $row['nama_bulan'];
    $dataPendaftaran[] = (int)$row['total_pendaftaran'];
    $dataLulus[] = (int)$row['total_lulus'];
}

include "../include/header.php";
include "../include/navbar.php";
include "../include/sidebar.php";
?>

<style>
    /* ==========================
       TAMBAHAN AGAR CARD KLIKABLE
    ========================== */
    .stat-card-link {
        text-decoration: none;
        display: block;
        transition: transform .2s;
    }
    .stat-card-link:hover {
        transform: scale(1.02);
    }

    /* ==========================
       CARD STATISTIK
    ========================== */
    .stat-card{
        border: none;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        transition: all .3s ease;
        color: #fff;
        box-shadow: 0 8px 20px rgba(30,58,138,.15);
        cursor: pointer;
    }

    .stat-card:hover{
        box-shadow: 0 15px 30px rgba(30,58,138,.25);
    }

    /* Efek Glass */
    .stat-card::before{
        content: '';
        position: absolute;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        top: -40px;
        right: -30px;
    }

    .stat-card .card-body{
        position: relative;
        z-index: 2;
        padding: 1.5rem;
    }

    .stat-card .stat-icon{
        position: absolute;
        right: 1rem;
        top: 1rem;
        font-size: 3rem;
        opacity: .15;
        z-index: 1;
    }

    .stat-card .stat-label{
        font-size: .85rem;
        font-weight: 500;
        opacity: .9;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: .5rem;
    }

    .stat-card .stat-value{
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    /* ==========================
       WARNA CARD (TEMA BIRU)
    ========================== */
    .stat-pelajar{ background: linear-gradient(135deg, #0f172a, #1e3a8a); }
    .stat-pembimbing{ background: linear-gradient(135deg, #1e3a8a, #2563eb); }
    .stat-unit_kerja{ background: linear-gradient(135deg, #2563eb, #3b82f6); }
    .stat-pendaftaran{ background: linear-gradient(135deg, #3b82f6, #60a5fa); }

    /* ==========================
       WELCOME BANNER
    ========================== */
    .welcome-banner{
        background: linear-gradient(135deg, #0f172a, #1e40af);
        border-radius: 20px;
        padding: 1.8rem;
        color: #fff;
        margin-bottom: 1.8rem;
        box-shadow: 0 10px 25px rgba(30,64,175,.20);
    }

    #realtime-clock{ letter-spacing: 2px; font-size: 2rem; font-weight: 700; }

    /* ==========================
       CARD TABEL
    ========================== */
    .section-card{ border: none; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,.06); }
    .table-modern thead{ background: #1e3a8a; color: #fff; }
    .table-modern th, .table-modern td{ vertical-align: middle; padding: 1rem; }
    .table-modern tbody tr:hover{ background: #f8fafc; }

    .avatar-circle{
        width: 38px; height: 38px; border-radius: 50%;
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: white; display: inline-flex; align-items: center; justify-content: center;
        font-weight: 600; margin-right: .7rem;
    }

    .badge-status{ padding: .45rem .9rem; border-radius: 30px; font-size: .78rem; font-weight: 500; }

    @media(max-width:768px){
        .welcome-banner{ flex-direction: column; align-items: start !important; }
        #realtime-clock{ margin-top: 15px; font-size: 1.5rem; }
        .stat-card .stat-value{ font-size: 1.7rem; }
    }
</style>

<div class="container-fluid">
    <div class="welcome-banner d-flex justify-content-between align-items-center">
        <div>
            <h3><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</h3>
            <p>Selamat datang, <b><?php echo htmlspecialchars($_SESSION['nama']); ?></b></p>
        </div>
        <div id="realtime-clock" class="fw-bold fs-4 pe-3">--:--:--</div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3"><a href="pelajar/index.php" class="stat-card-link"><div class="card stat-card stat-pelajar shadow"><i class="bi bi-people-fill stat-icon"></i><div class="card-body"><div class="stat-label">Total Pelajar</div><p class="stat-value"><?= $totalPelajar; ?></p></div></div></a></div>
        <div class="col-md-3 mb-3"><a href="pembimbing/index.php" class="stat-card-link"><div class="card stat-card stat-pembimbing shadow"><i class="bi bi-person-video3 stat-icon"></i><div class="card-body"><div class="stat-label">Total Pembimbing</div><p class="stat-value"><?= $totalPembimbing; ?></p></div></div></a></div>
        <div class="col-md-3 mb-3"><a href="unit_kerja/index.php" class="stat-card-link"><div class="card stat-card stat-unit_kerja shadow"><i class="bi bi-building stat-icon"></i><div class="card-body"><div class="stat-label">Total Unit Kerja</div><p class="stat-value"><?= $totalUnitKerja; ?></p></div></div></a></div>
        <div class="col-md-3 mb-3"><a href="pendaftaran/index.php" class="stat-card-link"><div class="card stat-card stat-pendaftaran shadow"><i class="bi bi-file-earmark-text-fill stat-icon"></i><div class="card-body"><div class="stat-label">Total Pendaftaran</div><p class="stat-value"><?= $totalPendaftaran; ?></p></div></div></a></div>
    </div>

    <div class="card section-card mt-4">
        <div class="card-body">
            <h5><i class="bi bi-graph-up-arrow me-2"></i>Statistik Pendaftaran & Kelulusan</h5>
            <div style="height: 300px;">
                <canvas id="grafikPendaftaran"></canvas>
            </div>
        </div>
    </div>

    <div class="card section-card mt-2">
        <div class="card-body">
            <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Pendaftaran Terbaru</h5>
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead><tr><th>No</th><th>Nama Pelajar</th><th>Tanggal</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php $no=1; while ($row = mysqli_fetch_assoc($pendaftaranTerbaru)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><span class="avatar-circle"><?= strtoupper(substr($row['nama'],0,1)); ?></span><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_daftar'])); ?></td>
                            <td><?= $row['status']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('grafikPendaftaran').getContext('2d');
    new Chart(ctx, {
        type: 'line', 
        data: {
            labels: <?= json_encode($labelBulan); ?>,
            datasets: [{
                label: 'Pendaftaran',
                data: <?= json_encode($dataPendaftaran); ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.3
            }, {
                label: 'Lulus Magang',
                data: <?= json_encode($dataLulus); ?>,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { 
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grace: '5%' },
                x: { offset: true } 
            }
        }
    });

    function updateClock() {
        const now = new Date();
        document.getElementById('realtime-clock').textContent = now.toLocaleTimeString('id-ID');
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

<?php include "../include/footer.php"; ?>
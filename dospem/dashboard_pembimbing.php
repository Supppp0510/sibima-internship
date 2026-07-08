<?php
session_start();

// GATEKEEPER: Mengunci halaman jika belum login atau bukan Pembimbing (Role ID = 2)
if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php?pesan=belum_login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pembimbing - SIPKL BM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .navbar { background: linear-gradient(135deg, #11998e, #38ef7d); } /* Warna hijau khas dosen/pembimbing */
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-3">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-person-workspace me-2"></i>SIPKL BM - Pembimbing</a>
    <div class="navbar-nav ms-auto align-items-center">
      <span class="nav-link text-white me-3 mb-0">Selamat Datang, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong></span>
      <a class="btn btn-sm btn-danger px-3 rounded-pill fw-bold" href="../logout.php"><i class="bi bi-box-arrow-left me-1"></i> Keluar</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
    <div class="card card-custom p-4 bg-white mb-4">
        <h2 class="fw-bold text-dark mb-2">Portal Dosen Pembimbing Lapangan</h2>
        <p class="text-muted mb-0">Selamat datang di panel pembimbing SIPKL BM. Di halaman ini Anda dapat memantau perkembangan magang mahasiswa bimbingan Anda, memeriksa dan memberikan validasi pada laporan logbook harian, serta memberikan penilaian akhir PKL.</p>
    </div>

    <div class="row g-4 text-white">
        <div class="col-md-4">
            <div class="card card-custom p-4" style="background: linear-gradient(135deg, #1e3c72, #2a5298);">
                <h6 class="text-uppercase small fw-bold opacity-75">Total Mahasiswa Bimbingan</h6>
                <h3 class="fw-bold mb-0">0 Orang</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-4" style="background: linear-gradient(135deg, #f857a6, #ff5858);">
                <h6 class="text-uppercase small fw-bold opacity-75">Logbook Perlu Diperiksa</h6>
                <h3 class="fw-bold mb-0">0 Berkas</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-4" style="background: linear-gradient(135deg, #f1c40f, #f39c12);">
                <h6 class="text-uppercase small fw-bold opacity-75">Tahun Akademik</h6>
                <h3 class="fw-bold mb-0" style="font-size: 1.5rem;">2026/2027</h3>
            </div>
        </div>
    </div>
</div>

</body>
</html>
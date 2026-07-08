<?php
session_start();

// GATEKEEPER: Kunci halaman jika belum login atau bukan mahasiswa (Role ID = 3)
if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php?pesan=belum_login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - SIPKL BM JATIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Topbar Style (Meniru Persis Admin) */
        .topbar { background-color: #2a5298; color: white; height: 60px; }
        
        /* Sidebar Style */
        .sidebar { background-color: #1e3c72; min-height: calc(100vh - 60px); color: #fff; }
        .sidebar .nav-link { color: #cbd5e1; border-radius: 5px; margin: 4px 15px; padding: 10px 15px; font-size: 14px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #2a5298; color: white; }
        .sidebar-heading { font-size: 11px; text-uppercase: true; letter-spacing: 1px; color: #94a3b8; padding: 10px 30px 5px; font-bold: true; }
        
        /* Cards Style (Meniru Persis Admin) */
        .card-stat { border: none; border-radius: 10px; color: white; padding: 20px; position: relative; overflow: hidden; }
        .card-stat .icon-bg { position: absolute; right: 15px; bottom: 10px; font-size: 45px; opacity: 0.3; }
        
        /* Table Style */
        .card-table { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .table-responsive { border-radius: 10px; }
        
        /* Badge Status */
        .badge-waiting { background-color: #f1c40f; color: #fff; padding: 6px 12px; border-radius: 20px; font-size: 12px; }
    </style>
</head>
<body>

    <div class="topbar d-flex align-items-center justify-content-between px-4 shadow-sm">
        <div class="d-flex align-items-center">
            <button class="btn btn-link text-white me-3 p-0" id="menu-toggle"><i class="bi bi-list fs-4"></i></button>
            <span class="fw-bold fs-5 text-uppercase" style="letter-spacing: 1px;">Sipkl Bm Jatim</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="bi bi-bell-fill me-4 fs-5 position-relative" style="cursor: pointer;"></i>
            <div class="bg-primary text-white px-3 py-1 rounded-pill d-flex align-items-center" style="font-size: 14px;">
                <span class="bg-white text-primary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; font-size: 12px;">MH</span>
                Selamat datang, <strong>&nbsp;<?php echo htmlspecialchars($_SESSION['nama']); ?></strong>
            </div>
        </div>
    </div>

    <div class="container-fluid p-0">
        <div class="row g-0">
            
            <div class="col-md-3 col-lg-2 sidebar shadow-sm">
                <div class="nav flex-column pt-3">
                    <a class="nav-link active" href="dashboard_mahasiswa.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    
                    <div class="sidebar-heading">Master Data</div>
                    <a class="nav-link" href="biodata.php"><i class="bi bi-person-vcard me-2"></i> Biodata & Berkas</a>
                    <a class="nav-link" href="pengajuan.php"><i class="bi bi-file-earmark-arrow-up me-2"></i> Pengajuan PKL</a>
                    
                    <div class="sidebar-heading">Monitoring</div>
                    <a class="nav-link disabled text-muted" href="#"><i class="bi bi-geo-alt me-2"></i> Absensi</a>
                    <a class="nav-link disabled text-muted" href="#"><i class="bi bi-book me-2"></i> Logbook</a>
                    
                    <div class="sidebar-heading">Laporan</div>
                    <a class="nav-link disabled text-muted" href="#"><i class="bi bi-graph-up me-2"></i> Penilaian</a>
                    <a class="nav-link disabled text-muted" href="#"><i class="bi bi-patch-check me-2"></i> Sertifikat</a>
                    
                    <hr class="mx-3 opacity-25">
                    <a class="nav-link text-danger fw-bold" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <div class="mb-4">
                    <h3 class="fw-bold text-dark m-0 mb-1">Dashboard Mahasiswa</h3>
                    <p class="text-muted mb-0" style="font-size: 14px;">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong> 👋</p>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card-stat" style="background-color: #4e73df;">
                            <small class="text-uppercase fw-bold opacity-75 d-block mb-1" style="font-size: 11px;">Status Akun</small>
                            <h4 class="fw-bold m-0" style="font-size: 22px;">Calon Peserta</h4>
                            <i class="bi bi-person-badge icon-bg"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-stat" style="background-color: #1cc88a;">
                            <small class="text-uppercase fw-bold opacity-75 d-block mb-1" style="font-size: 11px;">Presensi Hadir</small>
                            <h4 class="fw-bold m-0" style="font-size: 22px;">0 Hari</h4>
                            <i class="bi bi-geo-alt icon-bg"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-stat" style="background-color: #f6c23e;">
                            <small class="text-uppercase fw-bold opacity-75 d-block mb-1" style="font-size: 11px;">Logbook Mengisi</small>
                            <h4 class="fw-bold m-0" style="font-size: 22px;">0 Jurnal</h4>
                            <i class="bi bi-book icon-bg"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-stat" style="background-color: #e74a3b;">
                            <small class="text-uppercase fw-bold opacity-75 d-block mb-1" style="font-size: 11px;">Nilai Akhir</small>
                            <h4 class="fw-bold m-0" style="font-size: 22px;">Belum Rilis</h4>
                            <i class="bi bi-graph-up icon-bg"></i>
                        </div>
                    </div>
                </div>

                <div class="card card-table bg-white p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Status Pendaftaran Terbaru</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary" style="font-size: 13px;">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Bidang / Divisi Dituju</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Status Kelulusan</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 14px;">
                                <tr>
                                    <td>1</td>
                                    <td class="fw-bold text-dark">-</td>
                                    <td><?php echo date('Y-m-d'); ?></td>
                                    <td>
                                        <span class="badge-waiting"><i class="bi bi-hourglass-split me-1"></i>Belum Mengajukan</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
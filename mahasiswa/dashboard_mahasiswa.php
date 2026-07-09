<?php
session_start();

// GATEKEEPER: Kunci halaman jika belum login atau bukan mahasiswa (Role ID = 3)
if (!isset($_SESSION['id_user']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php?pesan=belum_login");
    exit();
}

include "../config/koneksi.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - SIPKL BM JATIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .page-header-bar{
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .page-header-bar h3{
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .page-header-bar p{
            color: #64748b;
            font-size: 0.85rem;
            margin: 0.2rem 0 0;
        }

        .card-stat{
            border: none;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            color: #fff;
            padding: 1.4rem;
        }
        .card-stat:hover{
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.18);
        }
        .card-stat .icon-bg{
            position: absolute;
            right: 0.8rem;
            bottom: 0.6rem;
            font-size: 2.6rem;
            opacity: 0.25;
        }
        .card-stat small{
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            opacity: 0.9;
            display: block;
            margin-bottom: 0.3rem;
        }
        .card-stat h4{
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-status{ background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .stat-presensi{ background: linear-gradient(135deg, #16a34a, #14532d); }
        .stat-logbook{ background: linear-gradient(135deg, #f59e0b, #b45309); }
        .stat-nilai{ background: linear-gradient(135deg, #dc2626, #7f1d1d); }

        .card-table{
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.06);
        }

        table.table-modern-mhsdash{
            border-collapse: separate !important;
            border-spacing: 0;
            width: 100% !important;
        }
        table.table-modern-mhsdash thead th{
            background: #2563eb;
            color: #fff;
            border: none !important;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: center;
        }
        table.table-modern-mhsdash tbody td{
            vertical-align: middle;
            font-size: 0.88rem;
            text-align: center;
            border-color: #eef1f5 !important;
        }
        table.table-modern-mhsdash tbody tr:hover{
            background: #f8fafc;
        }

        .badge-soft{
            padding: 0.4rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.78rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .badge-soft-warning{ background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    </style>
</head>
<body>

    <?php include "../include/navbar_mahasiswa.php"; ?>
    <?php include "../include/sidebar_mahasiswa.php"; ?>

    <div class="content">

        <div class="page-header-bar">
            <div>
                <h3><i class="bi bi-speedometer2 me-2"></i>Dashboard Mahasiswa</h3>
                <p>Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong> 👋</p>
            </div>
        </div>

        <div class="row g-4 mb-4">

            <div class="col-md-3">
                <div class="card-stat stat-status shadow">
                    <small>Status Akun</small>
                    <h4>Calon Peserta</h4>
                    <i class="bi bi-person-badge icon-bg"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-stat stat-presensi shadow">
                    <small>Presensi Hadir</small>
                    <h4>0 Hari</h4>
                    <i class="bi bi-geo-alt icon-bg"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-stat stat-logbook shadow">
                    <small>Logbook Mengisi</small>
                    <h4>0 Jurnal</h4>
                    <i class="bi bi-book icon-bg"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-stat stat-nilai shadow">
                    <small>Nilai Akhir</small>
                    <h4>Belum Rilis</h4>
                    <i class="bi bi-graph-up icon-bg"></i>
                </div>
            </div>

        </div>

        <div class="card card-table">
            <div class="card-body">

                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Status Pendaftaran Terbaru
                </h5>

                <div class="table-responsive">
                    <table class="table table-hover table-modern-mhsdash align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="60">No</th>
                                <th>Bidang / Divisi Dituju</th>
                                <th width="180">Tanggal Pengajuan</th>
                                <th width="200">Status Kelulusan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td class="fw-bold text-dark">-</td>
                                <td><?= date('Y-m-d'); ?></td>
                                <td>
                                    <span class="badge-soft badge-soft-warning">
                                        <i class="bi bi-hourglass-split"></i> Belum Mengajukan
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    <?php include "../include/footer_mahasiswa.php"; ?>
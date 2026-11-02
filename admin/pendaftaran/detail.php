<?php

    include "../../helper/auth_admin.php";
    include "../../config/config.php";
    include "../../config/koneksi.php"; // WAJIB ADA: Untuk menyambungkan ke database

    // 1. Ambil ID dengan aman
    $id = isset($_GET['id']) ? $_GET['id'] : '';

    if (empty($id)) {
    echo "<script>alert('ID tidak ditemukan!'); window.location='index.php';</script>";
    exit;
    }

    // 2. Query Data diperbarui dengan JOIN ke tabel ruangan
    $query = "SELECT
    p.*,
    m.nama,
    m.nim,
    m.no_hp,
    m.email,
    u.nama_instansi,
    ps.nama_jurusan,
    pr.nama_periode,
    uk.unit_kerja AS nama_bidang,
    pb.nama AS nama_pembimbing,
    r.nama_ruangan,
    r.lokasi AS lokasi_ruangan
FROM pendaftaran p
LEFT JOIN pelajar m
    ON p.pelajar_id = m.id
LEFT JOIN instansi u
    ON m.instansi_id = u.id
LEFT JOIN jurusan ps
    ON m.jurusan_id = ps.id
LEFT JOIN periode_pkl pr
    ON p.periode_id = pr.id
LEFT JOIN unit_kerja uk
    ON p.unit_kerja_id = uk.id
LEFT JOIN pembimbing pb
    ON p.pembimbing_id = pb.id
LEFT JOIN ruangan r
    ON m.id_ruangan = r.id_ruangan
WHERE p.id='$id'";

    $data = mysqli_query($conn, $query);

    if (! $data) {
    die("Query Error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($data) == 0) {
    header("Location:index.php");
    exit;
    }

    // 3. Simpan data ke variabel yang UNIK ($detail_pendaftaran) agar tidak tertimpa
    $detail_pendaftaran = mysqli_fetch_assoc($data);

    include "../../include/header.php";
    include "../../include/navbar.php";
    include "../../include/sidebar.php";

?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Detail Pendaftaran PKL</h4>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th width="180">Nama</th>
                            <td><?php echo $detail_pendaftaran['nama']; ?></td>
                        </tr>
                        <tr>
                            <th>NIM/NIS</th>
                            <td><?php echo $detail_pendaftaran['nim']; ?></td>
                        </tr>
                        <tr>
                            <th>Instansi</th>
                            <td><?php echo $detail_pendaftaran['nama_instansi']; ?></td>
                        </tr>
                        <tr>
                            <th>Jurusan</th>
                            <td><?php echo $detail_pendaftaran['nama_jurusan']; ?></td>
                        </tr>
                        <tr>
                            <th>No HP</th>
                            <td><?php echo $detail_pendaftaran['no_hp']; ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo $detail_pendaftaran['email']; ?></td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th width="180">Periode</th>
                            <td><?php echo $detail_pendaftaran['nama_periode']; ?></td>
                        </tr>
                        <tr>
                            <th>Bidang</th>
                            <td><?php echo $detail_pendaftaran['nama_bidang']; ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Daftar</th>
                            <td><?php echo date('d-m-Y', strtotime($detail_pendaftaran['tanggal_daftar'])); ?></td>
                        </tr>
                        <tr>
                            <th>Pembimbing</th>
                            <td><?php echo $detail_pendaftaran['nama_pembimbing'] ? $detail_pendaftaran['nama_pembimbing'] : "-"; ?>
                            </td>
                        </tr>
                        <!-- Menambahkan baris untuk Ruangan -->
                        <tr>
                            <th>Ruangan (Lokasi)</th>
                            <td>
                                <?php
                                    if ($detail_pendaftaran['nama_ruangan']) {
                                        echo $detail_pendaftaran['nama_ruangan'];
                                        if ($detail_pendaftaran['lokasi_ruangan']) {
                                            echo " <small class='text-muted'>(" . $detail_pendaftaran['lokasi_ruangan'] . ")</small>";
                                        }
                                    } else {
                                        echo "-";
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php
                                    if ($detail_pendaftaran['status'] == "menunggu") {
                                        echo "<span class='badge bg-warning text-dark'>Menunggu</span>";
                                    } elseif ($detail_pendaftaran['status'] == "diterima") {
                                        echo "<span class='badge bg-success'>Diterima</span>";
                                    } else {
                                        echo "<span class='badge bg-danger'>Ditolak</span>";
                                    }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>

            <hr>

            <h5>Berkas</h5>
            <table class="table table-bordered">
                <tr>
                    <th width="220">Surat Pengantar</th>
                    <td>
                        <a target="_blank" class="btn btn-primary btn-sm"
                            href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $detail_pendaftaran['surat_pengantar']; ?>">
                            Lihat PDF
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>CV</th>
                    <td>
                        <a target="_blank" class="btn btn-success btn-sm"
                            href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $detail_pendaftaran['cv']; ?>">
                            Lihat PDF
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Transkrip</th>
                    <td>
                        <a target="_blank" class="btn btn-warning btn-sm"
                            href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $detail_pendaftaran['transkrip']; ?>">
                            Lihat PDF
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Proposal</th>
                    <td>
                        <a target="_blank" class="btn btn-danger btn-sm"
                            href="<?php echo BASE_URL ?>assets/upload/pendaftaran/<?php echo $detail_pendaftaran['proposal']; ?>">
                            Lihat PDF
                        </a>
                    </td>
                </tr>
            </table>

            <hr>

            <h5>Catatan Admin</h5>
            <div class="border rounded p-3">
                <?php echo $detail_pendaftaran['catatan_admin'] ? nl2br($detail_pendaftaran['catatan_admin']) : "-"; ?>
            </div>

            <br>

            <a href="index.php" class="btn btn-secondary">
                Kembali
            </a>

        </div>

    </div>

</div>

<?php
include "../../include/footer.php";
?>
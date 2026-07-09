<?php

// =========================
// Ambil data notifikasi khusus mahasiswa yang sedang login
// =========================
// Asumsi: $_SESSION['mahasiswa_id'] menyimpan id baris mahasiswa yang bersangkutan.
// Sesuaikan nama session ini dengan yang benar-benar dipakai di proses_login.php kamu.

$mahasiswaId = isset($_SESSION['mahasiswa_id']) ? $_SESSION['mahasiswa_id'] : 0;

// Update status pendaftaran (diterima / ditolak) miliknya sendiri
$qNotifPendaftaran = mysqli_query($conn, "
    SELECT p.id, p.status, p.tanggal_daftar
    FROM pendaftaran p
    WHERE p.mahasiswa_id = $mahasiswaId
    AND p.status IN ('diterima', 'ditolak')
    ORDER BY p.id DESC
    LIMIT 5
");
$totalNotifPendaftaran = $qNotifPendaftaran ? mysqli_num_rows($qNotifPendaftaran) : 0;

// Logbook miliknya yang sudah diverifikasi (disetujui / revisi)
$qNotifLogbook = mysqli_query($conn, "
    SELECT l.id, l.status, l.tanggal
    FROM logbook l
    WHERE l.mahasiswa_id = $mahasiswaId
    AND l.status IN ('disetujui', 'revisi')
    ORDER BY l.id DESC
    LIMIT 5
");
$totalNotifLogbook = $qNotifLogbook ? mysqli_num_rows($qNotifLogbook) : 0;

$totalNotifikasi = $totalNotifPendaftaran + $totalNotifLogbook;

?>

<div class="navbar-custom">

    <div>
        <button type="button" id="btnToggleSidebar" class="btn-hamburger" aria-label="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div>
        <h5>
            SIPKL BM JATIM
        </h5>
    </div>

    <div class="d-flex align-items-center">

        <!-- Notifikasi -->
        <div class="dropdown me-3">

            <button type="button"
                    class="btn-notif"
                    id="btnNotifikasi"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">

                <i class="bi bi-bell-fill"></i>

                <?php if($totalNotifikasi > 0){ ?>
                <span class="notif-badge"><?= $totalNotifikasi > 9 ? '9+' : $totalNotifikasi; ?></span>
                <?php } ?>

            </button>

            <div class="dropdown-menu dropdown-menu-end notif-dropdown shadow" aria-labelledby="btnNotifikasi">

                <div class="notif-header">
                    <span><i class="bi bi-bell-fill me-2"></i> Notifikasi</span>
                    <?php if($totalNotifikasi > 0){ ?>
                        <span class="notif-count"><?= $totalNotifikasi; ?> baru</span>
                    <?php } ?>
                </div>

                <div class="notif-body">

                    <?php if($totalNotifikasi == 0){ ?>

                        <div class="notif-empty">
                            <i class="bi bi-check-circle-fill"></i>
                            <p>Tidak ada notifikasi baru</p>
                        </div>

                    <?php }else{ ?>

                        <?php while($row = mysqli_fetch_assoc($qNotifPendaftaran)){ ?>
                        <a href="pendaftaran.php" class="notif-item">
                            <div class="notif-icon <?= $row['status'] == 'diterima' ? 'notif-icon-success' : 'notif-icon-danger'; ?>">
                                <i class="bi <?= $row['status'] == 'diterima' ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i>
                            </div>
                            <div class="notif-text">
                                Pendaftaran PKL kamu
                                <strong><?= $row['status'] == 'diterima' ? 'diterima' : 'ditolak'; ?></strong>
                                <div class="notif-time"><?= date('d-m-Y', strtotime($row['tanggal_daftar'])); ?></div>
                            </div>
                        </a>
                        <?php } ?>

                        <?php while($row = mysqli_fetch_assoc($qNotifLogbook)){ ?>
                        <a href="logbook.php" class="notif-item">
                            <div class="notif-icon <?= $row['status'] == 'disetujui' ? 'notif-icon-success' : 'notif-icon-warning'; ?>">
                                <i class="bi <?= $row['status'] == 'disetujui' ? 'bi-check-circle-fill' : 'bi-arrow-repeat'; ?>"></i>
                            </div>
                            <div class="notif-text">
                                Logbook kamu
                                <strong><?= $row['status'] == 'disetujui' ? 'disetujui' : 'perlu direvisi'; ?></strong>
                                <div class="notif-time"><?= date('d-m-Y', strtotime($row['tanggal'])); ?></div>
                            </div>
                        </a>
                        <?php } ?>

                    <?php } ?>

                </div>

            </div>

        </div>

        <div class="dropdown">

            <button type="button" class="btn-profile" id="btnProfile" data-bs-toggle="dropdown" aria-expanded="false">
                <img
                src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=2563eb&color=fff"
                class="rounded-circle"
                width="40">

                <b class="ms-2"><?= htmlspecialchars($_SESSION['nama']); ?></b>

                <i class="bi bi-chevron-down ms-2 profile-chevron"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-end profile-dropdown shadow" aria-labelledby="btnProfile">

                <div class="profile-header">
                    <img
                    src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=ffffff&color=2563eb"
                    class="rounded-circle"
                    width="48">
                    <div>
                        <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong>
                        <div class="profile-role">Mahasiswa</div>
                    </div>
                </div>

                <a href="profil.php" class="profile-item">
                    <i class="bi bi-person-fill"></i> Profil Saya
                </a>

                <a href="ganti_password.php" class="profile-item">
                    <i class="bi bi-key-fill"></i> Ganti Password
                </a>

                <div class="profile-divider"></div>

                <a href="#" id="btnLogout" class="profile-item profile-item-danger">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>

            </div>

        </div>
    </div>

</div>

<style>
    .btn-hamburger{
        background: none;
        border: none;
        color: #1e293b;
        font-size: 1.3rem;
        padding: 0.4rem 0.6rem;
        border-radius: 8px;
        cursor: pointer;
        transition: background .2s ease;
    }
    .btn-hamburger:hover{
        background: #f1f5f9;
    }

    .btn-notif{
        position: relative;
        background: none;
        border: none;
        font-size: 1.25rem;
        color: #1e293b;
        padding: 0.4rem 0.6rem;
        border-radius: 8px;
        cursor: pointer;
        transition: background .2s ease;
    }
    .btn-notif:hover{
        background: #f1f5f9;
    }
    .notif-badge{
        position: absolute;
        top: 2px;
        right: 2px;
        background: #dc2626;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 3px;
        border: 2px solid #fff;
    }

    .notif-dropdown{
        width: 340px;
        padding: 0;
        border: none;
        border-radius: 14px;
        overflow: hidden;
        margin-top: 10px;
    }
    .notif-header{
        background: #2563eb;
        color: #fff;
        padding: 0.9rem 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .notif-count{
        background: rgba(255,255,255,.2);
        font-size: 0.72rem;
        font-weight: 500;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
    }

    .notif-body{
        max-height: 340px;
        overflow-y: auto;
    }

    .notif-item{
        display: flex;
        gap: 0.7rem;
        padding: 0.8rem 1.1rem;
        text-decoration: none;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s ease;
    }
    .notif-item:hover{
        background: #f8fafc;
    }
    .notif-item:last-child{
        border-bottom: none;
    }

    .notif-icon{
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }
    .notif-icon-primary{ background: #eff6ff; color: #1d4ed8; }
    .notif-icon-warning{ background: #fef3c7; color: #92400e; }
    .notif-icon-success{ background: #dcfce7; color: #166534; }
    .notif-icon-danger{ background: #fee2e2; color: #991b1b; }

    .notif-text{
        font-size: 0.85rem;
        line-height: 1.4;
    }
    .notif-time{
        color: #94a3b8;
        font-size: 0.75rem;
        margin-top: 2px;
    }

    .notif-empty{
        text-align: center;
        padding: 2.2rem 1rem;
        color: #94a3b8;
    }
    .notif-empty i{
        font-size: 1.8rem;
        color: #16a34a;
        display: block;
        margin-bottom: 0.5rem;
    }
    .notif-empty p{
        margin: 0;
        font-size: 0.85rem;
    }

    .btn-profile{
        background: none;
        border: none;
        display: flex;
        align-items: center;
        padding: 0.3rem 0.6rem;
        border-radius: 10px;
        cursor: pointer;
        transition: background .2s ease;
    }
    .btn-profile:hover{
        background: #f1f5f9;
    }
    .profile-chevron{
        font-size: 0.7rem;
        color: #94a3b8;
        transition: transform .2s ease;
    }
    .btn-profile[aria-expanded="true"] .profile-chevron{
        transform: rotate(180deg);
    }

    .profile-dropdown{
        width: 260px;
        padding: 0;
        border: none;
        border-radius: 14px;
        overflow: hidden;
        margin-top: 10px;
    }

    .profile-header{
        background: #2563eb;
        color: #fff;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    .profile-header strong{
        display: block;
        font-size: 0.95rem;
    }
    .profile-role{
        font-size: 0.75rem;
        opacity: 0.85;
        text-transform: capitalize;
    }

    .profile-item{
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.75rem 1.1rem;
        color: #334155;
        text-decoration: none;
        font-size: 0.88rem;
        transition: background .15s ease;
    }
    .profile-item:hover{
        background: #f8fafc;
        color: #1e293b;
    }
    .profile-item i{
        width: 18px;
        color: #64748b;
    }

    .profile-item-danger{
        color: #dc2626;
    }
    .profile-item-danger i{
        color: #dc2626;
    }
    .profile-item-danger:hover{
        background: #fef2f2;
        color: #b91c1c;
    }

    .profile-divider{
        border-top: 1px solid #f1f5f9;
        margin: 0.2rem 0;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){

    var body = document.body;
    var btnToggle = document.getElementById('btnToggleSidebar');
    var storageKey = 'sipklbm_sidebar_hidden_mhs';

    // Terapkan state tersembunyi/tampil sesuai yang tersimpan sebelumnya
    var isHidden = sessionStorage.getItem(storageKey) === '1';
    if(isHidden){
        body.classList.add('sidebar-hidden');
    }

    btnToggle.addEventListener('click', function(){
        body.classList.toggle('sidebar-hidden');

        var nowHidden = body.classList.contains('sidebar-hidden');
        sessionStorage.setItem(storageKey, nowHidden ? '1' : '0');
    });

    // Konfirmasi sebelum logout
    var btnLogout = document.getElementById('btnLogout');
    if(btnLogout){
        btnLogout.addEventListener('click', function(e){
            e.preventDefault();

            Swal.fire({
                title: 'Keluar dari sistem?',
                text: 'Kamu akan logout dari SIPKL BM.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626'
            }).then((result) => {
                if(result.isConfirmed){
                    window.location = '../logout.php';
                }
            });
        });
    }

});
</script>
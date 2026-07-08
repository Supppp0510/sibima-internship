<?php

include "../helper/auth_admin.php";
include "../config/config.php";

// Ambil data admin yang sedang login
$idUser = $_SESSION['id'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$idUser'");
$user = mysqli_fetch_assoc($query);

// =========================
// Proses update profil
// =========================
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])){

    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $update = mysqli_query($conn, "
        UPDATE users
        SET name = '$nama', email = '$email'
        WHERE id = '$idUser'
    ");

    if($update){
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        header("Location: profil.php?success=1");
        exit;
    }else{
        $errorMsg = "Gagal memperbarui profil: " . mysqli_error($conn);
    }

}

include "../include/header.php";
include "../include/navbar.php";
include "../include/sidebar.php";

?>

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

    .profile-card{
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    .profile-banner{
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        padding: 2rem 1.5rem;
        text-align: center;
        color: #fff;
    }
    .profile-banner img{
        border: 4px solid rgba(255,255,255,.3);
        margin-bottom: 0.8rem;
    }
    .profile-banner h5{
        margin: 0;
        font-weight: 700;
    }
    .profile-banner span{
        font-size: 0.82rem;
        opacity: 0.85;
        text-transform: capitalize;
    }

    .form-label-custom{
        font-weight: 600;
        color: #334155;
        font-size: 0.88rem;
        margin-bottom: 0.4rem;
    }

    .form-control-custom{
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        padding: 0.6rem 0.9rem;
        font-size: 0.9rem;
    }
    .form-control-custom:focus{
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
    }

    .btn-simpan{
        border-radius: 10px;
        font-weight: 600;
        padding: 0.6rem 1.4rem;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border: none;
        color: #fff;
    }
    .btn-simpan:hover{
        background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        color: #fff;
    }
</style>

<?php if(isset($_GET['success'])){ ?>
<script>
document.addEventListener("DOMContentLoaded", function(){
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Profil berhasil diperbarui.',
        timer: 2000,
        showConfirmButton: false
    });
});
</script>
<?php } ?>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-person-circle me-2"></i>Profil Saya</h3>
            <p>Kelola informasi akun kamu di sini.</p>
        </div>
    </div>

    <?php if(isset($errorMsg)){ ?>
    <div class="alert alert-danger"><?= $errorMsg; ?></div>
    <?php } ?>

    <div class="row">

        <div class="col-md-4 mb-3">
            <div class="card profile-card">
                <div class="profile-banner">
                    <img
                    src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=ffffff&color=2563eb&size=90"
                    class="rounded-circle"
                    width="90">
                    <h5><?= $user['name']; ?></h5>
                    <span><?= isset($_SESSION['role']) ? $_SESSION['role'] : 'Admin'; ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-3">
            <div class="card profile-card">
                <div class="card-body p-4">

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label-custom">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control form-control-custom"
                                   value="<?= $user['name']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Email</label>
                            <input type="email" name="email" class="form-control form-control-custom"
                                   value="<?= $user['email']; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-custom">Role</label>
                            <input type="text" class="form-control form-control-custom"
                                   value="<?= isset($_SESSION['role']) ? $_SESSION['role'] : '-'; ?>" disabled>
                        </div>

                        <button type="submit" name="update_profil" class="btn btn-simpan">
                            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                        </button>

                    </form>

                </div>
            </div>
        </div>

    </div>

</div>

<?php

include "../include/footer.php";

?>
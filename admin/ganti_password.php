<?php

include "../helper/auth_admin.php";
include "../config/config.php";

$idUser = $_SESSION['id'];

$errorMsg = "";

// =========================
// Proses ganti password
// =========================
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_password'])){

    $passwordLama = $_POST['password_lama'];
    $passwordBaru = $_POST['password_baru'];
    $konfirmasiPassword = $_POST['konfirmasi_password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$idUser'");
    $user = mysqli_fetch_assoc($query);

    // Cocokkan password lama.
    // Catatan: sesuaikan dengan cara password disimpan di database kamu.
    // Kalau password di database sudah di-hash (disarankan), gunakan password_verify().
    // Kalau password masih disimpan plaintext, bandingkan langsung seperti di bawah.

    $passwordCocok = false;

    if(function_exists('password_verify') && strlen($user['password']) >= 60){
        // Kemungkinan besar password sudah di-hash (bcrypt)
        $passwordCocok = password_verify($passwordLama, $user['password']);
    }else{
        // Fallback: password masih plaintext
        $passwordCocok = ($passwordLama === $user['password']);
    }

    if(!$passwordCocok){

        $errorMsg = "Password lama tidak sesuai.";

    }elseif($passwordBaru !== $konfirmasiPassword){

        $errorMsg = "Konfirmasi password tidak cocok.";

    }elseif(strlen($passwordBaru) < 6){

        $errorMsg = "Password baru minimal 6 karakter.";

    }else{

        // Simpan password baru dalam bentuk hash (disarankan)
        $passwordHash = password_hash($passwordBaru, PASSWORD_BCRYPT);

        $update = mysqli_query($conn, "
            UPDATE users
            SET password = '$passwordHash'
            WHERE id = '$idUser'
        ");

        if($update){
            header("Location: ganti_password.php?success=1");
            exit;
        }else{
            $errorMsg = "Gagal memperbarui password: " . mysqli_error($conn);
        }

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

    .form-card{
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
        max-width: 520px;
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

    .input-group-icon{
        position: relative;
    }
    .input-group-icon .toggle-eye{
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        cursor: pointer;
        background: none;
        border: none;
    }
    .input-group-icon .toggle-eye:hover{
        color: #2563eb;
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

    .password-hint{
        font-size: 0.78rem;
        color: #94a3b8;
        margin-top: 0.3rem;
    }
</style>

<?php if(isset($_GET['success'])){ ?>
<script>
document.addEventListener("DOMContentLoaded", function(){
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Password berhasil diperbarui.',
        timer: 2000,
        showConfirmButton: false
    });
});
</script>
<?php } ?>

<div class="container-fluid">

    <div class="page-header-bar">
        <div>
            <h3><i class="bi bi-key-fill me-2"></i>Ganti Password</h3>
            <p>Perbarui kata sandi akun kamu secara berkala demi keamanan.</p>
        </div>
    </div>

    <?php if(!empty($errorMsg)){ ?>
    <div class="alert alert-danger"><?= $errorMsg; ?></div>
    <?php } ?>

    <div class="card form-card">
        <div class="card-body p-4">

            <form method="POST" id="formGantiPassword">

                <div class="mb-3">
                    <label class="form-label-custom">Password Lama</label>
                    <div class="input-group-icon">
                        <input type="password" name="password_lama" id="passwordLama"
                               class="form-control form-control-custom" required>
                        <button type="button" class="toggle-eye" data-target="passwordLama">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Password Baru</label>
                    <div class="input-group-icon">
                        <input type="password" name="password_baru" id="passwordBaru"
                               class="form-control form-control-custom" required minlength="6">
                        <button type="button" class="toggle-eye" data-target="passwordBaru">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                    <div class="password-hint">Minimal 6 karakter.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label-custom">Konfirmasi Password Baru</label>
                    <div class="input-group-icon">
                        <input type="password" name="konfirmasi_password" id="konfirmasiPassword"
                               class="form-control form-control-custom" required minlength="6">
                        <button type="button" class="toggle-eye" data-target="konfirmasiPassword">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="ganti_password" class="btn btn-simpan">
                    <i class="bi bi-shield-check me-1"></i> Simpan Password Baru
                </button>

            </form>

        </div>
    </div>

</div>

<script>
document.querySelectorAll('.toggle-eye').forEach(function(btn){
    btn.addEventListener('click', function(){
        var targetId = btn.getAttribute('data-target');
        var input = document.getElementById(targetId);
        var icon = btn.querySelector('i');

        var isPassword = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPassword ? 'text' : 'password');
        icon.classList.toggle('bi-eye-fill');
        icon.classList.toggle('bi-eye-slash-fill');
    });
});
</script>

<?php

include "../include/footer.php";

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login SIPKL BM</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<!-- Library SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root{
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --accent: #06b6d4;
    }

    * { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }

    /* --- KODE ORISINAL ANDA (TAMPILAN LAPTOP TIDAK BERUBAH SAMA SEKALI) --- */
    body {
        min-height: 100vh;
        margin: 0;
        display: flex;
        align-items: center; 
        justify-content: center;
        overflow: hidden;
        position: relative;
        padding-left: 1rem;
        padding-right: 1rem;

        background-image: url('assets/img/bg.jpg'); 
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    body::before {
        content: '';
        position: absolute;
        inset: 0; 
        background: linear-gradient(160deg, rgba(15, 23, 42, 0.92), rgba(15, 42, 74, 0.9), rgba(8, 30, 54, 0.94));
        background-size: 400% 400%;
        animation: gradientShift 12s ease infinite;
        z-index: 0; 
    }

    @keyframes gradientShift{
        0%{background-position:0% 50%;}
        50%{background-position:100% 50%;}
        100%{background-position:0% 50%;}
    }

    .blob{
        position: absolute;
        border-radius: 50%;
        filter: blur(60px);
        opacity: 0.25;
        z-index: 1;
        animation: float 8s ease-in-out infinite;
    }
    .blob1{ width: 300px; height: 300px; background: #1e40af; top: -80px; left: -80px; }
    .blob2{ width: 250px; height: 250px; background: #0e7490; bottom: -60px; right: -60px; animation-delay: 2s; }
    .blob3{ width: 200px; height: 200px; background: #164e63; top: 60%; left: 70%; animation-delay: 4s; }

    @keyframes float{
        0%,100%{ transform: translateY(0) translateX(0); }
        50%{ transform: translateY(-25px) translateX(15px); }
    }

    .login-card{
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 400px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.4);
        box-shadow: 0 20px 45px rgba(0,0,0,0.25);
        padding: 2.5rem 2rem;
        animation: fadeUp 0.7s ease;
        
        transform-style: preserve-3d;
        will-change: transform;
        transition: transform 0.15s ease-out, box-shadow 0.15s ease-out;
    }

    .login-card .tilt-glare{
        position: absolute;
        inset: 0;
        border-radius: 20px;
        pointer-events: none;
        background: radial-gradient(circle at var(--glare-x, 50%) var(--glare-y, 50%), rgba(255,255,255,0.35), transparent 60%);
        opacity: 0;
        transition: opacity 0.2s ease;
        z-index: 3;
    }
    .login-card.tilting .tilt-glare{
        opacity: 1;
    }

    @keyframes fadeUp{
        from{ opacity: 0; margin-top: 40px; } 
        to{ opacity: 1; margin-top: 0; }
    }

    .logo-circle{
        width: 90px;
        height: 90px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(37,99,235,0.4);
        overflow: hidden;
        padding: 8px;
    }

    .logo-circle img{
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .card-title-custom{
        text-align: center;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.2rem;
        font-size: 1.4rem;
    }

    .subtitle{
        text-align: center;
        color: #64748b;
        font-size: 0.85rem;
        margin-bottom: 1.8rem;
    }

    .input-group-custom{
        position: relative;
        margin-bottom: 1.3rem;
    }

    .input-group-custom .form-control{
        padding: 0.75rem 1rem 0.75rem 2.7rem;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        transition: all 0.25s ease;
        font-size: 0.95rem;
    }

    .input-group-custom .form-control:focus{
        border-color: var(--primary);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
    }

    .input-group-custom i.icon-left{
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        transition: color 0.25s ease;
    }

    .input-group-custom .form-control:focus ~ i.icon-left{
        color: var(--primary);
    }

    .toggle-password{
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        cursor: pointer;
        border: none;
        background: none;
        padding: 0;
    }
    .toggle-password:hover{ color: var(--primary); }

    .btn-login{
        border: none;
        border-radius: 12px;
        padding: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-login:hover{
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(37,99,235,0.35);
    }

    .btn-login:active{ transform: translateY(0); }

    .form-check-custom{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.3rem;
        font-size: 0.85rem;
    }

    .form-check-custom a{
        color: var(--primary);
        text-decoration: none;
    }
    .form-check-custom a:hover{ text-decoration: underline; }

    .footer-text{
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.78rem;
        color: #94a3b8;
    }

    /* --- TAMBAHAN KHUSUS UNTUK HP (Layar di bawah 768px) --- */
    @media screen and (max-width: 768px) {
        html, body {
            background-color: #081e36; /* Warna gelap mencegah putih tembus di bawah */
        }
        
        body {
            overflow-y: auto; 
            align-items: flex-start; 
            padding-top: 2.5rem; 
            padding-bottom: 2.5rem; 
            background-attachment: fixed; /* Mengunci gambar background agar tidak tergulung */
        }
        
        body::before {
            position: fixed; /* Mengunci lapisan gradien transparan */
        }

        .blob {
            position: fixed; /* KUNCI: Mencegah elemen blur "tumpah" ke bawah & menciptakan area scroll kosong */
        }
        
        .login-card {
            margin: auto; 
        }
    }
</style>
</head>
<body>

<div class="blob blob1"></div>
<div class="blob blob2"></div>
<div class="blob blob3"></div>

<div class="login-card">

    <div class="tilt-glare"></div>

    <div class="logo-circle">
        <img src="assets/img/sibima.png" alt="Logo SIBIMA">
    </div>

    <h4 class="card-title-custom">SIBIMA INTERNSHIP</h4>
    <p class="subtitle">Sistem Informasi Bina Marga Internship</p>

    <form action="proses_login.php" method="POST" id="loginForm">

        <div class="input-group-custom">
            <i class="bi bi-envelope-fill icon-left"></i>
            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="Email"
                required
                autofocus>
        </div>

        <div class="input-group-custom">
            <i class="bi bi-lock-fill icon-left"></i>
            <input
                type="password"
                name="password"
                id="password"
                class="form-control"
                placeholder="Password"
                required>
            <button type="button" class="toggle-password" id="togglePassword">
                <i class="bi bi-eye-fill" id="toggleIcon"></i>
            </button>
        </div>

        <div class="form-check-custom">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                <label class="form-check-label" for="remember" style="color:#64748b;">Ingat Password</label>
            </div>
            <a href="#">Lupa password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-login w-100" id="btnLogin">
            <span id="btnText"><i class="bi bi-box-arrow-in-right me-1"></i> Login</span>
        </button>

        <div class="mt-4 text-center">
            <span style="color: #2a3a5e; font-size: 0.9rem;">Belum punya akun? </span>
            <a href="register.php" class="text-decoration-none fw-bold" style="color: #2a5298;">Daftar</a>
        </div>

    </form>

    <p class="footer-text">&copy; <?php echo date('Y'); ?> Dinas PU Bina Marga — Divisi IT</p>

</div>

<script>
    // 1. Show/Hide Password
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    togglePassword.addEventListener('click', function () {
        const isPassword = password.getAttribute('type') === 'password';
        password.setAttribute('type', isPassword ? 'text' : 'password');
        toggleIcon.classList.toggle('bi-eye-fill');
        toggleIcon.classList.toggle('bi-eye-slash-fill');
    });

    // 2. Loading State Submit
    const form = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const btnText = document.getElementById('btnText');

    form.addEventListener('submit', function () {
        btnLogin.disabled = true;
        btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
    });

    // 3. Efek Tilt 3D Custom
    const tiltCard = document.querySelector('.login-card');

    tiltCard.addEventListener('mousemove', function(e){
        const rect = tiltCard.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const maxTilt = 4;

        const rotateY = ((x - centerX) / centerX) * maxTilt;
        const rotateX = -((y - centerY) / centerY) * maxTilt;

        tiltCard.style.transform = 
            `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.01, 1.01, 1.01)`;

        const glareX = (x / rect.width) * 100;
        const glareY = (y / rect.height) * 100;
        tiltCard.style.setProperty('--glare-x', glareX + '%');
        tiltCard.style.setProperty('--glare-y', glareY + '%');

        tiltCard.classList.add('tilting');
    });

    tiltCard.addEventListener('mouseleave', function(){
        tiltCard.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
        tiltCard.classList.remove('tilting');
    });
</script>

<!-- 4. Trigger Notifikasi SweetAlert jika redirect dari proses login gagal -->
<?php if (isset($_GET['error'])): ?>
<script>
    Swal.fire({
        title: 'Login Gagal!',
        text: 'Email atau password yang Anda masukkan salah.',
        icon: 'error',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Coba Lagi'
    });
</script>
<?php endif; ?>

</body>
</html>
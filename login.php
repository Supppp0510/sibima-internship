<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login SIPKL BM</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
    :root{
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --accent: #06b6d4;
    }

    * { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }

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

        /* 1. Tambahkan foto sebagai background dasar di sini */
        /* Pastikan arah path '../assets/img/...' sesuai dengan posisi file php kamu */
        background-image: url('assets/img/bg.jpg'); 
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    /* 2. Pindahkan efek gradient ke lapisan ::before agar jadi overlay (kaca film transparan) */
    body::before {
        content: '';
        position: absolute;
        inset: 0; /* Membuat overlay menutupi seluruh layar */
        
        /* Gunakan warna RGBA (A = Alpha/Transparansi). 
           Angka 0.85 di belakang artinya opacity 85%. Ubah jadi 0.7 kalau mau foto lebih kelihatan */
        background: linear-gradient(-45deg, rgba(30, 58, 138, 0.85), rgba(37, 99, 235, 0.85), rgba(8, 145, 178, 0.85), rgba(15, 23, 42, 0.9));
        background-size: 400% 400%;
        animation: gradientShift 12s ease infinite;
        z-index: 0; /* Taruh di atas foto dasar */
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
        opacity: 0.35;
        z-index: 1;
        animation: float 8s ease-in-out infinite;
    }
    .blob1{ width: 300px; height: 300px; background: #06b6d4; top: -80px; left: -80px; }
    .blob2{ width: 250px; height: 250px; background: #3b82f6; bottom: -60px; right: -60px; animation-delay: 2s; }
    .blob3{ width: 200px; height: 200px; background: #22d3ee; top: 60%; left: 70%; animation-delay: 4s; }

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
        
        /* Properti tambahan untuk efek 3D Tilt */
        transform-style: preserve-3d;
        will-change: transform;
        transition: transform 0.15s ease-out, box-shadow 0.15s ease-out;
    }

    /* Efek pantulan cahaya (Glare) */
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
        from{ opacity: 0; margin-top: 40px; } /* Gunakan margin-top untuk hindari jumping */
        to{ opacity: 1; margin-top: 0; }
    }

    .logo-circle{
        width: 70px;
        height: 70px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(37,99,235,0.4);
    }

    .logo-circle i{ font-size: 1.8rem; color: #fff; }

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

    .alert-custom{
        border-radius: 10px;
        font-size: 0.85rem;
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
        <i class="bi bi-cone-striped"></i>
    </div>

    <h4 class="card-title-custom">SIPKL BM</h4>
    <p class="subtitle">Sistem Informasi Pekerjaan &amp; Kerusakan Jalan — Bina Marga</p>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-custom py-2 mb-3" role="alert">
        <i class="bi bi-exclamation-circle me-1"></i> Email atau password salah.
    </div>
    <?php endif; ?>

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
                <label class="form-check-label" for="remember" style="color:#64748b;">Ingat saya</label>
            </div>
            <a href="#">Lupa password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-login w-100" id="btnLogin">
            <span id="btnText"><i class="bi bi-box-arrow-in-right me-1"></i> Login</span>
        </button>

        <div class="mt-4 text-center">
            <span style="color: #2a3a5e; font-size: 0.9rem;">Belum punya akun? </span>
            <a href="register.php" class="text-decoration-none fw-bold" style="color: #2a5298;">Daftar di sini</a>
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

        const maxTilt = 4; // derajat maksimal kemiringan

        const rotateY = ((x - centerX) / centerX) * maxTilt;
        const rotateX = -((y - centerY) / centerY) * maxTilt;

        tiltCard.style.transform = 
            `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.01, 1.01, 1.01)`;

        // Posisi glare ngikutin cursor
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

</body>
</html>
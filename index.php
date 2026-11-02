<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SIBIMA Internship — Dinas PU Bina Marga</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

<style>
:root{
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --accent: #06b6d4;
    --marka: #fbbf24;

    --navy-1: #0f172a;
    --navy-2: #0f2a4a;
    --navy-3: #081e36;

    --ink: #1e293b;
    --muted: #64748b;
}

*{
    box-sizing: border-box;
}

html{
    scroll-behavior: smooth;
}

body{
    margin: 0;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: var(--ink);
    background: #f8fafc;
    overflow-x: hidden;
}

h1,
h2,
h3,
.display-font{
    font-family: 'Space Grotesk', 'Inter', sans-serif;
    letter-spacing: -0.02em;
}

.mono{
    font-family: 'JetBrains Mono', monospace;
    letter-spacing: 0.04em;
}

a{
    text-decoration: none;
}


/* =====================================================
   NAVBAR
===================================================== */

.navbar-sibima{
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
    padding: 1rem 0;
    transition:
        background 0.3s ease,
        padding 0.3s ease,
        box-shadow 0.3s ease;
}

.navbar-sibima.scrolled{
    background: rgba(15, 23, 42, 0.88);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    padding: 0.6rem 0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.navbar-sibima .container{
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.nav-brand{
    display: flex;
    align-items: center;
    gap: 0.6rem;
    color: #fff;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 700;
    font-size: 1.1rem;
}

.nav-brand img{
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #fff;
    padding: 3px;
}

.nav-links{
    display: flex;
    align-items: center;
    gap: 1.8rem;
}

.nav-links a{
    color: rgba(255,255,255,0.8);
    font-size: 0.92rem;
    font-weight: 500;
    transition: color 0.2s ease;
}

.nav-links a:hover{
    color: #fff;
}

.nav-cta{
    display: flex;
    gap: 0.7rem;
    align-items: center;
}

.btn-nav-outline{
    border: 1.5px solid rgba(255,255,255,0.35);
    color: #fff;
    padding: 0.45rem 1.1rem;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-nav-outline:hover{
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.btn-nav-solid{
    background: linear-gradient(
        135deg,
        var(--primary),
        var(--primary-dark)
    );
    color: #fff;
    padding: 0.45rem 1.2rem;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-nav-solid:hover{
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(37,99,235,0.4);
}

.nav-toggle{
    display: none;
    background: none;
    border: none;
    color: #fff;
    font-size: 1.5rem;
}


/* =====================================================
   HERO
===================================================== */

.hero{
    position: relative;
    min-height: 100vh;

    display: flex;
    align-items: center;

    padding: 7rem 0 5rem;

    background-image: url('assets/img/bg.jpg');
    background-size: cover;
    background-position: center;

    overflow: hidden;
}

.hero::before{
    content: '';
    position: absolute;
    inset: 0;

    background:
        linear-gradient(
            160deg,
            rgba(15,23,42,0.94),
            rgba(15,42,74,0.92),
            rgba(8,30,54,0.95)
        );

    z-index: 0;
}

.blob{
    position: absolute;
    border-radius: 50%;
    filter: blur(70px);
    opacity: 0.28;
    z-index: 1;

    animation: float 9s ease-in-out infinite;
}

.blob1{
    width: 340px;
    height: 340px;
    background: #1e40af;
    top: -100px;
    left: -100px;
}

.blob2{
    width: 300px;
    height: 300px;
    background: #0e7490;
    bottom: -80px;
    right: -60px;
    animation-delay: 2s;
}

.blob3{
    width: 220px;
    height: 220px;
    background: var(--marka);
    opacity: 0.12;
    top: 40%;
    right: 15%;
    animation-delay: 4s;
}

@keyframes float{
    0%,100%{
        transform: translate(0,0);
    }

    50%{
        transform: translate(15px,-25px);
    }
}

.hero-content{
    position: relative;
    z-index: 2;
}

.eyebrow{
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;

    color: var(--marka);

    font-family: 'JetBrains Mono', monospace;
    font-size: 0.78rem;
    font-weight: 500;

    letter-spacing: 0.12em;
    text-transform: uppercase;

    margin-bottom: 1.2rem;
}

.eyebrow::before{
    content: '';

    width: 22px;
    height: 2px;

    background: var(--marka);

    display: inline-block;
}

.hero h1{
    color: #fff;

    font-size: clamp(2.1rem, 4.5vw, 3.4rem);
    font-weight: 700;

    line-height: 1.15;

    margin-bottom: 1.3rem;

    max-width: 720px;
}

.hero h1 span{
    color: #7dd3fc;
}

.hero p.lead-text{
    color: rgba(255,255,255,0.78);

    font-size: 1.08rem;

    max-width: 600px;

    margin-bottom: 2.2rem;

    line-height: 1.7;
}

.hero-ctas{
    display: flex;

    gap: 1rem;

    flex-wrap: wrap;

    margin-bottom: 3rem;
}

.btn-hero-primary{
    background:
        linear-gradient(
            135deg,
            var(--primary),
            var(--primary-dark)
        );

    color: #fff;

    padding: 0.85rem 1.8rem;

    border-radius: 12px;

    font-weight: 600;
    font-size: 0.98rem;

    transition: all 0.25s ease;

    display: inline-flex;
    align-items: center;

    gap: 0.5rem;
}

.btn-hero-primary:hover{
    color: #fff;

    transform: translateY(-2px);

    box-shadow:
        0 12px 25px rgba(37,99,235,0.45);
}

.btn-hero-secondary{
    border: 1.5px solid rgba(255,255,255,0.35);

    color: #fff;

    padding: 0.85rem 1.8rem;

    border-radius: 12px;

    font-weight: 600;
    font-size: 0.98rem;

    transition: all 0.25s ease;

    display: inline-flex;
    align-items: center;

    gap: 0.5rem;
}

.btn-hero-secondary:hover{
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.hero-stats{
    display: flex;

    gap: 2.8rem;

    flex-wrap: wrap;
}

.hero-stat .num{
    font-family: 'Space Grotesk', sans-serif;

    font-size: 1.8rem;

    font-weight: 700;

    color: #fff;
}

.hero-stat .label{
    font-family: 'JetBrains Mono', monospace;

    font-size: 0.72rem;

    color: rgba(255,255,255,0.6);

    text-transform: uppercase;

    letter-spacing: 0.08em;
}


/* =====================================================
   MARKA DIVIDER
===================================================== */

.marka-divider{
    position: relative;

    height: 46px;

    background: #1e293b;

    overflow: hidden;

    display: flex;
    align-items: center;
}

.marka-track{
    position: absolute;

    top: 50%;
    left: 0;

    width: 200%;
    height: 6px;

    transform: translateY(-50%);

    background-image:
        repeating-linear-gradient(
            90deg,
            var(--marka) 0px,
            var(--marka) 44px,
            transparent 44px,
            transparent 84px
        );

    animation: markaMove 3.2s linear infinite;
}

@keyframes markaMove{
    from{
        transform:
            translateY(-50%)
            translateX(0);
    }

    to{
        transform:
            translateY(-50%)
            translateX(-84px);
    }
}


/* =====================================================
   GENERAL SECTION
===================================================== */

.section{
    padding: 5.5rem 0;
}

.section-bg-light{
    background: #f8fafc;
}

.section-bg-navy{
    background: var(--navy-1);
}

.section-eyebrow{
    font-family: 'JetBrains Mono', monospace;

    font-size: 0.75rem;

    font-weight: 500;

    letter-spacing: 0.1em;

    text-transform: uppercase;

    color: var(--primary);

    margin-bottom: 0.8rem;

    display: block;
}

.section-title{
    font-size: clamp(1.6rem, 3vw, 2.2rem);

    font-weight: 700;

    color: var(--ink);

    max-width: 620px;

    margin-bottom: 1rem;
}

.section-desc{
    color: var(--muted);

    max-width: 620px;

    font-size: 1rem;

    line-height: 1.7;
}


/* =====================================================
   FITUR
===================================================== */

.feature-card{
    background: #fff;

    border-radius: 16px;

    padding: 2rem 1.7rem;

    border: 1px solid #eef2f7;

    height: 100%;

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;

    opacity: 0;

    transform: translateY(24px);
}

.feature-card.in-view{
    opacity: 1;

    transform: translateY(0);
}

.feature-card:hover{
    transform: translateY(-6px);

    box-shadow:
        0 18px 35px rgba(15,23,42,0.08);
}

.feature-icon{
    width: 52px;
    height: 52px;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,0.12),
            rgba(6,182,212,0.12)
        );

    display: flex;

    align-items: center;
    justify-content: center;

    color: var(--primary);

    font-size: 1.4rem;

    margin-bottom: 1.1rem;
}

.feature-card h3{
    font-size: 1.08rem;

    font-weight: 600;

    margin-bottom: 0.5rem;
}

.feature-card p{
    color: var(--muted);

    font-size: 0.92rem;

    line-height: 1.65;

    margin: 0;
}


/* =====================================================
   ALUR MAGANG
===================================================== */

.step-row{
    display: flex;

    gap: 1.5rem;

    align-items: flex-start;

    padding: 1.6rem 0;

    border-bottom:
        1px solid rgba(255,255,255,0.08);

    opacity: 0;

    transform: translateX(-20px);

    transition:
        opacity 0.5s ease,
        transform 0.5s ease;
}

.step-row.in-view{
    opacity: 1;

    transform: translateX(0);
}

.step-row:last-child{
    border-bottom: none;
}

.step-num{
    font-family: 'JetBrains Mono', monospace;

    font-size: 0.95rem;

    color: var(--marka);

    font-weight: 500;

    min-width: 40px;
}

.step-body h3{
    color: #fff;

    font-size: 1.05rem;

    font-weight: 600;

    margin-bottom: 0.35rem;
}

.step-body p{
    color: rgba(255,255,255,0.6);

    font-size: 0.92rem;

    margin: 0;

    line-height: 1.6;
}


/* =====================================================
   TENTANG
===================================================== */

.about-card{
    background: #fff;

    border-radius: 20px;

    padding: 2.5rem;

    box-shadow:
        0 20px 45px rgba(15,23,42,0.06);

    border: 1px solid #eef2f7;
}

.about-photo{
    border-radius: 16px;

    overflow: hidden;

    position: relative;

    min-height: 320px;

    background-image: url('assets/img/bg.jpg');

    background-size: cover;

    background-position: center;
}

.about-photo::after{
    content: '';

    position: absolute;

    inset: 0;

    background:
        linear-gradient(
            160deg,
            rgba(15,23,42,0.55),
            rgba(15,42,74,0.35)
        );
}

.kontak-section{
    position: relative;
}

.kontak-section .section-desc{
    max-width: 620px;
}

.kontak-grid{
    margin-top: 2.6rem;
}

.addr-card{
    height: 100%;

    background:
        linear-gradient(
            165deg,
            var(--navy-2),
            var(--navy-1) 70%
        );

    border-radius: 22px;

    padding: 2.4rem 2.1rem;

    color: #fff;

    position: relative;

    overflow: hidden;
}

.addr-card .route-svg{
    position: absolute;

    right: -30px;
    bottom: -30px;

    width: 190px;
    height: 190px;

    opacity: 0.35;

    pointer-events: none;
}

.addr-pin{
    width: 50px;
    height: 50px;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            var(--primary),
            var(--accent)
        );

    display: flex;

    align-items: center;
    justify-content: center;

    color: #fff;

    font-size: 1.3rem;

    margin-bottom: 1.3rem;

    position: relative;

    z-index: 1;
}

.addr-card h3{
    font-size: 1.15rem;

    font-weight: 600;

    margin-bottom: 0.6rem;

    position: relative;

    z-index: 1;
}

.addr-card p{
    color: rgba(255,255,255,0.62);

    font-size: 0.94rem;

    line-height: 1.65;

    margin-bottom: 1.5rem;

    max-width: 300px;

    position: relative;

    z-index: 1;
}

.addr-map-link{
    display: inline-flex;

    align-items: center;

    gap: 0.45rem;

    color: #7dd3fc;

    font-weight: 600;

    font-size: 0.88rem;

    position: relative;

    z-index: 1;

    transition: gap 0.2s ease;
}

.addr-map-link:hover{
    gap: 0.7rem;

    color: #7dd3fc;
}


.contact-list{
    height: 100%;

    background: #fff;

    border-radius: 22px;

    border: 1px solid #eef2f7;

    box-shadow:
        0 20px 45px rgba(15,23,42,0.06);

    overflow: hidden;
}

.contact-row{
    display: flex;

    align-items: center;

    gap: 1rem;

    padding: 1.35rem 1.7rem;

    border-bottom: 1px solid #f1f5f9;

    transition: background 0.2s ease;
}

.contact-list a.contact-row{
    color: inherit;
}

.contact-row:last-child{
    border-bottom: none;
}

.contact-row:hover{
    background: #f8fafc;
}

.avatar-circle{
    width: 46px;
    height: 46px;

    border-radius: 50%;

    flex-shrink: 0;

    background:
        linear-gradient(
            135deg,
            var(--primary),
            var(--accent)
        );

    color: #fff;

    display: flex;

    align-items: center;
    justify-content: center;

    font-family: 'Space Grotesk', sans-serif;

    font-weight: 700;

    font-size: 0.92rem;
}

.contact-meta{
    flex: 1;

    min-width: 0;
}

.contact-meta .role{
    display: block;

    font-family: 'JetBrains Mono', monospace;

    font-size: 0.68rem;

    letter-spacing: 0.08em;

    text-transform: uppercase;

    color: var(--muted);

    margin-bottom: 0.15rem;
}

.contact-meta .name{
    font-weight: 600;

    font-size: 0.98rem;

    color: var(--ink);
}

.contact-meta .phone{
    font-family: 'JetBrains Mono', monospace;

    font-size: 0.85rem;

    color: var(--primary-dark);

    margin-top: 0.15rem;
}

.call-btn{
    width: 40px;
    height: 40px;

    border-radius: 50%;

    border: 1.5px solid #e2e8f0;

    display: flex;

    align-items: center;
    justify-content: center;

    color: var(--primary);

    flex-shrink: 0;

    transition: all 0.2s ease;
}

.contact-row:hover .call-btn{
    background: var(--primary);

    color: #fff;

    border-color: var(--primary);
}


/* =====================================================
   CTA
===================================================== */

.cta-banner{
    background:
        linear-gradient(
            135deg,
            var(--navy-2),
            var(--navy-1)
        );

    border-radius: 24px;

    padding: 3.5rem 2.5rem;

    text-align: center;

    position: relative;

    overflow: hidden;
}

.cta-banner h2{
    color: #fff;

    font-size: clamp(1.5rem, 3vw, 2.1rem);

    font-weight: 700;

    margin-bottom: 0.8rem;
}

.cta-banner p{
    color: rgba(255,255,255,0.7);

    margin-bottom: 2rem;
}


/* =====================================================
   FOOTER
===================================================== */

.footer-sibima{
    background: var(--navy-1);

    padding: 2.5rem 0 1.5rem;

    border-top:
        1px solid rgba(255,255,255,0.08);
}

.footer-sibima .footer-brand{
    display: flex;

    align-items: center;

    gap: 0.6rem;

    color: #fff;

    font-family: 'Space Grotesk', sans-serif;

    font-weight: 700;

    margin-bottom: 0.8rem;
}

.footer-sibima .footer-brand img{
    width: 32px;
    height: 32px;

    border-radius: 50%;

    background: #fff;

    padding: 3px;
}

.footer-sibima p{
    color: rgba(255,255,255,0.5);

    font-size: 0.88rem;

    margin: 0;
}

.footer-bottom{
    margin-top: 2rem;

    padding-top: 1.5rem;

    border-top:
        1px solid rgba(255,255,255,0.08);

    text-align: center;

    color: rgba(255,255,255,0.4);

    font-size: 0.8rem;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 991px){

    .nav-links,
    .nav-cta .btn-nav-outline{
        display: none;
    }

    .nav-toggle{
        display: block;
    }
}

@media (max-width: 767px){

    .hero{
        padding-top: 6.5rem;
    }

    .hero h1{
        font-size: 2.15rem;
    }

    .hero-stats{
        gap: 1.5rem;
    }

    .about-card{
        padding: 1.6rem;
    }

    .contact-row{
        padding: 1.2rem;
    }

    .cta-banner{
        padding: 2.5rem 1.5rem;
    }
}

@media (prefers-reduced-motion: reduce){

    .blob,
    .marka-track{
        animation: none;
    }

    .feature-card,
    .step-row{
        transition: none;

        opacity: 1;

        transform: none;
    }
}
</style>
</head>

<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar-sibima" id="mainNav">

    <div class="container">

        <a href="#" class="nav-brand">

            <img
                src="assets/img/sibimaa.png"
                alt="Logo SIBIMA"
            >

            SIBIMA Internship

        </a>


        <div class="nav-links">

            <a href="#fitur">
                Fitur
            </a>

            <a href="#alur">
                Alur Magang
            </a>

            <a href="#tentang">
                Tentang
            </a>

            <a href="#kontak">
                Kontak
            </a>

        </div>


        <div class="nav-cta">

            <a
                href="login.php"
                class="btn-nav-outline"
            >
                Masuk
            </a>

            <a
                href="login.php?form=register"
                class="btn-nav-solid"
            >
                Daftar
            </a>

        </div>


        <button
            class="nav-toggle"
            type="button"
            aria-label="Buka menu"
        >
            <i class="bi bi-list"></i>
        </button>

    </div>

</nav>



<!-- =====================================================
     HERO
===================================================== -->

<section class="hero">

    <div class="blob blob1"></div>
    <div class="blob blob2"></div>
    <div class="blob blob3"></div>


    <div class="container hero-content">

        <div class="row">

            <div class="col-lg-9">

                <span class="eyebrow">
                    Sistem Informasi Bina Marga Internship
                </span>


                <h1>
                    Magang di Bina Marga,
                    <span>tercatat rapi</span>
                    sejak hari pertama.
                </h1>


                <p class="lead-text">

                    SIBIMA membantu peserta magang dan pembimbing
                    di Dinas PU Bina Marga mengelola data peserta,
                    aktivitas logbook, serta proses persetujuan
                    dalam satu sistem yang terintegrasi —
                    lebih rapi, terstruktur, dan mudah dipantau.

                </p>


                <div class="hero-ctas">

                    <a
                        href="login.php?form=register"
                        class="btn-hero-primary"
                    >
                        <i class="bi bi-person-plus-fill"></i>

                        Daftar Sekarang

                    </a>


                    <a
                        href="login.php"
                        class="btn-hero-secondary"
                    >
                        <i class="bi bi-box-arrow-in-right"></i>

                        Masuk

                    </a>

                </div>


                <div class="hero-stats">

                    <div class="hero-stat">

                        <div class="num">
                            1
                        </div>

                        <div class="label">
                            Sistem Terpadu
                        </div>

                    </div>


                    <div class="hero-stat">

                        <div class="num">
                            100%
                        </div>

                        <div class="label">
                            Digital
                        </div>

                    </div>


                    <div class="hero-stat">

                        <div class="num">
                            24/7
                        </div>

                        <div class="label">
                            Akses Logbook
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     MARKA DIVIDER
===================================================== -->

<div class="marka-divider">
    <div class="marka-track"></div>
</div>



<!-- =====================================================
     FITUR
===================================================== -->

<section
    class="section section-bg-light"
    id="fitur"
>

    <div class="container">

        <span class="section-eyebrow">
            Fitur Utama
        </span>


        <h2 class="section-title">
            Satu Sistem untuk Seluruh Proses Magang
        </h2>


        <p class="section-desc mb-5">

            Mulai dari pendaftaran hingga penyelesaian magang,
            seluruh proses dan informasi dikelola dalam satu sistem
            yang dapat diakses oleh peserta, pembimbing, dan admin.

        </p>


        <div class="row g-4">


            <!-- FITUR 1 -->

            <div class="col-md-6 col-lg-3">

                <div class="feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>


                    <h3>
                        Profil &amp; Data Diri
                    </h3>


                    <p>

                        Kelola biodata dan informasi penempatan
                        secara terpusat. Data tersimpan dengan rapi
                        dan dapat diperbarui sesuai kebutuhan.

                    </p>

                </div>

            </div>



            <!-- FITUR 2 -->

            <div class="col-md-6 col-lg-3">

                <div class="feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-journal-check"></i>
                    </div>


                    <h3>
                        Logbook Kegiatan
                    </h3>


                    <p>

                        Catat aktivitas magang secara berkala
                        dan pantau perkembangan kegiatan melalui
                        logbook yang terintegrasi dengan pembimbing.

                    </p>

                </div>

            </div>



            <!-- FITUR 3 -->

            <div class="col-md-6 col-lg-3">

                <div class="feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-clipboard2-check"></i>
                    </div>


                    <h3>
                        Persetujuan Digital
                    </h3>


                    <p>

                        Proses pendaftaran dan pelaporan dapat
                        dikelola melalui sistem sehingga proses
                        persetujuan menjadi lebih cepat dan terarah.

                    </p>

                </div>

            </div>



            <!-- FITUR 4 -->

            <div class="col-md-6 col-lg-3">

                <div class="feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-bell"></i>
                    </div>


                    <h3>
                        Notifikasi Status
                    </h3>


                    <p>

                        Pantau setiap perubahan status secara
                        lebih mudah, mulai dari pendaftaran,
                        proses magang, hingga penyelesaian.

                    </p>

                </div>

            </div>


        </div>

    </div>

</section>



<!-- =====================================================
     MARKA DIVIDER
===================================================== -->

<div class="marka-divider">
    <div class="marka-track"></div>
</div>



<!-- =====================================================
     ALUR MAGANG
===================================================== -->

<section
    class="section section-bg-navy"
    id="alur"
>

    <div class="container">

        <div class="row g-5 align-items-start">


            <div class="col-lg-5">

                <span
                    class="section-eyebrow"
                    style="color:#7dd3fc;"
                >
                    Alur Magang
                </span>


                <h2
                    class="section-title"
                    style="color:#fff;"
                >
                    Empat Tahap, dari Pendaftaran hingga Penyelesaian
                </h2>


                <p
                    class="section-desc"
                    style="color:rgba(255,255,255,0.6);"
                >

                    Setiap tahapan dirancang secara terstruktur
                    agar peserta dan pembimbing dapat mengetahui
                    proses serta status magang dengan jelas.

                </p>

            </div>


            <div class="col-lg-7">


                <!-- STEP 1 -->

                <div class="step-row">

                    <div class="step-num">
                        01
                    </div>

                    <div class="step-body">

                        <h3>
                            Pendaftaran Akun
                        </h3>

                        <p>
                            Buat akun menggunakan email aktif,
                            kemudian lengkapi data diri dan informasi
                            yang diperlukan untuk proses pendaftaran.
                        </p>

                    </div>

                </div>



                <!-- STEP 2 -->

                <div class="step-row">

                    <div class="step-num">
                        02
                    </div>

                    <div class="step-body">

                        <h3>
                            Verifikasi Pendaftaran
                        </h3>

                        <p>
                            Admin memeriksa kelengkapan data dan
                            memverifikasi pendaftaran peserta
                            sebelum proses magang dimulai.
                        </p>

                    </div>

                </div>



                <!-- STEP 3 -->

                <div class="step-row">

                    <div class="step-num">
                        03
                    </div>

                    <div class="step-body">

                        <h3>
                            Pengisian Logbook
                        </h3>

                        <p>
                            Catat aktivitas dan perkembangan kegiatan
                            magang secara berkala melalui logbook
                            yang tersedia di dalam sistem.
                        </p>

                    </div>

                </div>



                <!-- STEP 4 -->

                <div class="step-row">

                    <div class="step-num">
                        04
                    </div>

                    <div class="step-body">

                        <h3>
                            Penyelesaian &amp; Rekapitulasi
                        </h3>

                        <p>
                            Setelah periode magang selesai,
                            seluruh aktivitas dapat direkap sebagai
                            dokumentasi dan laporan kegiatan.
                        </p>

                    </div>

                </div>


            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     TENTANG
===================================================== -->

<section
    class="section section-bg-light"
    id="tentang"
>

    <div class="container">

        <div class="about-card">

            <div class="row g-4 align-items-center">


                <div class="col-lg-5">

                    <div class="about-photo"></div>

                </div>


                <div class="col-lg-7">

                    <span class="section-eyebrow">
                        Tentang SIBIMA
                    </span>


                    <h2 class="section-title">
                        Mendukung Pengelolaan Magang yang Terintegrasi
                    </h2>


                    <p class="section-desc mb-0">

                        Dinas Pekerjaan Umum Bina Marga memiliki
                        peran dalam pembangunan dan pemeliharaan
                        infrastruktur jalan serta jembatan di wilayah
                        kerjanya. SIBIMA dikembangkan sebagai bagian
                        dari transformasi digital untuk mendukung
                        pengelolaan program magang secara lebih
                        terstruktur, terdokumentasi, dan efisien.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<section
    class="section section-bg-light kontak-section"
    id="kontak"
>
    <div class="container">

        <span class="section-eyebrow">
            Kontak
        </span>

        <h2 class="section-title">
            Hubungi Kami
        </h2>

        <p class="section-desc">
            Memiliki pertanyaan mengenai pendaftaran, alur magang, atau kendala teknis pada SIBIMA Internship? 
            Silakan hubungi tim pengelola melalui kontak di bawah ini.
        </p>

        <div class="row g-4 align-items-stretch kontak-grid">

            <!-- Kolom 1: Alamat Kantor (Lebar 4 Grid) -->
            <div class="col-lg-4">
                <div class="addr-card h-100 position-relative overflow-hidden" style="background: #0f1f4d; border-radius: 16px; padding: 2rem; color: #fff;">
                    <svg
                        class="route-svg position-absolute top-0 end-0 opacity-25"
                        viewBox="0 0 100 100"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        style="width: 120px; height: 120px; pointer-events: none;"
                    >
                        <path
                            d="M5 90 C 30 90, 20 55, 45 55 S 70 20, 95 20"
                            stroke="#fbbf24"
                            stroke-width="2.5"
                            stroke-dasharray="1 8"
                            stroke-linecap="round"
                        />
                        <circle cx="5" cy="90" r="3" fill="#7dd3fc" />
                        <circle cx="95" cy="20" r="3" fill="#fbbf24" />
                    </svg>

                    <div class="addr-pin mb-3 text-warning fs-4">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>

                    <h3 class="fw-bold mb-3 text-white fs-5">
                        Alamat Kantor
                    </h3>

                    <p class="text-light mb-4" style="font-size: 0.9rem; line-height: 1.6;">
                        Dinas PU Bina Marga Provinsi Jawa Timur,
                        Jl. Gayungsari 167 — Surabaya,
                        Jawa Timur.
                    </p>

                    <a
                        href="https://maps.google.com/?q=Dinas+PU+Bina+Marga+Provinsi+Jawa+Timur"
                        target="_blank"
                        rel="noopener"
                        class="addr-map-link text-info text-decoration-none fw-semibold d-inline-flex align-items: center"
                        style="font-size: 0.9rem;"
                    >
                        Lihat Lokasi di Google Maps
                        <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Kolom 2: Admin 1 (Lebar 4 Grid) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 h-100 d-flex flex-column justify-content-between" style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle text-white d-flex align-items: center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px; background: #2563eb; font-weight: bold; font-size: 0.95rem;">
                                A1
                            </div>
                            <div>
                                <span class="d-block text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Admin Pendaftaran</span>
                                <h5 class="fw-bold m-0 text-dark fs-6">Nama Admin 1</h5>
                            </div>
                        </div>
                        <p class="text-secondary mb-3" style="font-size: 0.88rem;">
                            Melayani pertanyaan seputar alur pendaftaran, berkas persyaratan, dan penempatan.
                        </p>
                    </div>

                    <div class="d-flex align-items: center justify-content-between p-2.5 px-3 rounded-3 mt-auto" style="background: #f8fafc; border: 1px solid #eef2f6;">
                        <span class="text-dark font-monospace fw-semibold" style="font-size: 0.8rem;">031-8290186</span>
                        <!-- Tombol Telepon Biasa -->
                        <a href="tel:0318290186" class="btn btn-primary btn-sm px-3 fw-bold rounded-pill shadow-sm" style="font-size: 0.8rem;">
                            <i class="bi bi-telephone-fill me-1"></i> Telepon
                        </a>
                    </div>
                </div>
            </div>

            <!-- Kolom 3: Admin 2 (Lebar 4 Grid) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 h-100 d-flex flex-column justify-content-between" style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <div>
                        <div class="d-flex align-items: center mb-3">
                            <div class="rounded-circle text-white d-flex align-items: center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px; background: #0d9488; font-weight: bold; font-size: 0.95rem;">
                                A2
                            </div>
                            <div>
                                <span class="d-block text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Admin Teknis & Sistem</span>
                                <h5 class="fw-bold m-0 text-dark fs-6">Nama Admin 2</h5>
                            </div>
                        </div>
                        <p class="text-secondary mb-3" style="font-size: 0.88rem;">
                            Melayani kendala login, reset password, logbook, dan transkrip nilai sistem.
                        </p>
                    </div>

                    <div class="d-flex align-items: center justify-content-between p-2.5 px-3 rounded-3 mt-auto" style="background: #f8fafc; border: 1px solid #eef2f6;">
                        <span class="text-dark font-monospace fw-semibold" style="font-size: 0.8rem;">031-8280433</span>
                        <!-- Tombol Telepon Biasa -->
                        <a href="tel:0318280433" class="btn btn-teal btn-sm px-3 fw-bold rounded-pill shadow-sm text-white" style="font-size: 0.8rem; background: #0d9488;">
                            <i class="bi bi-telephone-fill me-1"></i> Telepon
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- =====================================================
     CTA
===================================================== -->

<section
    class="section section-bg-light pt-0"
>

    <div class="container">

        <div class="cta-banner">

            <h2>
                Siap Memulai Proses Magang?
            </h2>


            <p>

                Daftarkan akun, lengkapi data diri,
                dan kelola aktivitas magang Anda
                melalui SIBIMA Internship.

            </p>


            <div class="hero-ctas justify-content-center mb-0">


                <a
                    href="login.php?form=register"
                    class="btn-hero-primary"
                >

                    <i class="bi bi-person-plus-fill"></i>

                    Daftar Sekarang

                </a>


                <a
                    href="login.php"
                    class="btn-hero-secondary"
                >

                    <i class="bi bi-box-arrow-in-right"></i>

                    Masuk

                </a>


            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer-sibima">

    <div class="container">

        <div class="row">

            <div class="col-md-7">

                <div class="footer-brand">

                    <img
                        src="assets/img/sibimaa.png"
                        alt="Logo SIBIMA"
                    >

                    SIBIMA Internship

                </div>


                <p>

                    Sistem Informasi Bina Marga Internship
                    untuk mendukung pengelolaan program magang
                    secara terintegrasi dan terdokumentasi.

                </p>

            </div>

        </div>


        <div class="footer-bottom">

            &copy;
            <?php echo date('Y'); ?>

            Dinas PU Bina Marga — Divisi IT.

        </div>

    </div>

</footer>



<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script>

/* =====================================================
   NAVBAR SCROLL
===================================================== */

const nav =
    document.getElementById('mainNav');

window.addEventListener(
    'scroll',
    () => {

        nav.classList.toggle(
            'scrolled',
            window.scrollY > 30
        );

    }
);



/* =====================================================
   REVEAL ANIMATION
===================================================== */

const revealEls =
    document.querySelectorAll(
        '.feature-card, .step-row'
    );


const observer =
    new IntersectionObserver(
        (entries) => {

            entries.forEach(
                (entry, i) => {

                    if(entry.isIntersecting){

                        setTimeout(
                            () => {

                                entry.target
                                    .classList
                                    .add('in-view');

                            },
                            i * 60
                        );

                        observer.unobserve(
                            entry.target
                        );

                    }

                }
            );

        },
        {
            threshold: 0.15
        }
    );


revealEls.forEach(
    el => observer.observe(el)
);



/* =====================================================
   SMOOTH SCROLL
===================================================== */

document
    .querySelectorAll(
        'a[href^="#"]'
    )
    .forEach(
        link => {

            link.addEventListener(
                'click',
                function(e){

                    const target =
                        document.querySelector(
                            this.getAttribute('href')
                        );

                    if(target){

                        e.preventDefault();

                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });

                    }

                }
            );

        }
);

</script>

</body>
</html>
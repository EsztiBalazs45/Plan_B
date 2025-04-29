<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kajtár Könyvelés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/1c89eb1f85.js" crossorigin="anonymous"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a2e44;
            /* Sötétkék */
            --secondary-color: #d4af37;
            /* Arany */
            --accent-color: #ffffff;
            /* Fehér */
            --text-color: #333333;
            /* Szürke szöveg */
            --light-bg: #f5f7fa;
            /* Világos háttér */
        }

        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            padding: 1.5rem 0;
            background-color: var(--accent-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: background-color 0.3s ease;
        }

        .navbar-brand {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: 1px;
        }

        .nav-link {
            color: var(--text-color);
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--secondary-color);
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #c19b32;
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: var(--accent-color);
            transform: translateY(-3px);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(26, 46, 68, 0.85), rgba(26, 46, 68, 0.65)), url('accounting/assets/images/szamologep.jpg');
            background-size: cover;
            background-position: center;
            min-height: 85vh;
            display: flex;
            align-items: center;
            color: var(--accent-color);
            padding: 0 2rem;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero-section p {
            font-size: 1.25rem;
            opacity: 0.9;
        }

        /* Szolgáltatások */
        .service-card {
            padding: 2.5rem;
            border-radius: 15px;
            background: var(--accent-color);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }

        .service-card i {
            color: var(--primary-color);
        }

        .service-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 1rem 0;
        }

        /* Rólunk */
        .about-section {
            background: var(--light-bg);
            padding: 6rem 0;
        }

        .about-section img {
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        /* Testimonials */
        .testimonial-card {
            background: var(--accent-color);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        }

        /* Footer */
        .footer {
            background: var(--primary-color);
            color: var(--accent-color);
            padding: 4rem 0;
        }

        .footer h5 {
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .footer a {
            color: var(--accent-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: var(--secondary-color);
        }

        .social-icons img {
            width: 32px;
            margin-right: 1rem;
            transition: transform 0.3s ease;
        }

        .social-icons img:hover {
            transform: scale(1.1);
        }

        /* Reszponzivitás */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: var(--accent-color);
                padding: 1rem;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .hero-section h1 {
                font-size: 2.5rem;
            }

            .hero-section p {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Kajtár Könyvelés</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#kezdolap">Kezdőlap</a></li>
                    <li class="nav-item"><a class="nav-link" href="#szolgaltatasok">Szolgáltatások</a></li>
                    <li class="nav-item"><a class="nav-link" href="#rolunk">Rólunk</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kapcsolat">Kapcsolat</a></li>
                </ul>
            </div>
            <div class="d-flex gap-3">
                <a class="btn btn-outline-primary d-none d-lg-block" href="accounting/pages/login.php">Bejelentkezés</a>
                <a class="btn btn-outline-primary d-none d-lg-block" href="accounting/pages/register.php">Regisztráció</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="kezdolap">
        <div class="container">
            <div class="row">
                <div class="col-lg-7" data-aos="fade-up">
                    <h1>Megbízható könyvelés a siker érdekében</h1>
                    <p class="lead mb-4">Professzionális pénzügyi megoldások vállalkozása számára, precizitással és szakértelemmel.</p>
                    <a href="#kapcsolat" class="btn btn-primary btn-lg">Kapcsolatfelvétel</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Szolgáltatások -->
    <section class="py-5" id="szolgaltatasok">
        <div class="container py-5">
            <h2 class="text-center mb-5" data-aos="fade-up">Szolgáltatásaink</h2>
            <div class="row g-4">
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card text-center">
                        <i class="fas fa-calculator fa-3x mb-4"></i>
                        <h3>Könyvelés</h3>
                        <p>Teljes körű könyvelési szolgáltatások vállalkozása igényeire szabva.</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card text-center">
                        <i class="fas fa-money-bill-wave fa-3x mb-4"></i>
                        <h3>Bérszámfejtés</h3>
                        <p>Pontos bérszámfejtés és járulékbevallások készítése.</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-card text-center">
                        <i class="fas fa-comments-dollar fa-3x mb-4"></i>
                        <h3>Adótanácsadás</h3>
                        <p>Szakértői tanácsadás az adóoptimalizáláshoz.</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="service-card text-center">
                        <i class="fas fa-chart-line fa-3x mb-4"></i>
                        <h3>Pénzügyi tervezés</h3>
                        <p>Hosszú távú stratégiák a pénzügyi stabilitásért.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Rólunk -->
    <section class="about-section" id="rolunk">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="accounting/assets/images/pacsi.jpg" class="img-fluid" alt="Csapatunk">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2 class="mb-4">Rólunk</h2>
                    <p class="lead">Több mint 15 év tapasztalatával kínálunk személyre szabott könyvelési és pénzügyi megoldásokat vállalkozások számára.</p>
                    <div class="row mt-4">
                        <div class="col-6">
                            <h3 class="h2 text-primary">500+</h3>
                            <p>Elégedett ügyfél</p>
                        </div>
                        <div class="col-6">
                            <h3 class="h2 text-primary">15+</h3>
                            <p>Év tapasztalat</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ügyfélvélemények -->
    <section class="py-3">
        <div class="container py-3">
            <h2 class="text-center mb-3" data-aos="fade-up">Ügyfeleink mondták</h2>
            <div class="row">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <p class="mb-3">"Profi csapat, gyors és pontos munkavégzés. Minden vállalkozónak ajánlom!"</p>
                        <h6 class="mb-1">Hesz Milán Mihály</h6>
                        <p class="small mb-0 text-muted">Pizza futár, Tech Solutions Kft.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <p class="mb-3">"Pontos munkavégzés segítőkész csapat. Csak ajánlani tudom!"</p>
                        <h6 class="mb-1">Fenyvesi Lajos</h6>
                        <p class="small mb-0 text-muted">Villanyszerelő, Fenyvesvillany Kft.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <p class="mb-3">"Nagyon jó kis csapat mindig mindenben segítenek. Gyorsak pontosak mindenkinek csak ajánlani tudom őket!"</p>
                        <h6 class="mb-1">Szamarasi Béla</h6>
                        <p class="small mb-0 text-muted">Pék, Szamaraspékség Kft.</p>
                    </div>
                </div>



            </div>
        </div>
    </section>

    <!-- Kapcsolat -->
    <section class="py-5" id="kapcsolat">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="mb-4">Kapcsolat</h2>
                    <form>
                        <div class="mb-3">
                            <input type="text" class="form-control rounded-pill" placeholder="Név" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control rounded-pill" placeholder="Email" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control rounded-3" rows="5" placeholder="Üzenet" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Küldés</button>
                    </form>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h4 class="mb-4">Elérhetőségeink</h4>
                    <p><i class="fas fa-map-marker-alt me-2 text-primary"></i> 9241 Jánossomorja, Kápolna utca 16</p>
                    <p><i class="fas fa-phone me-2 text-primary"></i> +36 30 225 014</p>
                    <p><i class="fas fa-envelope me-2 text-primary"></i> kajarkonyveles@gmail.com</p>
                    <div class="ratio ratio-16x9 mt-4">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2695.6504565904584!2d19.0505!3d47.4983!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDfCsDI5JzUzLjkiTiAxOcKwMDMnMDEuOCJF!5e0!3m2!1shu!2shu!4v1635789245784!5m2!1shu!2shu"
                            style="border:0; border-radius: 15px;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>Kajtár Könyvelés</h5>
                    <p>Megbízható könyvelési és pénzügyi szolgáltatások vállalkozásoknak.</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Gyors linkek</h5>
                    <ul class="list-unstyled">
                        <li><a href="#kezdolap">Kezdőlap</a></li>
                        <li><a href="#szolgaltatasok">Szolgáltatások</a></li>
                        <li><a href="#rolunk">Rólunk</a></li>
                        <li><a href="#kapcsolat">Kapcsolat</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5>Kövess minket</h5>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/heszmilan/"><img src="accounting/assets/icons/facebook.png" alt="Facebook"></a>
                        <a href="https://www.instagram.com/milan_hesz/"><img src="accounting/assets/icons/instagram.png" alt="Instagram"></a>
                        <a href="https://x.com/Milee_exe"><img src="accounting/assets/icons/twitter.png" alt="Twitter"></a>
                    </div>
                </div>
            </div>
            <hr class="bg-light opacity-25">
            <div class="row text-center text-md-start">
                <div class="col-md-6">
                    <p class="mb-0">© 2025 Kajtár Könyvelés. Minden jog fenntartva.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="me-3">Adatvédelmi nyilatkozat</a>
                    <a href="#">ÁSZF</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
            } else {
                navbar.style.backgroundColor = 'var(--accent-color)';
                navbar.style.boxShadow = '0 4px 12px rgba(0,0,0,0.05)';
            }
        });
    </script>
</body>

</html>
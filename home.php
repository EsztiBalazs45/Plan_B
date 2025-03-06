<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bozont</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/1c89eb1f85.js" crossorigin="anonymous"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --text-color: #2c3e50;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Arial', sans-serif;
            color: var(--text-color);
        }

        .navbar {
            padding: 1rem 2rem;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-right: 2rem;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-collapse {
            flex-grow: 0;
        }

        @media (max-width: 991px) {
            .navbar-collapse {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                padding: 1rem;
                border-radius: 8px;
                margin-top: 0.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .navbar-nav {
                text-align: center;
            }

            .nav-link {
                padding: 0.5rem 0;
            }
        }

        .nav-link {
            color: var(--text-color);
            margin: 0 1rem;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--secondary-color);
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            background-color: #2980b9;
        }

        .btn-outline-primary {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }

        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('accounting/assets/images/szamologep.jpg');
            background-size: cover;
            background-position: center;
            height: 80vh;
            display: flex;
            align-items: center;
            color: white;
        }

        .service-card {
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            background: white;
            height: 100%;
        }

        .service-card:hover {
            transform: translateY(-10px);
        }

        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 1rem 0;
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 4rem 0;
        }

        .social-icons a {
            color: white;
            margin: 0 1rem;
            font-size: 1.5rem;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: var(--secondary-color);
        }

        .accordion-button:not(.collapsed) {
            background-color: var(--light-bg);
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Fejléc -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Kajtár Könyvelés</a>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#kezdolap">Kezdőlap</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#szolgaltatasok">Szolgáltatások</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#rolunk">Rólunk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kapcsolat">Kapcsolat</a>
                    </li>
                    <li class="nav-item d-lg-none">
                        <a class="btn btn-outline-primary w-100 mt-2" href="accounting/index.php">Bejelentkezés / Regisztráció</a>
                    </li>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <a class="btn btn-outline-primary d-none d-lg-block" href="accounting/pages/login.php">Bejelentkezés</a>
                <a class="btn btn-outline-primary d-none d-lg-block" href="accounting/pages/register.php">Regisztráció</a>
                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <!-- Mobil menü -->
            <div class="collapse navbar-collapse d-lg-none mt-2" id="navbarNavMobile">
                <ul class="navbar-nav">
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hősrész -->
    <section class="hero-section" id="kezdolap">
        <div class="container">
            <div class="row">
                <div class="col-lg-8" data-aos="fade-up">
                    <h1 class="display-4 fw-bold mb-4">Megbízható könyvelés vállalkozásoknak</h1>
                    <p class="lead mb-4">Precizitás, szakértelem és pénzügyi biztonság</p>
                    <a href="#kapcsolat" class="btn btn-primary btn-lg">Kapcsolatfelvétel</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Szolgáltatások -->
    <section class="py-5" id="szolgaltatasok">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Szolgáltatásaink</h2>
            <div class="row g-4">
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card text-center">
                        <i class="fas fa-calculator fa-3x mb-4 text-primary"></i>
                        <h3 class="h5">Könyvelés</h3>
                        <p>Teljes körű könyvelési szolgáltatás vállalkozása számára.</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card text-center">
                        <i class="fas fa-money-bill-wave fa-3x mb-4 text-primary"></i>
                        <h3 class="h5">Bérszámfejtés</h3>
                        <p>Pontos és időben történő bérszámfejtés, járulékbevallások.</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-card text-center">
                        <i class="fas fa-comments-dollar fa-3x mb-4 text-primary"></i>
                        <h3 class="h5">Adótanácsadás</h3>
                        <p>Szakértői tanácsadás adóoptimalizálás céljából.</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="service-card text-center">
                        <i class="fas fa-chart-line fa-3x mb-4 text-primary"></i>
                        <h3 class="h5">Pénzügyi tervezés</h3>
                        <p>Hosszú távú pénzügyi stratégia kialakítása.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Rólunk -->
    <section class="py-5 bg-light" id="rolunk">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="accounting/assets/images/pacsi.jpg" class="img-fluid rounded" alt="Csapatunk">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2 class="mb-4">Rólunk</h2>
                    <p class="lead">15 éves szakmai tapasztalattal rendelkező könyvelőiroda vagyunk, amely személyre szabott megoldásokat kínál vállalkozása számára.</p>
                    <div class="row mt-4">
                        <div class="col-6">
                            <h3 class="h2 mb-3">500+</h3>
                            <p>Elégedett ügyfél</p>
                        </div>
                        <div class="col-6">
                            <h3 class="h2 mb-3">15+</h3>
                            <p>Év tapasztalat</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ügyfélvélemények -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Ügyfeleink mondták</h2>
            <div class="row">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <p class="mb-3">"Kiváló szakmai tudás és megbízhatóság jellemzi a csapatot."</p>
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-1">Hesz Milán Mihály</h6>
                                <p class="small mb-0">Pizza futár, Tech Solutions Kft.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- További vélemények... -->
            </div>
        </div>
    </section>

    <!-- GYIK -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Gyakran Ismételt Kérdések</h2>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item" data-aos="fade-up">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Milyen szolgáltatásokat nyújtanak?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Teljes körű könyvelési szolgáltatást, bérszámfejtést, adótanácsadást és pénzügyi tervezést kínálunk.
                        </div>
                    </div>
                </div>
                <!-- További GYIK elemek... -->
            </div>
        </div>
    </section>

    <!-- Kapcsolat -->
    <section class="py-5" id="kapcsolat">
        <div class="container">
            <div class="row">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="mb-4">Kapcsolat</h2>
                    <form>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Név">
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Email">
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="5" placeholder="Üzenet"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Küldés</button>
                    </form>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="mb-4">
                        <h4>Elérhetőségeink</h4>
                        <p><i class="fas fa-map-marker-alt me-2"></i> 9241 Jánossomorja Kápolna utca 16</p>
                        <p><i class="fas fa-phone me-2"></i> +36 1 234 5678</p>
                        <p><i class="fas fa-envelope me-2"></i> kajarkonyveles@gmail.com</p>
                    </div>
                    <div class="ratio ratio-16x9">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2695.6504565904584!2d19.0505!3d47.4983!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDfCsDI5JzUzLjkiTiAxOcKwMDMnMDEuOCJF!5e0!3m2!1shu!2shu!4v1635789245784!5m2!1shu!2shu" 
                                style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Lábjegyzet -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>KönyvelőPro</h5>
                    <p>Megbízható könyvelési szolgáltatások vállalkozásoknak</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Gyors linkek</h5>
                    <ul class="list-unstyled">
                        <li><a href="#kezdolap" class="text-white">Kezdőlap</a></li>
                        <li><a href="#szolgaltatasok" class="text-white">Szolgáltatások</a></li>
                        <li><a href="#rolunk" class="text-white">Rólunk</a></li>
                        <li><a href="#kapcsolat" class="text-white">Kapcsolat</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5>Kövess minket</h5>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/heszmilan/"><img src="accounting/assets/icons/facebook.png" alt="Facebook" width="30"></a>
                        <a href="https://www.instagram.com/milan_hesz/"><img src="accounting/assets/icons/instagram.png" alt="Instagram" width="30"></a>
                        <a href="https://x.com/Milee_exe"><img src="accounting/assets/icons/twitter.png" alt="Twitter" width="30"></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 KönyvelőPro. Minden jog fenntartva.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white me-3">Adatvédelmi nyilatkozat</a>
                    <a href="#" class="text-white">ÁSZF</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Navbar color change on scroll
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
                document.querySelector('.navbar').style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
            } else {
                document.querySelector('.navbar').style.backgroundColor = 'white';
                document.querySelector('.navbar').style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html> 
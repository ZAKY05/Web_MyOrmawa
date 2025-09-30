<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyOrmawa - Platform Digital Organisasi Mahasiswa</title>
    <meta name="description" content="Platform digital terdepan untuk revolusi pengelolaan organisasi mahasiswa di Politeknik Negeri Jember.">
    <meta name="keywords" content="myormawa, organisasi mahasiswa, digitalisasi kampus, aplikasi ormawa, politeknik negeri jember">
    <meta name="author" content="MyOrmawa Team">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../../Asset/Css/LandingPage.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-gradient-secondary" href="#home">
                MyOrmawa
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#download">Download</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <a href="../SuperAdmin/Login.php"><button class="btn btn-gradient-secondary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="hero-content">
                        <h1 class="display-3 fw-bold mb-4">
                            <span class="text-gradient-primary">Revolusi Digital</span><br>
                            <span class="text-dark">Organisasi Mahasiswa</span>
                        </h1>
                        <p class="lead text-muted mb-5 pe-lg-5">
                            Kelola kegiatan, anggota, dan laporan dalam satu platform terintegrasi yang mudah digunakan.
                        </p>
                        <div class="hero-actions mb-4">
                            <a href="#download" class="btn btn-gradient-primary btn-lg px-4 py-3 me-3">
                                <i class="bi bi-download me-2"></i>Download Aplikasi
                            </a>
                            <button class="btn btn-outline-primary btn-lg px-4 py-3" onclick="openLoginModal()">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </div>
                        <div class="hero-features d-flex flex-wrap gap-4 text-muted">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <small>Gratis untuk semua ormawa</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-shield-lock text-primary me-2"></i>
                                <small>Data aman & terenkripsi</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="hero-image text-center position-relative">
                    
                        <div class="phones-mockup">
                            <!-- Ganti dengan path gambar Anda jika tersedia -->
                            <img src="../../../Asset/Img/hero-mockup.png" alt="MyOrmawa Mobile App" 
                                 class="img-fluid" style="max-height: 700px; object-fit: contain;">
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section py-5 bg-white">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-4 fw-bold">
                        <span class="text-dark">Fitur</span> 
                        <span class="text-gradient-secondary">Unggulan</span>
                    </h2>
                    <p class="lead text-muted">Temukan fitur-fitur canggih yang dirancang khusus untuk kebutuhan organisasi mahasiswa.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="p-4 border rounded text-center">
                        <i class="bi bi-people fs-1 text-primary mb-3"></i>
                        <h5>Manajemen Anggota</h5>
                        <p>Kelola data anggota, jabatan, dan kehadiran secara real-time.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="p-4 border rounded text-center">
                        <i class="bi bi-calendar-check fs-1 text-primary mb-3"></i>
                        <h5>Agenda & Kegiatan</h5>
                        <p>Buat, jadwalkan, dan pantau kegiatan organisasi dengan mudah.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="p-4 border rounded text-center">
                        <i class="bi bi-file-earmark-text fs-1 text-primary mb-3"></i>
                        <h5>Laporan Digital</h5>
                        <p>Buat dan kirim laporan kegiatan langsung dari aplikasi.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Statistics Section -->
    <section class="statistics-section py-5 bg-white">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-4 fw-bold">
                        <span class="text-dark">Statistik</span> 
                        <span class="text-gradient-secondary">Platform</span>
                    </h2>
                    <p class="lead text-muted">Lihat bagaimana MyOrmawa telah membantu ribuan mahasiswa di seluruh Indonesia.</p>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <h3 class="display-5 fw-bold text-gradient-secondary">50+</h3>
                    <p>Organisasi Aktif</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h3 class="display-5 fw-bold text-gradient-secondary">5000+</h3>
                    <p>Pengguna Terdaftar</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h3 class="display-5 fw-bold text-gradient-secondary">200+</h3>
                    <p>Kegiatan Terverifikasi</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h3 class="display-5 fw-bold text-gradient-secondary">98%</h3>
                    <p>Kepuasan Pengguna</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mobile Preview Section -->
    <section id="about" class="mobile-preview-section py-5 bg-white">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-4 fw-bold">
                        <span class="text-dark">Preview</span> 
                        <span class="text-gradient-secondary">Aplikasi Mobile</span>
                    </h2>
                    <p class="lead text-muted">Jelajahi antarmuka yang intuitif dan fitur-fitur canggih dalam aplikasi MyOrmawa</p>
                </div>
            </div>
            <div class="text-center">
                <img src="../../../Asset/Img/hero-mockup.png" alt="Preview Aplikasi MyOrmawa" class="img-fluid rounded shadow" style="max-width: 500px;">
            </div>
        </div>
    </section>

    <!-- Download Section -->
    <section id="download" class="download-section py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-4 fw-bold">
                        <span class="text-dark">Download</span> 
                        <span class="text-gradient-secondary">MyOrmawa</span>
                    </h2>
                    <p class="lead text-muted">Dapatkan aplikasi MyOrmawa sekarang juga dan mulai revolusi digital organisasi mahasiswa Anda</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-auto">
                    <a href="#" class="btn btn-outline-dark me-3">
                        <i class="bi bi-google-play me-2"></i> Google Play
                    </a>
                    <a href="#" class="btn btn-outline-dark">
                        <i class="bi bi-apple me-2"></i> App Store
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section py-5 bg-white">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-4 fw-bold">
                        <span class="text-dark">Hubungi</span> 
                        <span class="text-gradient-secondary">Kami</span>
                    </h2>
                    <p class="lead text-muted">Kami siap membantu Anda! Kirim pesan melalui formulir di bawah.</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form id="contactForm">
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-lg" placeholder="Nama Lengkap" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control form-control-lg" placeholder="Email" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control form-control-lg" rows="4" placeholder="Pesan Anda" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-gradient-primary btn-lg w-100">Kirim Pesan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-section bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="footer-brand">
                        <h5 class="fw-bold mb-3" style="color: #00D7FF;">MyOrmawa</h5>
                        <p class="text-light mb-4">Platform digital terdepan untuk revolusi pengelolaan organisasi mahasiswa di Politeknik Negeri Jember.</p>
                        <div class="social-links">
                            <a href="#" class="text-light me-3"><i class="bi bi-instagram fs-5"></i></a>
                            <a href="#" class="text-light me-3"><i class="bi bi-twitter fs-5"></i></a>
                            <a href="#" class="text-light me-3"><i class="bi bi-facebook fs-5"></i></a>
                            <a href="#" class="text-light"><i class="bi bi-youtube fs-5"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-semibold mb-3">Menu Utama</h6>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-light text-decoration-none">Beranda</a></li>
                        <li><a href="#features" class="text-light text-decoration-none">Fitur</a></li>
                        <li><a href="#about" class="text-light text-decoration-none">Tentang</a></li>
                        <li><a href="#download" class="text-light text-decoration-none">Download</a></li>
                        <li><a href="#contact" class="text-light text-decoration-none">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-semibold mb-3">Dukungan</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light text-decoration-none">Panduan Pengguna</a></li>
                        <li><a href="#" class="text-light text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Dukungan Teknis</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6 offset-lg-1">
                    <h6 class="fw-semibold mb-3">Kontak Kami</h6>
                    <div class="contact-info">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-geo-alt footer-icon me-3"></i>
                            <div>
                                <div>Politeknik Negeri Jember</div>
                                <small class="text-light">Jl. Mastrip 164, Jember 68121</small><br>
                                <small class="text-light">Jawa Timur, Indonesia</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-telephone footer-icon me-3"></i>
                            <span>+62 331 123 4567</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-envelope footer-icon me-3"></i>
                            <span>hello@myormawa.com</span>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 MyOrmawa. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-light text-decoration-none me-3">Kebijakan Privasi</a>
                    <a href="#" class="text-light text-decoration-none me-3">Syarat Layanan</a>
                    <a href="#" class="text-light text-decoration-none">Cookie Policy</a>
                </div>
            </div>
            <div class="text-center mt-3">
                <small class="text-muted">Dikembangkan dengan ❤️ untuk kemajuan organisasi mahasiswa Indonesia</small>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <?php include('../LandingPage/LoginModal.php');?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <!-- Custom JS -->
    <script src="../../../Asset/Js/LandingPage.js"></script>

    <script>
        // Initialize AOS
        AOS.init();

        function openLoginModal() {
            const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;
            const alertDiv = document.getElementById('loginAlert');
            
            if (email === 'admin@myormawa.com' && password === 'admin123') {
                alertDiv.classList.add('d-none');
                alert('Login berhasil! Redirect ke dashboard...');
            } else {
                alertDiv.classList.remove('d-none');
            }
        });

        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('passwordInput');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });

        document.getElementById('contactForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Terima kasih! Pesan Anda telah dikirim.');
            this.reset();
        });
    </script>
</body>
</html>
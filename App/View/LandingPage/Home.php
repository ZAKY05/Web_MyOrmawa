<?php include('../SuperAdmin/Header.php'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ormawa Kampus - Organisasi Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../Asset/Css/LandingPage.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand text-gradient" href="#home">
                <img src="../../../Asset/Img/Apps Desktop.svg" class="img-landing" alt="Logo"> MyOrmawa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#profil">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kegiatan">Kegiatan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#struktur">Struktur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#galeri">Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kontak">Kontak</a>
                    </li>
                    <a href="../SuperAdmin/Login.php"><button class="btn btn-primary">Login</button></a>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Slider -->
    <section id="home" class="hero-slider">
        <!-- Slide 1 -->
        <div class="slide active" style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1920');">
            <div class="slide-overlay">
                <div class="container">
                    <div class="slide-content">
                        <span class="slide-category">BERITA UTAMA</span>
                        <h1 class="slide-title">Pemilihan Ketua Ormawa 2025</h1>
                        <p class="slide-description">Proses demokrasi pemilihan ketua organisasi mahasiswa berjalan lancar dengan partisipasi aktif seluruh anggota.</p>
                        <button class="btn btn-light btn-lg">Baca Selengkapnya</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1920');">
            <div class="slide-overlay">
                <div class="container">
                    <div class="slide-content">
                        <span class="slide-category">PRESTASI</span>
                        <h1 class="slide-title">Juara Kompetisi Nasional</h1>
                        <p class="slide-description">Tim ormawa berhasil meraih juara 1 dalam kompetisi debat mahasiswa tingkat nasional di Jakarta.</p>
                        <button class="btn btn-light btn-lg">Baca Selengkapnya</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=1920');">
            <div class="slide-overlay">
                <div class="container">
                    <div class="slide-content">
                        <span class="slide-category">KEGIATAN SOSIAL</span>
                        <h1 class="slide-title">Bakti Sosial Desa Binaan</h1>
                        <p class="slide-description">Program pengabdian masyarakat dengan memberikan bantuan pendidikan dan kesehatan di desa binaan kampus.</p>
                        <button class="btn btn-light btn-lg">Baca Selengkapnya</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slider Controls -->
        <div class="slider-arrow prev" onclick="changeSlide(-1)">
            <i class="bi bi-chevron-left fs-4"></i>
        </div>
        <div class="slider-arrow next" onclick="changeSlide(1)">
            <i class="bi bi-chevron-right fs-4"></i>
        </div>

        <!-- Slider Navigation -->
        <div class="slider-nav">
            <span class="slider-dot active" onclick="goToSlide(0)"></span>
            <span class="slider-dot" onclick="goToSlide(1)"></span>
            <span class="slider-dot" onclick="goToSlide(2)"></span>
        </div>
    </section>

    <!-- Activities Section -->
    <section id="kegiatan">
        <div class="container">
            <div class="section-title">
                <h2><span class="text-gradient">Kegiatan</span> Terbaru</h2>
                <p>Berbagai kegiatan yang telah dan akan dilaksanakan</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="activity-card">
                        <div class="activity-img" style="background-image: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800');">
                            <div class="activity-date">
                                <span class="day">25</span>
                                <span class="month">Jan</span>
                            </div>
                        </div>
                        <div class="activity-content">
                            <span class="badge bg-primary mb-2">Workshop</span>
                            <h5 class="mb-2">Workshop Kepemimpinan Mahasiswa</h5>
                            <p class="text-muted mb-3">Pelatihan softskill kepemimpinan untuk seluruh pengurus ormawa dengan narasumber berpengalaman.</p>
                            <a href="#" class="text-decoration-none">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="activity-card">
                        <div class="activity-img" style="background-image: url('https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=800');">
                            <div class="activity-date">
                                <span class="day">20</span>
                                <span class="month">Jan</span>
                            </div>
                        </div>
                        <div class="activity-content">
                            <span class="badge bg-success mb-2">Seminar</span>
                            <h5 class="mb-2">Seminar Kewirausahaan Digital</h5>
                            <p class="text-muted mb-3">Menghadirkan pengusaha muda sukses untuk berbagi pengalaman membangun bisnis digital.</p>
                            <a href="#" class="text-decoration-none">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="activity-card">
                        <div class="activity-img" style="background-image: url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=800');">
                            <div class="activity-date">
                                <span class="day">15</span>
                                <span class="month">Jan</span>
                            </div>
                        </div>
                        <div class="activity-content">
                            <span class="badge bg-info mb-2">Kompetisi</span>
                            <h5 class="mb-2">Lomba Debat Antar Fakultas</h5>
                            <p class="text-muted mb-3">Kompetisi debat bahasa Indonesia dan Inggris untuk meningkatkan kemampuan public speaking.</p>
                            <a href="#" class="text-decoration-none">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="activity-card">
                        <div class="activity-img" style="background-image: url('https://images.unsplash.com/photo-1593113598332-cd288d649433?w=800');">
                            <div class="activity-date">
                                <span class="day">10</span>
                                <span class="month">Jan</span>
                            </div>
                        </div>
                        <div class="activity-content">
                            <span class="badge bg-warning mb-2">Sosial</span>
                            <h5 class="mb-2">Bakti Sosial Masyarakat</h5>
                            <p class="text-muted mb-3">Program pengabdian masyarakat di desa binaan dengan kegiatan pendidikan dan kesehatan.</p>
                            <a href="#" class="text-decoration-none">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="activity-card">
                        <div class="activity-img" style="background-image: url('https://images.unsplash.com/photo-1523580494863-6f3031224c94?w=800');">
                            <div class="activity-date">
                                <span class="day">05</span>
                                <span class="month">Jan</span>
                            </div>
                        </div>
                        <div class="activity-content">
                            <span class="badge bg-danger mb-2">Olahraga</span>
                            <h5 class="mb-2">Turnamen Futsal Antar Ormawa</h5>
                            <p class="text-muted mb-3">Kompetisi futsal untuk mempererat silaturahmi dan sportivitas antar organisasi mahasiswa.</p>
                            <a href="#" class="text-decoration-none">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="activity-card">
                        <div class="activity-img" style="background-image: url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800');">
                            <div class="activity-date">
                                <span class="day">01</span>
                                <span class="month">Jan</span>
                            </div>
                        </div>
                        <div class="activity-content">
                            <span class="badge bg-secondary mb-2">Pelatihan</span>
                            <h5 class="mb-2">Training Manajemen Organisasi</h5>
                            <p class="text-muted mb-3">Pelatihan manajemen dan administrasi organisasi untuk meningkatkan efektivitas kerja.</p>
                            <a href="#" class="text-decoration-none">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Structure Section - Daftar Ormawa dengan Slider -->
    <section id="struktur" class="structure-section">
        <div class="container">
            <div class="section-title">
                <h2><span class="text-gradient">Daftar</span> Organisasi Mahasiswa</h2>
                <p>Himpunan Mahasiswa Jurusan di Kampus</p>
            </div>

            <div class="ormawa-slider-container">
                <div class="ormawa-slider-wrapper" id="ormawaSlider">
                    <!-- Slide 1 -->
                    <div class="ormawa-slide">
                        <div class="ormawa-card">
                            <div class="ormawa-logo">
                                <img src="https://via.placeholder.com/150/667eea/ffffff?text=BD" alt="Logo BD">
                            </div>
                            <div class="ormawa-content">
                                <h5 class="ormawa-title">HMJ Bisnis Digital</h5>
                                <p class="ormawa-description">Himpunan mahasiswa yang fokus pada pengembangan kompetensi bisnis digital dan kewirausahaan.</p>
                                <button class="btn btn-primary btn-sm">Selengkapnya</button>
                            </div>
                        </div>

                        <div class="ormawa-card">
                            <div class="ormawa-logo">
                                <img src="https://via.placeholder.com/150/764ba2/ffffff?text=TI" alt="Logo TI">
                            </div>
                            <div class="ormawa-content">
                                <h5 class="ormawa-title">HMJ Teknologi Informasi</h5>
                                <p class="ormawa-description">Wadah mahasiswa IT untuk mengembangkan skill programming, networking, dan teknologi terkini.</p>
                                <button class="btn btn-primary btn-sm">Selengkapnya</button>
                            </div>
                        </div>

                        <div class="ormawa-card">
                            <div class="ormawa-logo">
                                <img src="https://via.placeholder.com/150/00D7FF/ffffff?text=TM" alt="Logo TM">
                            </div>
                            <div class="ormawa-content">
                                <h5 class="ormawa-title">HMJ Teknik Mesin</h5>
                                <p class="ormawa-description">Organisasi mahasiswa teknik mesin yang berfokus pada inovasi dan pengembangan teknologi mesin.</p>
                                <button class="btn btn-primary btn-sm">Selengkapnya</button>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2 -->
                    <div class="ormawa-slide">
                        <div class="ormawa-card">
                            <div class="ormawa-logo">
                                <img src="https://via.placeholder.com/150/667eea/ffffff?text=AK" alt="Logo AK">
                            </div>
                            <div class="ormawa-content">
                                <h5 class="ormawa-title">HMJ Akuntansi</h5>
                                <p class="ormawa-description">Himpunan mahasiswa akuntansi yang fokus pada pengelolaan dan pelaporan informasi keuangan.</p>
                                <button class="btn btn-primary btn-sm">Selengkapnya</button>
                            </div>
                        </div>

                        <div class="ormawa-card">
                            <div class="ormawa-logo">
                                <img src="https://via.placeholder.com/150/764ba2/ffffff?text=MP" alt="Logo MP">
                            </div>
                            <div class="ormawa-content">
                                <h5 class="ormawa-title">HMJ Manajemen Perkantoran</h5>
                                <p class="ormawa-description">Organisasi yang fokus pada pengelolaan administrasi dan operasional perkantoran.</p>
                                <button class="btn btn-primary btn-sm">Selengkapnya</button>
                            </div>
                        </div>

                        <div class="ormawa-card">
                            <div class="ormawa-logo">
                                <img src="https://via.placeholder.com/150/00D7FF/ffffff?text=TE" alt="Logo TE">
                            </div>
                            <div class="ormawa-content">
                                <h5 class="ormawa-title">HMJ Teknik Elektro</h5>
                                <p class="ormawa-description">Wadah mahasiswa elektro untuk pengembangan teknologi kelistrikan dan elektronika.</p>
                                <button class="btn btn-primary btn-sm">Selengkapnya</button>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 3 -->
                    <div class="ormawa-slide">
                        <div class="ormawa-card">
                            <div class="ormawa-logo">
                                <img src="https://via.placeholder.com/150/667eea/ffffff?text=TS" alt="Logo TS">
                            </div>
                            <div class="ormawa-content">
                                <h5 class="ormawa-title">HMJ Teknik Sipil</h5>
                                <p class="ormawa-description">Himpunan mahasiswa teknik sipil yang fokus pada infrastruktur dan konstruksi.</p>
                                <button class="btn btn-primary btn-sm">Selengkapnya</button>
                            </div>
                        </div>

                        <div class="ormawa-card">
                            <div class="ormawa-logo">
                                <img src="https://via.placeholder.com/150/764ba2/ffffff?text=LB" alt="Logo LB">
                            </div>
                            <div class="ormawa-content">
                                <h5 class="ormawa-title">HMJ Layanan Perbankan</h5>
                                <p class="ormawa-description">Organisasi yang mengembangkan kompetensi di bidang perbankan dan layanan keuangan.</p>
                                <button class="btn btn-primary btn-sm">Selengkapnya</button>
                            </div>
                        </div>

                        <div class="ormawa-card">
                            <div class="ormawa-logo">
                                <img src="https://via.placeholder.com/150/00D7FF/ffffff?text=PW" alt="Logo PW">
                            </div>
                            <div class="ormawa-content">
                                <h5 class="ormawa-title">HMJ Pariwisata</h5>
                                <p class="ormawa-description">Wadah mahasiswa pariwisata untuk mengembangkan industri hospitality dan tourism.</p>
                                <button class="btn btn-primary btn-sm">Selengkapnya</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slider Controls -->
            <div class="ormawa-slider-controls">
                <span class="ormawa-arrow" onclick="changeOrmawaSlide(-1)">
                    <i class="bi bi-chevron-left"></i>
                </span>
                <div class="ormawa-dots">
                    <span class="ormawa-dot active" onclick="goToOrmawaSlide(0)"></span>
                    <span class="ormawa-dot" onclick="goToOrmawaSlide(1)"></span>
                    <span class="ormawa-dot" onclick="goToOrmawaSlide(2)"></span>
                </div>
                <span class="ormawa-arrow" onclick="changeOrmawaSlide(1)">
                    <i class="bi bi-chevron-right"></i>
                </span>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="kontak" class="contact-section">
        <div class="container">
            <div class="section-title">
                <h2 class="text-white">Hubungi Kami</h2>
                <p class="text-white-50">Jangan ragu untuk menghubungi kami</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <i class="bi bi-geo-alt contact-icon"></i>
                        <h5>Alamat</h5>
                        <p class="mb-0">Kampus Universitas<br>Jl. Pendidikan No. 123<br>Kota, Provinsi 12345</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <i class="bi bi-telephone contact-icon"></i>
                        <h5>Telepon</h5>
                        <p class="mb-0">(021) 1234-5678<br>+62 812-3456-7890</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <i class="bi bi-envelope contact-icon"></i>
                        <h5>Email</h5>
                        <p class="mb-0">ormawa@kampus.ac.id<br>info@ormawakampus.com</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="contact-form">
                        <h4 class="text-center mb-4">Kirim Pesan</h4>
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" placeholder="Nama Lengkap" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control" placeholder="Subjek" required>
                                </div>
                                <div class="col-12">
                                    <textarea class="form-control" rows="5" placeholder="Pesan Anda" required></textarea>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-light btn-lg px-5">
                                        <i class="bi bi-send me-2"></i>Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-title">Ormawa Kampus</h5>
                    <p class="text-white-50 mb-4">
                        Wadah aspirasi dan kreativitas mahasiswa untuk mengembangkan potensi
                        kepemimpinan dan berkontribusi bagi kampus dan masyarakat.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Tautan Cepat</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#home" class="text-white-50 text-decoration-none">Beranda</a></li>
                        <li class="mb-2"><a href="#profil" class="text-white-50 text-decoration-none">Profil</a></li>
                        <li class="mb-2"><a href="#kegiatan" class="text-white-50 text-decoration-none">Kegiatan</a></li>
                        <li class="mb-2"><a href="#struktur" class="text-white-50 text-decoration-none">Struktur</a></li>
                        <li class="mb-2"><a href="#galeri" class="text-white-50 text-decoration-none">Galeri</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Layanan</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Pendaftaran Anggota</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Proposal Kegiatan</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Peminjaman Fasilitas</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Kerjasama</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Kontak</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt me-2"></i>
                            Jl. Pendidikan No. 123, Kota
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            (021) 1234-5678
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            ormawa@kampus.ac.id
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4 border-secondary">

            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-white-50 mb-0">&copy; 2025 Ormawa Kampus. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="text-white-50 mb-0">Developed with <i class="bi bi-heart-fill text-danger"></i> by Tim IT Ormawa</p>
                </div>
            </div>
        </div>
    </footer>


    <script src="../../../Asset/Js/LandingPage.js"></script>
</body>
</html>
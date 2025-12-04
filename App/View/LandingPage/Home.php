<?php

include('../SuperAdmin/Header.php');
include('../../../Config/ConnectDB.php');
function getOrmawaData($koneksi) {
    $sql = "SELECT id, nama_ormawa, deskripsi, logo, kategori, visi, misi, email, contact_person FROM ormawa ORDER BY nama_ormawa ASC";
    $result = mysqli_query($koneksi, $sql);
    $data = [];
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        
        mysqli_free_result($result);
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
    return $data;
}
function getKegiatanTerbaru($koneksi, $jumlah = 6) {

    $sql = "SELECT e.id, e.nama_event, e.deskripsi, e.tgl_mulai, e.tgl_selesai, e.lokasi, e.gambar, o.nama_ormawa
            FROM event e
            JOIN ormawa o ON e.ormawa_id = o.id
            ORDER BY e.tgl_mulai DESC
            LIMIT ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $jumlah);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);
    } else {
        echo "Error fetching events: " . mysqli_error($koneksi);
    }
    mysqli_stmt_close($stmt);
    return $data;
}

$ormawa_list = getOrmawaData($koneksi);
$kegiatan_list = getKegiatanTerbaru($koneksi, 6); // Ambil 6 kegiatan terbaru
$logo_dir = '../uploads/logos/'; // Path dari Home.php ke folder uploads/logos

// Fungsi untuk membagi array menjadi chunk 3 (untuk slider ormawa)
function array_chunk_3($array) {
    return array_chunk($array, 3, true);
}
$ormawa_chunks = array_chunk_3($ormawa_list);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ormawa Kampus - Organisasi Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css  " rel="stylesheet">
    <link rel="stylesheet" href="../../../Asset/Css/LandingPage.css">
    <link rel="stylesheet" href="../../../Asset/Css/Modal.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css  ">

</head>

<body>
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
                        <a class="nav-link" href="#kegiatan">Kegiatan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#struktur">Ormawa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kontak">Kontak</a>
                    </li>
                    <a href="../SuperAdmin/Login.php"><button class="btn btn-primary">Login</button></a>
                </ul>
            </div>
        </div>
    </nav>

    <section id="home" class="hero-slider">
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

        <div class="slide" style="background-image: url('  https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1920');">
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

        <div class="slide" style="background-image: url('  https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=1920');">
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

        <div class="slider-arrow prev" onclick="changeSlide(-1)">
            <i class="bi bi-chevron-left fs-4"></i>
        </div>
        <div class="slider-arrow next" onclick="changeSlide(1)">
            <i class="bi bi-chevron-right fs-4"></i>
        </div>

        <div class="slider-nav">
            <span class="slider-dot active" onclick="goToSlide(0)"></span>
            <span class="slider-dot" onclick="goToSlide(1)"></span>
            <span class="slider-dot" onclick="goToSlide(2)"></span>
        </div>
    </section>



<!-- SECTION KEGIATAN TERBARU - MODIFIED -->
<section id="kegiatan">
    <div class="container">
        <div class="section-title">
            <h2><span class="text-gradient">Kegiatan</span> Terbaru</h2>
            <p>Berbagai kegiatan yang telah dan akan dilaksanakan</p>
        </div>

        <div class="row g-4">
            <?php if (!empty($kegiatan_list)): ?>
                <?php foreach ($kegiatan_list as $kegiatan): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="activity-card">
                            <?php
                            $gambar_nama_file = $kegiatan['gambar'];
                            $gambar_path = '../../../Uploads/event/' . $gambar_nama_file;
                            $gambar_url = ($gambar_nama_file && file_exists(__DIR__ . '/../../../Uploads/event/' . $gambar_nama_file)) 
                                ? $gambar_path 
                                : 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800';
                            ?>
                            <div class="activity-img" style="background-image: url('<?php echo htmlspecialchars($gambar_url); ?>');">
                                <div class="activity-date">
                                    <?php
                                    $tgl_mulai_obj = new DateTime($kegiatan['tgl_mulai']);
                                    ?>
                                    <span class="day"><?php echo $tgl_mulai_obj->format('d'); ?></span>
                                    <span class="month"><?php echo $tgl_mulai_obj->format('M'); ?></span>
                                </div>
                            </div>
                            <div class="activity-content">
                                <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($kegiatan['nama_ormawa']); ?></span>
                                <h5 class="mb-2"><?php echo htmlspecialchars($kegiatan['nama_event']); ?></h5>
                                <p class="text-muted mb-3"><?php echo htmlspecialchars(substr($kegiatan['deskripsi'], 0, 100)) . (strlen($kegiatan['deskripsi']) > 100 ? '...' : ''); ?></p>
                                
                                <!-- MODIFIED: Button dengan onclick event -->
                                <a href="#" class="text-decoration-none" onclick="event.preventDefault(); showEventDetail({
                                    id: <?php echo $kegiatan['id']; ?>,
                                    nama_event: '<?php echo addslashes($kegiatan['nama_event']); ?>',
                                    deskripsi: '<?php echo addslashes($kegiatan['deskripsi']); ?>',
                                    tgl_mulai: '<?php echo $kegiatan['tgl_mulai']; ?>',
                                    tgl_selesai: '<?php echo $kegiatan['tgl_selesai']; ?>',
                                    lokasi: '<?php echo addslashes($kegiatan['lokasi']); ?>',
                                    gambar: '<?php echo htmlspecialchars($gambar_url); ?>',
                                    nama_ormawa: '<?php echo addslashes($kegiatan['nama_ormawa']); ?>'
                                });">
                                    Lihat Detail <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">Belum ada kegiatan yang diumumkan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- SECTION DAFTAR ORMAWA - MODIFIED -->
<section id="struktur" class="structure-section">
    <div class="container">
        <div class="section-title">
            <h2><span class="text-gradient">Daftar</span> Organisasi Mahasiswa</h2>
            <p>Himpunan Mahasiswa Jurusan di Kampus</p>
        </div>

        <div class="ormawa-slider-container">
            <div class="ormawa-slider-wrapper" id="ormawaSlider">
                <?php if (!empty($ormawa_chunks)): ?>
                    <?php foreach ($ormawa_chunks as $index => $chunk): ?>
                        <div class="ormawa-slide">
                            <?php foreach ($chunk as $ormawa): ?>
                                <div class="ormawa-card">
                                    <div class="ormawa-logo">
                                        <?php
                                        $logo_nama_file = $ormawa['logo'];
                                        $logo_url = ($logo_nama_file && file_exists(__DIR__ . '/../../../uploads/logos/' . $logo_nama_file)) 
                                            ? '../../../uploads/logos/' . $logo_nama_file 
                                            : 'https://via.placeholder.com/150/667eea/ffffff?text=' . substr($ormawa['nama_ormawa'], 0, 2);
                                        ?>
                                        <img src="<?php echo $logo_url; ?>" alt="Logo <?php echo htmlspecialchars($ormawa['nama_ormawa']); ?>">
                                    </div>
                                    <div class="ormawa-content">
                                        <h5 class="ormawa-title"><?php echo htmlspecialchars($ormawa['nama_ormawa']); ?></h5>
                                        <?php
                                        // Add the dynamically generated logo_url to the ormawa data array
                                        $ormawa['logo'] = $logo_url;
                                        // Encode the whole ormawa data object as a JSON string for JavaScript
                                        $ormawa_json = htmlspecialchars(json_encode($ormawa), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <p class="ormawa-description"><?php echo htmlspecialchars(substr($ormawa['deskripsi'], 0, 100)) . (strlen($ormawa['deskripsi']) > 100 ? '...' : ''); ?></p>
                                        
                                        <!-- MODIFIED: Button uses json_encode for safe data transfer -->
                                        <button class="btn btn-primary btn-sm" onclick="showOrmawaDetail(<?php echo $ormawa_json; ?>)">
                                            Selengkapnya
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Placeholder untuk memastikan 3 card per slide -->
                            <?php for($i = count($chunk); $i < 3; $i++): ?>
                                <div class="ormawa-card" style="visibility: hidden; border: none; box-shadow: none;">
                                    <div class="ormawa-logo">
                                        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="">
                                    </div>
                                    <div class="ormawa-content">
                                        <h5 class="ormawa-title">&nbsp;</h5>
                                        <p class="ormawa-description">&nbsp;</p>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ormawa-slide">
                        <div class="col-12">
                            <p class="text-center">Belum ada data Organisasi Mahasiswa.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Slider Controls -->
        <div class="ormawa-slider-controls">
            <span class="ormawa-arrow" onclick="changeOrmawaSlide(-1)">
                <i class="bi bi-chevron-left"></i>
            </span>
            <div class="ormawa-dots">
                <?php for($i = 0; $i < count($ormawa_chunks); $i++): ?>
                    <span class="ormawa-dot <?php echo $i === 0 ? 'active' : ''; ?>" onclick="goToOrmawaSlide(<?php echo $i; ?>)"></span>
                <?php endfor; ?>
            </div>
            <span class="ormawa-arrow" onclick="changeOrmawaSlide(1)">
                <i class="bi bi-chevron-right"></i>
            </span>
        </div>
    </div>
</section>


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


        </div>
    </section>

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

    <script>
        const totalOrmawaSlides = <?php echo count($ormawa_chunks); ?>;
    </script>
    <script src="../../../Asset/Js/LandingPage.js"></script>
    <script src="../../../Asset/Js/Modal.js"></script>
</body>
</html>
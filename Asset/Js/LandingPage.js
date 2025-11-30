// Hero Slider functionality
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.slider-dot');
let slideInterval;

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));

    if (index >= slides.length) currentSlide = 0;
    else if (index < 0) currentSlide = slides.length - 1;
    else currentSlide = index;

    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

function changeSlide(direction) {
    clearInterval(slideInterval);
    showSlide(currentSlide + direction);
    startAutoSlide();
}

function goToSlide(index) {
    clearInterval(slideInterval);
    showSlide(index);
    startAutoSlide();
}

function autoSlide() {
    currentSlide++;
    showSlide(currentSlide);
}

function startAutoSlide() {
    slideInterval = setInterval(autoSlide, 5000);
}

// Initialize hero slider
showSlide(0);
startAutoSlide();

// Ormawa Slider functionality
let currentOrmawaSlide = 0;
const ormawaSlides = document.querySelectorAll('.ormawa-slide');
const ormawaDots = document.querySelectorAll('.ormawa-dot');
const ormawaSlider = document.getElementById('ormawaSlider');

function showOrmawaSlide(index) {
    ormawaDots.forEach(dot => dot.classList.remove('active'));

    if (index >= ormawaSlides.length) currentOrmawaSlide = 0;
    else if (index < 0) currentOrmawaSlide = ormawaSlides.length - 1;
    else currentOrmawaSlide = index;

    ormawaSlider.style.transform = `translateX(-${currentOrmawaSlide * 100}%)`;
    ormawaDots[currentOrmawaSlide].classList.add('active');
}

function changeOrmawaSlide(direction) {
    showOrmawaSlide(currentOrmawaSlide + direction);
}

function goToOrmawaSlide(index) {
    showOrmawaSlide(index);
}

// Initialize ormawa slider
showOrmawaSlide(0);

// Smooth scroll for navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
    } else {
        navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    }
});

// Form submission
document.querySelector('.contact-form form').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Terima kasih! Pesan Anda telah dikirim.');
    this.reset();
});

// Inisialisasi slider
let currentOrmawaSlideJs = 0;
// const totalOrmawaSlides = <?php echo count($ormawa_chunks); ?>; // This is now defined in the HTML
let slideIntervalJs; // Variabel untuk menyimpan referensi interval auto-slide

// Fungsi untuk menampilkan slide tertentu menggunakan transform
function showOrmawaSlideJs(n) {
    const slides = document.querySelectorAll('.ormawa-slide'); // Ambil semua elemen slide
    const dots = document.querySelectorAll('.ormawa-dot');    // Ambil semua dot indikator

    // Validasi jumlah slide
    if (slides.length === 0) return;

    // Perbarui currentOrmawaSlide dan pastikan tetap dalam batas
    if (n >= slides.length) {
        currentOrmawaSlideJs = 0;
    } else if (n < 0) {
        currentOrmawaSlideJs = slides.length - 1;
    } else {
        currentOrmawaSlideJs = n;
    }

    // Ambil elemen wrapper slider
    const wrapper = document.getElementById('ormawaSlider');

    // Hapus kelas 'active' dari semua dot
    dots.forEach(dot => dot.classList.remove('active'));

    // Tambahkan kelas 'active' ke dot yang sesuai dengan slide saat ini
    if (dots[currentOrmawaSlideJs]) {
        dots[currentOrmawaSlideJs].classList.add('active');
    }

    // Gunakan transform untuk menggeser wrapper ke slide yang benar
    if (wrapper) {
        wrapper.style.transform = `translateX(-${currentOrmawaSlideJs * 100}%)`;
    }
}

// Fungsi untuk menggeser slide ke kiri atau kanan
function changeOrmawaSlideJs(n) {
    showOrmawaSlideJs(currentOrmawaSlideJs + n);
}

// Fungsi untuk langsung ke slide tertentu (berdasarkan dot yang diklik)
function goToOrmawaSlideJs(n) {
    showOrmawaSlideJs(n);
}

// Fungsi untuk memulai auto-slide
function startAutoSlideJs() {
    if (totalOrmawaSlides <= 1) return; // Jangan auto-slide jika hanya ada satu slide
    // Hentikan interval sebelumnya jika ada (untuk mencegah banyak interval)
    stopAutoSlideJs();
    // Buat interval baru, panggil changeOrmawaSlide(1) setiap 5 detik (5000 ms)
    slideIntervalJs = setInterval(() => {
        changeOrmawaSlideJs(1); // Geser ke kanan
    }, 5000); // 5000 milidetik = 5 detik
}

// Fungsi untuk menghentikan auto-slide
function stopAutoSlideJs() {
    if (slideIntervalJs) {
        clearInterval(slideIntervalJs);
        slideIntervalJs = null;
    }
}

// Fungsi untuk melanjutkan auto-slide (kebalikan dari stop)
function resumeAutoSlideJs() {
    if (totalOrmawaSlides > 1) { // Hanya lanjutkan jika lebih dari satu slide
        startAutoSlideJs();
    }
}

// Jalankan showOrmawaSlide(0) saat halaman dimuat untuk menampilkan slide pertama
document.addEventListener('DOMContentLoaded', function() {
    showOrmawaSlideJs(currentOrmawaSlideJs);
    startAutoSlideJs(); // Mulai auto-slide saat halaman selesai dimuat

    // Tambahkan event listener untuk menjeda saat hover
    const sliderContainer = document.querySelector('.ormawa-slider-container');
    if (sliderContainer) {
        sliderContainer.addEventListener('mouseenter', stopAutoSlideJs);
        sliderContainer.addEventListener('mouseleave', resumeAutoSlideJs);
    }
});

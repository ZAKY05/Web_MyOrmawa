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
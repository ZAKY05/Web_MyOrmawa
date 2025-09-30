<div class="container">
    <div class="row align-items-center">
        <div class="col-lg-6" data-aos="fade-right">
            <div class="hero-content">
                <h1 class="display-3 fw-bold mb-4">
                    <span class="text-gradient-primary">Revolusi Digital</span><br>
                    <span class="text-dark">TESSSSSSSSSSSSSSSS</span>
                </h1>
                <p class="lead text-muted mb-5 pe-lg-5">
                    AAAAAAAA
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
                        <i class="bi bi-<?php echo $feature['icon']; ?> <?php echo $feature['color']; ?> me-2"></i>
                        <small>TES</small>
                    </div>
                  
                </div>
            </div>
        </div>
        
        <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
            <div class="hero-image text-center position-relative">
                <div class="floating-badge badge-primary">QR Code Ready</div>
                <div class="phones-mockup">
                    <?php if (file_exists($hero->getHeroImage())): ?>
                        <img src="<?php echo $hero->getHeroImage(); ?>" alt="MyOrmawa Mobile App" 
                                class="img-fluid" style="max-height: 600px;">
                    <?php else: ?>
                        <div class="placeholder-mockup bg-light rounded-4 d-flex align-items-center justify-content-center" 
                                style="height: 600px; max-width: 400px; margin: 0 auto;">
                            <div class="text-center text-muted">
                                <i class="bi bi-phone display-1 mb-3"></i>
                                <h5>Mobile App Mockup</h5>
                                <p>Letakkan gambar hero-mockup.png di assets/img/</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="floating-badge badge-secondary">Digital Solution</div>
            </div>
        </div>
    </div>
</div>
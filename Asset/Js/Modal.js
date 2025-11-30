// ==========================================
// MODAL & LAZY LOADING SYSTEM
// Complete Natural Blue Design
// ==========================================

(function() {
    'use strict';

    // ==========================================
    // 1. LAZY LOADING SYSTEM
    // ==========================================
    
    const lazyLoadBackgrounds = () => {
        const backgrounds = document.querySelectorAll('[data-bg]');
        const bgObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    const bgUrl = element.getAttribute('data-bg');
                    
                    const tempImg = new Image();
                    tempImg.onload = () => {
                        element.style.backgroundImage = `url('${bgUrl}')`;
                        element.classList.add('bg-loaded');
                        element.removeAttribute('data-bg');
                    };
                    tempImg.onerror = () => {
                        const fallback = element.getAttribute('data-bg-fallback');
                        if (fallback) {
                            element.style.backgroundImage = `url('${fallback}')`;
                        }
                        element.classList.add('bg-loaded');
                    };
                    tempImg.src = bgUrl;
                    
                    observer.unobserve(element);
                }
            });
        }, {
            rootMargin: '50px'
        });

        backgrounds.forEach(bg => bgObserver.observe(bg));
    };

    const lazyLoadImages = () => {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.getAttribute('data-src');
                    
                    const tempImg = new Image();
                    tempImg.onload = () => {
                        img.src = src;
                        img.classList.add('loaded');
                        img.removeAttribute('data-src');
                    };
                    tempImg.onerror = () => {
                        const fallback = img.getAttribute('data-fallback');
                        if (fallback) {
                            img.src = fallback;
                        }
                        img.classList.add('loaded');
                    };
                    tempImg.src = src;
                    
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px'
        });

        images.forEach(img => imageObserver.observe(img));
    };

    const animateCards = () => {
        const cards = document.querySelectorAll('.activity-card, .ormawa-card');
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('loaded');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        cards.forEach(card => cardObserver.observe(card));
    };

    // ==========================================
    // 2. MODAL FUNCTIONS
    // ==========================================

    // Format date helper
    window.formatDate = (dateString) => {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', options);
    };

    // Show Event Detail Modal
    window.showEventDetail = (eventData) => {
        const modalHTML = `
            <div class="modal-overlay" id="eventModal" onclick="closeModal('eventModal')">
                <div class="modal-container" onclick="event.stopPropagation()">
                    <div class="modal-header">
                        <img src="${eventData.gambar}" 
                             alt="${eventData.nama_event}" 
                             class="modal-header-image"
                             loading="lazy"
                             onload="this.style.opacity='1'"
                             style="opacity: 0; transition: opacity 0.3s ease;"
                             onerror="this.src='https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800'; this.style.objectFit='cover';">
                        <button class="modal-close-btn" onclick="closeModal('eventModal')">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <span class="modal-badge">${eventData.nama_ormawa}</span>
                        <h2 class="modal-title">${eventData.nama_event}</h2>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-calendar-event"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Tanggal Mulai</h6>
                                    <p>${formatDate(eventData.tgl_mulai)}</p>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Tanggal Selesai</h6>
                                    <p>${formatDate(eventData.tgl_selesai)}</p>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Lokasi</h6>
                                    <p>${eventData.lokasi}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="description-section">
                            <h5><i class="bi bi-file-text"></i> Deskripsi Kegiatan</h5>
                            <p>${eventData.deskripsi}</p>
                        </div>
                        
       
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        setTimeout(() => {
            document.getElementById('eventModal').classList.add('active');
            document.querySelector('#eventModal .modal-container').classList.add('active');
        }, 10);
        
        document.body.style.overflow = 'hidden';
    };

    // Show Ormawa Detail Modal
    window.showOrmawaDetail = (ormawaData) => {
        const modalHTML = `
            <div class="modal-overlay" id="ormawaModal" onclick="closeModal('ormawaModal')">
                <div class="modal-container" onclick="event.stopPropagation()">
                    <div class="modal-header">
                        <div class="modal-logo-container">
                            <img src="${ormawaData.logo}" 
                                 alt="${ormawaData.nama_ormawa}" 
                                 class="modal-logo"
                                 loading="lazy"
                                 onload="this.style.opacity='1'"
                                 style="opacity: 0; transition: opacity 0.3s ease;"
                                 onerror="this.src='https://via.placeholder.com/150/667eea/ffffff?text=${ormawaData.nama_ormawa.substring(0, 2)}'; this.style.objectFit='contain';">
                        </div>
                        <button class="modal-close-btn" onclick="closeModal('ormawaModal')">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h2 class="modal-title">${ormawaData.nama_ormawa}</h2>
                        
                        <div class="description-section" style="margin-top: 20px; padding-top: 0; border-top: none;">
                            <h5><i class="bi bi-info-circle"></i> Tentang Organisasi</h5>
                            <p>${ormawaData.deskripsi}</p>
                        </div>
                        
                        <div class="description-section">
                            <h5><i class="bi bi-bullseye"></i> Visi</h5>
                            <p>${ormawaData.visi}</p>
                        </div>
                        
                        <div class="description-section">
                            <h5><i class="bi bi-list-check"></i> Misi</h5>
                            <p>${ormawaData.misi}</p>
                        </div>
                        
                        <div class="contact-section-modal">
                            <h5><i class="bi bi-telephone"></i> Informasi Kontak</h5>
                            <div class="contact-item">
                                <i class="bi bi-envelope"></i>
                                <span>${ormawaData.email}</span>
                            </div>
                            <div class="contact-item">
                                <i class="bi bi-person"></i>
                                <span>${ormawaData.contact_person}</span>
                            </div>
                        </div>
                        
                        
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        setTimeout(() => {
            document.getElementById('ormawaModal').classList.add('active');
            document.querySelector('#ormawaModal .modal-container').classList.add('active');
        }, 10);
        
        document.body.style.overflow = 'hidden';
    };

    // Close Modal
    window.closeModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            const container = modal.querySelector('.modal-container');
            container.classList.remove('active');
            modal.classList.remove('active');
            
            setTimeout(() => {
                modal.remove();
                document.body.style.overflow = 'auto';
            }, 300);
        }
    };

    // ==========================================
    // 3. ACTION FUNCTIONS
    // ==========================================

    window.registerEvent = (eventId) => {
        alert('Pendaftaran untuk event ID: ' + eventId + ' akan segera dibuka!');
        // Implement your registration logic here
    };

    window.shareEvent = (eventId) => {
        if (navigator.share) {
            navigator.share({
                title: 'Event Ormawa',
                text: 'Lihat event menarik ini!',
                url: window.location.href + '?event=' + eventId
            }).catch(err => console.log('Error sharing:', err));
        } else {
            // Fallback: copy to clipboard
            const url = window.location.href + '?event=' + eventId;
            navigator.clipboard.writeText(url).then(() => {
                alert('Link event berhasil disalin!');
            }).catch(() => {
                alert('Link: ' + url);
            });
        }
    };

    window.joinOrmawa = (ormawaId) => {
        alert('Formulir pendaftaran untuk Ormawa ID: ' + ormawaId + ' akan segera dibuka!');
        // Implement your join logic here
    };

    window.viewOrmawaEvents = (ormawaId) => {
        alert('Menampilkan kegiatan dari Ormawa ID: ' + ormawaId);
        // Navigate to events page or filter events
        // window.location.href = '/kegiatan?ormawa=' + ormawaId;
    };

    // ==========================================
    // 4. KEYBOARD & ACCESSIBILITY
    // ==========================================

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const eventModal = document.getElementById('eventModal');
            const ormawaModal = document.getElementById('ormawaModal');
            
            if (eventModal) closeModal('eventModal');
            if (ormawaModal) closeModal('ormawaModal');
        }
    });

    // ==========================================
    // 5. RIPPLE EFFECT ON CARDS
    // ==========================================

    const initRippleEffect = () => {
        const cards = document.querySelectorAll('.activity-card, .ormawa-card');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', function(e) {
                const ripple = document.createElement('div');
                ripple.style.position = 'absolute';
                ripple.style.width = '20px';
                ripple.style.height = '20px';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(102, 126, 234, 0.3)';
                ripple.style.pointerEvents = 'none';
                ripple.style.animation = 'ripple 0.6s ease-out';
                
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.transform = 'translate(-50%, -50%)';
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    };

    // Add ripple animation
    const rippleStyle = document.createElement('style');
    rippleStyle.textContent = `
        @keyframes ripple {
            from {
                width: 0;
                height: 0;
                opacity: 1;
            }
            to {
                width: 300px;
                height: 300px;
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(rippleStyle);

    // ==========================================
    // 6. PERFORMANCE OPTIMIZATION
    // ==========================================

    const optimizePerformance = () => {
        // Debounce scroll events
        let scrollTimer;
        window.addEventListener('scroll', () => {
            if (scrollTimer) {
                clearTimeout(scrollTimer);
            }
            scrollTimer = setTimeout(() => {
                document.body.classList.remove('is-scrolling');
            }, 100);
            document.body.classList.add('is-scrolling');
        });

        // Reduce motion for users who prefer it
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.body.classList.add('reduce-motion');
        }
    };

    // ==========================================
    // 7. INITIALIZE EVERYTHING
    // ==========================================

    const init = () => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }

        // Initialize all features
        lazyLoadImages();
        lazyLoadBackgrounds();
        animateCards();
        initRippleEffect();
        optimizePerformance();

        // Add loaded class to body
        setTimeout(() => {
            document.body.classList.add('page-loaded');
        }, 100);

        console.log('✅ Natural Blue Design - Loaded Successfully');
    };

    // Start initialization
    init();

    // ==========================================
    // 8. UTILITY FUNCTIONS
    // ==========================================

    window.debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    window.throttle = (func, limit) => {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

})();
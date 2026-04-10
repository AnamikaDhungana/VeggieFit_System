

(function() {
    'use strict';

    //  CUSTOM CURSOR //
    function initCustomCursor() {
        const cursorDot = document.querySelector('.cursor-dot');
        const cursorOutline = document.querySelector('.cursor-outline');

        if (!cursorDot || !cursorOutline || window.innerWidth <= 768) return;

        window.addEventListener('mousemove', (e) => {
            cursorDot.style.left = e.clientX + 'px';
            cursorDot.style.top = e.clientY + 'px';
            cursorOutline.style.left = e.clientX + 'px';
            cursorOutline.style.top = e.clientY + 'px';
        });

        // Hover effect
        const interactiveElements = document.querySelectorAll(
            'a, button, .value-card, .step-card, .gallery-item, .primary-btn, .cta-button, .cta-button-secondary'
        );
        
        interactiveElements.forEach(el => {
            el.addEventListener('mouseenter', () => {
                cursorOutline.style.transform = 'translate(-50%, -50%) scale(1.5)';
                cursorOutline.style.backgroundColor = 'rgba(45, 106, 79, 0.1)';
                cursorOutline.style.borderColor = 'transparent';
            });

            el.addEventListener('mouseleave', () => {
                cursorOutline.style.transform = 'translate(-50%, -50%) scale(1)';
                cursorOutline.style.backgroundColor = 'transparent';
                cursorOutline.style.borderColor = 'rgba(45, 106, 79, 0.3)';
            });
        });
    }

    // SCROLL REVEAL //
    function initScrollReveal() {
        const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
        
        function checkReveal() {
            const windowHeight = window.innerHeight;
            revealElements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                if (elementTop < windowHeight - 100) {
                    element.classList.add('active');
                }
            });
        }

        window.addEventListener('scroll', checkReveal);
        window.addEventListener('load', checkReveal);
        checkReveal();
    }

    //  DELAY ANIMATIONS //
    function initDelayAnimations() {
        document.querySelectorAll('[data-delay]').forEach(element => {
            element.style.transitionDelay = element.getAttribute('data-delay') + 'ms';
        });
    }

    //  GALLERY LIGHTBOX //
    function initGalleryLightbox() {
        const galleryItems = document.querySelectorAll('.gallery-item');
        if (!galleryItems.length) return;

        const lightbox = document.createElement('div');
        lightbox.className = 'lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-content">
                <span class="lightbox-close">&times;</span>
                <img class="lightbox-image" src="" alt="">
                <div class="lightbox-caption"></div>
                <button class="lightbox-prev">‹</button>
                <button class="lightbox-next">›</button>
            </div>
        `;
        document.body.appendChild(lightbox);

        // Add lightbox styles
        const style = document.createElement('style');
        style.textContent = `
            .lightbox {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.95);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            .lightbox.active {
                opacity: 1;
                visibility: visible;
            }
            .lightbox-content {
                position: relative;
                max-width: 90%;
                max-height: 90%;
            }
            .lightbox-image {
                max-width: 100%;
                max-height: 80vh;
                border-radius: 10px;
            }
            .lightbox-caption {
                position: absolute;
                bottom: -30px;
                left: 0;
                width: 100%;
                text-align: center;
                color: white;
                font-family: 'Times New Roman', Times, serif;
            }
            .lightbox-close {
                position: absolute;
                top: -40px;
                right: 0;
                color: white;
                font-size: 40px;
                cursor: pointer;
                font-family: 'Times New Roman', Times, serif;
            }
            .lightbox-prev,
            .lightbox-next {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(255,255,255,0.1);
                color: white;
                border: none;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                font-size: 30px;
                cursor: pointer;
                font-family: 'Times New Roman', Times, serif;
            }
            .lightbox-prev { left: -70px; }
            .lightbox-next { right: -70px; }
            @media (max-width: 768px) {
                .lightbox-prev { left: 10px; }
                .lightbox-next { right: 10px; }
            }
        `;
        document.head.appendChild(style);

        const lightboxImage = lightbox.querySelector('.lightbox-image');
        const lightboxCaption = lightbox.querySelector('.lightbox-caption');
        const images = Array.from(galleryItems).map(item => ({
            src: item.querySelector('img').src,
            alt: item.querySelector('img').alt,
            caption: item.querySelector('.gallery-tag')?.textContent || 'Weight loss meal'
        }));

        let currentIndex = 0;

        galleryItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                currentIndex = index;
                updateLightbox();
                lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });

        function updateLightbox() {
            lightboxImage.src = images[currentIndex].src;
            lightboxImage.alt = images[currentIndex].alt;
            lightboxCaption.textContent = images[currentIndex].caption;
        }

        lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
        lightbox.querySelector('.lightbox-prev').addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            updateLightbox();
        });
        lightbox.querySelector('.lightbox-next').addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % images.length;
            updateLightbox();
        });

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });

        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') lightbox.querySelector('.lightbox-prev').click();
            if (e.key === 'ArrowRight') lightbox.querySelector('.lightbox-next').click();
        });
    }

    //  FIX BUTTON CLICK ISSUE //
    function fixButtons() {
        const ctaButtons = document.querySelectorAll('.cta-button, .cta-button-secondary');
        
        ctaButtons.forEach(button => {
            // Ensure cursor is pointer
            button.style.cursor = 'pointer';
            button.style.pointerEvents = 'auto';
            button.style.position = 'relative';
            button.style.zIndex = '100';
            
            // Add direct click handler as backup
            button.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && href !== '#') {
                    window.location.href = href;
                    return true;
                }
            });
        });
        
        console.log('Buttons fixed - ready to click!');
    }

    // INITIALIZE //
    function init() {
        initCustomCursor();
        initScrollReveal();
        initDelayAnimations();
        initGalleryLightbox();
        fixButtons(); 
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
/**
 * Image Slider functionality with vanilla JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('heroSlider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.slide');
    const dots = slider.querySelectorAll('.slider-dot');
    
    if (slides.length <= 1) return; // Don't initialize if only one slide

    let currentSlide = 0;
    let slideInterval;
    let isTransitioning = false;

    // Get slider timer from WordPress (passed from PHP)
    const sliderTimer = window.sliderTimer || 5000;

    // Initialize slider
    function initSlider() {
        // Set up auto-advance
        startSlideShow();
        
        // Add click events to dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                if (!isTransitioning) {
                    goToSlide(index);
                }
            });
        });

        // Add touch/swipe support
        addTouchSupport();
        
        // Pause on hover
        slider.addEventListener('mouseenter', pauseSlideShow);
        slider.addEventListener('mouseleave', startSlideShow);
    }

    // Go to specific slide
    function goToSlide(slideIndex) {
        if (slideIndex === currentSlide || isTransitioning) return;
        
        isTransitioning = true;
        
        // Remove active class from current slide and dot
        slides[currentSlide].classList.remove('active');
        if (dots[currentSlide]) {
            dots[currentSlide].classList.remove('active');
        }
        
        // Add active class to new slide and dot
        currentSlide = slideIndex;
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add('active');
        }
        
        // Reset transition flag after animation completes
        setTimeout(() => {
            isTransitioning = false;
        }, 1000); // Match CSS transition duration
    }

    // Go to next slide
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        goToSlide(next);
    }

    // Go to previous slide
    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        goToSlide(prev);
    }

    // Start slideshow
    function startSlideShow() {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, sliderTimer);
    }

    // Pause slideshow
    function pauseSlideShow() {
        clearInterval(slideInterval);
    }

    // Add keyboard navigation
    function addKeyboardSupport() {
        document.addEventListener('keydown', function(e) {
            if (!slider.matches(':hover')) return;
            
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    pauseSlideShow();
                    prevSlide();
                    setTimeout(startSlideShow, 3000); // Resume after 3 seconds
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    pauseSlideShow();
                    nextSlide();
                    setTimeout(startSlideShow, 3000); // Resume after 3 seconds
                    break;
                case 'Escape':
                    pauseSlideShow();
                    break;
            }
        });
    }

    // Add touch/swipe support
    function addTouchSupport() {
        let startX = 0;
        let startY = 0;
        let endX = 0;
        let endY = 0;
        const minSwipeDistance = 50;

        slider.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            pauseSlideShow();
        }, { passive: true });

        slider.addEventListener('touchmove', function(e) {
            // Prevent scrolling while swiping
            if (Math.abs(e.touches[0].clientX - startX) > Math.abs(e.touches[0].clientY - startY)) {
                e.preventDefault();
            }
        }, { passive: false });

        slider.addEventListener('touchend', function(e) {
            endX = e.changedTouches[0].clientX;
            endY = e.changedTouches[0].clientY;
            
            const deltaX = endX - startX;
            const deltaY = endY - startY;
            
            // Check if horizontal swipe is longer than vertical
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
                if (deltaX > 0) {
                    // Swipe right - go to previous slide
                    prevSlide();
                } else {
                    // Swipe left - go to next slide
                    nextSlide();
                }
            }
            
            // Resume slideshow after 3 seconds
            setTimeout(startSlideShow, 3000);
        }, { passive: true });
    }

    // Handle visibility change (pause when tab is not active)
    function handleVisibilityChange() {
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                pauseSlideShow();
            } else {
                startSlideShow();
            }
        });
    }

    // Preload images for better performance
    function preloadImages() {
        slides.forEach(slide => {
            const bgImage = slide.style.backgroundImage;
            if (bgImage) {
                const imageUrl = bgImage.replace(/url\(['"]?(.*?)['"]?\)/i, '$1');
                const img = new Image();
                img.src = imageUrl;
            }
        });
    }

    // Add smooth scroll to CTA button if it links to an anchor
    function addSmoothScroll() {
        const ctaButton = slider.querySelector('.cta-button');
        if (ctaButton && ctaButton.getAttribute('href').startsWith('#')) {
            ctaButton.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        }
    }

    // Initialize all functionality
    function init() {
        preloadImages();
        initSlider();
        addKeyboardSupport();
        handleVisibilityChange();
        addSmoothScroll();
    }

    // Start everything
    init();

    // Expose methods for external use
    window.heroSlider = {
        next: nextSlide,
        prev: prevSlide,
        goTo: goToSlide,
        pause: pauseSlideShow,
        play: startSlideShow,
        getCurrentSlide: () => currentSlide,
        getTotalSlides: () => slides.length
    };
});
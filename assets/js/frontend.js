/**
 * Custom Image Gallery Frontend JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeGalleries();
});

function initializeGalleries() {
    const galleries = document.querySelectorAll('.custom-gallery');
    
    galleries.forEach(gallery => {
        initializeGallery(gallery);
    });
}

function initializeGallery(gallery) {
    const galleryItems = gallery.querySelectorAll('.gallery-item');
    const galleryId = gallery.getAttribute('data-gallery-id') || 'default';
    
    if (galleryItems.length === 0) return;
    
    // Create modal if it doesn't exist
    let modal = document.querySelector('.gallery-modal');
    if (!modal) {
        modal = createModal();
        document.body.appendChild(modal);
    }
    
    // Add click events to gallery items
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(gallery, index);
        });
        
        // Add keyboard support
        item.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openModal(gallery, index);
            }
        });
        
        // Make items focusable
        item.setAttribute('tabindex', '0');
        item.setAttribute('role', 'button');
        item.setAttribute('aria-label', `Open image ${index + 1} in gallery`);
    });
    
    // Add lazy loading
    addLazyLoading(gallery);
    
    // Add touch gestures for mobile
    addTouchGestures(gallery);
}

function createModal() {
    const modal = document.createElement('div');
    modal.className = 'gallery-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-label', 'Image gallery');
    
    modal.innerHTML = `
        <div class="modal-content">
            <button class="modal-close" aria-label="Close gallery">&times;</button>
            <div class="modal-counter"></div>
            <img src="" alt="" />
            <button class="modal-nav modal-prev" aria-label="Previous image">&#8249;</button>
            <button class="modal-nav modal-next" aria-label="Next image">&#8250;</button>
        </div>
    `;
    
    // Add event listeners
    const closeBtn = modal.querySelector('.modal-close');
    const prevBtn = modal.querySelector('.modal-prev');
    const nextBtn = modal.querySelector('.modal-next');
    
    closeBtn.addEventListener('click', closeModal);
    prevBtn.addEventListener('click', () => navigateModal(-1));
    nextBtn.addEventListener('click', () => navigateModal(1));
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (!modal.style.display || modal.style.display === 'none') return;
        
        switch(e.key) {
            case 'Escape':
                e.preventDefault();
                closeModal();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                navigateModal(-1);
                break;
            case 'ArrowRight':
                e.preventDefault();
                navigateModal(1);
                break;
            case 'Home':
                e.preventDefault();
                goToModalImage(0);
                break;
            case 'End':
                e.preventDefault();
                if (modal.currentGallery) {
                    const images = modal.currentGallery.querySelectorAll('.gallery-item');
                    goToModalImage(images.length - 1);
                }
                break;
        }
    });
    
    // Touch gestures for modal
    addModalTouchGestures(modal);
    
    return modal;
}

function openModal(gallery, imageIndex) {
    const modal = document.querySelector('.gallery-modal');
    const modalImg = modal.querySelector('img');
    const modalCounter = modal.querySelector('.modal-counter');
    const galleryItems = gallery.querySelectorAll('.gallery-item img');
    
    if (!galleryItems[imageIndex]) return;
    
    // Set current gallery and image index
    modal.currentGallery = gallery;
    modal.currentIndex = imageIndex;
    modal.originalActiveElement = document.activeElement;
    
    // Load image
    const img = galleryItems[imageIndex];
    modalImg.src = img.src;
    modalImg.alt = img.alt || `Gallery image ${imageIndex + 1}`;
    
    // Update counter
    modalCounter.textContent = `${imageIndex + 1} / ${galleryItems.length}`;
    
    // Show modal with animation
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
    
    // Focus management
    modal.querySelector('.modal-close').focus();
    
    // Update navigation buttons visibility
    updateNavigationButtons(modal, galleryItems.length);
    
    // Preload adjacent images
    preloadAdjacentImages(galleryItems, imageIndex);
    
    // Add loading state
    modalImg.classList.add('loading');
    modalImg.onload = function() {
        this.classList.remove('loading');
    };
}

function closeModal() {
    const modal = document.querySelector('.gallery-modal');
    
    modal.classList.remove('active');
    
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Return focus to the original gallery item
        if (modal.currentGallery && modal.currentIndex !== undefined) {
            const originalItem = modal.currentGallery.querySelectorAll('.gallery-item')[modal.currentIndex];
            if (originalItem) {
                originalItem.focus();
            }
        } else if (modal.originalActiveElement) {
            modal.originalActiveElement.focus();
        }
    }, 300);
}

function navigateModal(direction) {
    const modal = document.querySelector('.gallery-modal');
    if (!modal.currentGallery) return;
    
    const galleryItems = modal.currentGallery.querySelectorAll('.gallery-item img');
    const totalImages = galleryItems.length;
    
    let newIndex = modal.currentIndex + direction;
    
    // Loop around
    if (newIndex < 0) {
        newIndex = totalImages - 1;
    } else if (newIndex >= totalImages) {
        newIndex = 0;
    }
    
    goToModalImage(newIndex);
}

function goToModalImage(imageIndex) {
    const modal = document.querySelector('.gallery-modal');
    if (!modal.currentGallery) return;
    
    const modalImg = modal.querySelector('img');
    const modalCounter = modal.querySelector('.modal-counter');
    const galleryItems = modal.currentGallery.querySelectorAll('.gallery-item img');
    
    if (!galleryItems[imageIndex]) return;
    
    const newImg = galleryItems[imageIndex];
    
    // Add loading state
    modalImg.classList.add('loading');
    
    // Update image
    modalImg.src = newImg.src;
    modalImg.alt = newImg.alt || `Gallery image ${imageIndex + 1}`;
    modal.currentIndex = imageIndex;
    
    // Update counter
    modalCounter.textContent = `${imageIndex + 1} / ${galleryItems.length}`;
    
    // Remove loading state when image loads
    modalImg.onload = function() {
        this.classList.remove('loading');
    };
    
    // Preload adjacent images
    preloadAdjacentImages(galleryItems, imageIndex);
}

function updateNavigationButtons(modal, totalImages) {
    const prevBtn = modal.querySelector('.modal-prev');
    const nextBtn = modal.querySelector('.modal-next');
    
    if (totalImages <= 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
    } else {
        prevBtn.style.display = 'flex';
        nextBtn.style.display = 'flex';
    }
}

function preloadAdjacentImages(galleryItems, currentIndex) {
    const totalImages = galleryItems.length;
    const preloadIndices = [
        (currentIndex - 1 + totalImages) % totalImages,
        (currentIndex + 1) % totalImages
    ];
    
    preloadIndices.forEach(index => {
        if (index !== currentIndex && galleryItems[index]) {
            const img = new Image();
            img.src = galleryItems[index].src;
        }
    });
}

function addLazyLoading(gallery) {
    const images = gallery.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });
        
        images.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
            img.classList.add('loaded');
        });
    }
}

function addTouchGestures(gallery) {
    const galleryItems = gallery.querySelectorAll('.gallery-item');
    
    galleryItems.forEach(item => {
        let touchStartX = 0;
        let touchStartY = 0;
        let touchEndX = 0;
        let touchEndY = 0;
        
        item.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });
        
        item.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            touchEndY = e.changedTouches[0].screenY;
            
            // Simple tap detection
            const deltaX = Math.abs(touchEndX - touchStartX);
            const deltaY = Math.abs(touchEndY - touchStartY);
            
            if (deltaX < 10 && deltaY < 10) {
                // It's a tap, trigger click
                const index = Array.from(gallery.querySelectorAll('.gallery-item')).indexOf(item);
                openModal(gallery, index);
            }
        }, { passive: true });
    });
}

function addModalTouchGestures(modal) {
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;
    const minSwipeDistance = 50;
    
    const modalContent = modal.querySelector('.modal-content');
    
    modalContent.addEventListener('touchstart', function(e) {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
    }, { passive: true });
    
    modalContent.addEventListener('touchmove', function(e) {
        // Prevent scrolling while swiping
        const deltaX = Math.abs(e.touches[0].clientX - touchStartX);
        const deltaY = Math.abs(e.touches[0].clientY - touchStartY);
        
        if (deltaX > deltaY) {
            e.preventDefault();
        }
    }, { passive: false });
    
    modalContent.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].clientX;
        touchEndY = e.changedTouches[0].clientY;
        
        const deltaX = touchEndX - touchStartX;
        const deltaY = touchEndY - touchStartY;
        
        // Check if horizontal swipe is longer than vertical
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
            if (deltaX > 0) {
                // Swipe right - go to previous image
                navigateModal(-1);
            } else {
                // Swipe left - go to next image
                navigateModal(1);
            }
        } else if (Math.abs(deltaY) > minSwipeDistance && Math.abs(deltaY) > Math.abs(deltaX)) {
            if (deltaY > 0) {
                // Swipe down - close modal
                closeModal();
            }
        }
    }, { passive: true });
}

// Utility functions for external use
window.CustomGallery = {
    initialize: initializeGalleries,
    openModal: openModal,
    closeModal: closeModal,
    navigateModal: navigateModal,
    
    // Create gallery programmatically
    createGallery: function(container, images, options = {}) {
        const defaults = {
            columns: 3,
            showOverlay: true,
            lazyLoad: true,
            lightbox: true
        };
        
        const settings = Object.assign({}, defaults, options);
        
        // Create gallery structure
        const gallery = document.createElement('div');
        gallery.className = 'custom-gallery';
        gallery.setAttribute('data-gallery-id', options.id || 'dynamic');
        
        const grid = document.createElement('div');
        grid.className = 'gallery-grid';
        grid.setAttribute('data-columns', settings.columns);
        
        images.forEach((image, index) => {
            const item = document.createElement('div');
            item.className = 'gallery-item';
            
            const img = document.createElement('img');
            if (settings.lazyLoad) {
                img.setAttribute('data-src', image.url);
                img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNmNWY1ZjUiLz48L3N2Zz4=';
                img.className = 'lazy';
            } else {
                img.src = image.url;
            }
            img.alt = image.alt || `Gallery image ${index + 1}`;
            img.loading = 'lazy';
            
            item.appendChild(img);
            
            if (settings.showOverlay) {
                const overlay = document.createElement('div');
                overlay.className = 'gallery-overlay';
                overlay.innerHTML = '<i>🔍</i>';
                item.appendChild(overlay);
            }
            
            grid.appendChild(item);
        });
        
        gallery.appendChild(grid);
        container.appendChild(gallery);
        
        // Initialize the gallery
        if (settings.lightbox) {
            initializeGallery(gallery);
        }
        
        return gallery;
    },
    
    // Refresh galleries (useful for AJAX loaded content)
    refresh: function() {
        initializeGalleries();
    }
};

// Auto-initialize when new content is loaded (for AJAX sites)
if (window.MutationObserver) {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const galleries = node.querySelectorAll ? node.querySelectorAll('.custom-gallery') : [];
                    galleries.forEach(initializeGallery);
                    
                    // Check if the node itself is a gallery
                    if (node.classList && node.classList.contains('custom-gallery')) {
                        initializeGallery(node);
                    }
                }
            });
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

// Handle window resize for responsive galleries
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        // Re-initialize galleries on significant viewport changes
        const galleries = document.querySelectorAll('.custom-gallery');
        galleries.forEach(gallery => {
            // Trigger a refresh of lazy loaded images that might now be visible
            const lazyImages = gallery.querySelectorAll('img.lazy');
            lazyImages.forEach(img => {
                if (img.getBoundingClientRect().top < window.innerHeight + 100) {
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        img.classList.add('loaded');
                    }
                }
            });
        });
    }, 250);
});

// Performance optimization: Pause all animations when page is hidden
document.addEventListener('visibilitychange', function() {
    const galleries = document.querySelectorAll('.custom-gallery');
    galleries.forEach(gallery => {
        if (document.hidden) {
            gallery.style.animationPlayState = 'paused';
        } else {
            gallery.style.animationPlayState = 'running';
        }
    });
});
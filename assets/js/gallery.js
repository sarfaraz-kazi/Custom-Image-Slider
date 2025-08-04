/**
 * Custom Gallery functionality with vanilla JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeGalleries();
    
    // Re-initialize galleries when new content is loaded (for AJAX)
    document.addEventListener('contentLoaded', initializeGalleries);
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
        item.addEventListener('click', function() {
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
    
    // Add lazy loading for better performance
    addLazyLoading(gallery);
}

function createModal() {
    const modal = document.createElement('div');
    modal.className = 'gallery-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <button class="modal-close" aria-label="Close modal">&times;</button>
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
                closeModal();
                break;
            case 'ArrowLeft':
                navigateModal(-1);
                break;
            case 'ArrowRight':
                navigateModal(1);
                break;
        }
    });
    
    return modal;
}

function openModal(gallery, imageIndex) {
    const modal = document.querySelector('.gallery-modal');
    const modalImg = modal.querySelector('img');
    const galleryItems = gallery.querySelectorAll('.gallery-item img');
    
    if (!galleryItems[imageIndex]) return;
    
    // Set current gallery and image index
    modal.currentGallery = gallery;
    modal.currentIndex = imageIndex;
    
    // Load image
    const img = galleryItems[imageIndex];
    modalImg.src = img.src;
    modalImg.alt = img.alt || `Gallery image ${imageIndex + 1}`;
    
    // Show modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Focus management
    modal.querySelector('.modal-close').focus();
    
    // Update navigation buttons visibility
    updateNavigationButtons(modal, galleryItems.length);
    
    // Preload adjacent images
    preloadAdjacentImages(galleryItems, imageIndex);
}

function closeModal() {
    const modal = document.querySelector('.gallery-modal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    
    // Return focus to the original gallery item
    if (modal.currentGallery && modal.currentIndex !== undefined) {
        const originalItem = modal.currentGallery.querySelectorAll('.gallery-item')[modal.currentIndex];
        if (originalItem) {
            originalItem.focus();
        }
    }
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
    
    // Update modal
    const modalImg = modal.querySelector('img');
    const newImg = galleryItems[newIndex];
    
    modalImg.src = newImg.src;
    modalImg.alt = newImg.alt || `Gallery image ${newIndex + 1}`;
    modal.currentIndex = newIndex;
    
    // Preload adjacent images
    preloadAdjacentImages(galleryItems, newIndex);
}

function updateNavigationButtons(modal, totalImages) {
    const prevBtn = modal.querySelector('.modal-prev');
    const nextBtn = modal.querySelector('.modal-next');
    
    if (totalImages <= 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
    } else {
        prevBtn.style.display = 'block';
        nextBtn.style.display = 'block';
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
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// Utility function to create gallery grid dynamically
function createGalleryGrid(images, galleryId) {
    const gallery = document.createElement('div');
    gallery.className = 'custom-gallery';
    gallery.setAttribute('data-gallery-id', galleryId);
    
    const grid = document.createElement('div');
    grid.className = 'gallery-grid';
    
    images.forEach((image, index) => {
        const item = document.createElement('div');
        item.className = 'gallery-item';
        
        const img = document.createElement('img');
        img.src = image.url;
        img.alt = image.alt || `Gallery image ${index + 1}`;
        img.loading = 'lazy';
        
        const overlay = document.createElement('div');
        overlay.className = 'gallery-overlay';
        overlay.innerHTML = '<i>🔍</i>';
        
        item.appendChild(img);
        item.appendChild(overlay);
        grid.appendChild(item);
    });
    
    gallery.appendChild(grid);
    return gallery;
}

// Export functions for external use
window.CustomGallery = {
    initialize: initializeGalleries,
    createGrid: createGalleryGrid,
    openModal: openModal,
    closeModal: closeModal
};
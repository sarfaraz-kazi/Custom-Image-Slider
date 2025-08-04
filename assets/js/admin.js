/**
 * Custom Image Gallery Admin JavaScript
 */

(function($) {
    'use strict';
    
    // Gallery form management
    window.cigGalleryForm = {
        galleryId: 0,
        isEdit: false,
        
        init: function(isEdit, galleryId) {
            this.isEdit = isEdit;
            this.galleryId = galleryId;
            
            this.initGalleryForm();
            if (isEdit) {
                this.initImageUpload();
                this.initImageManagement();
            }
        },
        
        initGalleryForm: function() {
            const form = document.getElementById('cig-gallery-form');
            const updateBtn = document.getElementById('update-gallery');
            
            if (!this.isEdit) {
                // Create new gallery
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.createGallery();
                });
            } else if (updateBtn) {
                // Update existing gallery
                updateBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.updateGallery();
                });
            }

        },
        
        createGallery: function() {
            const formData = new FormData();
            formData.append('action', 'cig_create_gallery');
            formData.append('nonce', cig_ajax.nonce);
            formData.append('gallery_name', document.getElementById('gallery_name').value);
            formData.append('gallery_description', document.getElementById('gallery_description').value);
            
            fetch(cig_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    window.location.href = 'admin.php?page=cig-add-new&edit=' + result.data.id;
                } else {
                    alert('Error: ' + result.data);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        },
        
        updateGallery: function() {
            const formData = new FormData();
            formData.append('action', 'cig_create_gallery');
            formData.append('nonce', cig_ajax.nonce);
            formData.append('gallery_id', this.galleryId);
            formData.append('gallery_name', document.getElementById('gallery_name').value);
            formData.append('gallery_description', document.getElementById('gallery_description').value);
            
            fetch(cig_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.showNotice('Gallery updated successfully!', 'success');
                } else {
                    this.showNotice('Error: ' + result.data, 'error');
                }
            })
            .catch(error => {
                this.showNotice('Error: ' + error, 'error');
            });
        },
        
        initImageUpload: function() {
            const dropzone = document.getElementById('cig-dropzone');
            const fileInput = document.getElementById('cig-file-input');
            const browseBtn = document.getElementById('cig-browse-btn');
            
            // Click to browse
            browseBtn.addEventListener('click', () => {
                fileInput.click();
            });
            
            dropzone.addEventListener('click', () => {
                fileInput.click();
            });
            
            // File input change
            fileInput.addEventListener('change', (e) => {
                this.handleFiles(e.target.files);
            });
            
            // Drag and drop
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
            
            dropzone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            });
            
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
                this.handleFiles(e.dataTransfer.files);
            });
        },
        
        handleFiles: function(files) {
            const maxFileSize = cig_ajax.max_file_size;
            const allowedExtensions = cig_ajax.allowed_extensions;
            
            Array.from(files).forEach(file => {
                // Validate file
                if (!this.validateFile(file, maxFileSize, allowedExtensions)) {
                    return;
                }
                
                this.uploadFile(file);
            });
        },
        
        validateFile: function(file, maxSize, allowedExtensions) {
            // Check file size
            if (file.size > maxSize) {
                alert(`File "${file.name}" is too large. Maximum size is ${maxSize / (1024 * 1024)}MB.`);
                return false;
            }
            
            // Check file extension
            const extension = file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(extension)) {
                alert(`File "${file.name}" has an invalid extension. Allowed: ${allowedExtensions.join(', ')}`);
                return false;
            }
            
            // Check if it's actually an image
            if (!file.type.startsWith('image/')) {
                alert(`File "${file.name}" is not a valid image file.`);
                return false;
            }
            
            return true;
        },
        
        uploadFile: function(file) {
            const formData = new FormData();
            formData.append('action', 'cig_upload_image');
            formData.append('nonce', cig_ajax.nonce);
            formData.append('gallery_id', this.galleryId);
            formData.append('file', file);
            
            // Show progress
            this.showUploadProgress();
            
            fetch(cig_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                this.hideUploadProgress();
                
                if (result.success) {
                    this.addImageToGrid(result.data);
                    this.showNotice('Image uploaded successfully!', 'success');
                } else {
                    this.showNotice('Error: ' + result.data, 'error');
                }
            })
            .catch(error => {
                this.hideUploadProgress();
                this.showNotice('Error: ' + error, 'error');
            });
        },
        
        addImageToGrid: function(imageData) {
            const grid = document.getElementById('cig-images-grid');
            const imageItem = document.createElement('div');
            imageItem.className = 'cig-image-item';
            imageItem.setAttribute('data-image-id', imageData.id);
            
            imageItem.innerHTML = `
                <div class="cig-image-preview">
                    <img src="${imageData.url}" alt="${imageData.filename}">
                    <div class="cig-image-overlay">
                        <button type="button" class="cig-delete-image" data-image-id="${imageData.id}">✕</button>
                    </div>
                </div>
                <div class="cig-image-info">
                    <input type="text" placeholder="Alt text" value="" class="cig-alt-text" data-image-id="${imageData.id}">
                    <span class="cig-filename">${imageData.filename}</span>
                </div>
                <div class="cig-drag-handle">⋮⋮</div>
            `;
            
            grid.appendChild(imageItem);
            
            // Reinitialize sortable
            this.initSortable();
            
            // Add event listeners to new image
            this.bindImageEvents(imageItem);
        },
        
        initImageManagement: function() {
            // Initialize sortable
            this.initSortable();
            
            // Bind events to existing images
            const imageItems = document.querySelectorAll('.cig-image-item');
            imageItems.forEach(item => {
                this.bindImageEvents(item);
            });

            
        },
        
        bindImageEvents: function(imageItem) {
            // Delete button
            const deleteBtn = imageItem.querySelector('.cig-delete-image');
            deleteBtn.addEventListener('click', (e) => {
                const imageId = e.target.getAttribute('data-image-id');
                this.deleteImage(imageId, imageItem);
            });

            const saveTextInput = imageItem.querySelector('.save-image-data');
            
            saveTextInput.addEventListener('click', (e) => {
                const imageId = e.target.getAttribute('data-image-id');
                const imageItem = e.target.closest('.cig-image-item');
                this.updateSaveText(imageId, imageItem);
            });
        },
        
        initSortable: function() {
            const grid = document.getElementById('cig-images-grid');
            if (!grid) return;
            
            $(grid).sortable({
                items: '.cig-image-item',
                handle: '.cig-drag-handle',
                placeholder: 'ui-sortable-placeholder',
                update: (event, ui) => {
                    this.updateImageOrder();
                }
            });
        },
        
        deleteImage: function(imageId, imageItem) {
            if (!confirm('Are you sure you want to delete this image?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'cig_delete_image');
            formData.append('nonce', cig_ajax.nonce);
            formData.append('image_id', imageId);
            
            fetch(cig_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    imageItem.remove();
                    this.showNotice('Image deleted successfully!', 'success');
                } else {
                    this.showNotice('Error: ' + result.data, 'error');
                }
            })
            .catch(error => {
                this.showNotice('Error: ' + error, 'error');
            });
        },
        
        updateSaveText: function(imageId, imageItem) {


            const altText = imageItem.querySelector('.cig-alt-text')?.value || '';
            const title = imageItem.querySelector('.cig-title-text')?.value || '';
            const description = imageItem.querySelector('.cig-image-description')?.value || '';

            const formData = new FormData();
            formData.append('action', 'cig_update_image_data');
            formData.append('nonce', cig_ajax.nonce);
            formData.append('image_id', imageId);
            formData.append('alt_text', altText);
            formData.append('title', title);
            formData.append('description', description);
            
            fetch(cig_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    this.showNotice('Error updating information: ' + result.data, 'error');
                }
                else{
                    this.showNotice('Image information updated successfully!', 'success');
                }
            })
            .catch(error => {
                this.showNotice('Error: ' + error, 'error');
            });
        },
        
        updateImageOrder: function() {
            const imageItems = document.querySelectorAll('.cig-image-item');
            const imageIds = Array.from(imageItems).map(item => 
                item.getAttribute('data-image-id')
            );
            console.log(imageIds);
            const formData = new FormData();
            formData.append('action', 'cig_sort_images');
            formData.append('nonce', cig_ajax.nonce);
            formData.append('image_ids', JSON.stringify(imageIds));
            fetch(cig_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    this.showNotice('Error updating image order: ' + result.data, 'error');
                }
            })
            .catch(error => {
                this.showNotice('Error: ' + error, 'error');
            });
        },
        
        showUploadProgress: function() {
            const progress = document.getElementById('cig-upload-progress');
            progress.style.display = 'block';
            
            // Simulate progress (in real implementation, you'd track actual progress)
            const progressBar = progress.querySelector('.cig-progress-fill');
            let width = 0;
            const interval = setInterval(() => {
                width += 10;
                progressBar.style.width = width + '%';
                if (width >= 90) {
                    clearInterval(interval);
                }
            }, 100);
        },
        
        hideUploadProgress: function() {
            const progress = document.getElementById('cig-upload-progress');
            const progressBar = progress.querySelector('.cig-progress-fill');
            
            progressBar.style.width = '100%';
            setTimeout(() => {
                progress.style.display = 'none';
                progressBar.style.width = '0%';
            }, 500);
        },
        
        showNotice: function(message, type) {
            // Remove existing notices
            const existingNotices = document.querySelectorAll('.cig-notice');
            existingNotices.forEach(notice => notice.remove());
            
            // Create new notice
            const notice = document.createElement('div');
            notice.className = `cig-notice notice notice-${type} is-dismissible`;
            notice.innerHTML = `<p>${message}</p>`;
            
            // Add to page
            const wrap = document.querySelector('.wrap');
            wrap.insertBefore(notice, wrap.firstChild.nextSibling);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                notice.remove();
            }, 5000);
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        // Copy shortcode functionality
        $(document).on('click', '.cig-shortcode-box input', function() {
            this.select();
            document.execCommand('copy');
            
            const hint = $(this).siblings('.cig-copy-hint');
            const originalText = hint.text();
            hint.text('Copied!');
            
            setTimeout(() => {
                hint.text(originalText);
            }, 2000);
        });
        
        // Gallery card hover effects
        $('.cig-gallery-card').hover(
            function() {
                $(this).find('.cig-gallery-overlay').fadeIn(200);
            },
            function() {
                $(this).find('.cig-gallery-overlay').fadeOut(200);
            }
        );
    });
    
})(jQuery);
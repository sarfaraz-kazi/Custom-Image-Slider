<?php
/**
 * Add/Edit Gallery page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$gallery_plugin = new CustomImageGallery();
$is_edit = isset($_GET['edit']) && !empty($_GET['edit']);
$gallery_id = $is_edit ? absint($_GET['edit']) : 0;
$gallery = null;
$images = array();

if ($is_edit) {
    global $wpdb;
    $galleries_table = $wpdb->prefix . 'cig_galleries';
    $gallery = $wpdb->get_row($wpdb->prepare("SELECT * FROM $galleries_table WHERE id = %d", $gallery_id));
    
    if (!$gallery) {
        echo '<div class="notice notice-error"><p>Gallery not found.</p></div>';
        return;
    }
    
    $images = $gallery_plugin->get_gallery_images($gallery_id);
}
?>

<div class="wrap">
    <h1><?php echo $is_edit ? 'Edit Gallery' : 'Add New Gallery'; ?></h1>
    
    <div class="cig-form-container">
        <div class="cig-gallery-form">
            <h2>Gallery Information</h2>
            
            <form id="cig-gallery-form">
                <?php wp_nonce_field('cig_nonce', 'cig_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="gallery_name">Gallery Name *</label>
                        </th>
                        <td>
                            <input type="text" id="gallery_name" name="gallery_name" 
                                   value="<?php echo $is_edit ? esc_attr($gallery->name) : ''; ?>" 
                                   class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="gallery_description">Description</label>
                        </th>
                        <td>
                            <textarea id="gallery_description" name="gallery_description" 
                                      rows="4" cols="50" class="large-text"><?php echo $is_edit ? esc_textarea($gallery->description) : ''; ?></textarea>
                        </td>
                    </tr>
                </table>
                
                <?php if (!$is_edit) : ?>
                    <p class="submit">
                        <button type="submit" class="button button-primary">Create Gallery</button>
                        <a href="<?php echo admin_url('admin.php?page=custom-image-gallery'); ?>" class="button">Cancel</a>
                    </p>
                <?php else : ?>
                    <p class="submit">
                        <button type="button" id="update-gallery" class="button button-primary">Update Gallery</button>
                        <a href="<?php echo admin_url('admin.php?page=custom-image-gallery'); ?>" class="button">Back to Galleries</a>
                    </p>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if ($is_edit) : ?>
        <div class="cig-images-section">
            <h2>Gallery Images</h2>
            
            <div class="cig-upload-area">
                <div class="cig-dropzone" id="cig-dropzone">
                    <div class="cig-dropzone-content">
                        <div class="cig-dropzone-icon">📁</div>
                        <p>Drag & drop images here or <button type="button" id="cig-browse-btn" class="button">Browse Files</button></p>
                        <p class="cig-upload-info">
                            Max file size: <?php echo get_option('cig_max_file_size', 5); ?>MB | 
                            Allowed types: <?php echo get_option('cig_allowed_extensions', 'jpg,jpeg,png,gif,webp'); ?>
                        </p>
                    </div>
                    <input type="file" id="cig-file-input" multiple accept="image/*" style="display: none;">
                </div>
                
                <div class="cig-upload-progress" id="cig-upload-progress" style="display: none;">
                    <div class="cig-progress-bar">
                        <div class="cig-progress-fill"></div>
                    </div>
                    <span class="cig-progress-text">Uploading...</span>
                </div>
            </div>
            
            <div class="cig-images-grid" id="cig-images-grid">
                <?php foreach ($images as $image) : ?>
                        <div class="cig-image-item" data-image-id="<?php echo $image->id; ?>">
                            <div class="cig-image-preview">
                                <img src="<?php echo esc_url($image->file_url); ?>" alt="<?php echo esc_attr($image->original_filename); ?>">
                                <div class="cig-image-overlay">
                                    <button type="button" class="cig-delete-image" data-image-id="<?php echo $image->id; ?>">
                                        ✕
                                    </button>
                                </div>
                            </div>
                                <span class="cig-filename"><?php echo esc_html($image->original_filename); ?></span>
                            <div class="cig-image-info">
                                <input type="text" placeholder="Alt text" value="<?php echo esc_attr($image->alt_text); ?>" 
                                    class="cig-alt-text" data-image-id="<?php echo $image->id; ?>">

                                <input type="text" placeholder="Image Title" value="<?php echo esc_attr($image->title); ?>" 
                                    class="cig-title-text" data-image-id="<?php echo $image->id; ?>">

                                <textarea rows="5" name="cig-image-description" class="cig-image-description" placeholder="Image Description"><?php echo esc_attr($image->description) ?? ''; ?></textarea>

                                <button type="button" class="save-image-data button button-primary" data-image-id="<?php echo $image->id; ?>">Save</button>

                            </div>

                            <div class="cig-drag-handle">⋮⋮</div>
                        </div>
                <?php endforeach; ?>
                
            </div>
            
            <?php if (!empty($images)) : ?>
                <div class="cig-shortcode-display">
                    <h3>Shortcode</h3>
                    <p>Use this shortcode to display the gallery:</p>
                    <div class="cig-shortcode-box">
                        <input type="text" readonly value="[image_gallery id=<?php echo $gallery_id; ?>]" 
                               onclick="this.select(); document.execCommand('copy');" 
                               title="Click to copy">
                        <span class="cig-copy-hint">Click to copy</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.cig-form-container {
    max-width: 1200px;
}

.cig-gallery-form {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.cig-images-section {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.cig-upload-area {
    margin-bottom: 30px;
}

.cig-dropzone {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    transition: border-color 0.2s ease;
    cursor: pointer;
}

.cig-dropzone.dragover {
    border-color: #0073aa;
    background-color: #f7fcfe;
}

.cig-dropzone-icon {
    font-size: 48px;
    margin-bottom: 10px;
}

.cig-upload-info {
    font-size: 12px;
    color: #666;
    margin-top: 10px;
}

.cig-upload-progress {
    margin-top: 20px;
    text-align: center;
}

.cig-progress-bar {
    width: 100%;
    height: 20px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.cig-progress-fill {
    height: 100%;
    background: #0073aa;
    transition: width 0.3s ease;
    width: 0%;
}

.cig-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.cig-image-item {
    position: relative;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    cursor: move;
}

.cig-image-preview {
    position: relative;
    height: 150px;
    overflow: hidden;
}

.cig-image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cig-image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.cig-image-item:hover .cig-image-overlay {
    opacity: 1;
}

.cig-delete-image {
    background: #dc3232;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-size: 16px;
}

.cig-image-info {
    padding: 10px;
}

.cig-alt-text {
    width: 100%;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 3px;
    margin-bottom: 5px;
}

.cig-filename {
    font-size: 12px;
    color: #666;
    display: block;
    word-break: break-all;
}

.cig-drag-handle {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px;
    border-radius: 3px;
    font-size: 12px;
    cursor: move;
}

.cig-shortcode-display {
    margin-top: 30px;
    padding: 20px;
    background: #f0f8ff;
    border: 1px solid #cce7ff;
    border-radius: 5px;
}

.cig-shortcode-box {
    position: relative;
    margin-top: 10px;
}

.cig-shortcode-box input {
    width: 100%;
    padding: 10px;
    font-family: monospace;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
}

.cig-copy-hint {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    color: #666;
    pointer-events: none;
}

/* Sortable styles */
.ui-sortable-helper {
    transform: rotate(5deg);
    box-shadow: 0 8px 16px rgba(0,0,0,0.3);
}

.ui-sortable-placeholder {
    border: 2px dashed #0073aa;
    background: #f7fcfe;
    visibility: visible !important;
    height: 200px !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isEdit = <?php echo $is_edit ? 'true' : 'false'; ?>;
    const galleryId = <?php echo $gallery_id; ?>;
    
    // Initialize the gallery form functionality
    if (typeof window.cigGalleryForm !== 'undefined') {
        window.cigGalleryForm.init(isEdit, galleryId);
    }
});
</script>
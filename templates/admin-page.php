<?php
/**
 * Admin page template for Custom Image Gallery
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle gallery deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['gallery_id'])) {
    $gallery_id = absint($_GET['gallery_id']);
    $gallery_plugin = new CustomImageGallery();
    if ($gallery_plugin->delete_gallery($gallery_id)) {
        echo '<div class="notice notice-success"><p>Gallery deleted successfully.</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Failed to delete gallery.</p></div>';
    }
}

$gallery_plugin = new CustomImageGallery();
$galleries = $gallery_plugin->get_all_galleries();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Custom Image Galleries</h1>
    <a href="<?php echo admin_url('admin.php?page=cig-add-new'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    
    <?php if (empty($galleries)) : ?>
        <div class="cig-empty-state">
            <div class="cig-empty-icon">📷</div>
            <h2>No galleries found</h2>
            <p>Create your first image gallery to get started.</p>
            <a href="<?php echo admin_url('admin.php?page=cig-add-new'); ?>" class="button button-primary">Create Gallery</a>
        </div>
    <?php else : ?>
        <div class="cig-galleries-grid">
            <?php foreach ($galleries as $gallery) : 
                $images = $gallery_plugin->get_gallery_images($gallery->id);
                $image_count = count($images);
                $featured_image = !empty($images) ? $images[0]->file_url : CIG_PLUGIN_URL . 'assets/placeholder.jpg';
            ?>
                <div class="cig-gallery-card">
                    <div class="cig-gallery-featured">
                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($gallery->name); ?>">
                        <div class="cig-gallery-overlay">
                            <span class="cig-image-count"><?php echo $image_count; ?> images</span>
                        </div>
                    </div>
                    
                    <div class="cig-gallery-info">
                        <h3><?php echo esc_html($gallery->name); ?></h3>
                        <?php if ($gallery->description) : ?>
                            <p><?php echo esc_html(wp_trim_words($gallery->description, 15)); ?></p>
                        <?php endif; ?>
                        
                        <div class="cig-gallery-meta">
                            <span>Created: <?php echo date('M j, Y', strtotime($gallery->created_date)); ?></span>
                        </div>
                        
                        <div class="cig-gallery-shortcode">
                            <label>Shortcode:</label>
                            <input type="text" readonly value="[image_gallery id=<?php echo $gallery->id; ?>]" 
                                   onclick="this.select(); document.execCommand('copy');" 
                                   title="Click to copy">
                        </div>
                        
                        <div class="cig-gallery-actions">
                            <a href="<?php echo admin_url('admin.php?page=cig-add-new&edit=' . $gallery->id); ?>" 
                               class="button button-small">Edit</a>
                            <a href="<?php echo admin_url('admin.php?page=custom-image-gallery&action=delete&gallery_id=' . $gallery->id); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('Are you sure you want to delete this gallery? This action cannot be undone.')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.cig-empty-state {
    text-align: center;
    margin: 50px 0;
    padding: 40px;
}

.cig-empty-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.cig-empty-state h2 {
    color: #666;
    margin-bottom: 10px;
}

.cig-galleries-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.cig-gallery-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s ease;
}

.cig-gallery-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.cig-gallery-featured {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.cig-gallery-featured img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cig-gallery-overlay {
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

.cig-gallery-card:hover .cig-gallery-overlay {
    opacity: 1;
}

.cig-image-count {
    color: white;
    font-weight: bold;
    font-size: 18px;
}

.cig-gallery-info {
    padding: 15px;
}

.cig-gallery-info h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
}

.cig-gallery-info p {
    color: #666;
    margin: 0 0 10px 0;
    font-size: 14px;
}

.cig-gallery-meta {
    font-size: 12px;
    color: #999;
    margin-bottom: 15px;
}

.cig-gallery-shortcode {
    margin-bottom: 15px;
}

.cig-gallery-shortcode label {
    display: block;
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.cig-gallery-shortcode input {
    width: 100%;
    padding: 5px;
    font-size: 12px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
}

.cig-gallery-actions {
    display: flex;
    gap: 10px;
}

.cig-gallery-actions .button {
    flex: 1;
    text-align: center;
}
</style>
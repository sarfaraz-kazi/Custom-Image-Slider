<?php
/**
 * Settings page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$max_file_size = get_option('cig_max_file_size', 5);
$allowed_extensions = get_option('cig_allowed_extensions', 'jpg,jpeg,png,gif,webp');
$max_images_per_gallery = get_option('cig_max_images_per_gallery', 50);
$timer = get_option('slider_timer', 2000);
?>

<div class="wrap">
    <h1>Custom Image Gallery Settings</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('cig_settings_nonce', 'cig_settings_nonce'); ?>
        
        <div class="cig-settings-section">
            <h2>Slider Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Slider Timer (milliseconds)</th>
                    <td>
                        <input type="number" name="slider_timer" value="<?php echo esc_attr($timer); ?>" min="1000" max="10000" step="500" />
                        <p class="description">Time between slide transitions in milliseconds</p>
                    </td>
                </tr>
            </table>
        </div>
        

        <div class="cig-settings-section">
            <h2>Upload Settings</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="max_file_size">Maximum File Size (MB)</label>
                    </th>
                    <td>
                        <input type="number" id="max_file_size" name="max_file_size" 
                               value="<?php echo esc_attr($max_file_size); ?>" 
                               min="1" max="100" step="1" class="small-text">
                        <p class="description">
                            Maximum file size allowed for image uploads. Server limit: <?php echo ini_get('upload_max_filesize'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="allowed_extensions">Allowed File Extensions</label>
                    </th>
                    <td>
                        <input type="text" id="allowed_extensions" name="allowed_extensions" 
                               value="<?php echo esc_attr($allowed_extensions); ?>" 
                               class="regular-text">
                        <p class="description">
                            Comma-separated list of allowed file extensions (e.g., jpg,jpeg,png,gif,webp)
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="max_images_per_gallery">Maximum Images per Gallery</label>
                    </th>
                    <td>
                        <input type="number" id="max_images_per_gallery" name="max_images_per_gallery" 
                               value="<?php echo esc_attr($max_images_per_gallery); ?>" 
                               min="1" max="1000" step="1" class="small-text">
                        <p class="description">
                            Maximum number of images allowed in a single gallery
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="cig-settings-section">
            <h2>Display Settings</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Width</th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">Width</legend>
                            <label>
                                <input type="radio" name="default_size" value="small" <?php checked(get_option('cig_default_size', 'medium'), 'small'); ?>>
                                Small
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="default_size" value="medium" <?php checked(get_option('cig_default_size', 'Medium'), 'medium'); ?>>
                                Medium
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="default_size" value="full" <?php checked(get_option('cig_default_size', 'medium'), 'full'); ?>>
                                Full
                            </label>
                        </fieldset>
                        <p class="description">
                            Default Size can be replaced from shortcode by passing size="{size}" parameter.
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="cig-settings-section">
            <h2>System Information</h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Upload Directory</th>
                    <td>
                        <?php 
                        $upload_dir = wp_upload_dir();
                        $gallery_dir = $upload_dir['basedir'] . '/custom-gallery';
                        ?>
                        <code><?php echo esc_html($gallery_dir); ?></code>
                        <?php if (is_writable($gallery_dir)) : ?>
                            <span style="color: green;">✓ Writable</span>
                        <?php else : ?>
                            <span style="color: red;">✗ Not writable</span>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Server Upload Limit</th>
                    <td>
                        <code><?php echo ini_get('upload_max_filesize'); ?></code>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Server Post Max Size</th>
                    <td>
                        <code><?php echo ini_get('post_max_size'); ?></code>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">PHP Memory Limit</th>
                    <td>
                        <code><?php echo ini_get('memory_limit'); ?></code>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Total Galleries</th>
                    <td>
                        <?php
                        global $wpdb;
                        $galleries_table = $wpdb->prefix . 'cig_galleries';
                        $gallery_count = $wpdb->get_var("SELECT COUNT(*) FROM $galleries_table");
                        echo esc_html($gallery_count);
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Total Images</th>
                    <td>
                        <?php
                        $images_table = $wpdb->prefix . 'cig_images';
                        $image_count = $wpdb->get_var("SELECT COUNT(*) FROM $images_table");
                        echo esc_html($image_count);
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button('Save Settings'); ?>
    </form>
    
</div>

<style>
.cig-settings-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.cig-settings-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

#resize_options {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

#resize_options label {
    display: inline-block;
    width: 80px;
    font-weight: bold;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle resize options
    const autoResizeCheckbox = document.getElementById('auto_resize');
    const resizeOptions = document.getElementById('resize_options');
    
    autoResizeCheckbox.addEventListener('change', function() {
        resizeOptions.style.display = this.checked ? 'block' : 'none';
    });
    
    // Cleanup orphaned files
    document.getElementById('cleanup-orphaned').addEventListener('click', function() {
        if (confirm('Are you sure you want to remove orphaned files? This action cannot be undone.')) {
            // AJAX call to cleanup function
            const data = new FormData();
            data.append('action', 'cig_cleanup_orphaned');
            data.append('nonce', '<?php echo wp_create_nonce('cig_nonce'); ?>');
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Cleanup completed successfully.');
                } else {
                    alert('Cleanup failed: ' + result.data);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
    });
    
    // Export galleries
    document.getElementById('export-galleries').addEventListener('click', function() {
        window.location.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=cig_export_galleries&nonce=<?php echo wp_create_nonce('cig_nonce'); ?>';
    });
});
</script>
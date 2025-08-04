<?php
/**
 * Plugin Name: Custom Image Gallery
 * Plugin URI: https://sarfarajkazi7.link/
 * Description: A custom image gallery plugin with drag & drop upload, sorting, and shortcode support.
 * Version: 1.0.0
 * Author: Sarfaraz Kazi
 * Author URI: https://sarfarajkazi7.link/
 * License: GPL v2 or later
 * Text Domain: custom-image-gallery
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CIG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CIG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CIG_VERSION', '1.0.0');
define('CIG_SMALL_SIZE', '50%');
define('CIG_MEDIUM_SIZE', '75%');
define('CIG_FULL_SIZE', '100%');

// Main plugin class
class CustomImageGallery {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_cig_upload_image', array($this, 'ajax_upload_image'));
        add_action('wp_ajax_cig_update_image_data', array($this, 'cig_update_image_data'));
        add_action('wp_ajax_cig_delete_image', array($this, 'ajax_delete_image'));
        add_action('wp_ajax_cig_sort_images', array($this, 'ajax_sort_images'));
        add_action('wp_ajax_cig_create_gallery', array($this, 'ajax_create_gallery'));
        
        // Shortcode
        add_shortcode('image_gallery', array($this, 'gallery_shortcode'));
        
        // Create database tables
        $this->create_tables();
    }
    
    public function activate() {
        $this->create_tables();
        
        // Create upload directory
        $upload_dir = wp_upload_dir();
        $gallery_dir = $upload_dir['basedir'] . '/custom-gallery';
        if (!file_exists($gallery_dir)) {
            wp_mkdir_p($gallery_dir);
        }
        
        // Set default options
        add_option('cig_max_file_size', 5); // 5MB
        add_option('cig_allowed_extensions', 'jpg,jpeg,png,gif,webp');
        add_option('cig_max_images_per_gallery', 50);
    }
    
    public function deactivate() {
        // Clean up if needed
    }
    
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Galleries table
        $galleries_table = $wpdb->prefix . 'cig_galleries';
        $galleries_sql = "CREATE TABLE $galleries_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Images table
        $images_table = $wpdb->prefix . 'cig_images';
        $images_sql = "CREATE TABLE $images_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            gallery_id int(11) NOT NULL,
            filename varchar(255) NOT NULL,
            original_filename varchar(255) NOT NULL,
            file_path varchar(500) NOT NULL,
            file_url varchar(500) NOT NULL,
            alt_text varchar(255),
            title varchar(255),
            description TEXT,
            sort_order int(11) DEFAULT 0,
            uploaded_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($galleries_sql);
        dbDelta($images_sql);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Custom Image Gallery',
            'Image Gallery',
            'manage_options',
            'custom-image-gallery',
            array($this, 'admin_page'),
            'dashicons-format-gallery',
            30
        );
        
        add_submenu_page(
            'custom-image-gallery',
            'All Galleries',
            'All Galleries',
            'manage_options',
            'custom-image-gallery',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'custom-image-gallery',
            'Add New Gallery',
            'Add New',
            'manage_options',
            'cig-add-new',
            array($this, 'add_gallery_page')
        );
        
        add_submenu_page(
            'custom-image-gallery',
            'Settings',
            'Settings',
            'manage_options',
            'cig-settings',
            array($this, 'settings_page')
        );
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'custom-image-gallery') === false && strpos($hook, 'cig-') === false) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('cig-admin-js', CIG_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), CIG_VERSION, true);
        wp_enqueue_style('cig-admin-css', CIG_PLUGIN_URL . 'assets/css/admin.css', array(), CIG_VERSION);
        
        wp_localize_script('cig-admin-js', 'cig_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cig_nonce'),
            'max_file_size' => get_option('cig_max_file_size', 5) * 1024 * 1024, // Convert to bytes
            'allowed_extensions' => explode(',', get_option('cig_allowed_extensions', 'jpg,jpeg,png,gif,webp'))
        ));
    }
    
    public function frontend_enqueue_scripts() {
        wp_enqueue_style('cig-frontend-css', CIG_PLUGIN_URL . 'assets/css/frontend.css', array(), CIG_VERSION);
        wp_enqueue_script('cig-frontend-js', CIG_PLUGIN_URL . 'assets/js/frontend.js', array(), CIG_VERSION, true);

        wp_enqueue_script('cig-slider-js', CIG_PLUGIN_URL . 'assets/js/slider.js', array(), '1.0.0', true);
        wp_enqueue_script('cig-slider-css', CIG_PLUGIN_URL . 'assets/js/gallery.js', array(), '1.0.0', true);
        
        // Localize script for AJAX
        wp_localize_script('dynamic-theme-slider', 'slider_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('slider_nonce')
        ));
        
    }
    
    public function admin_page() {
        include CIG_PLUGIN_PATH . 'templates/admin-page.php';
    }
    
    public function add_gallery_page() {
        include CIG_PLUGIN_PATH . 'templates/add-gallery.php';
    }
    
    public function settings_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cig_settings_nonce']) && wp_verify_nonce($_POST['cig_settings_nonce'], 'cig_settings_nonce')) {
            update_option('cig_max_file_size', absint($_POST['max_file_size']));
            update_option('cig_allowed_extensions', sanitize_text_field($_POST['allowed_extensions']));
            update_option('cig_max_images_per_gallery', absint($_POST['max_images_per_gallery']));
            update_option('slider_timer', absint($_POST['slider_timer']));
            update_option('cig_default_size', sanitize_text_field($_POST['default_size']));
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }
        
        include CIG_PLUGIN_PATH . 'templates/settings.php';
    }
    
    public function ajax_upload_image() {
        check_ajax_referer('cig_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $gallery_id = absint($_POST['gallery_id']);
        $max_file_size = get_option('cig_max_file_size', 5) * 1024 * 1024;
        $allowed_extensions = explode(',', get_option('cig_allowed_extensions', 'jpg,jpeg,png,gif,webp'));
        
        if (empty($_FILES['file'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file = $_FILES['file'];
        
        // Validate file size
        if ($file['size'] > $max_file_size) {
            wp_send_json_error('File size exceeds maximum allowed size');
        }
        
        // Validate file extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            wp_send_json_error('File type not allowed');
        }
        
        // Upload file
        $upload_dir = wp_upload_dir();
        $gallery_dir = $upload_dir['basedir'] . '/custom-gallery';
        $filename = uniqid() . '_' . sanitize_file_name($file['name']);
        $file_path = $gallery_dir . '/' . $filename;
        $file_url = $upload_dir['baseurl'] . '/custom-gallery/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Save to database
            global $wpdb;
            $images_table = $wpdb->prefix . 'cig_images';
            
            $result = $wpdb->insert(
                $images_table,
                array(
                    'gallery_id' => $gallery_id,
                    'filename' => $filename,
                    'original_filename' => $file['name'],
                    'file_path' => $file_path,
                    'file_url' => $file_url,
                    'sort_order' => 0
                ),
                array('%d', '%s', '%s', '%s', '%s', '%d')
            );
            
            if ($result) {
                wp_send_json_success(array(
                    'id' => $wpdb->insert_id,
                    'url' => $file_url,
                    'filename' => $filename
                ));
            } else {
                wp_send_json_error('Failed to save image to database');
            }
        } else {
            wp_send_json_error('Failed to upload file');
        }
    }
    
    public function cig_update_image_data(){
        check_ajax_referer('cig_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        global $wpdb;
        $image_id    = isset($_POST['image_id']) ? absint($_POST['image_id']) : 0;
        $alt_text    = isset($_POST['alt_text']) ? sanitize_text_field($_POST['alt_text']) : '';
        $title       = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';

        if ($image_id <= 0) {
            wp_send_json_error('Invalid image ID');
        }

        $table = $wpdb->prefix . 'cig_images'; // Adjust table name as needed

        $result = $wpdb->update(
            $table,
            [
                'alt_text'   => $alt_text,
                'title'      => $title,
                'description'=> $description
            ],
            ['id' => $image_id],
            ['%s', '%s', '%s'],
            ['%d']
        );

        if ($result !== false) {
            wp_send_json_success('Image data updated');
        } else {
            wp_send_json_error('Failed to update image data');
        }
    }

    public function ajax_delete_image() {
        check_ajax_referer('cig_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $image_id = absint($_POST['image_id']);
        
        global $wpdb;
        $images_table = $wpdb->prefix . 'cig_images';
        
        // Get image details before deleting
        $image = $wpdb->get_row($wpdb->prepare("SELECT * FROM $images_table WHERE id = %d", $image_id));
        
        if ($image) {
            // Delete file from server
            if (file_exists($image->file_path)) {
                unlink($image->file_path);
            }
            
            // Delete from database
            $result = $wpdb->delete($images_table, array('id' => $image_id), array('%d'));
            
            if ($result) {
                wp_send_json_success('Image deleted successfully');
            } else {
                wp_send_json_error('Failed to delete image from database');
            }
        } else {
            wp_send_json_error('Image not found');
        }
    }
    
    public function ajax_sort_images() {
        check_ajax_referer('cig_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $raw_input = stripslashes($_POST['image_ids']); 
        $image_ids = json_decode($raw_input, true);
      
        global $wpdb;
        $images_table = $wpdb->prefix . 'cig_images';
      
        foreach ($image_ids as $index => $image_id) {
            $wpdb->update(
                $images_table,
                array('sort_order' => $index),
                array('id' => $image_id),
                array('%d'),
                array('%d')
            );
           
        }
        
        wp_send_json_success('Images sorted successfully');
    }
    
    public function ajax_create_gallery() {
        check_ajax_referer('cig_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $gallery_id = isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
        $gallery_name = sanitize_text_field($_POST['gallery_name']);
        $gallery_description = sanitize_textarea_field($_POST['gallery_description']);

        if (empty($gallery_name)) {
            wp_send_json_error('Gallery name is required');
        }

        global $wpdb;
        $galleries_table = $wpdb->prefix . 'cig_galleries';

        if ($gallery_id > 0) {
            // Update existing gallery
            $result = $wpdb->update(
                $galleries_table,
                array(
                    'name' => $gallery_name,
                    'description' => $gallery_description
                ),
                array('id' => $gallery_id),
                array('%s', '%s'),
                array('%d')
            );

            if ($result !== false) {
                wp_send_json_success(array(
                    'id' => $gallery_id,
                    'message' => 'Gallery updated successfully'
                ));
            } else {
                wp_send_json_error('Failed to update gallery');
            }
        } else {
            // Insert new gallery
            $result = $wpdb->insert(
                $galleries_table,
                array(
                    'name' => $gallery_name,
                    'description' => $gallery_description
                ),
                array('%s', '%s')
            );

            if ($result) {
                wp_send_json_success(array(
                    'id' => $wpdb->insert_id,
                    'message' => 'Gallery created successfully'
                ));
            } else {
                wp_send_json_error('Failed to create gallery');
            }
        }
    }

    
    public function gallery_shortcode($atts) {
        $size_array= array(
            'small' => CIG_SMALL_SIZE,
            'medium' => CIG_MEDIUM_SIZE,
            'full' => CIG_FULL_SIZE,
        );
        $default_size = get_option('cig_default_size', 'medium');

        $atts = shortcode_atts(array(
            'id' => 0,
            'size' => $default_size
        ), $atts);
        
        $gallery_id = absint($atts['id']);


        if (!$gallery_id) {
            return '<p>Gallery ID is required.</p>';
        }

        global $wpdb;
        $images_table = $wpdb->prefix . 'cig_images';
        $galleries_table = $wpdb->prefix . 'cig_galleries';
        
        // Get gallery info
        $gallery = $wpdb->get_row($wpdb->prepare("SELECT * FROM $galleries_table WHERE id = %d", $gallery_id));
        if (!$gallery) {
            return '<p>Gallery not found.</p>';
        }
        
        // Get images
        $images = $wpdb->get_results($wpdb->prepare("SELECT * FROM $images_table WHERE gallery_id = %d ORDER BY sort_order ASC", $gallery_id));
        
        if (empty($images)) {
            return '<p>No images found in this gallery.</p>';
        }
        
        ob_start();
        ?>

         <section class="hero-section" style="max-width: <?php echo $size_array[$default_size] ?>;">
            <div class="slider-container" id="heroSlider">
                <?php 
                $slider_images = $images;
               
                if (!empty($slider_images)) :
                    foreach ($slider_images as $index => $image) : ?>
                        <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo esc_url($image->file_url); ?>')">
                             <div class="slide-content">
                                <?php if($image->title): ?>
                                    <h1 class="hero-title"><?php echo $image->title ?></h1>
                                    <hr class="hero-divider">
                                <?php endif; ?>
                                <?php if($image->description): ?>
                                    <p class="hero-subtitle"><?php echo $image->description ?></p>
                                    <a href="#" class="hero-button">SIGN UP</a>
                                <?php endif; ?>
                             </div>
                        </div>
                    <?php endforeach;
                    ?>
                   
                    <?php
                endif; ?>
                
                
                
                <?php if (count($slider_images) > 1) : ?>
                    <div class="slider-controls">
                        <?php foreach ($slider_images as $index => $image_url) : ?>
                            <div class="slider-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php
        return ob_get_clean();
    }
    
    public function get_all_galleries() {
        global $wpdb;
        $galleries_table = $wpdb->prefix . 'cig_galleries';
        return $wpdb->get_results("SELECT * FROM $galleries_table ORDER BY created_date DESC");
    }
    
    public function get_gallery_images($gallery_id) {
        global $wpdb;
        $images_table = $wpdb->prefix . 'cig_images';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $images_table WHERE gallery_id = %d ORDER BY sort_order ASC", $gallery_id));
    }
    
    public function delete_gallery($gallery_id) {
        global $wpdb;
        $galleries_table = $wpdb->prefix . 'cig_galleries';
        $images_table = $wpdb->prefix . 'cig_images';
        
        // Get all images in the gallery
        $images = $this->get_gallery_images($gallery_id);
        
        // Delete image files
        foreach ($images as $image) {
            if (file_exists($image->file_path)) {
                unlink($image->file_path);
            }
        }
        
        // Delete from database (images will be deleted by foreign key constraint)
        return $wpdb->delete($galleries_table, array('id' => $gallery_id), array('%d'));
    }
}

// Initialize the plugin
new CustomImageGallery();
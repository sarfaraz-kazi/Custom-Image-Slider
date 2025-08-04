# Dynamic Custom Image Gallery Plugin

A complete WordPress solution featuring image slider functionality and a custom image gallery plugin with drag & drop upload capabilities.

## 🚀 Features

### Image Gallery Plugin Features
- **Drag & Drop Upload**: Modern file upload interface
- **Image Management**: Sort, delete, and organize images
- **Shortcode Support**: Easy integration with `[image_gallery id=X]`
- **Lightbox Gallery**: Modal popup with navigation
- **Responsive Grid**: Configurable column layouts
- **Lazy Loading**: Performance optimization for large galleries
- **Touch Gestures**: Mobile-friendly swipe navigation
- **Accessibility**: Full keyboard navigation and screen reader support

## 📁 Project Structure

```
project-root/
├── plugin/
    ├── custom-image-gallery.php
    ├── templates/
    │   ├── admin-page.php
    │   ├── add-gallery.php
    │   └── settings.php
    └── assets/
        ├── admin.js
        ├── admin.css
        ├── frontend.js
        └── frontend.css
```

## 🛠 Installation

### Plugin Installation
1. Download the plugin files
2. Create a folder named `custom-image-gallery` in `/wp-content/plugins/`
3. Upload all plugin files to this folder
4. Activate the plugin from WordPress admin
5. Access the plugin via **Image Gallery** in the admin menu

## 📸 Gallery Plugin Usage

### Creating Galleries
1. Go to **Image Gallery → Add New**
2. Enter gallery name and description
3. Upload images via drag & drop or file browser
4. Sort images by dragging
5. Add alt text for accessibility
6. Copy the generated shortcode

### Using Shortcodes
```php
// Basic usage
[image_gallery id=1]

// With custom image size
[image_gallery id=1 size={small,medium,large}]
```

### Plugin Settings
Configure the plugin via **Image Gallery → Settings**:
- **Upload Settings**: File size limits, allowed extensions
- **Display Settings**: Default Size

## 🔧 Technical Specifications

### Requirements
- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Modern browser support (IE11+)

### Technologies Used
- **Frontend**: Vanilla JavaScript, CSS3, HTML5
- **Backend**: PHP, MySQL, WordPress APIs
- **Build Tools**: No build process required
- **Styling**: Custom CSS (no frameworks)

## 🎛 Configuration Options


### Plugin Options
```php
// Plugin settings:
- cig_max_file_size (default: 5MB)
- cig_allowed_extensions (default: jpg,jpeg,png,gif,webp)
- cig_max_images_per_gallery (default: 50)
- cig_default_size (default: medium)
```

## 🔌 API & Hooks

### Plugin Hooks
```php
// Available actions
do_action('cig_before_gallery_display', $gallery_id);
do_action('cig_after_gallery_display', $gallery_id);

// Available filters
apply_filters('cig_gallery_shortcode_output', $output, $atts);
apply_filters('cig_image_upload_path', $path, $filename);
```

## 📱 Responsive Breakpoints

```css
/* Tablet */
@media (max-width: 768px) {
    /* 2-column layout */
}

/* Mobile */
@media (max-width: 480px) {
    /* Single column layout */
}
```

## 🎯 Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Internet Explorer 11 (limited)
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🔒 Security Features

- Nonce verification for all AJAX requests
- File type validation
- SQL injection prevention
- XSS protection
- Sanitized input/output
- Capability checks

## 🐛 Troubleshooting

### Common Issues

**Slider not working:**
- Check JavaScript console for errors
- Verify images are uploaded correctly
- Ensure proper file permissions

**Upload failing:**
- Check PHP upload limits
- Verify folder permissions
- Review error logs

**Gallery not displaying:**
- Confirm shortcode syntax
- Check gallery ID exists
- Verify plugin activation

### Debug Mode
Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📈 Performance Tips

1. **Optimize Images**: Use WebP format when possible
2. **Enable Caching**: Use a caching plugin
3. **CDN Integration**: Serve images from CDN
4. **Lazy Loading**: Enabled by default
5. **Database Optimization**: Regular cleanup

## 🔄 Updates & Maintenance

### Plugin Updates
- Automatic update notifications
- Database migrations handled automatically
- Settings preserved during updates

## 📝 Development Notes

### Coding Standards
- WordPress Coding Standards
- PSR-4 autoloading (where applicable)
- ESLint for JavaScript
- Semantic versioning

### File Organization
- Modular architecture
- Separation of concerns
- Reusable components
- Clear documentation

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Follow coding standards
4. Write tests where applicable
5. Submit pull request

## 📄 License

This project is licensed under the GPL v2 or later - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Check documentation first
- Review troubleshooting section
- Contact developer
- Submit GitHub issues

## 📊 Changelog

### Version 1.0.0
- Initial release
- Image gallery plugin
- Admin interface
- Responsive design
- Accessibility features

---

*Built with ❤️ for WordPress*
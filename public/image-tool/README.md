# Advanced Image Processing Tool

A comprehensive, portable image processing solution with resize, crop, filters, watermarks, optimization, and batch processing capabilities.

## Features

### ✨ Core Operations
- **📏 Resize** - Change image dimensions with presets for common sizes
- **✂️ Crop** - Interactive cropping with aspect ratio presets
- **🎨 Filters** - Brightness, contrast, blur, grayscale, sepia, sharpen
- **💧 Watermark** - Add text or image watermarks
- **📦 Optimize** - Compress and optimize images for web
- **🔄 Batch** - Process multiple images at once

### 🎯 Key Features
- Drag & drop file upload
- Multiple file support
- Real-time preview
- Preset dimensions for common use cases
- Format conversion (JPEG, PNG, WebP)
- Quality control
- Maintain aspect ratio options
- Progress tracking
- Download individual or batch files

## Installation

### Quick Setup

1. **Copy the entire `image-tool` folder** to your project's public directory

2. **Ensure PHP GD extension is enabled**
   ```bash
   # Check if GD is installed
   php -m | grep -i gd
   
   # Install if needed (Ubuntu/Debian)
   sudo apt-get install php-gd
   
   # Install if needed (macOS with Homebrew)
   brew install php-gd
   ```

3. **Set proper permissions**
   ```bash
   chmod 755 image-tool
   chmod 777 image-tool/temp  # Or create temp directory
   ```

4. **Access the tool**
   ```
   http://your-domain.com/image-tool/
   ```

## Usage

### Basic Workflow

1. **Upload Images**
   - Click the upload area or drag & drop images
   - Multiple files supported
   - Supports: JPEG, PNG, GIF, WebP

2. **Choose Operation**
   - Select a tab: Resize, Crop, Filters, Watermark, Optimize, or Batch
   - Configure settings for your desired operation

3. **Process**
   - Click "Process Images"
   - Wait for processing to complete
   - Preview results

4. **Download**
   - Download individual processed images
   - Or download all as ZIP (batch mode)

### Operation Guides

#### Resize
- Select from preset dimensions or enter custom
- Choose fit mode:
  - **Exact**: Uses exact dimensions (may stretch)
  - **Contain**: Fits inside dimensions (no cropping)
  - **Cover**: Fills dimensions (may crop)
- Toggle "Maintain aspect ratio" as needed

#### Crop
- Select aspect ratio preset or use free crop
- Choose shape (rectangle or circle)
- Use controls to rotate/flip
- Drag to select crop area

#### Filters
- Adjust brightness, contrast, blur with sliders
- Apply quick filters: Grayscale, Sepia, Sharpen
- Enable auto-enhance for automatic adjustments

#### Watermark
- **Text**: Add custom text with size and opacity control
- **Image**: Upload logo with scale and opacity control
- Position: Top-left, top-right, bottom-left, bottom-right, center

#### Optimize
- Set quality (1-100%)
- Target specific file size
- Convert format
- Strip metadata
- Progressive JPEG option

#### Batch
- Apply same operations to all uploaded images
- Choose naming convention
- Download as ZIP or individual files

## Presets

### Resize Presets

**Hero & Banners**
- Hero Image - Desktop: 1920×1080
- Hero Banner - Wide: 1440×600
- Page Banner: 1200×400
- Hero Image - Tablet: 768×400

**Service & Product Cards**
- Service Card: 600×400
- Product Card: 400×300
- Square Card: 300×300

**Thumbnails**
- Small: 150×150
- Medium: 200×200
- Wide: 300×200

**Social Media**
- Facebook/LinkedIn: 1200×630
- Instagram Square: 1080×1080
- Instagram Story: 1080×1920

## Configuration

Edit `includes/config.php` to customize:

```php
// Maximum file size (bytes)
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Allowed image types
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Default quality
define('DEFAULT_QUALITY', 90);

// Maximum dimension
define('MAX_DIMENSION', 5000);
```

## File Structure

```
image-tool/
├── index.php              # Main entry point
├── README.md             # This file
├── includes/
│   ├── config.php        # Configuration
│   └── ImageProcessor.php # Core image processing class
├── ajax/
│   ├── process.php       # Process images handler
│   └── download.php      # Download handler
├── assets/
│   ├── css/
│   │   └── style.css     # Styles
│   └── js/
│       └── app.js        # JavaScript functionality
└── views/
    └── tabs/
        ├── resize.php    # Resize tab
        ├── crop.php      # Crop tab
        ├── filters.php   # Filters tab
        ├── watermark.php # Watermark tab
        ├── optimize.php  # Optimize tab
        └── batch.php     # Batch tab
```

## Requirements

- PHP 8.0 or higher
- GD extension
- Write permissions for temp directory
- Modern web browser with JavaScript enabled

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Opera (latest)

## Troubleshooting

### Images not processing
- Check PHP error logs
- Verify GD extension is installed: `php -m | grep gd`
- Ensure temp directory has write permissions

### Upload fails
- Check `upload_max_filesize` in php.ini
- Check `post_max_size` in php.ini
- Verify file permissions

### Memory errors
- Increase `memory_limit` in php.ini
- Process fewer images at once
- Reduce image dimensions

## Security Notes

- Files are stored in temporary directory
- Automatic cleanup after download (optional)
- File type validation
- Size limits enforced
- Sanitized file names

## Performance Tips

1. **For large images**: Increase PHP memory limit
2. **For batch processing**: Process in smaller batches
3. **For web optimization**: Use WebP format with 80-85% quality
4. **For faster processing**: Disable unnecessary operations

## License

Free to use in any project. No attribution required.

## Support

For issues or questions, refer to the code comments or PHP documentation for GD functions.

## Changelog

### Version 1.0.0
- Initial release
- Resize, crop, filters, watermark, optimize, batch operations
- Drag & drop upload
- Multiple file support
- Preset dimensions
- Format conversion
- Quality control

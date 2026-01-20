# GrowBuilder Image Optimization

**Last Updated:** January 20, 2026
**Status:** Production

## Overview

Automatic client-side image optimization for GrowBuilder uploads. Images are resized and compressed before upload to improve performance, reduce storage costs, and enhance user experience.

## Features

- **Automatic Optimization**: Images over 500KB are automatically optimized
- **Smart Resizing**: Maintains aspect ratio while reducing dimensions
- **Format Conversion**: Converts to optimal formats (JPEG for photos, PNG for logos)
- **Quality Control**: Balances file size with visual quality
- **Real-time Feedback**: Shows compression results to users
- **Type-Specific Optimization**: Different settings for logos vs backgrounds

## Implementation

### Files Created/Modified

1. **resources/js/pages/GrowBuilder/Editor/composables/useImageOptimization.ts** (NEW)
   - Core optimization logic
   - Canvas-based image resizing
   - Format conversion
   - File size utilities

2. **resources/js/pages/GrowBuilder/Editor/Index.vue**
   - Integrated optimization into upload flow
   - Added optimization for drag-and-drop uploads
   - Type-specific optimization (logo vs background)

## Optimization Settings

### Logo Optimization
- Max Width: 800px
- Max Height: 400px
- Quality: 90%
- Format: PNG (preserves transparency)

### Background Images
- Max Width: 1920px
- Max Height: 1080px
- Quality: 85%
- Format: JPEG

### General Images
- Max Width: 1920px
- Max Height: 1080px
- Quality: 85%
- Format: JPEG

## Usage

### Automatic Optimization

Images are automatically optimized when:
1. Uploaded via media library
2. Dragged and dropped onto editor
3. File size exceeds 500KB threshold

### User Feedback

Users see toast notifications showing:
- "Optimizing image..." during processing
- "Image optimized: 2.5MB → 450KB (82% smaller)" on success

## Technical Details

### Optimization Process

1. **File Validation**: Check if file is an image
2. **Size Check**: Determine if optimization needed (>500KB)
3. **Image Loading**: Load image into memory
4. **Dimension Calculation**: Calculate new dimensions maintaining aspect ratio
5. **Canvas Rendering**: Draw resized image on canvas with high-quality smoothing
6. **Compression**: Convert to blob with specified quality
7. **File Creation**: Create new File object with optimized data
8. **Upload**: Send optimized file to server

### Browser Compatibility

- Uses HTML5 Canvas API
- Supported in all modern browsers
- Graceful fallback: uploads original if optimization fails

## Benefits

### Performance
- Faster page loads (smaller images)
- Reduced bandwidth usage
- Better mobile experience

### Storage
- Lower storage costs
- More efficient CDN usage
- Reduced backup sizes

### User Experience
- Faster uploads
- Transparent optimization
- No quality loss visible to users

## API Reference

### useImageOptimization()

```typescript
const {
    optimizeImage,      // Generic optimization
    optimizeLogo,       // Logo-specific optimization
    optimizeBackground, // Background-specific optimization
    optimizeThumbnail,  // Thumbnail optimization
    formatFileSize,     // Format bytes to human-readable
    isImage,            // Check if file is image
    needsOptimization,  // Check if optimization needed
} = useImageOptimization();
```

### optimizeImage(file, options)

```typescript
interface OptimizationOptions {
    maxWidth?: number;        // Default: 1920
    maxHeight?: number;       // Default: 1080
    quality?: number;         // Default: 0.85 (85%)
    format?: 'jpeg' | 'png' | 'webp';  // Default: 'jpeg'
    maintainAspectRatio?: boolean;     // Default: true
}

interface OptimizationResult {
    file: File;              // Optimized file
    originalSize: number;    // Original size in bytes
    optimizedSize: number;   // New size in bytes
    compressionRatio: number; // Percentage saved
    width: number;           // Final width
    height: number;          // Final height
}
```

## Examples

### Basic Usage

```typescript
import { useImageOptimization } from './composables/useImageOptimization';

const { optimizeImage, formatFileSize } = useImageOptimization();

// Optimize an image
const result = await optimizeImage(file, {
    maxWidth: 1920,
    maxHeight: 1080,
    quality: 0.85,
    format: 'jpeg',
});

console.log(`Saved ${result.compressionRatio}%`);
console.log(`${formatFileSize(result.originalSize)} → ${formatFileSize(result.optimizedSize)}`);
```

### Logo Optimization

```typescript
const { optimizeLogo } = useImageOptimization();

// Optimize a logo (smaller dimensions, PNG format)
const result = await optimizeLogo(logoFile);
```

## Troubleshooting

### Image Quality Issues
- Increase quality setting (0.85 → 0.9)
- Use PNG format for images with transparency
- Adjust maxWidth/maxHeight for larger images

### Large File Sizes
- Decrease quality setting (0.85 → 0.75)
- Reduce maxWidth/maxHeight
- Convert PNG to JPEG where appropriate

### Optimization Failures
- Check browser console for errors
- Verify file is valid image format
- Ensure sufficient memory available

## Future Enhancements

- [ ] WebP format support for better compression
- [ ] Progressive JPEG encoding
- [ ] Batch optimization for multiple uploads
- [ ] Server-side optimization fallback
- [ ] Custom optimization profiles per site
- [ ] Image format auto-detection and conversion
- [ ] EXIF data preservation
- [ ] Orientation correction

## Changelog

### January 20, 2026
- Initial implementation of client-side optimization
- Added logo-specific optimization (800x400, PNG)
- Added background optimization (1920x1080, JPEG)
- Integrated into upload and drag-drop flows
- Added user feedback with compression stats
- Created useImageOptimization composable

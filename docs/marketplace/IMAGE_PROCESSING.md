# Marketplace Image Processing

**Last Updated:** January 17, 2026  
**Status:** Production

## Overview

The marketplace uses a hybrid approach combining minimum size validation with optional cropping tools:
- **Minimum size requirement**: 500×500 pixels (enforced at upload)
- **Optional cropping**: Available in media library for users who want to adjust images
- **Smart processing**: Avoids redundant processing by detecting image source

## Image Requirements

### Minimum Dimensions
- **Required**: 500×500 pixels minimum
- **Recommended**: 1000×1000 pixels or larger
- **Aspect Ratio**: Square (1:1) works best for product listings
- **Max File Size**: 5MB per image
- **Formats**: JPG, PNG, WebP

### Validation
Images are validated on upload:
```php
'images.*' => 'image|max:5120|dimensions:min_width=500,min_height=500'
```

If an image is too small, the user receives a clear error message explaining the requirement.

## Image Processing Flow

### Single Processing Point: Media Library Upload

All image processing happens ONCE when images are uploaded to the media library:

1. **Upload to Media Library** (`SellerMediaController::store`)
   - Validates minimum dimensions (500×500px)
   - Validates file size (max 5MB)
   - Stores original image
   - Creates thumbnail (300px wide)
   - Records dimensions and metadata

2. **Use in Products** (`SellerProductController::store/update`)
   - References existing media library images (no re-processing)
   - Direct uploads are validated and stored raw
   - No redundant processing

### Why This Approach?

**Efficiency**: Process once, use many times
- Upload to library → processed once
- Use in multiple products → just reference the path
- No duplicate processing or storage

**Consistency**: All images processed the same way
- Same quality settings
- Same thumbnail generation
- Predictable results

**Performance**: Faster product creation
- No processing delay when adding products
- Images already optimized and ready to use

## Frontend Image Editor (Optional)

The image editor is available in the media library for users who want to crop or adjust their images before using them.

### Features

1. **Crop Tool**: Drag and resize crop box (aspect ratio can be locked to 1:1)
2. **Adjustments**: Brightness, contrast, saturation, blur
3. **Export Options**: Quality and scale settings
4. **Output**: JPEG at 90% quality by default

### Usage Pattern

1. User uploads image to media library
2. **Optional**: User can click "Edit" to open image editor
3. User crops/adjusts image if desired
4. Cropped image is saved with `cropped_` prefix
5. User selects image for product

**Note**: The cropping tool is optional. Users can upload properly-sized images and use them directly without any editing.

## Backend Processing Phases

### Current Phase: Phase 2 (Optimized)

**Configuration:** `config/marketplace.php`
```php
'images' => [
    'processing_phase' => 'phase2',
]
```

### Processing Logic

```php
// 1. Check if cropped image (skip processing)
if (str_starts_with($filename, 'cropped_')) {
    return uploadRaw($file);
}

// 2. Process based on phase
match ($phase) {
    'mvp' => uploadRaw($file),
    'phase2' => uploadOptimized($file),
    'phase3' => uploadWithBackgroundRemoval($file),
    'phase4' => uploadAdvanced($file),
}
```

## Image Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│ Seller Adds Product Image                                   │
└─────────────────┬───────────────────────────────────────────┘
                  │
        ┌─────────┴─────────┐
        │                   │
        ▼                   ▼
┌───────────────┐   ┌──────────────────┐
│ Media Library │   │ Direct Upload    │
│ Button        │   │ Button           │
└───────┬───────┘   └────────┬─────────┘
        │                    │
        ▼                    ▼
┌───────────────────────────────────────┐
│ MediaLibraryModal Opens               │
│ - My Media Tab                        │
│ - Stock Photos Tab                    │
└───────┬───────────────────────────────┘
        │
        ├─── Select Existing ──► Reference (media_ids) ──► No Processing
        │
        ├─── Edit/Crop ──────► ImageEditorModal
        │                      │
        │                      ├─ Crop to 1:1
        │                      ├─ Apply filters
        │                      ├─ Export as JPEG
        │                      │
        │                      ▼
        │                   cropped_*.jpg ──► Raw Upload Only
        │
        └─── Direct Upload ──► images[] ──► Full Processing
```

## Best Practices

### For Sellers
1. **Use properly sized images**: Upload images that are at least 500×500px (1000×1000px recommended)
2. **Square images work best**: Product listings display better with 1:1 aspect ratio
3. **Use Media Library**: Upload once, reuse multiple times
4. **Optional cropping**: Use the image editor if you need to adjust framing
5. **Avoid Re-uploading**: Select from library instead of uploading again

### For Developers
1. **Validate early**: Check dimensions on upload, not during processing
2. **Clear error messages**: Tell users exactly what's wrong ("Image must be at least 500×500 pixels")
3. **Check Image Source**: Detect cropped vs new uploads
4. **Avoid Double Processing**: Skip optimization for already-processed images
5. **Make cropping optional**: Don't force users through complex tools if their images are already good

## Troubleshooting

### Issue: Image rejected on upload
**Cause:** Image dimensions are below 500×500 pixels  
**Fix:** Use a larger image or resize the image before uploading

### Issue: Cropping tool not working properly
**Cause:** Complex interaction between drag and resize handlers  
**Fix:** Use properly-sized images to avoid needing to crop. The cropping tool is optional.

### Issue: Images processed twice
**Cause:** Cropped images treated as new uploads  
**Fix:** Detect `cropped_` prefix and skip processing

### Issue: Media library images re-uploaded
**Cause:** Frontend sending file instead of media ID  
**Fix:** Use `media_ids` array for references, `images` array only for new files

## Configuration

### Image Requirements
- **Minimum Dimensions:** 500×500px (enforced by validation)
- **Recommended Dimensions:** 1000×1000px or larger
- **Maximum File Size:** 5MB
- **Formats:** JPEG, PNG, WebP
- **Aspect Ratio:** Square (1:1) recommended but not enforced

### Processing Settings
```php
// config/marketplace.php
'images' => [
    'processing_phase' => 'phase2',
    'min_width' => 500,
    'min_height' => 500,
    'max_file_size' => 5120, // KB
]
```

### Validation Rules
```php
'images.*' => 'image|max:5120|dimensions:min_width=500,min_height=500'
```

## Future Enhancements

- [ ] Bulk image editing
- [ ] AI-powered background removal (Phase 3)
- [ ] Automatic image enhancement (Phase 4)
- [ ] Watermarking for featured products
- [ ] Fix cropping tool interaction issues

## Changelog

### January 17, 2026
- Added minimum dimension validation (500×500px)
- Made cropping tool optional instead of required
- Updated documentation to reflect hybrid approach
- Simplified user experience following industry best practices


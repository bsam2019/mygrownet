<?php
/**
 * Process images AJAX handler
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['images'])) {
    echo json_encode(['error' => 'No images uploaded']);
    exit;
}

try {
    $results = [];
    $operations = json_decode($_POST['operations'] ?? '{}', true);
    $tab = $_POST['tab'] ?? 'resize';
    
    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['images']['error'][$index] !== UPLOAD_ERR_OK) {
            continue;
        }
        
        $originalName = $_FILES['images']['name'][$index];
        $processor = new ImageProcessor();
        $processor->load($tmpName);
        
        // Check if watermark should be applied (can be combined with other operations)
        if (isset($_POST['watermark_text']) && !empty($_POST['watermark_text'])) {
            $color = isset($_POST['watermark_color']) ? explode(',', $_POST['watermark_color']) : [255, 255, 255];
            
            $processor->addTextWatermark($_POST['watermark_text'], [
                'font' => (int) ($_POST['watermark_font'] ?? 3),
                'size' => (int) ($_POST['watermark_size'] ?? 20),
                'color' => $color,
                'opacity' => (int) ($_POST['watermark_opacity'] ?? 50),
                'position' => $_POST['watermark_position'] ?? 'bottom-right',
                'bold' => isset($_POST['watermark_bold']) && $_POST['watermark_bold'] === '1',
                'outline' => isset($_POST['watermark_outline']) && $_POST['watermark_outline'] === '1',
                'rotation' => (int) ($_POST['watermark_rotation'] ?? 0)
            ]);
        }
        
        // Apply crop first if available (regardless of active tab)
        if (isset($operations['crop']) && !empty($operations['crop'])) {
            $crop = $operations['crop'];
            $x = (int) ($crop['x'] ?? 0);
            $y = (int) ($crop['y'] ?? 0);
            $width = (int) ($crop['width'] ?? 100);
            $height = (int) ($crop['height'] ?? 100);
            
            // Apply rotation if set
            if (isset($crop['rotate']) && $crop['rotate'] != 0) {
                $processor->rotate($crop['rotate']);
            }
            
            // Apply flip if set
            if (isset($crop['scaleX']) && $crop['scaleX'] < 0) {
                $processor->flip('horizontal');
            }
            if (isset($crop['scaleY']) && $crop['scaleY'] < 0) {
                $processor->flip('vertical');
            }
            
            // Apply crop
            $processor->crop($x, $y, $width, $height);
        }
        
        // Apply resize if available (can be combined with crop)
        if (isset($_POST['width']) && !empty($_POST['width'])) {
            $width = (int) $_POST['width'];
            $height = !empty($_POST['height']) ? (int) $_POST['height'] : null;
            $maintain = isset($_POST['maintain_aspect']) && $_POST['maintain_aspect'] === 'true';
            $fit = $_POST['fit'] ?? 'exact';
            
            $processor->resize($width, $height, [
                'maintain_aspect' => $maintain,
                'fit' => $fit
            ]);
        }
        
        // Apply operations based on active tab
        switch ($tab) {
            case 'resize':
                // Already handled above
                break;
                
            case 'crop':
                // Already handled above
                break;
                
            case 'filters':
                if (isset($operations['filters'])) {
                    $filters = $operations['filters'];
                    if (isset($filters['brightness'])) {
                        $processor->brightness($filters['brightness']);
                    }
                    if (isset($filters['contrast'])) {
                        $processor->contrast($filters['contrast']);
                    }
                    if (isset($filters['blur']) && $filters['blur'] > 0) {
                        $processor->blur($filters['blur']);
                    }
                    if (isset($filters['quick'])) {
                        switch ($filters['quick']) {
                            case 'grayscale':
                                $processor->grayscale();
                                break;
                            case 'sharpen':
                                $processor->sharpen();
                                break;
                        }
                    }
                }
                break;
                
            case 'background':
                $method = $_POST['bg_method'] ?? 'auto';
                $tolerance = (int) ($_POST['bg_tolerance'] ?? 30);
                
                switch ($method) {
                    case 'white':
                        $processor->removeWhiteBackground($tolerance);
                        break;
                    case 'custom':
                        $color = $_POST['bg_color'] ?? '#ffffff';
                        $color = ltrim($color, '#');
                        $r = hexdec(substr($color, 0, 2));
                        $g = hexdec(substr($color, 2, 2));
                        $b = hexdec(substr($color, 4, 2));
                        $processor->removeBackgroundByColor($r, $g, $b, $tolerance);
                        break;
                    case 'auto':
                    default:
                        $processor->removeBackground(['tolerance' => $tolerance]);
                        break;
                }
                break;
                
            case 'watermark':
                if (isset($operations['watermark'])) {
                    $wm = $operations['watermark'];
                    if ($wm['type'] === 'text' && !empty($wm['text'])) {
                        // Parse color
                        $color = isset($wm['color']) ? explode(',', $wm['color']) : [255, 255, 255];
                        
                        $processor->addTextWatermark($wm['text'], [
                            'size' => (int) ($wm['fontSize'] ?? 20),
                            'color' => $color,
                            'opacity' => (int) ($wm['opacity'] ?? 50),
                            'position' => $wm['position'] ?? 'bottom-right'
                        ]);
                    } elseif ($wm['type'] === 'image' && !empty($wm['imagePath'])) {
                        $processor->addImageWatermark($wm['imagePath'], [
                            'opacity' => (int) ($wm['opacity'] ?? 50),
                            'position' => $wm['position'] ?? 'bottom-right',
                            'scale' => (float) ($wm['scale'] ?? 0.2)
                        ]);
                    }
                }
                break;
        }
        
        // Set quality
        $quality = (int) ($_POST['quality'] ?? 90);
        $processor->setQuality($quality);
        
        // Determine output format
        $format = $_POST['format'] ?? 'same';
        if ($format === 'same') {
            $format = null;
        }
        
        // Force PNG format for background removal (to preserve transparency)
        if ($tab === 'background') {
            $format = 'png';
        }
        
        // Save processed image
        $extension = $format ?? pathinfo($originalName, PATHINFO_EXTENSION);
        $outputFilename = pathinfo($originalName, PATHINFO_FILENAME) . '-processed-' . time() . '-' . $index . '.' . $extension;
        $outputPath = OUTPUT_DIR . '/' . $outputFilename;
        $processor->save($outputPath, $format);
        
        // Create preview
        $previewFilename = 'preview-' . $outputFilename;
        $previewPath = PREVIEW_DIR . '/' . $previewFilename;
        copy($outputPath, $previewPath);
        
        // Get dimensions
        $dims = $processor->getDimensions();
        
        $results[] = [
            'name' => $originalName,
            'filename' => $outputFilename,
            'preview' => $previewFilename,
            'width' => $dims['width'],
            'height' => $dims['height'],
            'size' => filesize($outputPath)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'files' => $results
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

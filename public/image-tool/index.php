<?php
/**
 * Advanced Image Processing Tool
 * 
 * A comprehensive image processing solution with resize, crop, filters, watermarks, and more.
 * Portable - copy the entire 'image-tool' folder to any project.
 */

require_once __DIR__ . '/includes/ImageProcessor.php';
require_once __DIR__ . '/includes/config.php';

session_start();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'process':
            require __DIR__ . '/ajax/process.php';
            break;
        case 'batch':
            require __DIR__ . '/ajax/batch.php';
            break;
        case 'download':
            require __DIR__ . '/ajax/download.php';
            break;
        case 'preview':
            require __DIR__ . '/ajax/preview.php';
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}

// Serve preview images
if (isset($_GET['preview'])) {
    $file = basename($_GET['preview']);
    $filePath = TEMP_DIR . '/previews/' . $file;
    
    if (file_exists($filePath)) {
        $imageInfo = getimagesize($filePath);
        header('Content-Type: ' . $imageInfo['mime']);
        readfile($filePath);
        exit;
    }
}

// Main interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Image Tool</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <style>
        <?php include __DIR__ . '/assets/css/style.css'; ?>
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <h1>🎨 Advanced Image Tool</h1>
            <p class="subtitle">Professional image processing with resize, crop, filters, watermarks & more</p>
        </header>

        <!-- Main Content: Sidebar + Preview Area -->
        <div class="main-content">
            <!-- Left Sidebar: Settings -->
            <div class="sidebar">
                <div class="sidebar-content">
                    <!-- Upload and Operations Side by Side -->
                    <div class="section section-compact">
                        <div class="sidebar-grid">
                            <!-- Left: Upload -->
                            <div class="upload-column">
                                <h2>📤 Upload</h2>
                                <div class="upload-area" id="uploadArea">
                                    <input type="file" id="imageInput" accept="image/*" multiple hidden>
                                    <div class="upload-prompt">
                                        <span class="upload-icon">📁</span>
                                        <p>Click or drag</p>
                                        <small>Images</small>
                                    </div>
                                </div>
                                <div id="imageList" class="image-list"></div>
                            </div>

                            <!-- Right: Operations -->
                            <div class="operations-column">
                                <h2>🛠️ Operations</h2>
                                <div class="tabs">
                                    <button class="tab-btn active" data-tab="resize">📏 Resize</button>
                                    <button class="tab-btn" data-tab="crop">✂️ Crop</button>
                                    <button class="tab-btn" data-tab="filters">🎨 Filters</button>
                                    <button class="tab-btn" data-tab="watermark">💧 Watermark</button>
                                    <button class="tab-btn" data-tab="background">🎭 Remove BG</button>
                                    <button class="tab-btn" data-tab="optimize">📦 Optimize</button>
                                    <button class="tab-btn" data-tab="batch">🔄 Batch</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Contents -->
                    <div class="section">
                        <!-- Resize Tab -->
                        <div class="tab-content active" id="resize-tab">
                            <?php include __DIR__ . '/views/tabs/resize.php'; ?>
                        </div>

                        <!-- Crop Tab -->
                        <div class="tab-content" id="crop-tab">
                            <?php include __DIR__ . '/views/tabs/crop.php'; ?>
                        </div>

                        <!-- Filters Tab -->
                        <div class="tab-content" id="filters-tab">
                            <?php include __DIR__ . '/views/tabs/filters.php'; ?>
                        </div>

                        <!-- Watermark Tab -->
                        <div class="tab-content" id="watermark-tab">
                            <?php include __DIR__ . '/views/tabs/watermark.php'; ?>
                        </div>

                        <!-- Background Removal Tab -->
                        <div class="tab-content" id="background-tab">
                            <?php include __DIR__ . '/views/tabs/background.php'; ?>
                        </div>

                        <!-- Optimize Tab -->
                        <div class="tab-content" id="optimize-tab">
                            <?php include __DIR__ . '/views/tabs/optimize.php'; ?>
                        </div>

                        <!-- Batch Tab -->
                        <div class="tab-content" id="batch-tab">
                            <?php include __DIR__ . '/views/tabs/batch.php'; ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-bar">
                    <button class="btn btn-primary" id="processBtn">🚀 Process</button>
                    <button class="btn btn-secondary" id="resetBtn">↺ Reset</button>
                </div>
            </div>

            <!-- Right Content Area: Live Preview -->
            <div class="content-area">
                <div class="preview-main" id="previewMain">
                    <div class="preview-placeholder" id="previewPlaceholder">
                        <div class="icon">🖼️</div>
                        <h3>No Image Loaded</h3>
                        <p>Upload an image to see live preview</p>
                    </div>
                    <div class="preview-canvas-container" id="previewCanvasContainer" style="display: none;">
                        <canvas id="mainPreviewCanvas"></canvas>
                    </div>
                </div>

                <!-- Results Section -->
                <div class="section preview-section" id="previewSection" style="display: none;">
                    <h2>✅ Processed Results</h2>
                    <div id="previewContainer"></div>
                </div>
            </div>
        </div>

        <!-- Progress Modal -->
        <div class="modal" id="progressModal">
            <div class="modal-content">
                <h3>Processing Images...</h3>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p id="progressText">Please wait...</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        <?php include __DIR__ . '/assets/js/app.js'; ?>
    </script>
</body>
</html>

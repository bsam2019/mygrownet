/**
 * Advanced Image Tool - Main JavaScript
 */

// Global state
const state = {
    uploadedFiles: [],
    currentTab: 'resize',
    cropper: null,
    previewImage: null,
    originalImageData: null,
    operations: {
        resize: {},
        crop: {},
        filters: {},
        watermark: {},
        optimize: {}
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeUpload();
    initializeTabs();
    initializeResize();
    initializeCrop();
    initializeFilters();
    initializeWatermark();
    initializeBackground();
    initializeBatch();
    initializeActions();
});

// Upload functionality
function initializeUpload() {
    const uploadArea = document.getElementById('uploadArea');
    const imageInput = document.getElementById('imageInput');
    
    uploadArea.addEventListener('click', () => imageInput.click());
    
    imageInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
}

function handleFiles(files) {
    const imageList = document.getElementById('imageList');
    
    Array.from(files).forEach((file, index) => {
        if (!file.type.startsWith('image/')) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const item = createImageItem(file, e.target.result);
            imageList.appendChild(item);
            state.uploadedFiles.push({
                file: file,
                preview: e.target.result
            });
            
            // Use first image for preview
            if (index === 0 && !state.previewImage) {
                loadPreviewImage(e.target.result);
            }
        };
        reader.readAsDataURL(file);
    });
}

function loadPreviewImage(src) {
    const img = new Image();
    img.onload = function() {
        state.previewImage = img;
        showPreviewCanvas();
        // Update previews based on current tab
        updateMainPreview();
    };
    img.src = src;
}

function showPreviewCanvas() {
    const placeholder = document.getElementById('previewPlaceholder');
    const canvasContainer = document.getElementById('previewCanvasContainer');
    
    if (placeholder) placeholder.style.display = 'none';
    if (canvasContainer) canvasContainer.style.display = 'flex';
}

function updateMainPreview() {
    if (!state.previewImage) return;
    
    const canvas = document.getElementById('mainPreviewCanvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Calculate canvas size to fit the preview area
    const maxWidth = window.innerWidth - 450; // Account for sidebar
    const maxHeight = window.innerHeight - 150;
    const scale = Math.min(1, maxWidth / state.previewImage.width, maxHeight / state.previewImage.height);
    
    canvas.width = state.previewImage.width * scale;
    canvas.height = state.previewImage.height * scale;
    
    // Draw image
    ctx.drawImage(state.previewImage, 0, 0, canvas.width, canvas.height);
    
    // Apply effects based on current tab
    if (state.currentTab === 'filters') {
        applyFilterPreview(canvas, ctx);
    } else if (state.currentTab === 'watermark') {
        applyWatermarkPreview(canvas, ctx, scale);
    } else if (state.currentTab === 'background') {
        applyBackgroundRemovalPreview(canvas, ctx);
    }
}

function applyFilterPreview(canvas, ctx) {
    const brightnessEl = document.getElementById('brightness');
    const contrastEl = document.getElementById('contrast');
    const saturationEl = document.getElementById('saturation');
    const blurEl = document.getElementById('blur');
    const hueRotateEl = document.getElementById('hueRotate');
    const invertEl = document.getElementById('invertColors');
    
    const brightness = brightnessEl ? parseInt(brightnessEl.value) : 0;
    const contrast = contrastEl ? parseInt(contrastEl.value) : 0;
    const saturation = saturationEl ? parseInt(saturationEl.value) : 0;
    const blur = blurEl ? parseInt(blurEl.value) : 0;
    const hueRotate = hueRotateEl ? parseInt(hueRotateEl.value) : 0;
    const invert = invertEl ? invertEl.checked : false;
    
    const filters = [];
    if (brightness !== 0) filters.push(`brightness(${100 + brightness}%)`);
    if (contrast !== 0) filters.push(`contrast(${100 + contrast}%)`);
    if (saturation !== 0) filters.push(`saturate(${100 + saturation}%)`);
    if (blur > 0) filters.push(`blur(${blur}px)`);
    if (hueRotate !== 0) filters.push(`hue-rotate(${hueRotate}deg)`);
    if (invert) filters.push(`invert(100%)`);
    
    // Apply quick filter if set
    if (state.operations.filters.quick) {
        switch (state.operations.filters.quick) {
            case 'grayscale':
                filters.push('grayscale(100%)');
                break;
            case 'sepia':
                filters.push('sepia(100%)');
                break;
            case 'vintage':
                filters.push('sepia(50%) contrast(110%) brightness(110%)');
                break;
        }
    }
    
    canvas.style.filter = filters.join(' ');
}

function applyWatermarkPreview(canvas, ctx, scale) {
    const text = document.getElementById('watermarkText');
    
    // Clear any filters first
    canvas.style.filter = '';
    
    if (!text || !text.value) {
        return;
    }
    
    // Get watermark settings with fallbacks
    const wmText = text.value;
    const fontSizeEl = document.getElementById('watermarkFontSize');
    const opacityEl = document.getElementById('watermarkOpacity');
    const positionEl = document.getElementById('watermarkPosition');
    const colorInput = document.getElementById('watermarkColor');
    
    if (!fontSizeEl || !opacityEl || !positionEl || !colorInput) {
        console.error('Watermark elements not found');
        return;
    }
    
    const fontSize = parseInt(fontSizeEl.value) * scale;
    const opacity = parseInt(opacityEl.value) / 100;
    const position = positionEl.value;
    const colorRGB = colorInput.value.split(',').map(Number);
    
    // Set text style
    ctx.font = `bold ${fontSize}px Arial`;
    ctx.fillStyle = `rgba(${colorRGB[0]}, ${colorRGB[1]}, ${colorRGB[2]}, ${opacity})`;
    
    // Calculate position
    const textWidth = ctx.measureText(wmText).width;
    const padding = 20 * scale;
    let x, y;
    
    switch (position) {
        case 'top-left':
            x = padding;
            y = padding + fontSize;
            break;
        case 'top-right':
            x = canvas.width - textWidth - padding;
            y = padding + fontSize;
            break;
        case 'bottom-left':
            x = padding;
            y = canvas.height - padding;
            break;
        case 'bottom-right':
            x = canvas.width - textWidth - padding;
            y = canvas.height - padding;
            break;
        case 'center':
            x = (canvas.width - textWidth) / 2;
            y = canvas.height / 2;
            break;
        default:
            x = canvas.width - textWidth - padding;
            y = canvas.height - padding;
    }
    
    // Draw watermark
    ctx.fillText(wmText, x, y);
}

function createImageItem(file, preview) {
    const div = document.createElement('div');
    div.className = 'image-item';
    div.innerHTML = `
        <img src="${preview}" alt="${file.name}">
        <div class="image-item-info">
            <div>${file.name}</div>
            <div>${(file.size / 1024).toFixed(2)} KB</div>
        </div>
        <button class="image-item-remove" onclick="removeImage(this)">×</button>
    `;
    return div;
}

function removeImage(btn) {
    const item = btn.closest('.image-item');
    const index = Array.from(item.parentNode.children).indexOf(item);
    state.uploadedFiles.splice(index, 1);
    item.remove();
}

// Tab functionality
function initializeTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            switchTab(tab);
        });
    });
}

function switchTab(tab) {
    // Update buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    
    // Update content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.toggle('active', content.id === `${tab}-tab`);
    });
    
    state.currentTab = tab;
    
    // Handle crop tab specially
    if (tab === 'crop') {
        initializeCropper();
    } else {
        destroyCropper();
        // Update preview when switching tabs
        updateMainPreview();
    }
}

// Resize functionality
function initializeResize() {
    const preset = document.getElementById('resizePreset');
    const width = document.getElementById('resizeWidth');
    const height = document.getElementById('resizeHeight');
    const maintain = document.getElementById('maintainAspect');
    
    preset.addEventListener('change', function() {
        if (this.value) {
            const [w, h] = this.value.split('x');
            width.value = w;
            height.value = h;
            maintain.checked = false;
        }
    });
    
    // Store resize settings
    [width, height, maintain].forEach(el => {
        el.addEventListener('change', updateResizeSettings);
    });
    
    document.getElementById('resizeFit').addEventListener('change', updateResizeSettings);
}

function updateResizeSettings() {
    state.operations.resize = {
        width: parseInt(document.getElementById('resizeWidth').value) || 800,
        height: parseInt(document.getElementById('resizeHeight').value) || null,
        maintain: document.getElementById('maintainAspect').checked,
        fit: document.getElementById('resizeFit').value
    };
}

// Crop functionality
function initializeCrop() {
    const aspectRatio = document.getElementById('cropAspect');
    const shape = document.getElementById('cropShape');
    const cropThenResize = document.getElementById('cropThenResize');
    const cropResizeOptions = document.getElementById('cropResizeOptions');
    
    if (aspectRatio) {
        aspectRatio.addEventListener('change', function() {
            if (state.cropper) {
                let ratio = NaN; // Free crop
                if (this.value !== 'free') {
                    const [w, h] = this.value.split(':');
                    ratio = parseFloat(w) / parseFloat(h);
                }
                state.cropper.setAspectRatio(ratio);
            }
        });
    }
    
    if (shape) {
        shape.addEventListener('change', function() {
            if (state.cropper) {
                // Destroy and recreate with new shape
                const cropData = state.cropper.getData();
                destroyCropper();
                initializeCropper();
                if (cropData) {
                    state.cropper.setData(cropData);
                }
            }
        });
    }
    
    // Toggle resize options
    if (cropThenResize && cropResizeOptions) {
        cropThenResize.addEventListener('change', function() {
            cropResizeOptions.style.display = this.checked ? 'block' : 'none';
        });
    }
}

function initializeCropper() {
    console.log('initializeCropper called');
    console.log('state.previewImage:', state.previewImage);
    console.log('state.uploadedFiles:', state.uploadedFiles);
    
    if (!state.previewImage) {
        alert('Please upload an image first');
        return;
    }
    
    // Get the main preview area
    const previewMain = document.getElementById('previewMain');
    const placeholder = document.getElementById('previewPlaceholder');
    const canvasContainer = document.getElementById('previewCanvasContainer');
    const cropControls = document.getElementById('cropControls');
    
    console.log('previewMain:', previewMain);
    console.log('cropControls:', cropControls);
    
    // Hide placeholder and canvas
    if (placeholder) placeholder.style.display = 'none';
    if (canvasContainer) canvasContainer.style.display = 'none';
    if (cropControls) cropControls.style.display = 'flex';
    
    // Create or get crop container in main preview area
    let cropContainer = document.getElementById('mainCropContainer');
    if (!cropContainer) {
        cropContainer = document.createElement('div');
        cropContainer.id = 'mainCropContainer';
        cropContainer.style.cssText = 'width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 20px;';
        previewMain.appendChild(cropContainer);
    }
    
    // Create or get crop image
    let cropImage = document.getElementById('mainCropImage');
    if (!cropImage) {
        cropImage = document.createElement('img');
        cropImage.id = 'mainCropImage';
        cropImage.style.cssText = 'max-width: 100%; max-height: 100%; display: block;';
        cropContainer.appendChild(cropImage);
    }
    
    cropContainer.style.display = 'flex';
    
    // Set image source and wait for it to load
    if (state.uploadedFiles.length > 0) {
        // Destroy existing cropper if any
        if (state.cropper) {
            state.cropper.destroy();
            state.cropper = null;
        }
        
        cropImage.src = state.uploadedFiles[0].preview;
        
        console.log('Image src set to:', cropImage.src);
        
        // Wait for image to load before initializing cropper
        cropImage.onload = function() {
            console.log('Image loaded, initializing Cropper.js');
            console.log('Image dimensions:', cropImage.width, 'x', cropImage.height);
            
            // Get aspect ratio setting
            const aspectSelect = document.getElementById('cropAspect');
            let aspectRatio = NaN; // Free crop by default
            if (aspectSelect && aspectSelect.value !== 'free') {
                const [w, h] = aspectSelect.value.split(':');
                aspectRatio = parseFloat(w) / parseFloat(h);
            }
            
            // Get shape setting
            const shapeSelect = document.getElementById('cropShape');
            const isCircle = shapeSelect && shapeSelect.value === 'circle';
            
            console.log('Creating Cropper with aspectRatio:', aspectRatio);
            
            // Check if Cropper is available
            if (typeof Cropper === 'undefined') {
                console.error('Cropper.js library not loaded!');
                alert('Cropper.js library failed to load. Please refresh the page.');
                return;
            }
            
            // Initialize Cropper.js
            state.cropper = new Cropper(cropImage, {
                aspectRatio: aspectRatio,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.8,
                restore: false,
                guides: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                ready: function() {
                    // Apply circle shape if needed
                    if (isCircle) {
                        const cropBox = document.querySelector('.cropper-crop-box');
                        if (cropBox) {
                            cropBox.style.borderRadius = '50%';
                        }
                        const face = document.querySelector('.cropper-face');
                        if (face) {
                            face.style.borderRadius = '50%';
                        }
                    }
                },
                crop: function(event) {
                    // Store crop data
                    state.operations.crop = {
                        x: Math.round(event.detail.x),
                        y: Math.round(event.detail.y),
                        width: Math.round(event.detail.width),
                        height: Math.round(event.detail.height),
                        rotate: Math.round(event.detail.rotate),
                        scaleX: event.detail.scaleX,
                        scaleY: event.detail.scaleY
                    };
                }
            });
            
            console.log('Cropper initialized:', state.cropper);
        };
        
        // Also handle error case
        cropImage.onerror = function() {
            console.error('Failed to load image');
            alert('Failed to load image. Please try again.');
        };
    }
}

function destroyCropper() {
    if (state.cropper) {
        state.cropper.destroy();
        state.cropper = null;
    }
    
    const canvasContainer = document.getElementById('previewCanvasContainer');
    const mainCropContainer = document.getElementById('mainCropContainer');
    const cropControls = document.getElementById('cropControls');
    
    if (canvasContainer) canvasContainer.style.display = 'flex';
    if (mainCropContainer) mainCropContainer.style.display = 'none';
    if (cropControls) cropControls.style.display = 'none';
}

// Filters functionality
function initializeFilters() {
    const brightness = document.getElementById('brightness');
    const contrast = document.getElementById('contrast');
    const saturation = document.getElementById('saturation');
    const blur = document.getElementById('blur');
    const hueRotate = document.getElementById('hueRotate');
    const invertColors = document.getElementById('invertColors');
    
    if (brightness) {
        brightness.addEventListener('input', function() {
            document.getElementById('brightnessValue').textContent = this.value;
            updateFilterSettings();
            updateMainPreview();
        });
    }
    
    if (contrast) {
        contrast.addEventListener('input', function() {
            document.getElementById('contrastValue').textContent = this.value;
            updateFilterSettings();
            updateMainPreview();
        });
    }
    
    if (saturation) {
        saturation.addEventListener('input', function() {
            document.getElementById('saturationValue').textContent = this.value;
            updateFilterSettings();
            updateMainPreview();
        });
    }
    
    if (blur) {
        blur.addEventListener('input', function() {
            document.getElementById('blurValue').textContent = this.value;
            updateFilterSettings();
            updateMainPreview();
        });
    }
    
    if (hueRotate) {
        hueRotate.addEventListener('input', function() {
            document.getElementById('hueValue').textContent = this.value;
            updateFilterSettings();
            updateMainPreview();
        });
    }
    
    if (invertColors) {
        invertColors.addEventListener('change', function() {
            updateFilterSettings();
            updateMainPreview();
        });
    }
    
    document.querySelectorAll('.btn-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            applyQuickFilter(filter);
        });
    });
}

function updateFilterSettings() {
    const brightnessEl = document.getElementById('brightness');
    const contrastEl = document.getElementById('contrast');
    const saturationEl = document.getElementById('saturation');
    const blurEl = document.getElementById('blur');
    const hueRotateEl = document.getElementById('hueRotate');
    const invertEl = document.getElementById('invertColors');
    
    state.operations.filters = {
        brightness: brightnessEl ? parseInt(brightnessEl.value) : 0,
        contrast: contrastEl ? parseInt(contrastEl.value) : 0,
        saturation: saturationEl ? parseInt(saturationEl.value) : 0,
        blur: blurEl ? parseInt(blurEl.value) : 0,
        hueRotate: hueRotateEl ? parseInt(hueRotateEl.value) : 0,
        invert: invertEl ? invertEl.checked : false
    };
}

function applyQuickFilter(filter) {
    if (filter === 'none') {
        // Reset all filters
        state.operations.filters.quick = null;
        const brightnessEl = document.getElementById('brightness');
        const contrastEl = document.getElementById('contrast');
        const saturationEl = document.getElementById('saturation');
        const blurEl = document.getElementById('blur');
        const hueRotateEl = document.getElementById('hueRotate');
        const invertEl = document.getElementById('invertColors');
        
        if (brightnessEl) {
            brightnessEl.value = 0;
            document.getElementById('brightnessValue').textContent = '0';
        }
        if (contrastEl) {
            contrastEl.value = 0;
            document.getElementById('contrastValue').textContent = '0';
        }
        if (saturationEl) {
            saturationEl.value = 0;
            document.getElementById('saturationValue').textContent = '0';
        }
        if (blurEl) {
            blurEl.value = 0;
            document.getElementById('blurValue').textContent = '0';
        }
        if (hueRotateEl) {
            hueRotateEl.value = 0;
            document.getElementById('hueValue').textContent = '0';
        }
        if (invertEl) {
            invertEl.checked = false;
        }
        updateFilterSettings();
    } else {
        state.operations.filters.quick = filter;
    }
    updateMainPreview();
}

// Watermark functionality
function initializeWatermark() {
    const type = document.getElementById('watermarkType');
    const textOptions = document.getElementById('textWatermarkOptions');
    const imageOptions = document.getElementById('imageWatermarkOptions');
    
    type.addEventListener('change', function() {
        if (this.value === 'text') {
            textOptions.style.display = 'block';
            imageOptions.style.display = 'none';
        } else {
            textOptions.style.display = 'none';
            imageOptions.style.display = 'block';
        }
    });
    
    // Color swatch functionality - compact version
    const swatches = document.querySelectorAll('.color-swatch-small');
    const colorInput = document.getElementById('watermarkColor');
    const customColorInput = document.getElementById('watermarkCustomColor');
    
    swatches.forEach(swatch => {
        swatch.addEventListener('click', function() {
            // Remove active class from all swatches
            document.querySelectorAll('.color-swatch-small').forEach(s => s.classList.remove('active'));
            // Add active class to clicked swatch
            this.classList.add('active');
            // Update hidden input
            colorInput.value = this.dataset.color;
            // Update preview
            updateMainPreview();
        });
    });
    
    // Custom color input
    if (customColorInput) {
        customColorInput.addEventListener('input', function() {
            const hex = this.value;
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            
            // Remove active from all swatches
            document.querySelectorAll('.color-swatch-small').forEach(s => s.classList.remove('active'));
            // Update hidden input
            colorInput.value = `${r},${g},${b}`;
            // Update preview
            updateMainPreview();
        });
    }
    
    // Wire up all watermark inputs to update preview
    const watermarkText = document.getElementById('watermarkText');
    const watermarkFontSize = document.getElementById('watermarkFontSize');
    const watermarkOpacity = document.getElementById('watermarkOpacity');
    const watermarkPosition = document.getElementById('watermarkPosition');
    const watermarkFont = document.getElementById('watermarkFont');
    
    if (watermarkText) {
        watermarkText.addEventListener('input', updateMainPreview);
    }
    if (watermarkFontSize) {
        watermarkFontSize.addEventListener('input', updateMainPreview);
    }
    if (watermarkOpacity) {
        watermarkOpacity.addEventListener('input', updateMainPreview);
    }
    if (watermarkPosition) {
        watermarkPosition.addEventListener('change', updateMainPreview);
    }
    if (watermarkFont) {
        watermarkFont.addEventListener('change', updateMainPreview);
    }
}

// Background removal functionality
function initializeBackground() {
    const method = document.getElementById('bgRemovalMethod');
    const customColorGroup = document.getElementById('bgCustomColorGroup');
    const customColor = document.getElementById('bgCustomColor');
    const customColorHex = document.getElementById('bgCustomColorHex');
    const tolerance = document.getElementById('bgTolerance');
    const toleranceValue = document.getElementById('bgToleranceValue');
    
    if (method && customColorGroup) {
        method.addEventListener('change', function() {
            customColorGroup.style.display = this.value === 'custom' ? 'block' : 'none';
            updateMainPreview();
        });
    }
    
    if (customColor && customColorHex) {
        customColor.addEventListener('input', function() {
            customColorHex.textContent = this.value;
            updateMainPreview();
        });
    }
    
    if (tolerance && toleranceValue) {
        tolerance.addEventListener('input', function() {
            toleranceValue.textContent = this.value;
            updateMainPreview();
        });
    }
}

function applyBackgroundRemovalPreview(canvas, ctx) {
    const method = document.getElementById('bgRemovalMethod');
    const tolerance = document.getElementById('bgTolerance');
    const customColor = document.getElementById('bgCustomColor');
    
    if (!method || !tolerance) return;
    
    const toleranceValue = parseInt(tolerance.value);
    const methodValue = method.value;
    
    // Get image data
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;
    
    let targetR, targetG, targetB;
    
    // Determine target color based on method
    if (methodValue === 'white') {
        targetR = 255;
        targetG = 255;
        targetB = 255;
    } else if (methodValue === 'custom' && customColor) {
        const hex = customColor.value;
        targetR = parseInt(hex.slice(1, 3), 16);
        targetG = parseInt(hex.slice(3, 5), 16);
        targetB = parseInt(hex.slice(5, 7), 16);
    } else {
        // Auto mode - sample corners
        const corners = [
            { x: 0, y: 0 },
            { x: canvas.width - 1, y: 0 },
            { x: 0, y: canvas.height - 1 },
            { x: canvas.width - 1, y: canvas.height - 1 }
        ];
        
        // Get average color from corners
        let totalR = 0, totalG = 0, totalB = 0;
        corners.forEach(corner => {
            const index = (corner.y * canvas.width + corner.x) * 4;
            totalR += data[index];
            totalG += data[index + 1];
            totalB += data[index + 2];
        });
        
        targetR = Math.round(totalR / corners.length);
        targetG = Math.round(totalG / corners.length);
        targetB = Math.round(totalB / corners.length);
    }
    
    // Process pixels
    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        
        // Calculate color difference
        const diff = Math.abs(r - targetR) + Math.abs(g - targetG) + Math.abs(b - targetB);
        
        // If color is similar to target, make it transparent
        if (diff <= toleranceValue) {
            data[i + 3] = 0; // Set alpha to 0 (transparent)
        }
    }
    
    // Put modified image data back
    ctx.putImageData(imageData, 0, 0);
    
    // Add checkerboard pattern behind to show transparency
    const tempCanvas = document.createElement('canvas');
    tempCanvas.width = canvas.width;
    tempCanvas.height = canvas.height;
    const tempCtx = tempCanvas.getContext('2d');
    
    // Draw checkerboard
    const squareSize = 10;
    for (let y = 0; y < canvas.height; y += squareSize) {
        for (let x = 0; x < canvas.width; x += squareSize) {
            tempCtx.fillStyle = ((x / squareSize + y / squareSize) % 2 === 0) ? '#ffffff' : '#e0e0e0';
            tempCtx.fillRect(x, y, squareSize, squareSize);
        }
    }
    
    // Draw processed image on top
    tempCtx.drawImage(canvas, 0, 0);
    
    // Copy back to main canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(tempCanvas, 0, 0);
}

// Batch functionality
function initializeBatch() {
    const naming = document.getElementById('batchNaming');
    const prefixGroup = document.getElementById('batchPrefixGroup');
    const suffixGroup = document.getElementById('batchSuffixGroup');
    
    if (naming) {
        naming.addEventListener('change', function() {
            prefixGroup.style.display = this.value === 'prefix' ? 'block' : 'none';
            suffixGroup.style.display = this.value === 'suffix' ? 'block' : 'none';
        });
    }
    
    // Quality slider for optimize tab
    const qualitySlider = document.getElementById('quality');
    const qualityValue = document.getElementById('qualityValue');
    if (qualitySlider && qualityValue) {
        qualitySlider.addEventListener('input', function() {
            qualityValue.textContent = this.value;
        });
    }
}

// Action buttons
function initializeActions() {
    document.getElementById('processBtn').addEventListener('click', processImages);
    document.getElementById('resetBtn').addEventListener('click', resetAll);
}

async function processImages() {
    if (state.uploadedFiles.length === 0) {
        alert('Please upload at least one image');
        return;
    }
    
    const modal = document.getElementById('progressModal');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    modal.classList.add('active');
    
    const formData = new FormData();
    
    // Add files
    state.uploadedFiles.forEach((item, index) => {
        formData.append(`images[]`, item.file);
    });
    
    // Add operations
    formData.append('operations', JSON.stringify(state.operations));
    formData.append('tab', state.currentTab);
    
    // Add specific settings based on current tab
    if (state.currentTab === 'resize') {
        formData.append('width', document.getElementById('resizeWidth').value);
        formData.append('height', document.getElementById('resizeHeight').value || '');
        formData.append('maintain_aspect', document.getElementById('maintainAspect').checked);
        formData.append('fit', document.getElementById('resizeFit').value);
    }
    
    // Add crop+resize settings if enabled
    if (state.currentTab === 'crop') {
        const cropThenResize = document.getElementById('cropThenResize');
        if (cropThenResize && cropThenResize.checked) {
            const width = document.getElementById('cropResizeWidth');
            const height = document.getElementById('cropResizeHeight');
            const maintain = document.getElementById('cropResizeMaintainAspect');
            
            if (width && width.value) {
                formData.append('width', width.value);
                formData.append('height', height && height.value ? height.value : '');
                formData.append('maintain_aspect', maintain ? maintain.checked : true);
                formData.append('fit', 'exact');
            }
        }
    }
    
    // Add background removal settings
    if (state.currentTab === 'background') {
        const method = document.getElementById('bgRemovalMethod');
        const tolerance = document.getElementById('bgTolerance');
        const customColor = document.getElementById('bgCustomColor');
        
        if (method) formData.append('bg_method', method.value);
        if (tolerance) formData.append('bg_tolerance', tolerance.value);
        if (customColor && method.value === 'custom') {
            formData.append('bg_color', customColor.value);
        }
    }
    
    // Always check for watermark settings (can be applied with any operation)
    const wmText = document.getElementById('watermarkText');
    if (wmText && wmText.value) {
        const colorInput = document.getElementById('watermarkColor');
        const color = colorInput.value;
        
        formData.append('watermark_text', wmText.value);
        formData.append('watermark_font', document.getElementById('watermarkFont').value);
        formData.append('watermark_size', document.getElementById('watermarkFontSize').value);
        formData.append('watermark_opacity', document.getElementById('watermarkOpacity').value);
        formData.append('watermark_color', color);
        formData.append('watermark_position', document.getElementById('watermarkPosition').value);
        formData.append('watermark_bold', document.getElementById('watermarkBold').checked ? '1' : '0');
        formData.append('watermark_outline', document.getElementById('watermarkOutline').checked ? '1' : '0');
        formData.append('watermark_rotation', document.getElementById('watermarkRotation').value);
    }
    
    // Add quality
    const quality = document.getElementById('quality');
    if (quality) {
        formData.append('quality', quality.value);
    }
    
    // Add format
    const format = document.getElementById('outputFormat');
    if (format) {
        formData.append('format', format.value);
    }
    
    try {
        const response = await fetch('?action=process', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayResults(result.files);
            modal.classList.remove('active');
        } else {
            alert('Error: ' + result.error);
            modal.classList.remove('active');
        }
    } catch (error) {
        alert('Processing failed: ' + error.message);
        modal.classList.remove('active');
    }
}

function displayResults(files) {
    const previewSection = document.getElementById('previewSection');
    const previewContainer = document.getElementById('previewContainer');
    
    previewContainer.innerHTML = '';
    
    files.forEach(file => {
        const item = document.createElement('div');
        item.className = 'preview-item';
        
        // Get file extension from processed filename
        const fileExt = file.filename.split('.').pop().toUpperCase();
        
        item.innerHTML = `
            <img src="?preview=${file.preview}" alt="Processed">
            <div class="preview-info">
                <strong>${file.filename}</strong><br>
                ${file.width} × ${file.height} px<br>
                ${(file.size / 1024).toFixed(2)} KB<br>
                <span style="display: inline-block; margin-top: 4px; padding: 2px 8px; background: #667eea; color: white; border-radius: 3px; font-size: 11px; font-weight: 600;">${fileExt}</span>
            </div>
            <a href="?action=download&file=${file.filename}" class="btn btn-primary" style="display: block; margin-top: 10px; text-decoration: none; text-align: center;">
                ⬇️ Download ${fileExt}
            </a>
        `;
        previewContainer.appendChild(item);
    });
    
    previewSection.style.display = 'block';
}

function resetAll() {
    if (confirm('Reset all settings and uploaded images?')) {
        state.uploadedFiles = [];
        state.operations = {
            resize: {},
            crop: {},
            filters: {},
            watermark: {},
            optimize: {}
        };
        
        document.getElementById('imageList').innerHTML = '';
        document.getElementById('previewSection').style.display = 'none';
        
        // Reset form values
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.value = input.defaultValue || '';
        });
        document.querySelectorAll('input[type="range"]').forEach(input => {
            input.value = input.defaultValue || 0;
        });
        document.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });
    }
}

// Crop functions (global for inline onclick)
function cropRotate(angle) {
    if (state.cropper) {
        state.cropper.rotate(angle);
    }
}

function cropFlip(direction) {
    if (state.cropper) {
        if (direction === 'h') {
            state.cropper.scaleX(-state.cropper.getData().scaleX || -1);
        } else {
            state.cropper.scaleY(-state.cropper.getData().scaleY || -1);
        }
    }
}

function cropReset() {
    if (state.cropper) {
        state.cropper.reset();
    }
}

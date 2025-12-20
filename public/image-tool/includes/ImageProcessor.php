<?php
/**
 * Advanced Image Processor Class
 * Handles all image operations: resize, crop, filters, watermarks, optimization
 */

class ImageProcessor
{
    private $quality = 90;
    private $image;
    private $imageType;
    private $width;
    private $height;
    
    /**
     * Load image from file
     */
    public function load($path)
    {
        $info = getimagesize($path);
        if (!$info) {
            throw new Exception('Invalid image file');
        }
        
        $this->imageType = $info[2];
        $this->width = $info[0];
        $this->height = $info[1];
        
        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($path);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($path);
                break;
            case IMAGETYPE_WEBP:
                $this->image = imagecreatefromwebp($path);
                break;
            default:
                throw new Exception('Unsupported image type');
        }
        
        return $this;
    }
    
    /**
     * Resize image
     */
    public function resize($width, $height = null, $options = [])
    {
        $maintainAspect = $options['maintain_aspect'] ?? false;
        $fit = $options['fit'] ?? 'cover';
        
        if ($height === null) {
            $height = (int) ($this->height * ($width / $this->width));
        } elseif ($maintainAspect) {
            [$width, $height] = $this->calculateAspectRatio($width, $height, $fit);
        }
        
        $newImage = imagecreatetruecolor($width, $height);
        $this->preserveTransparency($newImage);
        
        imagecopyresampled(
            $newImage, $this->image,
            0, 0, 0, 0,
            $width, $height,
            $this->width, $this->height
        );
        
        imagedestroy($this->image);
        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;
        
        return $this;
    }
    
    /**
     * Crop image
     */
    public function crop($x, $y, $width, $height)
    {
        $newImage = imagecreatetruecolor($width, $height);
        $this->preserveTransparency($newImage);
        
        imagecopy($newImage, $this->image, 0, 0, $x, $y, $width, $height);
        
        imagedestroy($this->image);
        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;
        
        return $this;
    }
    
    /**
     * Apply brightness filter
     */
    public function brightness($level)
    {
        imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $level);
        return $this;
    }
    
    /**
     * Apply contrast filter
     */
    public function contrast($level)
    {
        imagefilter($this->image, IMG_FILTER_CONTRAST, $level);
        return $this;
    }
    
    /**
     * Apply grayscale filter
     */
    public function grayscale()
    {
        imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        return $this;
    }
    
    /**
     * Apply blur filter
     */
    public function blur($level = 1)
    {
        for ($i = 0; $i < $level; $i++) {
            imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);
        }
        return $this;
    }
    
    /**
     * Apply sharpen filter
     */
    public function sharpen()
    {
        imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }
    
    /**
     * Add text watermark
     */
    public function addTextWatermark($text, $options = [])
    {
        $font = $options['font'] ?? 3;
        $fontSize = $options['size'] ?? 20;
        $color = $options['color'] ?? [255, 255, 255];
        $opacity = $options['opacity'] ?? 50;
        $position = $options['position'] ?? 'bottom-right';
        $bold = $options['bold'] ?? false;
        $outline = $options['outline'] ?? false;
        $rotation = $options['rotation'] ?? 0;
        
        // Calculate text dimensions
        $charWidth = imagefontwidth($font);
        $charHeight = imagefontheight($font);
        $textWidth = strlen($text) * $charWidth + 20;
        $textHeight = $charHeight + 20;
        
        // Create a larger canvas for rotation
        $canvasSize = max($textWidth, $textHeight) * 2;
        $textImage = imagecreatetruecolor($canvasSize, $canvasSize);
        imagealphablending($textImage, false);
        imagesavealpha($textImage, true);
        
        // Make background transparent
        $transparent = imagecolorallocatealpha($textImage, 0, 0, 0, 127);
        imagefilledrectangle($textImage, 0, 0, $canvasSize, $canvasSize, $transparent);
        
        // Add text color with opacity
        $alpha = (int) (127 - ($opacity * 1.27));
        $textColor = imagecolorallocatealpha(
            $textImage,
            $color[0], $color[1], $color[2],
            $alpha
        );
        
        // Calculate center position for text
        $textX = ($canvasSize - $textWidth) / 2 + 10;
        $textY = ($canvasSize - $textHeight) / 2 + 10;
        
        // Add outline if enabled
        if ($outline) {
            $outlineColor = imagecolorallocatealpha($textImage, 0, 0, 0, $alpha);
            for ($ox = -1; $ox <= 1; $ox++) {
                for ($oy = -1; $oy <= 1; $oy++) {
                    imagestring($textImage, $font, $textX + $ox, $textY + $oy, $text, $outlineColor);
                }
            }
        }
        
        // Add main text
        imagestring($textImage, $font, $textX, $textY, $text, $textColor);
        
        // Add bold effect (draw text twice with slight offset)
        if ($bold) {
            imagestring($textImage, $font, $textX + 1, $textY, $text, $textColor);
        }
        
        // Rotate if needed
        if ($rotation != 0) {
            $textImage = imagerotate($textImage, $rotation, $transparent);
        }
        
        // Calculate position on main image
        [$x, $y] = $this->calculateWatermarkPosition($text, $fontSize, $position);
        
        // Merge watermark onto main image
        imagecopy($this->image, $textImage, $x, $y, 0, 0, $canvasSize, $canvasSize);
        
        imagedestroy($textImage);
        
        return $this;
    }
    
    /**
     * Add image watermark
     */
    public function addImageWatermark($watermarkPath, $options = [])
    {
        $watermark = new self();
        $watermark->load($watermarkPath);
        
        $opacity = $options['opacity'] ?? 50;
        $position = $options['position'] ?? 'bottom-right';
        $scale = $options['scale'] ?? 0.2;
        
        // Resize watermark
        $newWidth = (int) ($this->width * $scale);
        $watermark->resize($newWidth);
        
        [$x, $y] = $this->calculateImageWatermarkPosition(
            $watermark->width,
            $watermark->height,
            $position
        );
        
        imagecopymerge(
            $this->image,
            $watermark->image,
            $x, $y, 0, 0,
            $watermark->width,
            $watermark->height,
            $opacity
        );
        
        return $this;
    }
    
    /**
     * Rotate image
     */
    public function rotate($angle)
    {
        $this->image = imagerotate($this->image, $angle, 0);
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        return $this;
    }
    
    /**
     * Flip image
     */
    public function flip($mode = 'horizontal')
    {
        if ($mode === 'horizontal') {
            imageflip($this->image, IMG_FLIP_HORIZONTAL);
        } else {
            imageflip($this->image, IMG_FLIP_VERTICAL);
        }
        return $this;
    }
    
    /**
     * Set quality
     */
    public function setQuality($quality)
    {
        $this->quality = max(1, min(100, $quality));
        return $this;
    }
    
    /**
     * Save image
     */
    public function save($path, $format = null)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        if ($format === null) {
            $format = $this->imageType;
        }
        
        switch ($format) {
            case IMAGETYPE_JPEG:
            case 'jpg':
            case 'jpeg':
                imagejpeg($this->image, $path, $this->quality);
                break;
            case IMAGETYPE_PNG:
            case 'png':
                $pngQuality = (int) (9 - ($this->quality / 10));
                imagepng($this->image, $path, $pngQuality);
                break;
            case IMAGETYPE_GIF:
            case 'gif':
                imagegif($this->image, $path);
                break;
            case IMAGETYPE_WEBP:
            case 'webp':
                imagewebp($this->image, $path, $this->quality);
                break;
        }
        
        return $path;
    }
    
    /**
     * Get image dimensions
     */
    public function getDimensions()
    {
        return ['width' => $this->width, 'height' => $this->height];
    }
    
    /**
     * Preserve transparency for PNG/GIF
     */
    private function preserveTransparency($image)
    {
        if ($this->imageType === IMAGETYPE_PNG || $this->imageType === IMAGETYPE_GIF) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefilledrectangle($image, 0, 0, $this->width, $this->height, $transparent);
        }
    }
    
    /**
     * Calculate aspect ratio
     */
    private function calculateAspectRatio($targetWidth, $targetHeight, $fit)
    {
        $ratio = $this->width / $this->height;
        
        if ($fit === 'contain') {
            if ($targetWidth / $targetHeight > $ratio) {
                $targetWidth = (int) ($targetHeight * $ratio);
            } else {
                $targetHeight = (int) ($targetWidth / $ratio);
            }
        } elseif ($fit === 'cover') {
            if ($targetWidth / $targetHeight < $ratio) {
                $targetWidth = (int) ($targetHeight * $ratio);
            } else {
                $targetHeight = (int) ($targetWidth / $ratio);
            }
        }
        
        return [$targetWidth, $targetHeight];
    }
    
    /**
     * Calculate watermark position
     */
    private function calculateWatermarkPosition($text, $fontSize, $position)
    {
        $padding = 20;
        $textWidth = strlen($text) * ($fontSize / 2);
        $textHeight = $fontSize + 10;
        
        switch ($position) {
            case 'top-left':
                return [$padding, $padding];
            case 'top-right':
                return [$this->width - $textWidth - $padding, $padding];
            case 'bottom-left':
                return [$padding, $this->height - $textHeight - $padding];
            case 'bottom-right':
                return [$this->width - $textWidth - $padding, $this->height - $textHeight - $padding];
            case 'center':
                return [($this->width - $textWidth) / 2, ($this->height - $textHeight) / 2];
            default:
                return [$this->width - $textWidth - $padding, $this->height - $textHeight - $padding];
        }
    }
    
    /**
     * Calculate image watermark position
     */
    private function calculateImageWatermarkPosition($wmWidth, $wmHeight, $position)
    {
        $padding = 20;
        
        switch ($position) {
            case 'top-left':
                return [$padding, $padding];
            case 'top-right':
                return [$this->width - $wmWidth - $padding, $padding];
            case 'bottom-left':
                return [$padding, $this->height - $wmHeight - $padding];
            case 'bottom-right':
                return [$this->width - $wmWidth - $padding, $this->height - $wmHeight - $padding];
            case 'center':
                return [($this->width - $wmWidth) / 2, ($this->height - $wmHeight) / 2];
            default:
                return [$this->width - $wmWidth - $padding, $this->height - $wmHeight - $padding];
        }
    }
    
    /**
     * Get default font path
     */
    private function getDefaultFont()
    {
        // Try to find a system font
        $fonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
            'C:/Windows/Fonts/arial.ttf'
        ];
        
        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }
        
        return null;
    }
    
    /**
     * Remove background (simple color-based)
     * Works best with solid color backgrounds
     */
    public function removeBackground($options = [])
    {
        $tolerance = $options['tolerance'] ?? 30;
        $sampleCorners = $options['sample_corners'] ?? true;
        $feather = $options['feather'] ?? 0;
        
        // Get background color from corners
        $bgColors = [];
        if ($sampleCorners) {
            $bgColors[] = imagecolorat($this->image, 0, 0);
            $bgColors[] = imagecolorat($this->image, $this->width - 1, 0);
            $bgColors[] = imagecolorat($this->image, 0, $this->height - 1);
            $bgColors[] = imagecolorat($this->image, $this->width - 1, $this->height - 1);
        }
        
        // Create new image with transparency
        $newImage = imagecreatetruecolor($this->width, $this->height);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefilledrectangle($newImage, 0, 0, $this->width, $this->height, $transparent);
        
        // Process each pixel
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $rgb = imagecolorat($this->image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                
                $isBackground = false;
                
                // Check if pixel matches any background color
                foreach ($bgColors as $bgColor) {
                    $bgR = ($bgColor >> 16) & 0xFF;
                    $bgG = ($bgColor >> 8) & 0xFF;
                    $bgB = $bgColor & 0xFF;
                    
                    $diff = abs($r - $bgR) + abs($g - $bgG) + abs($b - $bgB);
                    
                    if ($diff <= $tolerance) {
                        $isBackground = true;
                        break;
                    }
                }
                
                if (!$isBackground) {
                    // Keep the pixel
                    $color = imagecolorallocatealpha($newImage, $r, $g, $b, 0);
                    imagesetpixel($newImage, $x, $y, $color);
                }
            }
        }
        
        imagedestroy($this->image);
        $this->image = $newImage;
        
        return $this;
    }
    
    /**
     * Remove white background specifically
     */
    public function removeWhiteBackground($tolerance = 30)
    {
        return $this->removeBackgroundByColor(255, 255, 255, $tolerance);
    }
    
    /**
     * Remove background by specific color
     */
    public function removeBackgroundByColor($r, $g, $b, $tolerance = 30)
    {
        $newImage = imagecreatetruecolor($this->width, $this->height);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefilledrectangle($newImage, 0, 0, $this->width, $this->height, $transparent);
        
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $rgb = imagecolorat($this->image, $x, $y);
                $pixelR = ($rgb >> 16) & 0xFF;
                $pixelG = ($rgb >> 8) & 0xFF;
                $pixelB = $rgb & 0xFF;
                
                $diff = abs($pixelR - $r) + abs($pixelG - $g) + abs($pixelB - $b);
                
                if ($diff > $tolerance) {
                    $color = imagecolorallocatealpha($newImage, $pixelR, $pixelG, $pixelB, 0);
                    imagesetpixel($newImage, $x, $y, $color);
                }
            }
        }
        
        imagedestroy($this->image);
        $this->image = $newImage;
        
        return $this;
    }
    
    /**
     * Cleanup
     */
    public function __destruct()
    {
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }
    }
}

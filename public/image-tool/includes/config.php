<?php
/**
 * Configuration file for Image Tool
 */

// Directory paths
define('TEMP_DIR', sys_get_temp_dir() . '/image-tool');
define('UPLOAD_DIR', TEMP_DIR . '/uploads');
define('PREVIEW_DIR', TEMP_DIR . '/previews');
define('OUTPUT_DIR', TEMP_DIR . '/output');

// Create directories if they don't exist
$dirs = [TEMP_DIR, UPLOAD_DIR, PREVIEW_DIR, OUTPUT_DIR];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('DEFAULT_QUALITY', 90);
define('MAX_DIMENSION', 5000);

// Session timeout for temp files (1 hour)
define('SESSION_TIMEOUT', 3600);

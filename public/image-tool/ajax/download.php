<?php
/**
 * Download processed image
 */

if (!isset($_GET['file'])) {
    http_response_code(404);
    exit;
}

$file = basename($_GET['file']);
$filePath = OUTPUT_DIR . '/' . $file;

if (!file_exists($filePath)) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);

// Optionally delete after download
// unlink($filePath);
exit;

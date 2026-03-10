<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get a site to test export
$site = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::first();

if (!$site) {
    echo "No sites found\n";
    exit(1);
}

echo "=== Testing Export for Site ===\n";
echo "Site: {$site->name}\n";
echo "Subdomain: {$site->subdomain}\n";
echo "ID: {$site->id}\n\n";

try {
    $exportService = app(\App\Services\GrowBuilder\StaticExportService::class);
    
    echo "Starting export...\n";
    $zipPath = $exportService->exportSite($site);
    
    echo "✓ Export successful!\n";
    echo "ZIP Path: {$zipPath}\n";
    echo "File exists: " . (file_exists($zipPath) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($zipPath)) {
        echo "File size: " . filesize($zipPath) . " bytes\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Export failed!\n";
    echo "Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
}

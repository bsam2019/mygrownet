<?php

// Add custom domain to Flames of Hope site
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\CustomDomainService;

$site = GrowBuilderSite::find(15); // Flames of Hope site

if (!$site) {
    echo "Site not found!\n";
    exit(1);
}

echo "Site found: {$site->name} ({$site->subdomain})\n";
echo "Adding custom domain: flamesofhopechurch.com\n\n";

$service = app(CustomDomainService::class);
$result = $service->addCustomDomain($site, 'flamesofhopechurch.com');

echo "Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";

if ($result['success']) {
    echo "\n✅ Custom domain added successfully!\n";
    echo "Site URL: https://flamesofhopechurch.com\n";
} else {
    echo "\n❌ Failed to add custom domain\n";
    echo "Error: {$result['message']}\n";
}

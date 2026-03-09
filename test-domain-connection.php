<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\CustomDomainService;

$site = GrowBuilderSite::find(15); // Flames of Hope site

if (!$site) {
    echo "❌ Site not found!\n";
    exit(1);
}

echo "Site: {$site->name} (ID: {$site->id})\n";
echo "Subdomain: {$site->subdomain}\n";
echo "Current custom domain: " . ($site->custom_domain ?? 'None') . "\n\n";

$service = app(CustomDomainService::class);

echo "Testing domain: flamesofhopechurch.com\n";
echo "Checking DNS...\n\n";

// First check DNS status
$status = $service->checkDomainStatus('flamesofhopechurch.com');
echo "DNS Status:\n";
echo json_encode($status, JSON_PRETTY_PRINT) . "\n\n";

if (!$status['dns_configured']) {
    echo "❌ DNS check failed. Cannot proceed.\n";
    echo "Message: " . ($status['dns_details']['message'] ?? 'Unknown error') . "\n";
    exit(1);
}

echo "✅ DNS is valid. Attempting to connect domain...\n\n";

// Try to connect
$result = $service->addCustomDomain($site, 'flamesofhopechurch.com');

echo "Connection Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

if ($result['success']) {
    echo "✅ Domain connected successfully!\n";
} else {
    echo "❌ Failed to connect domain\n";
    echo "Error: {$result['message']}\n";
}

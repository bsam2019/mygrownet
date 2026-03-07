<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\GrowBuilder\CustomDomainService;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;

echo "🧪 Testing Automated Custom Domain Setup\n";
echo "=========================================\n\n";

$service = app(CustomDomainService::class);
$site = GrowBuilderSite::find(12);

if (!$site) {
    echo "❌ Site not found\n";
    exit(1);
}

echo "Site: {$site->name} (ID: {$site->id})\n";
echo "Subdomain: {$site->subdomain}\n";
echo "Domain to connect: flamesofhopechurch.com\n\n";

echo "Step 1: Verifying DNS...\n";
$dnsCheck = $service->checkDomainStatus('flamesofhopechurch.com');
echo json_encode($dnsCheck, JSON_PRETTY_PRINT) . "\n\n";

if (!$dnsCheck['dns_configured']) {
    echo "❌ DNS not configured correctly. Please fix DNS first.\n";
    exit(1);
}

echo "✅ DNS is configured correctly\n\n";

echo "Step 2: Connecting custom domain...\n";
$result = $service->addCustomDomain($site, 'flamesofhopechurch.com');
echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

if ($result['success']) {
    echo "✅ Custom domain connected successfully!\n";
    echo "🌐 Site should now be accessible at: https://flamesofhopechurch.com\n";
} else {
    echo "❌ Failed to connect custom domain\n";
    exit(1);
}

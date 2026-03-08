<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Cleaning up Flames of Hope custom domain setup...\n\n";

// Clear database
DB::table('grow_builder_sites')
    ->where('id', 12)
    ->update([
        'custom_domain' => null,
        'custom_domain_verified' => false,
        'ssl_enabled' => false,
    ]);

echo "✅ Database cleared\n";

// Remove nginx config
$nginxConfig = '/etc/nginx/sites-available/flamesofhopechurch.com';
$nginxEnabled = '/etc/nginx/sites-enabled/flamesofhopechurch.com';

if (file_exists($nginxEnabled)) {
    exec('sudo rm ' . $nginxEnabled, $output, $code);
    echo $code === 0 ? "✅ Removed nginx symlink\n" : "❌ Failed to remove nginx symlink\n";
}

if (file_exists($nginxConfig)) {
    exec('sudo rm ' . $nginxConfig, $output, $code);
    echo $code === 0 ? "✅ Removed nginx config\n" : "❌ Failed to remove nginx config\n";
}

// Reload nginx
exec('sudo systemctl reload nginx', $output, $code);
echo $code === 0 ? "✅ Nginx reloaded\n" : "❌ Failed to reload nginx\n";

// Note about SSL certificate
echo "\n⚠️  SSL certificate at /etc/letsencrypt/live/flamesofhopechurch.com/ still exists\n";
echo "   You can remove it manually with: sudo certbot delete --cert-name flamesofhopechurch.com\n";

echo "\n✅ Cleanup complete!\n";

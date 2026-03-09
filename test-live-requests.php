<?php

// Test what happens when we simulate requests to the custom domain

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$domain = 'flamesofhopechurch.com';
$paths = ['/', '/about', '/services', '/ministries', '/visit', '/school', '/contact'];

echo "Testing live requests to {$domain}\n\n";

foreach ($paths as $path) {
    $url = "https://{$domain}{$path}";
    echo "Testing: {$url}\n";
    
    // Use curl to test
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "  ✓ Status: 200 OK\n";
    } else {
        echo "  ✗ Status: {$httpCode}\n";
    }
    
    echo "\n";
}

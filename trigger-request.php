<?php

// Trigger a request to see if middleware logs appear

$urls = [
    'https://flamesofhopechurch.com/services',
    'https://flamesofhopechurch.com/about',
];

foreach ($urls as $url) {
    echo "Requesting: {$url}\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Status: {$httpCode}\n\n";
    
    sleep(1);
}

echo "Now check logs:\n";
echo "tail -50 storage/logs/laravel.log | grep DetectSubdomain\n";

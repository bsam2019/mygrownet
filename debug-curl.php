<?php

$urls = [
    'https://flamesofhopechurch.com/about' => 'WORKING',
    'https://flamesofhopechurch.com/services' => 'NOT WORKING',
];

foreach ($urls as $url => $status) {
    echo "\n========================================\n";
    echo "{$status}: {$url}\n";
    echo "========================================\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    
    curl_close($ch);
    
    echo "HTTP Status: {$httpCode}\n";
    echo "\nResponse Headers:\n";
    
    // Extract headers
    $headerSize = strpos($response, "\r\n\r\n");
    if ($headerSize !== false) {
        $headers = substr($response, 0, $headerSize);
        echo $headers . "\n";
    }
    
    echo "\n";
}

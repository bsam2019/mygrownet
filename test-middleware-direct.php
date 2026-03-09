<?php

// Test if middleware runs by simulating a request

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

// Create a request for the custom domain
$request = Illuminate\Http\Request::create(
    'https://flamesofhopechurch.com/services',
    'GET',
    [],
    [],
    [],
    ['HTTP_HOST' => 'flamesofhopechurch.com']
);

echo "Testing middleware with simulated request...\n";
echo "URL: https://flamesofhopechurch.com/services\n\n";

try {
    $response = $kernel->handle($request);
    echo "Response Status: " . $response->getStatusCode() . "\n";
    
    // Check if log file was created
    $logFile = __DIR__ . '/storage/logs/custom-domain-debug.log';
    if (file_exists($logFile)) {
        echo "\nDebug Log Contents:\n";
        echo file_get_contents($logFile);
    } else {
        echo "\nDebug log file not created - middleware not running!\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

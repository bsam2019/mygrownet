<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;

$timestamp = date('Y-m-d H:i:s');

echo "=== Testing Resend Email Delivery ===\n";
echo "Sending at: {$timestamp}\n\n";

try {
    Mail::raw("Testing Resend - Fast email delivery test\n\nSent at: {$timestamp}\n\nResend should deliver this email almost instantly!", function($m) use ($timestamp) {
        $m->to('samedulink@gmail.com')
          ->subject('Resend Test - ' . time());
    });
    
    echo "✅ Email sent via Resend successfully!\n";
    echo "Time: {$timestamp}\n";
    echo "\nCheck your inbox at samedulink@gmail.com\n";
    echo "Resend typically delivers in seconds!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

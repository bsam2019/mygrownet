<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;

$timestamp = date('Y-m-d H:i:s');

try {
    Mail::raw("URGENT TEST - Please check if you receive this email.\n\nTime sent: {$timestamp}\n\nIf you see this, the email system is working.\n\nThis is test email number: " . time(), function($m) use ($timestamp) {
        $m->to('samedulink@gmail.com')
          ->subject('MyGrowNet - URGENT Email Test - ' . $timestamp);
    });
    
    echo "✅ Email sent successfully at {$timestamp}\n";
    echo "Check your inbox (and spam folder) at samedulink@gmail.com\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

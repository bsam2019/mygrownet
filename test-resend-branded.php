<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\GenericNotificationMail;

$timestamp = date('Y-m-d H:i:s');

echo "=== Testing Resend BRANDED Email ===\n";
echo "Sending at: {$timestamp}\n\n";

try {
    Mail::to('samedulink@gmail.com')->send(
        new GenericNotificationMail(
            subject: 'MyGrowNet - Resend Branded Test',
            greeting: 'Hello Samuel!',
            message: "This is a BRANDED email via Resend sent at {$timestamp}.\n\nYou should see:\n✅ Blue gradient header with MyGrowNet logo\n✅ Professional styling\n✅ Green info box\n✅ Blue button\n✅ Professional footer\n\nAnd it should arrive in SECONDS!",
            actionText: 'Visit Dashboard',
            actionUrl: 'https://mygrownet.com/dashboard',
            infoBox: 'Resend delivers emails almost instantly with excellent deliverability!',
            infoBoxType: 'info-box-success'
        )
    );
    
    echo "✅ BRANDED email sent via Resend!\n";
    echo "Time: {$timestamp}\n";
    echo "\nCheck your inbox NOW - should arrive in seconds!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

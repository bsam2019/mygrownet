<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\GenericNotificationMail;

$timestamp = date('Y-m-d H:i:s');

echo "Sending BRANDED email at {$timestamp}...\n\n";

try {
    Mail::to('samedulink@gmail.com')->send(
        new GenericNotificationMail(
            subject: 'MyGrowNet - BRANDED Email Test - ' . time(),
            greeting: 'Hello Samuel!',
            message: "This is a BRANDED email test sent at {$timestamp}.\n\nYou should see:\n- Blue gradient header with MyGrowNet logo\n- Professional styling\n- Green info box\n- Blue button\n- Professional footer",
            actionText: 'Visit Dashboard',
            actionUrl: 'https://mygrownet.com/dashboard',
            infoBox: 'If you can see this styled box with the MyGrowNet branding, the branded email system is working perfectly!',
            infoBoxType: 'info-box-success'
        )
    );
    
    echo "✅ BRANDED email sent successfully!\n";
    echo "Sent at: {$timestamp}\n";
    echo "Check your inbox at samedulink@gmail.com\n";
    echo "\nNote the time and let me know when it arrives.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

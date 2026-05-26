<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\GenericNotificationMail;

try {
    Mail::send(
        new GenericNotificationMail(
            subject: 'MyGrowNet - Branded Email Test',
            greeting: 'Hello Samuel!',
            message: 'This is a test of the new branded email system. If you can see the MyGrowNet branding with the blue header, logo, and professional styling, then everything is working correctly!',
            actionText: 'Visit Dashboard',
            actionUrl: 'https://mygrownet.com/dashboard',
            infoBox: 'This email demonstrates the new branded template system with MyGrowNet colors and styling.',
            infoBoxType: 'info-box-success'
        )
    );
    
    echo "✅ Branded email sent successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

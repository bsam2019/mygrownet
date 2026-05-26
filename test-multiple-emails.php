<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Brevo\Brevo;

$timestamp = date('Y-m-d H:i:s');
$apiKey = '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';

echo "=== Testing Email Delivery ===\n\n";

// Test 1: Send via Laravel Mail to samedulink@gmail.com
echo "Test 1: Sending to samedulink@gmail.com via Laravel Mail...\n";
try {
    Mail::raw("Test email sent at {$timestamp}", function($m) use ($timestamp) {
        $m->to('samedulink@gmail.com')
          ->subject('Test Email ' . time());
    });
    echo "✅ Sent successfully\n\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Send directly via Brevo API to samedulink@gmail.com
echo "Test 2: Sending to samedulink@gmail.com via Direct Brevo API...\n";
try {
    $client = new Brevo($apiKey);
    
    $request = new \Brevo\TransactionalEmails\Requests\SendTransacEmailRequest([
        'sender' => new \Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender([
            'email' => 'noreply@mygrownet.com',
            'name' => 'MyGrowNet',
        ]),
        'to' => [
            new \Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem([
                'email' => 'samedulink@gmail.com',
                'name' => 'Samuel',
            ])
        ],
        'subject' => 'Direct Brevo Test ' . time(),
        'htmlContent' => '<p>Direct test at ' . $timestamp . '</p>',
    ]);
    
    $result = $client->transactionalEmails->sendTransacEmail($request);
    echo "✅ Sent successfully\n";
    echo "Message ID: " . ($result->messageId ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Send to sammygrownet@gmail.com (Brevo account email)
echo "Test 3: Sending to sammygrownet@gmail.com (Brevo account owner)...\n";
try {
    Mail::raw("Test email to Brevo account owner at {$timestamp}", function($m) use ($timestamp) {
        $m->to('sammygrownet@gmail.com')
          ->subject('Test to Brevo Owner ' . time());
    });
    echo "✅ Sent successfully\n\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

echo "=== All tests completed ===\n";
echo "Please check BOTH email addresses:\n";
echo "1. samedulink@gmail.com\n";
echo "2. sammygrownet@gmail.com\n";

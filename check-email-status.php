<?php

require __DIR__.'/vendor/autoload.php';

use Brevo\Brevo;

$apiKey = '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';

try {
    $client = new Brevo($apiKey);
    
    // Get account info
    echo "=== Brevo Account Info ===\n";
    $account = $client->account->getAccount();
    echo "Email: " . $account->email . "\n";
    echo "Company: " . ($account->companyName ?? 'N/A') . "\n\n";
    
    // Get senders list
    echo "=== Verified Senders ===\n";
    $senders = $client->senders->getSenders();
    if (isset($senders->senders) && count($senders->senders) > 0) {
        foreach ($senders->senders as $sender) {
            echo "Email: " . $sender->email . "\n";
            echo "Name: " . $sender->name . "\n";
            echo "Active: " . ($sender->active ? 'Yes' : 'No') . "\n";
            echo "---\n";
        }
    } else {
        echo "⚠️  No verified senders found!\n";
        echo "You need to verify 'noreply@mygrownet.com' in Brevo dashboard.\n\n";
    }
    
    // Get recent emails
    echo "\n=== Recent Emails (Last 10) ===\n";
    $emails = $client->transactionalEmails->getTransacEmailsList();
    if (isset($emails->transactionalEmails) && count($emails->transactionalEmails) > 0) {
        foreach (array_slice($emails->transactionalEmails, 0, 10) as $email) {
            echo "To: " . ($email->email ?? 'N/A') . "\n";
            echo "Subject: " . ($email->subject ?? 'N/A') . "\n";
            echo "Date: " . ($email->date ?? 'N/A') . "\n";
            echo "Status: " . ($email->messageId ? 'Sent' : 'Unknown') . "\n";
            echo "---\n";
        }
    } else {
        echo "No recent emails found.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

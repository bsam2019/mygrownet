<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $service = app(\App\Domain\Investor\Services\InvestorMessagingService::class);
    echo "Service resolved OK\n";
    
    // Get a valid investor account ID
    $investor = DB::table('investor_accounts')->first();
    if (!$investor) {
        echo "No investor accounts found. Creating a test one...\n";
        // Check if there's an investment round
        $round = DB::table('investment_rounds')->first();
        if (!$round) {
            echo "No investment rounds found. Please create one first.\n";
            exit(1);
        }
        
        $investorId = DB::table('investor_accounts')->insertGetId([
            'name' => 'Test Investor',
            'email' => 'test@investor.com',
            'investment_round_id' => $round->id,
            'investment_amount' => 10000,
            'equity_percentage' => 1.0,
            'investment_date' => now(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Created test investor with ID: $investorId\n";
    } else {
        $investorId = $investor->id;
        echo "Using existing investor ID: $investorId ({$investor->name})\n";
    }
    
    // Test getting messages
    $messages = $service->getMessagesForInvestor($investorId);
    echo "Messages count: " . count($messages) . "\n";
    
    // Test sending a message
    $messageId = $service->sendMessageFromInvestor(
        investorAccountId: $investorId,
        subject: 'Test Message',
        content: 'This is a test message from the investor portal.',
        parentId: null
    );
    echo "Message sent with ID: $messageId\n";
    
    // Verify message was saved
    $messages = $service->getMessagesForInvestor($investorId);
    echo "Messages count after send: " . count($messages) . "\n";
    
    // Show the message
    if (count($messages) > 0) {
        echo "\nLatest message:\n";
        print_r($messages[0]);
    }
    
    // Test unread count
    $unreadCount = $service->getUnreadCountForInvestor($investorId);
    echo "\nUnread count (from admin): $unreadCount\n";
    
    // Test sending a message from admin
    echo "\n--- Testing admin message ---\n";
    $adminMessageId = $service->sendMessageToInvestor(
        investorAccountId: $investorId,
        subject: 'Welcome to MyGrowNet',
        content: 'Thank you for your investment. We are excited to have you on board!',
        adminUserId: 1,
        parentId: null
    );
    echo "Admin message sent with ID: $adminMessageId\n";
    
    // Check unread count again
    $unreadCount = $service->getUnreadCountForInvestor($investorId);
    echo "Unread count (from admin): $unreadCount\n";
    
    // Get all messages
    $messages = $service->getMessagesForInvestor($investorId);
    echo "Total messages: " . count($messages) . "\n";
    
    // Mark the admin message as read
    $marked = $service->markAsRead($adminMessageId, $investorId);
    echo "Marked as read: " . ($marked ? 'yes' : 'no') . "\n";
    
    // Check unread count after marking as read
    $unreadCount = $service->getUnreadCountForInvestor($investorId);
    echo "Unread count after marking read: $unreadCount\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

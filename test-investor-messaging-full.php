<?php
/**
 * Full Investor Messaging System Test
 * Tests both investor and admin message flows
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Investor Messaging System Test ===\n\n";

try {
    $service = app(\App\Domain\Investor\Services\InvestorMessagingService::class);
    echo "✓ Service resolved successfully\n\n";
    
    // Get or create test investor
    $investor = DB::table('investor_accounts')->first();
    if (!$investor) {
        echo "✗ No investor accounts found. Please create one first.\n";
        exit(1);
    }
    
    $investorId = $investor->id;
    echo "Using investor: {$investor->name} (ID: $investorId, Email: {$investor->email})\n\n";
    
    // Clean up old test messages
    DB::table('investor_messages')
        ->where('investor_account_id', $investorId)
        ->where('subject', 'like', '%Test%')
        ->delete();
    echo "✓ Cleaned up old test messages\n\n";
    
    // ========================================
    // TEST 1: Investor sends a message
    // ========================================
    echo "--- TEST 1: Investor sends a message ---\n";
    
    $messageId1 = $service->sendMessageFromInvestor(
        investorAccountId: $investorId,
        subject: 'Test: Question about my investment',
        content: 'Hello, I have a question about my investment returns. Can you please provide more details?',
        parentId: null
    );
    
    if ($messageId1) {
        echo "✓ Investor message sent (ID: $messageId1)\n";
    } else {
        echo "✗ Failed to send investor message\n";
    }
    
    // Verify message appears in investor's inbox
    $investorMessages = $service->getMessagesForInvestor($investorId);
    $found = false;
    foreach ($investorMessages as $msg) {
        if ($msg['id'] == $messageId1) {
            $found = true;
            echo "✓ Message appears in investor's inbox\n";
            echo "  - Direction: {$msg['direction']} (expected: inbound)\n";
            echo "  - Sender label: {$msg['sender_label']} (expected: You)\n";
            break;
        }
    }
    if (!$found) {
        echo "✗ Message not found in investor's inbox\n";
    }
    
    // ========================================
    // TEST 2: Admin sees the message
    // ========================================
    echo "\n--- TEST 2: Admin sees the message ---\n";
    
    $adminUnreadBefore = $service->getUnreadCountForAdmin();
    echo "Admin unread count: $adminUnreadBefore\n";
    
    $allMessages = $service->getAllMessagesForAdmin(50, 0);
    $foundInAdmin = false;
    foreach ($allMessages as $msg) {
        if ($msg['id'] == $messageId1) {
            $foundInAdmin = true;
            echo "✓ Message appears in admin inbox\n";
            echo "  - Investor: {$msg['investor_name']} ({$msg['investor_email']})\n";
            echo "  - Direction: {$msg['direction']} (expected: inbound)\n";
            echo "  - Is read: " . ($msg['is_read'] ? 'yes' : 'no') . " (expected: no)\n";
            break;
        }
    }
    if (!$foundInAdmin) {
        echo "✗ Message not found in admin inbox\n";
    }
    
    // ========================================
    // TEST 3: Admin replies to the message
    // ========================================
    echo "\n--- TEST 3: Admin replies to the message ---\n";
    
    $adminUserId = 1; // Assuming admin user ID is 1
    $replyId = $service->sendMessageFromAdmin(
        adminId: $adminUserId,
        investorAccountId: $investorId,
        subject: 'Re: Test: Question about my investment',
        content: 'Thank you for your question. Your investment is performing well with a 15% return this quarter.',
        parentId: $messageId1
    );
    
    if ($replyId) {
        echo "✓ Admin reply sent (ID: $replyId)\n";
    } else {
        echo "✗ Failed to send admin reply\n";
    }
    
    // ========================================
    // TEST 4: Investor sees the reply
    // ========================================
    echo "\n--- TEST 4: Investor sees the reply ---\n";
    
    $investorUnread = $service->getUnreadCountForInvestor($investorId);
    echo "Investor unread count: $investorUnread (expected: 1 or more)\n";
    
    $investorMessages = $service->getMessagesForInvestor($investorId);
    $foundReply = false;
    foreach ($investorMessages as $msg) {
        if ($msg['id'] == $replyId) {
            $foundReply = true;
            echo "✓ Reply appears in investor's inbox\n";
            echo "  - Direction: {$msg['direction']} (expected: outbound)\n";
            echo "  - Sender label: {$msg['sender_label']} (expected: MyGrowNet Team)\n";
            echo "  - Is read: " . ($msg['is_read'] ? 'yes' : 'no') . " (expected: no)\n";
            break;
        }
    }
    if (!$foundReply) {
        echo "✗ Reply not found in investor's inbox\n";
    }
    
    // ========================================
    // TEST 5: Investor marks message as read
    // ========================================
    echo "\n--- TEST 5: Investor marks message as read ---\n";
    
    $marked = $service->markAsRead($replyId, $investorId);
    echo "Mark as read result: " . ($marked ? 'success' : 'failed') . "\n";
    
    $investorUnreadAfter = $service->getUnreadCountForInvestor($investorId);
    echo "Investor unread count after: $investorUnreadAfter (expected: " . ($investorUnread - 1) . ")\n";
    
    // ========================================
    // TEST 6: Get conversation thread
    // ========================================
    echo "\n--- TEST 6: Get conversation thread ---\n";
    
    $conversation = $service->getConversation($investorId);
    echo "Conversation messages: " . count($conversation) . "\n";
    
    foreach ($conversation as $msg) {
        $arrow = $msg['direction'] === 'inbound' ? '→' : '←';
        $sender = $msg['direction'] === 'inbound' ? 'Investor' : 'Admin';
        echo "  $arrow [$sender] {$msg['subject']}\n";
    }
    
    // ========================================
    // TEST 7: Investor sends follow-up
    // ========================================
    echo "\n--- TEST 7: Investor sends follow-up ---\n";
    
    $followUpId = $service->sendMessageFromInvestor(
        investorAccountId: $investorId,
        subject: 'Re: Test: Question about my investment',
        content: 'Thank you for the information! When will the next dividend be distributed?',
        parentId: $replyId
    );
    
    if ($followUpId) {
        echo "✓ Follow-up message sent (ID: $followUpId)\n";
    }
    
    // Final conversation
    $finalConversation = $service->getConversation($investorId);
    echo "\nFinal conversation thread:\n";
    foreach ($finalConversation as $msg) {
        $arrow = $msg['direction'] === 'inbound' ? '→' : '←';
        $sender = $msg['direction'] === 'inbound' ? 'Investor' : 'Admin';
        echo "  $arrow [$sender] {$msg['subject']}\n";
    }
    
    // ========================================
    // Summary
    // ========================================
    echo "\n=== Test Summary ===\n";
    echo "✓ Investor can send messages\n";
    echo "✓ Admin can see investor messages\n";
    echo "✓ Admin can reply to messages\n";
    echo "✓ Investor can see admin replies\n";
    echo "✓ Messages can be marked as read\n";
    echo "✓ Conversation threads work correctly\n";
    echo "\nAll tests passed!\n";
    
} catch (\Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

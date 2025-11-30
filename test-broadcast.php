<?php
/**
 * Test script to verify broadcasting is working
 * Run with: php test-broadcast.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\Member\MemberSupportMessage;
use Illuminate\Support\Facades\Log;

echo "=== Broadcast Test ===\n\n";

// Check broadcast configuration
$broadcastConnection = config('broadcasting.default');
echo "Broadcast Connection: {$broadcastConnection}\n";

if ($broadcastConnection === 'null') {
    echo "⚠️  WARNING: Broadcast connection is set to 'null'. Events won't be broadcast!\n";
    echo "Set BROADCAST_CONNECTION=reverb in your .env file.\n\n";
}

// Check Reverb configuration
$reverbConfig = config('broadcasting.connections.reverb');
echo "\nReverb Configuration:\n";
echo "  Host: " . ($reverbConfig['options']['host'] ?? 'not set') . "\n";
echo "  Port: " . ($reverbConfig['options']['port'] ?? 'not set') . "\n";
echo "  Scheme: " . ($reverbConfig['options']['scheme'] ?? 'not set') . "\n";
echo "  App ID: " . ($reverbConfig['app_id'] ?? 'not set') . "\n";
echo "  App Key: " . ($reverbConfig['key'] ?? 'not set') . "\n";

// Test broadcast
echo "\n--- Testing Broadcast ---\n";

$ticketId = 1; // Use a test ticket ID
$senderId = 1;
$senderName = 'Test User';
$senderType = 'member';
$message = 'Test message at ' . now()->toISOString();

echo "Broadcasting MemberSupportMessage to channel: member.support.{$ticketId}\n";
echo "Message: {$message}\n\n";

try {
    $event = new MemberSupportMessage(
        $ticketId,
        $senderId,
        $senderName,
        $senderType,
        $message,
        now()->toISOString()
    );
    
    // Broadcast without toOthers() to ensure it goes through
    broadcast($event);
    
    echo "✅ Broadcast sent successfully!\n";
    echo "\nCheck your browser console for the message.\n";
    echo "Also check storage/logs/laravel.log for broadcast logs.\n";
} catch (\Exception $e) {
    echo "❌ Broadcast failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";

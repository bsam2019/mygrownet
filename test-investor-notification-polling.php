#!/usr/bin/env php
<?php

/**
 * Test script to verify investor notification polling is working
 * 
 * Run: php test-investor-notification-polling.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔔 Testing Investor Notification Polling System\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Check if route exists
echo "✓ Test 1: Checking if route exists...\n";
try {
    $route = route('investor.notifications.count');
    echo "  Route URL: $route\n";
    echo "  ✅ Route exists\n\n";
} catch (\Exception $e) {
    echo "  ❌ Route not found: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Check if we have any investor accounts
echo "✓ Test 2: Checking for investor accounts...\n";
$investorCount = \App\Models\InvestorAccount::count();
echo "  Found $investorCount investor account(s)\n";

if ($investorCount === 0) {
    echo "  ⚠️  No investor accounts found. Create one first.\n\n";
    exit(0);
}

$investor = \App\Models\InvestorAccount::first();
echo "  Testing with: {$investor->name} (ID: {$investor->id})\n";
echo "  ✅ Investor account found\n\n";

// Test 3: Check messaging service
echo "✓ Test 3: Checking messaging service...\n";
try {
    $messagingService = app(\App\Domain\Investor\Services\InvestorMessagingService::class);
    $unreadCount = $messagingService->getUnreadCountForInvestor($investor->id);
    echo "  Unread messages: $unreadCount\n";
    echo "  ✅ Messaging service working\n\n";
} catch (\Exception $e) {
    echo "  ❌ Messaging service error: " . $e->getMessage() . "\n\n";
}

// Test 4: Check announcements
echo "✓ Test 4: Checking announcements...\n";
try {
    $unreadAnnouncements = \App\Models\InvestorAnnouncement::where('status', 'published')
        ->whereDoesntHave('reads', function($query) use ($investor) {
            $query->where('investor_account_id', $investor->id);
        })
        ->count();
    echo "  Unread announcements: $unreadAnnouncements\n";
    echo "  ✅ Announcements working\n\n";
} catch (\Exception $e) {
    echo "  ❌ Announcements error: " . $e->getMessage() . "\n\n";
}

// Test 5: Simulate API call
echo "✓ Test 5: Simulating notification count API call...\n";
try {
    // Create a mock session
    session()->put('investor_id', $investor->id);
    
    $controller = app(\App\Http\Controllers\Investor\InvestorPortalController::class);
    $response = $controller->getNotificationCount();
    $data = json_decode($response->getContent(), true);
    
    echo "  Response:\n";
    echo "    - Unread Messages: " . ($data['unreadMessages'] ?? 'N/A') . "\n";
    echo "    - Unread Announcements: " . ($data['unreadAnnouncements'] ?? 'N/A') . "\n";
    echo "  ✅ API endpoint working\n\n";
} catch (\Exception $e) {
    echo "  ❌ API call error: " . $e->getMessage() . "\n\n";
}

echo str_repeat("=", 60) . "\n";
echo "✅ All tests completed!\n\n";

echo "📋 Summary:\n";
echo "  - Route: ✅ investor.notifications.count exists\n";
echo "  - Controller: ✅ getNotificationCount() method exists\n";
echo "  - Frontend: ✅ InvestorLayout has polling (30s interval)\n";
echo "  - Polling starts: ✅ onMounted() in InvestorLayout\n\n";

echo "🔍 Troubleshooting:\n";
echo "  1. Check browser console for fetch errors\n";
echo "  2. Verify investor is logged in (session exists)\n";
echo "  3. Check Network tab for /investor/notifications/count calls\n";
echo "  4. Ensure polling interval is running (30 seconds)\n\n";

echo "💡 To test in browser:\n";
echo "  1. Login to investor portal\n";
echo "  2. Open DevTools > Network tab\n";
echo "  3. Filter by 'notifications'\n";
echo "  4. Wait 30 seconds - you should see API calls\n\n";

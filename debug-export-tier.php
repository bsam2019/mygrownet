<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the authenticated user (replace with your user ID)
$userId = 1; // Change this to your user ID
$user = \App\Models\User::find($userId);

if (!$user) {
    echo "User not found\n";
    exit(1);
}

echo "=== Export Tier Debug ===\n\n";
echo "User: {$user->name} (ID: {$user->id})\n";
echo "Email: {$user->email}\n\n";

// Get tier restriction service
$tierService = app(\App\Services\GrowBuilder\TierRestrictionService::class);

// Get user's tier
$tier = $tierService->getUserTier($user);
echo "Current Tier: {$tier}\n\n";

// Get restrictions
$restrictions = $tierService->getRestrictions($user);
echo "Restrictions:\n";
echo "  - Tier Name: {$restrictions['tier_name']}\n";
echo "  - Sites Limit: {$restrictions['sites_limit']}\n";
echo "  - Storage Limit: {$restrictions['storage_limit_formatted']}\n";
echo "  - Products Limit: " . ($restrictions['products_unlimited'] ? 'Unlimited' : $restrictions['products_limit']) . "\n";
echo "  - AI Prompts: " . ($restrictions['ai_unlimited'] ? 'Unlimited' : $restrictions['ai_prompts_limit']) . "\n\n";

// Check static_export feature
echo "Features:\n";
foreach ($restrictions['features'] as $feature => $enabled) {
    echo "  - {$feature}: " . ($enabled ? '✓ ENABLED' : '✗ DISABLED') . "\n";
}
echo "\nLooking for static_export: " . (isset($restrictions['features']['static_export']) ? 'FOUND' : 'NOT FOUND') . "\n";

// Check canExport directly
$exportService = app(\App\Services\GrowBuilder\StaticExportService::class);
$canExport = $exportService->canExport($user);
echo "\ncanExport() result: " . ($canExport ? '✓ TRUE' : '✗ FALSE') . "\n";

// Check database subscription
echo "\n=== Database Subscription ===\n";
$subscription = \Illuminate\Support\Facades\DB::table('module_subscriptions')
    ->where('user_id', $user->id)
    ->where('module_id', 'growbuilder')
    ->where('status', 'active')
    ->first();

if ($subscription) {
    echo "Found active subscription:\n";
    echo "  - ID: {$subscription->id}\n";
    echo "  - Status: {$subscription->status}\n";
    echo "  - Expires: {$subscription->expires_at}\n";
    echo "  - Columns: " . implode(', ', array_keys((array)$subscription)) . "\n";
} else {
    echo "No active subscription found in database\n";
}

// Clear cache and recheck
echo "\n=== After Cache Clear ===\n";
$tierService->clearCache($user);
$restrictions = $tierService->getRestrictions($user);
$canExport = $exportService->canExport($user);
echo "Tier: {$restrictions['tier']}\n";
echo "static_export feature: " . ($restrictions['features']['static_export'] ?? false ? '✓ ENABLED' : '✗ DISABLED') . "\n";
echo "canExport() result: " . ($canExport ? '✓ TRUE' : '✗ FALSE') . "\n";

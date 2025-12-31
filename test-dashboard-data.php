<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the first user (assuming it's the admin)
$user = \App\Models\User::first();

if (!$user) {
    echo "No users found\n";
    exit;
}

echo "User: {$user->name} (ID: {$user->id})\n";
echo "Is Admin: " . ($user->is_admin ? 'Yes' : 'No') . "\n\n";

// Get tier restriction service
$tierService = app(\App\Services\GrowBuilder\TierRestrictionService::class);

// Get restrictions
$restrictions = $tierService->getRestrictions($user);

echo "=== Restrictions ===\n";
echo "Tier: {$restrictions['tier']}\n";
echo "Tier Name: {$restrictions['tier_name']}\n";
echo "Sites Limit: {$restrictions['sites_limit']}\n";
echo "Storage Limit: {$restrictions['storage_limit_formatted']}\n";
echo "Products Limit: " . ($restrictions['products_unlimited'] ? 'Unlimited' : $restrictions['products_limit']) . "\n";
echo "AI Prompts: " . ($restrictions['ai_unlimited'] ? 'Unlimited' : $restrictions['ai_prompts_limit']) . "\n";

// Check subscription in database
echo "\n=== Database Subscription ===\n";
$subscription = \DB::table('module_subscriptions')
    ->where('user_id', $user->id)
    ->where('module_id', 'growbuilder')
    ->first();

if ($subscription) {
    echo "Tier: {$subscription->subscription_tier}\n";
    echo "Status: {$subscription->status}\n";
} else {
    echo "No subscription found\n";
}

// Count sites
$siteRepo = app(\App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface::class);
$sites = $siteRepo->findByUserId($user->id);
echo "\n=== Sites ===\n";
echo "Sites Used: " . count($sites) . "\n";
echo "Can Create: " . (count($sites) < $restrictions['sites_limit'] ? 'Yes' : 'No') . "\n";

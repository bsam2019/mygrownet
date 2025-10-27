<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find Jason Mwale (who has Esaya as referral)
$jason = App\Models\User::where('name', 'like', '%Jason%')->first();

if (!$jason) {
    echo "Jason not found\n";
    exit;
}

echo "Testing data for: {$jason->name}\n";
echo "=================================\n\n";

// Get the referral service
$referralService = app(App\Services\ReferralService::class);

// Get level 1 referrals
$level1Referrals = $referralService->getLevel1Referrals($jason);

echo "Level 1 Referrals Data:\n";
echo "-----------------------\n";
foreach ($level1Referrals as $referral) {
    echo "Name: {$referral['name']}\n";
    echo "  has_starter_kit: " . ($referral['has_starter_kit'] ? 'true' : 'false') . "\n";
    echo "  is_active: " . ($referral['is_active'] ? 'true' : 'false') . "\n";
    echo "  status: {$referral['status']}\n";
    echo "\n";
}

// Now check the actual database values
echo "\nDirect Database Check:\n";
echo "----------------------\n";
$referrals = $jason->referrals()->get();
foreach ($referrals as $ref) {
    echo "Name: {$ref->name}\n";
    echo "  has_starter_kit (DB): " . ($ref->has_starter_kit ? 'true' : 'false') . "\n";
    echo "  has_starter_kit (raw): " . var_export($ref->has_starter_kit, true) . "\n";
    echo "  has_starter_kit (type): " . gettype($ref->has_starter_kit) . "\n";
    echo "\n";
}

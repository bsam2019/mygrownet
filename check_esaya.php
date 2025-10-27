<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('name', 'like', '%Esaya%')->first();

if ($user) {
    echo "User: {$user->name}\n";
    echo "ID: {$user->id}\n";
    echo "Has Starter Kit: " . ($user->has_starter_kit ? 'YES' : 'NO') . "\n";
    echo "Starter Kit Date: {$user->starter_kit_purchased_at}\n";
    echo "\n";
    
    // Check referrals
    $referrals = $user->referrals()->get();
    echo "Total Referrals: " . $referrals->count() . "\n";
    foreach ($referrals as $referral) {
        echo "  - {$referral->name} (Has Kit: " . ($referral->has_starter_kit ? 'YES' : 'NO') . ")\n";
    }
} else {
    echo "User not found\n";
}

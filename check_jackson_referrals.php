<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking Jackson's Referrals for Verified Payments\n";
echo "===================================================\n\n";

$user = App\Models\User::find(31);

$referrals = App\Models\User::where('referrer_id', $user->id)->get();

echo "Total Referrals: " . $referrals->count() . "\n\n";

$verifiedCount = 0;

foreach ($referrals as $referral) {
 
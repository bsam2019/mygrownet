<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$jason = App\Models\User::where('name', 'like', '%Jason%')->first();

if (!$jason) {
    echo "Jason not found\n";
    exit;
}

echo "Checking Matrix Positions for: {$jason->name}\n";
echo "==============================================\n\n";

// Check Jason's referrals
$referrals = $jason->referrals;
echo "Total Referrals (from referrer_id): " . $referrals->count() . "\n";
foreach ($referrals as $ref) {
    echo "  - {$ref->name}\n";
}

echo "\n";

// Check matrix positions
$matrixPositions = App\Models\MatrixPosition::where('sponsor_id', $jason->id)->get();
echo "Matrix Positions (from matrix_positions table): " . $matrixPositions->count() . "\n";
foreach ($matrixPositions as $pos) {
    echo "  - User ID: {$pos->user_id}, Level: {$pos->level}, Position: {$pos->position}\n";
}

echo "\n";

// Check Jason's own matrix position
$jasonPosition = App\Models\MatrixPosition::where('user_id', $jason->id)->first();
if ($jasonPosition) {
    echo "Jason's Matrix Position: Level {$jasonPosition->level}, Position {$jasonPosition->position}\n";
} else {
    echo "Jason has NO matrix position!\n";
}

echo "\n";

// Check referral_count field
echo "Jason's referral_count field: {$jason->referral_count}\n";

// Check actual count
$actualCount = App\Models\User::where('referrer_id', $jason->id)->count();
echo "Actual referrals count: {$actualCount}\n";

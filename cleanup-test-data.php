<?php

/**
 * Cleanup script to remove test data accidentally seeded on production
 * Run with: php cleanup-test-data.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧹 Cleaning up test data from production...\n\n";

// SAFETY: Only delete users with specific test email patterns
$testEmails = [
    'sponsor@mygrownet.com',
];

// Add level users
for ($i = 1; $i <= 3; $i++) {
    $testEmails[] = "level1-user{$i}@mygrownet.com";
}
for ($i = 1; $i <= 9; $i++) {
    $testEmails[] = "level2-user{$i}@mygrownet.com";
}
for ($i = 1; $i <= 15; $i++) {
    $testEmails[] = "level3-user{$i}@mygrownet.com";
}
for ($i = 1; $i <= 3; $i++) {
    $testEmails[] = "pending-user{$i}@mygrownet.com";
}

// Get test users with safety checks
$testUsers = DB::table('users')
    ->whereIn('email', $testEmails)
    ->get(['id', 'name', 'email', 'created_at']);

if ($testUsers->isEmpty()) {
    echo "✅ No test users found. Database is clean.\n";
    exit(0);
}

echo "Found " . count($testUsers) . " test users to remove:\n\n";
echo "ID\tName\t\t\tEmail\t\t\t\tCreated\n";
echo str_repeat("-", 100) . "\n";
foreach ($testUsers as $user) {
    echo "{$user->id}\t{$user->name}\t\t{$user->email}\t\t{$user->created_at}\n";
}
echo "\n";

// SAFETY CHECK 1: Verify these are test users created recently (today)
$oldUsers = $testUsers->filter(function($user) {
    return \Carbon\Carbon::parse($user->created_at)->lt(now()->subDay());
});

if ($oldUsers->isNotEmpty()) {
    echo "⚠️  WARNING: Some users are older than 1 day:\n";
    foreach ($oldUsers as $user) {
        echo "   - {$user->email} (created: {$user->created_at})\n";
    }
    echo "\nThis script will NOT proceed to protect potentially real user data.\n";
    echo "Please manually review and delete if needed.\n";
    exit(1);
}

// SAFETY CHECK 2: Check for verified payments
$testUserIds = $testUsers->pluck('id')->toArray();
$paymentsCount = DB::table('member_payments')
    ->whereIn('user_id', $testUserIds)
    ->where('status', 'verified')
    ->count();

if ($paymentsCount > 0) {
    echo "⚠️  WARNING: Found {$paymentsCount} verified payments for these users.\n";
    echo "This script will NOT proceed to protect real transaction data.\n";
    echo "Please manually review and delete if needed.\n";
    exit(1);
}

// SAFETY CHECK 3: Ensure all emails match test patterns
foreach ($testUsers as $user) {
    if (!preg_match('/@mygrownet\.com$/', $user->email) || 
        !in_array($user->email, $testEmails)) {
        echo "⚠️  WARNING: User {$user->email} doesn't match expected test patterns.\n";
        echo "Aborting for safety.\n";
        exit(1);
    }
}

echo "✅ All safety checks passed. These appear to be test users.\n\n";

// Confirmation prompt
echo "⚠️  WARNING: This will permanently delete these users and all their data!\n";
echo "Are you sure you want to continue? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "❌ Cleanup cancelled.\n";
    exit(0);
}

echo "\nProceeding with cleanup...\n\n";

// Start transaction
DB::beginTransaction();

try {
    // 1. Delete matrix positions
    $matrixDeleted = DB::table('matrix_positions')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$matrixDeleted} matrix positions\n";
    
    // Also delete positions where test users are sponsors
    $sponsorMatrixDeleted = DB::table('matrix_positions')
        ->whereIn('sponsor_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$sponsorMatrixDeleted} matrix positions (as sponsor)\n";
    
    // 2. Delete point transactions
    $pointsDeleted = DB::table('point_transactions')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$pointsDeleted} point transactions\n";
    
    // 3. Delete user points
    $userPointsDeleted = DB::table('user_points')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$userPointsDeleted} user points records\n";
    
    // 4. Delete referral commissions
    $commissionsDeleted = DB::table('referral_commissions')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$commissionsDeleted} referral commissions\n";
    
    // 5. Delete member payments
    $paymentsDeleted = DB::table('member_payments')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$paymentsDeleted} member payments\n";
    
    // 6. Delete starter kit purchases
    $starterKitDeleted = DB::table('starter_kit_purchases')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$starterKitDeleted} starter kit purchases\n";
    
    // 7. Delete starter kit unlocks
    $unlocksDeleted = DB::table('starter_kit_unlocks')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$unlocksDeleted} starter kit unlocks\n";
    
    // 8. Delete member achievements
    $achievementsDeleted = DB::table('member_achievements')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$achievementsDeleted} member achievements\n";
    
    // 9. Delete package subscriptions
    $subscriptionsDeleted = DB::table('package_subscriptions')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$subscriptionsDeleted} package subscriptions\n";
    
    // 10. Delete notifications
    $notificationsDeleted = DB::table('notifications')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$notificationsDeleted} notifications\n";
    
    // 11. Delete notification preferences
    $preferencesDeleted = DB::table('notification_preferences')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$preferencesDeleted} notification preferences\n";
    
    // 12. Delete LGR qualifications
    $lgrQualDeleted = DB::table('lgr_qualifications')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$lgrQualDeleted} LGR qualifications\n";
    
    // 13. Delete LGR activities
    $lgrActivitiesDeleted = DB::table('lgr_activities')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$lgrActivitiesDeleted} LGR activities\n";
    
    // 14. Delete withdrawals
    $withdrawalsDeleted = DB::table('withdrawals')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$withdrawalsDeleted} withdrawals\n";
    
    // 15. Delete receipts
    $receiptsDeleted = DB::table('receipts')
        ->whereIn('user_id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$receiptsDeleted} receipts\n";
    
    // 16. Update referrer_id for any real users who were assigned test users as referrers
    $referrerUpdated = DB::table('users')
        ->whereIn('referrer_id', $testUserIds)
        ->update(['referrer_id' => null]);
    echo "✓ Updated {$referrerUpdated} users who had test users as referrers\n";
    
    // 17. Finally, delete the test users themselves
    $usersDeleted = DB::table('users')
        ->whereIn('id', $testUserIds)
        ->delete();
    echo "✓ Deleted {$usersDeleted} test users\n";
    
    // Commit transaction
    DB::commit();
    
    echo "\n✅ Cleanup complete! All test data has been removed.\n";
    echo "\n📊 Summary:\n";
    echo "   - Test users removed: {$usersDeleted}\n";
    echo "   - Matrix positions: {$matrixDeleted}\n";
    echo "   - Point transactions: {$pointsDeleted}\n";
    echo "   - Commissions: {$commissionsDeleted}\n";
    echo "   - Total records cleaned: " . (
        $matrixDeleted + $sponsorMatrixDeleted + $pointsDeleted + $userPointsDeleted + 
        $commissionsDeleted + $paymentsDeleted + $starterKitDeleted + $unlocksDeleted + 
        $achievementsDeleted + $subscriptionsDeleted + $notificationsDeleted + 
        $preferencesDeleted + $lgrQualDeleted + $lgrActivitiesDeleted + 
        $withdrawalsDeleted + $receiptsDeleted + $usersDeleted
    ) . "\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error during cleanup: " . $e->getMessage() . "\n";
    echo "Transaction rolled back. No changes were made.\n";
    exit(1);
}

<?php

/**
 * Fix Incorrect Points Issue
 * 
 * Problem: Points were being awarded at registration instead of starter kit purchase
 * Solution: Remove all incorrect point transactions and recalculate from starter kit purchases only
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== Fixing Incorrect Points Issue ===\n\n";

DB::beginTransaction();

try {
    // 1. Get all users with points
    $usersWithPoints = DB::table('users')
        ->where(function($query) {
            $query->where('life_points', '>', 0)
                  ->orWhere('bonus_points', '>', 0);
        })
        ->get(['id', 'name', 'life_points', 'bonus_points']);
    
    echo "Found " . $usersWithPoints->count() . " users with points\n\n";
    
    $fixedCount = 0;
    $keptCount = 0;
    
    foreach ($usersWithPoints as $user) {
        // Check if user has completed starter kit purchase
        $hasStarterKit = DB::table('starter_kit_purchases')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();
        
        if (!$hasStarterKit) {
            // User has points but no starter kit - INCORRECT
            echo "❌ User #{$user->id} ({$user->name}): LP={$user->life_points}, BP={$user->bonus_points} - NO STARTER KIT\n";
            
            // Delete all point transactions for this user
            $deletedCount = DB::table('point_transactions')
                ->where('user_id', $user->id)
                ->delete();
            
            // Reset points to zero
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'life_points' => 0,
                    'bonus_points' => 0,
                ]);
            
            echo "   Deleted {$deletedCount} transactions, reset points to 0\n\n";
            $fixedCount++;
        } else {
            // User has starter kit - points should be correct
            echo "✅ User #{$user->id} ({$user->name}): LP={$user->life_points}, BP={$user->bonus_points} - HAS STARTER KIT\n\n";
            $keptCount++;
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Fixed: {$fixedCount} users (removed incorrect points)\n";
    echo "Kept: {$keptCount} users (have starter kit)\n";
    echo "\nCommitting changes...\n";
    
    DB::commit();
    
    echo "✅ Done!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

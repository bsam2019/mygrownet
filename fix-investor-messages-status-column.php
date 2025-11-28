<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Investor Messages Status Column Fix ===\n\n";

try {
    // Check if investor_messages table exists
    if (!Schema::hasTable('investor_messages')) {
        echo "❌ investor_messages table does not exist!\n";
        echo "Running migration...\n";
        
        // Run the specific migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_11_27_100001_create_investor_messages_table.php',
            '--force' => true
        ]);
        
        echo "✅ Migration completed\n";
        exit(0);
    }
    
    echo "✅ investor_messages table exists\n";
    
    // Check if status column exists
    if (!Schema::hasColumn('investor_messages', 'status')) {
        echo "❌ status column missing from investor_messages table!\n";
        echo "Adding status column...\n";
        
        // Add the missing column
        Schema::table('investor_messages', function ($table) {
            $table->enum('status', ['unread', 'read', 'replied', 'archived'])->default('unread')->after('direction');
        });
        
        echo "✅ Status column added successfully\n";
        
        // Add index for performance
        Schema::table('investor_messages', function ($table) {
            $table->index(['investor_account_id', 'status']);
            $table->index(['direction', 'status']);
        });
        
        echo "✅ Indexes added for performance\n";
    } else {
        echo "✅ status column exists\n";
    }
    
    // Check table structure
    echo "\n=== Current Table Structure ===\n";
    $columns = DB::select("DESCRIBE investor_messages");
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) " . 
             ($column->Null === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column->Default ? " DEFAULT {$column->Default}" : '') . "\n";
    }
    
    // Check for any existing data
    $messageCount = DB::table('investor_messages')->count();
    echo "\n=== Data Check ===\n";
    echo "Total messages: {$messageCount}\n";
    
    if ($messageCount > 0) {
        // Update any existing messages without status
        $updated = DB::table('investor_messages')
            ->whereNull('status')
            ->update(['status' => 'unread']);
        
        if ($updated > 0) {
            echo "✅ Updated {$updated} messages with default status\n";
        }
        
        // Show status distribution
        $statusCounts = DB::table('investor_messages')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
        
        echo "\nStatus distribution:\n";
        foreach ($statusCounts as $status) {
            echo "- {$status->status}: {$status->count}\n";
        }
    }
    
    // Test the problematic query
    echo "\n=== Testing Problematic Query ===\n";
    try {
        $testCount = DB::table('investor_messages')
            ->where('investor_account_id', 1)
            ->where('direction', 'to_investor')
            ->where('status', 'unread')
            ->count();
        
        echo "✅ Query executed successfully. Unread messages for investor 1: {$testCount}\n";
    } catch (Exception $e) {
        echo "❌ Query still failing: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Fix Complete ===\n";
    echo "The investor_messages table status column issue has been resolved.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
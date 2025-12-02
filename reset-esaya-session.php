<?php

/**
 * Reset Session for Esaya Nkhata (User ID: 11)
 * 
 * This script:
 * 1. Clears the user's remember token (forces re-login)
 * 2. Clears any database sessions for this user
 * 3. Optionally clears file-based sessions
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "===========================================\n";
echo "  Reset Session for Esaya Nkhata\n";
echo "===========================================\n\n";

$userId = 11;

// Find the user
$user = User::find($userId);

if (!$user) {
    echo "❌ User with ID {$userId} not found!\n";
    exit(1);
}

echo "📋 User Found:\n";
echo "   ID: {$user->id}\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Phone: " . ($user->phone ?? 'N/A') . "\n\n";

// 1. Clear remember token
echo "🔄 Clearing remember token...\n";
$oldToken = $user->remember_token;
$user->remember_token = null;
$user->save();
echo "   ✅ Remember token cleared" . ($oldToken ? " (was set)" : " (was already null)") . "\n\n";

// 2. Clear database sessions if the sessions table exists
echo "🔄 Checking for database sessions...\n";
if (Schema::hasTable('sessions')) {
    $deletedCount = DB::table('sessions')
        ->where('user_id', $userId)
        ->delete();
    
    echo "   ✅ Deleted {$deletedCount} database session(s)\n\n";
} else {
    echo "   ℹ️  Sessions table not found (using file-based sessions)\n\n";
}

// 3. Clear personal access tokens if using Sanctum
echo "🔄 Checking for personal access tokens...\n";
if (Schema::hasTable('personal_access_tokens')) {
    $deletedTokens = DB::table('personal_access_tokens')
        ->where('tokenable_type', User::class)
        ->where('tokenable_id', $userId)
        ->delete();
    
    echo "   ✅ Deleted {$deletedTokens} personal access token(s)\n\n";
} else {
    echo "   ℹ️  Personal access tokens table not found\n\n";
}

echo "===========================================\n";
echo "  ✅ Session Reset Complete!\n";
echo "===========================================\n\n";
echo "Esaya will need to log in again on their next visit.\n";

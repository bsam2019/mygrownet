<?php

/**
 * Fix User Credentials Issue - Production Login Problems
 * 
 * Problem: Users unable to login in production environment
 * Solution: Comprehensive diagnosis and fix of authentication system
 * 
 * Run: php fix-user-credentials.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

echo "=== MyGrowNet Login Credentials Fix Script ===\n\n";
echo "Diagnosing and fixing production login issues...\n\n";

// Step 1: Database Structure Analysis
echo "Step 1: Analyzing database structure...\n";
echo str_repeat('-', 60) . "\n";

try {
    $usersColumns = Schema::getColumnListing('users');
    $profilesColumns = Schema::getColumnListing('user_profiles');
    
    echo "✓ Users table columns (" . count($usersColumns) . "): ";
    $authColumns = array_intersect(['id', 'name', 'email', 'password', 'phone'], $usersColumns);
    echo implode(', ', $authColumns) . "\n";
    
    echo "✓ User Profiles table columns (" . count($profilesColumns) . "): ";
    $profileOnlyColumns = array_intersect(['phone_number', 'address', 'city', 'kyc_status'], $profilesColumns);
    echo implode(', ', $profileOnlyColumns) . "\n\n";
    
    // Verify no credential columns in profiles
    $credentialColumns = ['email', 'password'];
    $foundInProfiles = array_intersect($credentialColumns, $profilesColumns);
    
    if (!empty($foundInProfiles)) {
        echo "❌ CRITICAL: Found credential columns in profiles table: " . implode(', ', $foundInProfiles) . "\n";
        echo "   This WILL cause login issues!\n\n";
    } else {
        echo "✅ GOOD: Credentials properly separated (users table only)\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Step 2: User Data Integrity Check
echo "Step 2: Checking user data integrity...\n";
echo str_repeat('-', 60) . "\n";

try {
    $totalUsers = User::count();
    $usersWithEmail = User::whereNotNull('email')->where('email', '!=', '')->count();
    $usersWithPassword = User::whereNotNull('password')->where('password', '!=', '')->count();
    $usersWithPhone = User::whereNotNull('phone')->where('phone', '!=', '')->count();
    
    echo "📊 User Statistics:\n";
    echo "   Total users: $totalUsers\n";
    echo "   Users with email: $usersWithEmail (" . round(($usersWithEmail/$totalUsers)*100, 1) . "%)\n";
    echo "   Users with password: $usersWithPassword (" . round(($usersWithPassword/$totalUsers)*100, 1) . "%)\n";
    echo "   Users with phone: $usersWithPhone (" . round(($usersWithPhone/$totalUsers)*100, 1) . "%)\n\n";
    
    // Check for problematic users
    $usersWithoutEmail = User::whereNull('email')->orWhere('email', '')->count();
    $usersWithoutPassword = User::whereNull('password')->orWhere('password', '')->count();
    
    if ($usersWithoutEmail > 0) {
        echo "❌ CRITICAL: $usersWithoutEmail users missing email addresses\n";
        $missingEmailUsers = User::whereNull('email')->orWhere('email', '')->limit(5)->get(['id', 'name', 'phone']);
        foreach ($missingEmailUsers as $user) {
            echo "   - User ID {$user->id}: {$user->name} (phone: {$user->phone})\n";
        }
        echo "\n";
    }
    
    if ($usersWithoutPassword > 0) {
        echo "❌ CRITICAL: $usersWithoutPassword users missing passwords\n";
        $missingPasswordUsers = User::whereNull('password')->orWhere('password', '')->limit(5)->get(['id', 'name', 'email']);
        foreach ($missingPasswordUsers as $user) {
            echo "   - User ID {$user->id}: {$user->name} ({$user->email})\n";
        }
        echo "\n";
    }
    
    if ($usersWithoutEmail == 0 && $usersWithoutPassword == 0) {
        echo "✅ GOOD: All users have required credentials\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error checking user data: " . $e->getMessage() . "\n\n";
}

// Step 3: Authentication Configuration Check
echo "Step 3: Verifying authentication configuration...\n";
echo str_repeat('-', 60) . "\n";

try {
    $authModel = config('auth.providers.users.model');
    $authDriver = config('auth.providers.users.driver');
    $authGuard = config('auth.defaults.guard');
    
    echo "🔧 Authentication Configuration:\n";
    echo "   Default Guard: $authGuard\n";
    echo "   Auth Driver: $authDriver\n";
    echo "   Auth Model: $authModel\n\n";
    
    if ($authModel === 'App\Models\User' || $authModel === 'App\\Models\\User') {
        echo "✅ GOOD: Auth model correctly points to User model\n";
    } else {
        echo "❌ CRITICAL: Auth model misconfigured!\n";
        echo "   Expected: App\\Models\\User\n";
        echo "   Current: $authModel\n";
        echo "   Fix: Update config/auth.php\n";
    }
    
    // Check if User model exists and is loadable
    if (class_exists($authModel)) {
        echo "✅ GOOD: User model class exists and is loadable\n";
        
        // Test model instantiation
        $testModel = new $authModel;
        if (method_exists($testModel, 'getAuthIdentifierName')) {
            echo "✅ GOOD: User model implements authentication interface\n";
        }
    } else {
        echo "❌ CRITICAL: User model class not found: $authModel\n";
    }
    
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error checking auth config: " . $e->getMessage() . "\n\n";
}

// Step 4: Authentication System Test
echo "Step 4: Testing authentication system...\n";
echo str_repeat('-', 60) . "\n";

try {
    // Find a test user with complete credentials
    $testUser = User::whereNotNull('email')
                   ->whereNotNull('password')
                   ->where('email', '!=', '')
                   ->where('password', '!=', '')
                   ->first();

    if ($testUser) {
        echo "🧪 Testing with user:\n";
        echo "   ID: {$testUser->id}\n";
        echo "   Name: {$testUser->name}\n";
        echo "   Email: {$testUser->email}\n";
        echo "   Phone: " . ($testUser->phone ?? 'Not set') . "\n";
        
        // Validate password hash
        $passwordLength = strlen($testUser->password);
        $passwordPrefix = substr($testUser->password, 0, 4);
        
        echo "\n🔐 Password Analysis:\n";
        echo "   Hash length: $passwordLength chars\n";
        echo "   Hash prefix: $passwordPrefix\n";
        
        if ($passwordLength === 60 && in_array($passwordPrefix, ['$2y$', '$2a$', '$2b$'])) {
            echo "   ✅ Valid bcrypt hash format\n";
        } else {
            echo "   ❌ Invalid password hash format!\n";
            echo "   Expected: 60 chars starting with \$2y\$\n";
        }
        
        // Check profile relationship
        $profile = $testUser->profile;
        echo "\n👤 Profile Check:\n";
        if ($profile) {
            echo "   ✅ Profile exists (ID: {$profile->id})\n";
            echo "   Phone in profile: " . ($profile->phone_number ?? 'Not set') . "\n";
        } else {
            echo "   ⚠️  No profile found\n";
        }
        
        // Test authentication methods
        echo "\n🔍 Authentication Test:\n";
        
        // Test findByPhoneOrEmail method
        if (method_exists(User::class, 'findByPhoneOrEmail')) {
            $foundByEmail = User::findByPhoneOrEmail($testUser->email);
            echo "   findByPhoneOrEmail (email): " . ($foundByEmail ? "✅ Found" : "❌ Not found") . "\n";
            
            if ($testUser->phone) {
                $foundByPhone = User::findByPhoneOrEmail($testUser->phone);
                echo "   findByPhoneOrEmail (phone): " . ($foundByPhone ? "✅ Found" : "❌ Not found") . "\n";
            }
        }
        
    } else {
        echo "❌ CRITICAL: No users found with valid email and password!\n";
        echo "   This explains why login is failing.\n";
        
        // Check if we have users at all
        $userCount = User::count();
        if ($userCount > 0) {
            echo "   Found $userCount users total, but none have complete credentials.\n";
        } else {
            echo "   No users found in database at all!\n";
        }
    }
    
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error testing authentication: " . $e->getMessage() . "\n\n";
}$') {
// Step 5: Production Environment Check
echo "Step 5: Production environment diagnostics...\n";
echo str_repeat('-', 60) . "\n";

try {
    // Check environment
    $environment = app()->environment();
    $debug = config('app.debug');
    $cacheDriver = config('cache.default');
    
    echo "🌍 Environment Information:\n";
    echo "   Environment: $environment\n";
    echo "   Debug mode: " . ($debug ? 'ON' : 'OFF') . "\n";
    echo "   Cache driver: $cacheDriver\n";
    
    // Check for common production issues
    echo "\n🔧 Production Checks:\n";
    
    // Check if config is cached
    if (file_exists(base_path('bootstrap/cache/config.php'))) {
        echo "   ✅ Config is cached (good for production)\n";
    } else {
        echo "   ⚠️  Config not cached (may cause performance issues)\n";
    }
    
    // Check if routes are cached
    if (file_exists(base_path('bootstrap/cache/routes-v7.php'))) {
        echo "   ✅ Routes are cached\n";
    } else {
        echo "   ⚠️  Routes not cached\n";
    }
    
    // Check session configuration
    $sessionDriver = config('session.driver');
    $sessionLifetime = config('session.lifetime');
    echo "   Session driver: $sessionDriver\n";
    echo "   Session lifetime: $sessionLifetime minutes\n";
    
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error checking environment: " . $e->getMessage() . "\n\n";
}

// Step 6: Data Consistency Check
echo "Step 6: Checking data consistency...\n";
echo str_repeat('-', 60) . "\n";

try {
    // Check for duplicate emails
    $duplicates = User::select('email', DB::raw('COUNT(*) as count'))
        ->whereNotNull('email')
        ->where('email', '!=', '')
        ->groupBy('email')
        ->having('count', '>', 1)
        ->get();

    if ($duplicates->count() > 0) {
        echo "❌ CRITICAL: Found duplicate emails:\n";
        foreach ($duplicates as $dup) {
            echo "   - {$dup->email} ({$dup->count} accounts)\n";
        }
        echo "   This WILL cause login conflicts!\n\n";
    } else {
        echo "✅ GOOD: No duplicate emails found\n";
    }
    
    // Check for users without profiles
    $usersWithoutProfiles = User::doesntHave('profile')->count();
    if ($usersWithoutProfiles > 0) {
        echo "⚠️  WARNING: $usersWithoutProfiles users without profiles\n";
    } else {
        echo "✅ GOOD: All users have profiles\n";
    }
    
    // Check for orphaned profiles
    $orphanedProfiles = UserProfile::doesntHave('user')->count();
    if ($orphanedProfiles > 0) {
        echo "⚠️  WARNING: $orphanedProfiles orphaned profiles\n";
    } else {
        echo "✅ GOOD: No orphaned profiles\n";
    }
    
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error checking data consistency: " . $e->getMessage() . "\n\n";
}

// Step 7: Automatic Fixes (if needed)
echo "Step 7: Applying automatic fixes...\n";
echo str_repeat('-', 60) . "\n";

$fixesApplied = 0;

try {
    // Fix 1: Clear all caches
    echo "🧹 Clearing application caches...\n";
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    echo "   ✅ Caches cleared\n";
    $fixesApplied++;
    
    // Fix 2: Rebuild optimized caches
    echo "\n🔧 Rebuilding optimized caches...\n";
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    echo "   ✅ Caches rebuilt\n";
    $fixesApplied++;
    
    // Fix 3: Fix users without profiles
    $usersWithoutProfiles = User::doesntHave('profile')->get();
    if ($usersWithoutProfiles->count() > 0) {
        echo "\n👤 Creating missing user profiles...\n";
        foreach ($usersWithoutProfiles as $user) {
            UserProfile::create([
                'user_id' => $user->id,
                'phone_number' => $user->phone ?? null,
            ]);
            echo "   ✅ Created profile for user {$user->id}\n";
        }
        $fixesApplied++;
    }
    
    // Fix 4: Normalize phone numbers
    echo "\n📱 Normalizing phone numbers...\n";
    $usersWithPhone = User::whereNotNull('phone')->where('phone', '!=', '')->get();
    $phoneFixed = 0;
    
    foreach ($usersWithPhone as $user) {
        $originalPhone = $user->phone;
        $normalizedPhone = User::normalizePhone($originalPhone);
        
        if ($originalPhone !== $normalizedPhone) {
            $user->update(['phone' => $normalizedPhone]);
            $phoneFixed++;
        }
    }
    
    if ($phoneFixed > 0) {
        echo "   ✅ Normalized $phoneFixed phone numbers\n";
        $fixesApplied++;
    } else {
        echo "   ✅ All phone numbers already normalized\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error applying fixes: " . $e->getMessage() . "\n";
}

// Step 8: Final Summary and Recommendations
echo "\n" . str_repeat('=', 60) . "\n";
echo "🎯 FINAL SUMMARY\n";
echo str_repeat('=', 60) . "\n\n";

try {
    $totalUsers = User::count();
    $usersWithCredentials = User::whereNotNull('email')
                               ->whereNotNull('password')
                               ->where('email', '!=', '')
                               ->where('password', '!=', '')
                               ->count();
    $percentageValid = $totalUsers > 0 ? round(($usersWithCredentials / $totalUsers) * 100, 2) : 0;

    echo "📊 Statistics:\n";
    echo "   Total users: $totalUsers\n";
    echo "   Users with valid credentials: $usersWithCredentials ($percentageValid%)\n";
    echo "   Fixes applied: $fixesApplied\n\n";

    if ($percentageValid >= 99) {
        echo "✅ EXCELLENT: Authentication system is healthy\n";
    } elseif ($percentageValid >= 95) {
        echo "✅ GOOD: Minor issues detected and fixed\n";
    } elseif ($percentageValid >= 80) {
        echo "⚠️  WARNING: Some users may have login issues\n";
    } else {
        echo "❌ CRITICAL: Major authentication problems detected!\n";
    }

    echo "\n🔧 Next Steps:\n";
    
    if ($percentageValid >= 95) {
        echo "1. ✅ Test login with multiple user accounts\n";
        echo "2. ✅ Monitor Laravel logs for any remaining issues\n";
        echo "3. ✅ Consider implementing login attempt logging\n";
    } else {
        echo "1. ❌ URGENT: Investigate users with missing credentials\n";
        echo "2. ❌ Check for data corruption or migration issues\n";
        echo "3. ❌ Consider restoring from backup if available\n";
    }
    
    echo "\n📝 Production Deployment Checklist:\n";
    echo "   □ Run this script on production server\n";
    echo "   □ Clear all caches: php artisan optimize:clear\n";
    echo "   □ Rebuild caches: php artisan optimize\n";
    echo "   □ Restart PHP-FPM and web server\n";
    echo "   □ Test login with known user accounts\n";
    echo "   □ Monitor error logs for 24 hours\n";
    
} catch (Exception $e) {
    echo "❌ Error generating summary: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "🏁 Script Complete - " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('=', 60) . "\n";

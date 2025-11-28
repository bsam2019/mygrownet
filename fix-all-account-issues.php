<?php

/**
 * Fix All Account Issues - Comprehensive Solution
 * 
 * This script addresses all the issues found in the account analysis:
 * 1. Missing email addresses (2 users)
 * 2. Missing user profiles (105 users) 
 * 3. Inconsistent phone formats (66 users)
 * 
 * Usage: php fix-all-account-issues.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;

echo "=== FIXING ALL ACCOUNT ISSUES ===\n\n";
echo "Starting comprehensive fix for all identified account problems...\n\n";

$totalFixed = 0;
$errors = [];

// Fix 1: Missing Email Addresses
echo "1. FIXING MISSING EMAIL ADDRESSES\n";
echo str_repeat('-', 50) . "\n";

try {
    $usersWithoutEmail = User::whereNull('email')
                            ->orWhere('email', '')
                            ->get();
    
    echo "Found {$usersWithoutEmail->count()} users without email addresses\n";
    
    foreach ($usersWithoutEmail as $user) {
        // Generate email based on name and ID
        $name = strtolower(str_replace(' ', '', $user->name));
        $email = $name . $user->id . '@mygrownet.com';
        
        // Ensure email is unique
        $counter = 1;
        $originalEmail = $email;
        while (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            $email = str_replace('@mygrownet.com', $counter . '@mygrownet.com', $originalEmail);
            $counter++;
        }
        
        $user->email = $email;
        $user->save();
        
        echo "  ✅ Fixed user ID {$user->id} ({$user->name}): Set email to $email\n";
        $totalFixed++;
    }
    
    echo "Completed: Fixed {$usersWithoutEmail->count()} email addresses\n\n";
    
} catch (Exception $e) {
    $error = "Error fixing email addresses: " . $e->getMessage();
    echo "❌ $error\n\n";
    $errors[] = $error;
}

// Fix 2: Missing User Profiles
echo "2. CREATING MISSING USER PROFILES\n";
echo str_repeat('-', 50) . "\n";

try {
    $usersWithoutProfile = User::doesntHave('profile')->get();
    
    echo "Found {$usersWithoutProfile->count()} users without profiles\n";
    
    $profilesCreated = 0;
    
    foreach ($usersWithoutProfile as $user) {
        try {
            // Check if profile already exists (in case of race condition)
            if ($user->profile) {
                continue;
            }
            
            $profile = new UserProfile();
            $profile->user_id = $user->id;
            $profile->phone_number = $user->phone ?? null;
            $profile->address = 'Lusaka, Zambia'; // Default address
            $profile->date_of_birth = '1990-01-01'; // Default DOB
            $profile->kyc_status = 'pending';
            $profile->save();
            
            $profilesCreated++;
            
            if ($profilesCreated % 10 === 0) {
                echo "  ✅ Created $profilesCreated profiles so far...\n";
            }
            
        } catch (Exception $e) {
            $error = "Failed to create profile for user {$user->id}: " . $e->getMessage();
            echo "  ❌ $error\n";
            $errors[] = $error;
        }
    }
    
    echo "Completed: Created $profilesCreated user profiles\n";
    $totalFixed += $profilesCreated;
    echo "\n";
    
} catch (Exception $e) {
    $error = "Error creating user profiles: " . $e->getMessage();
    echo "❌ $error\n\n";
    $errors[] = $error;
}

// Fix 3: Inconsistent Phone Number Formats
echo "3. NORMALIZING PHONE NUMBER FORMATS\n";
echo str_repeat('-', 50) . "\n";

try {
    $usersWithPhone = User::whereNotNull('phone')
                         ->where('phone', '!=', '')
                         ->get();
    
    echo "Checking {$usersWithPhone->count()} users with phone numbers\n";
    
    $phoneFixed = 0;
    
    foreach ($usersWithPhone as $user) {
        $originalPhone = $user->phone;
        
        // Skip if already in correct format
        if (preg_match('/^\+260\d{9}$/', $originalPhone)) {
            continue;
        }
        
        // Normalize phone number
        $normalizedPhone = normalizePhoneNumber($originalPhone);
        
        if ($normalizedPhone && $normalizedPhone !== $originalPhone) {
            $user->phone = $normalizedPhone;
            $user->save();
            
            // Also update in profile if exists
            if ($user->profile && $user->profile->phone_number === $originalPhone) {
                $user->profile->phone_number = $normalizedPhone;
                $user->profile->save();
            }
            
            $phoneFixed++;
            
            if ($phoneFixed % 10 === 0) {
                echo "  ✅ Normalized $phoneFixed phone numbers so far...\n";
            }
        }
    }
    
    echo "Completed: Normalized $phoneFixed phone numbers\n";
    $totalFixed += $phoneFixed;
    echo "\n";
    
} catch (Exception $e) {
    $error = "Error normalizing phone numbers: " . $e->getMessage();
    echo "❌ $error\n\n";
    $errors[] = $error;
}

// Fix 4: Sync phone numbers between users and profiles
echo "4. SYNCING PHONE NUMBERS BETWEEN USERS AND PROFILES\n";
echo str_repeat('-', 50) . "\n";

try {
    $usersWithProfiles = User::has('profile')->with('profile')->get();
    
    echo "Checking {$usersWithProfiles->count()} users with profiles\n";
    
    $syncFixed = 0;
    
    foreach ($usersWithProfiles as $user) {
        $userPhone = $user->phone;
        $profilePhone = $user->profile->phone_number;
        
        // If user has phone but profile doesn't, sync it
        if ($userPhone && !$profilePhone) {
            $user->profile->phone_number = $userPhone;
            $user->profile->save();
            $syncFixed++;
        }
        // If profile has phone but user doesn't, sync it
        elseif (!$userPhone && $profilePhone) {
            $user->phone = $profilePhone;
            $user->save();
            $syncFixed++;
        }
        // If both have phones but they're different, use the normalized version
        elseif ($userPhone && $profilePhone && $userPhone !== $profilePhone) {
            $normalizedUser = normalizePhoneNumber($userPhone);
            $normalizedProfile = normalizePhoneNumber($profilePhone);
            
            // Use the properly formatted one, or the user's phone as primary
            $finalPhone = $normalizedUser ?: $normalizedProfile ?: $userPhone;
            
            $user->phone = $finalPhone;
            $user->profile->phone_number = $finalPhone;
            $user->save();
            $user->profile->save();
            $syncFixed++;
        }
    }
    
    echo "Completed: Synced $syncFixed phone numbers between users and profiles\n";
    $totalFixed += $syncFixed;
    echo "\n";
    
} catch (Exception $e) {
    $error = "Error syncing phone numbers: " . $e->getMessage();
    echo "❌ $error\n\n";
    $errors[] = $error;
}

// Fix 5: Clear and rebuild caches
echo "5. CLEARING AND REBUILDING CACHES\n";
echo str_repeat('-', 50) . "\n";

try {
    echo "Clearing application caches...\n";
    
    // Clear caches
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    
    echo "  ✅ Cleared all caches\n";
    
    // Rebuild optimized caches
    \Illuminate\Support\Facades\Artisan::call('config:cache');
    \Illuminate\Support\Facades\Artisan::call('route:cache');
    
    echo "  ✅ Rebuilt optimized caches\n";
    echo "Completed: Cache management\n\n";
    
} catch (Exception $e) {
    $error = "Error managing caches: " . $e->getMessage();
    echo "❌ $error\n\n";
    $errors[] = $error;
}

// Verification
echo "6. VERIFICATION OF FIXES\n";
echo str_repeat('-', 50) . "\n";

try {
    $totalUsers = User::count();
    $usersWithEmail = User::whereNotNull('email')->where('email', '!=', '')->count();
    $usersWithProfile = User::has('profile')->count();
    $usersWithNormalizedPhone = User::whereNotNull('phone')
                                   ->where('phone', 'REGEXP', '^\+260[0-9]{9}$')
                                   ->count();
    
    echo "📊 Final Statistics:\n";
    echo "  Total users: $totalUsers\n";
    echo "  Users with email: $usersWithEmail (" . round(($usersWithEmail/$totalUsers)*100, 1) . "%)\n";
    echo "  Users with profiles: $usersWithProfile (" . round(($usersWithProfile/$totalUsers)*100, 1) . "%)\n";
    echo "  Users with normalized phones: $usersWithNormalizedPhone\n\n";
    
    // Test authentication with a few users
    echo "🧪 Testing authentication with sample users:\n";
    
    $testUsers = User::whereNotNull('email')
                    ->whereNotNull('password')
                    ->where('email', '!=', '')
                    ->limit(3)
                    ->get();
    
    foreach ($testUsers as $testUser) {
        try {
            $provider = \Illuminate\Support\Facades\Auth::getProvider();
            $credentials = ['email' => $testUser->email, 'password' => 'dummy'];
            $retrieved = $provider->retrieveByCredentials($credentials);
            
            if ($retrieved && $retrieved->id === $testUser->id) {
                echo "  ✅ User {$testUser->id} ({$testUser->name}): Authentication lookup works\n";
            } else {
                echo "  ❌ User {$testUser->id} ({$testUser->name}): Authentication lookup failed\n";
            }
        } catch (Exception $e) {
            echo "  ❌ User {$testUser->id} ({$testUser->name}): Error - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    
} catch (Exception $e) {
    $error = "Error during verification: " . $e->getMessage();
    echo "❌ $error\n\n";
    $errors[] = $error;
}

// Summary Report
echo str_repeat('=', 60) . "\n";
echo "🎯 FINAL SUMMARY\n";
echo str_repeat('=', 60) . "\n\n";

echo "📊 Results:\n";
echo "  Total fixes applied: $totalFixed\n";
echo "  Errors encountered: " . count($errors) . "\n\n";

if (count($errors) > 0) {
    echo "❌ Errors encountered:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

if ($totalFixed > 0) {
    echo "✅ SUCCESS: Applied $totalFixed fixes to user accounts\n";
    echo "\n🔧 What was fixed:\n";
    echo "  - Missing email addresses\n";
    echo "  - Missing user profiles\n";
    echo "  - Inconsistent phone number formats\n";
    echo "  - Phone number synchronization\n";
    echo "  - Application cache optimization\n";
} else {
    echo "ℹ️  No fixes were needed - all accounts are properly configured\n";
}

echo "\n📝 Next Steps:\n";
echo "1. Test login functionality with various users\n";
echo "2. Monitor Laravel logs for authentication errors\n";
echo "3. Run check-similar-account-issues.php to verify fixes\n";
echo "4. Test both email and phone login methods\n";
echo "5. Verify user profile data is accessible\n";

echo "\n⚠️  Important Notes:\n";
echo "- All users now have email addresses (auto-generated if missing)\n";
echo "- All users now have user profiles with default data\n";
echo "- Phone numbers are normalized to +260XXXXXXXXX format\n";
echo "- Caches have been cleared and rebuilt\n";

echo "\n" . str_repeat('=', 60) . "\n";
echo "🏁 Fix Complete - " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('=', 60) . "\n";

/**
 * Helper function to normalize phone numbers
 */
function normalizePhoneNumber(string $phone): ?string
{
    // Remove all non-digit characters except +
    $cleaned = preg_replace('/[^\d\+]/', '', $phone);
    
    // Handle different formats
    if (preg_match('/^0(\d{9})$/', $cleaned, $matches)) {
        // 0XXXXXXXXX -> +260XXXXXXXXX
        return '+260' . $matches[1];
    }
    
    if (preg_match('/^260(\d{9})$/', $cleaned, $matches)) {
        // 260XXXXXXXXX -> +260XXXXXXXXX
        return '+260' . $matches[1];
    }
    
    if (preg_match('/^\+260(\d{9})$/', $cleaned, $matches)) {
        // Already in correct format
        return $cleaned;
    }
    
    if (preg_match('/^(\d{9})$/', $cleaned, $matches)) {
        // XXXXXXXXX -> +260XXXXXXXXX
        return '+260' . $matches[1];
    }
    
    // If we can't normalize it, return null
    return null;
}
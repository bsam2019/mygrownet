<?php

/**
 * Fix Esaya Account - Specific fix script
 * 
 * Based on diagnostic results:
 * - User exists: Esaya Nkhata (ID: 11)
 * - Has phone: 0976311664
 * - Has password (valid bcrypt hash)
 * - Missing: Email and Profile
 * 
 * Usage: php fix-esaya-account.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;

echo "=== FIXING ESAYA'S ACCOUNT ===\n\n";

// Find Esaya
$esaya = User::where('name', 'Esaya Nkhata')->first();

if (!$esaya) {
    echo "❌ Esaya not found!\n";
    exit(1);
}

echo "✅ Found Esaya: {$esaya->name} (ID: {$esaya->id})\n";
echo "Current phone: {$esaya->phone}\n";
echo "Current email: " . ($esaya->email ?: 'NOT SET') . "\n\n";

$fixes = [];

// Fix 1: Set email if missing
if (empty($esaya->email)) {
    $email = 'esaya@mygrownet.com';
    $esaya->email = $email;
    $fixes[] = "Set email to: $email";
    echo "🔧 Setting email to: $email\n";
}

// Fix 2: Create profile if missing
if (!$esaya->profile) {
    echo "🔧 Creating user profile...\n";
    
    try {
        $profile = new UserProfile();
        $profile->user_id = $esaya->id;
        $profile->phone_number = $esaya->phone;
        $profile->address = 'Lusaka, Zambia'; // Default address
        $profile->date_of_birth = '1990-01-01'; // Default DOB
        $profile->save();
        
        $fixes[] = "Created user profile";
        echo "✅ Profile created successfully\n";
    } catch (Exception $e) {
        echo "❌ Failed to create profile: " . $e->getMessage() . "\n";
        
        // Try alternative approach - check if profile table exists
        try {
            $profileExists = DB::select("SHOW TABLES LIKE 'user_profiles'");
            if (empty($profileExists)) {
                echo "⚠️  Profile table doesn't exist - this is OK for now\n";
            }
        } catch (Exception $e2) {
            echo "Database check failed: " . $e2->getMessage() . "\n";
        }
    }
}

// Fix 3: Ensure phone is properly formatted
if ($esaya->phone && !str_starts_with($esaya->phone, '+260')) {
    $originalPhone = $esaya->phone;
    
    // Clean and format phone number
    $phone = preg_replace('/[^0-9]/', '', $esaya->phone);
    
    if (str_starts_with($phone, '0')) {
        $phone = '+260' . substr($phone, 1);
    } elseif (str_starts_with($phone, '260')) {
        $phone = '+' . $phone;
    } elseif (strlen($phone) === 9) {
        $phone = '+260' . $phone;
    }
    
    if ($phone !== $originalPhone) {
        $esaya->phone = $phone;
        $fixes[] = "Formatted phone: $originalPhone → $phone";
        echo "🔧 Formatted phone: $originalPhone → $phone\n";
    }
}

// Save changes
if (count($fixes) > 0) {
    try {
        $esaya->save();
        echo "✅ User updated successfully\n\n";
    } catch (Exception $e) {
        echo "❌ Failed to save user: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "ℹ️  No fixes needed\n\n";
}

// Verify the fixes
echo "=== VERIFICATION ===\n";
$esaya->refresh();

echo "Final account details:\n";
echo "  Name: {$esaya->name}\n";
echo "  Email: " . ($esaya->email ?: 'NOT SET') . "\n";
echo "  Phone: {$esaya->phone}\n";
echo "  Has password: " . (!empty($esaya->password) ? 'YES' : 'NO') . "\n";

try {
    $profile = $esaya->profile;
    echo "  Has profile: " . ($profile ? 'YES' : 'NO') . "\n";
    if ($profile) {
        echo "    Profile phone: " . ($profile->phone_number ?? 'Not set') . "\n";
        echo "    Profile address: " . ($profile->address ?? 'Not set') . "\n";
    }
} catch (Exception $e) {
    echo "  Profile check: ERROR - " . $e->getMessage() . "\n";
}

// Test authentication
echo "\n=== AUTHENTICATION TEST ===\n";

try {
    $provider = \Illuminate\Support\Facades\Auth::getProvider();
    
    // Test phone login
    if ($esaya->phone) {
        $credentials = ['phone' => $esaya->phone, 'password' => 'dummy'];
        $retrieved = $provider->retrieveByCredentials($credentials);
        
        if ($retrieved && $retrieved->id === $esaya->id) {
            echo "✅ Phone login lookup: WORKING\n";
        } else {
            echo "❌ Phone login lookup: FAILED\n";
        }
    }
    
    // Test email login
    if ($esaya->email) {
        $credentials = ['email' => $esaya->email, 'password' => 'dummy'];
        $retrieved = $provider->retrieveByCredentials($credentials);
        
        if ($retrieved && $retrieved->id === $esaya->id) {
            echo "✅ Email login lookup: WORKING\n";
        } else {
            echo "❌ Email login lookup: FAILED\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Auth test error: " . $e->getMessage() . "\n";
}

// Summary
echo "\n" . str_repeat('=', 50) . "\n";
echo "🎯 FIXES APPLIED\n";
echo str_repeat('=', 50) . "\n";

if (count($fixes) > 0) {
    foreach ($fixes as $fix) {
        echo "✅ $fix\n";
    }
} else {
    echo "ℹ️  No fixes were needed\n";
}

echo "\n📝 LOGIN INSTRUCTIONS FOR ESAYA:\n";
echo str_repeat('-', 30) . "\n";
echo "1. Go to: https://mygrownet.com/login\n";
echo "2. Use phone: {$esaya->phone}\n";
if ($esaya->email) {
    echo "   OR email: {$esaya->email}\n";
}
echo "3. Password: [Ask Esaya for their password]\n";
echo "   (Password hash exists and is valid)\n";

echo "\n⚠️  IF LOGIN STILL FAILS:\n";
echo "1. Check if Esaya remembers their password\n";
echo "2. Use password reset functionality\n";
echo "3. Check Laravel logs for specific errors\n";
echo "4. Verify web server is running properly\n";

echo "\n" . str_repeat('=', 50) . "\n";
echo "Fix completed - " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('=', 50) . "\n";
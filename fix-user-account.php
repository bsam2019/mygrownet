<?php

/**
 * Quick User Account Fix Script
 * 
 * Usage: php fix-user-account.php
 * 
 * This script can be run directly on the server to fix a user account
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== User Account Fix Tool ===\n\n";

// Get user email
echo "Enter user email: ";
$email = trim(fgets(STDIN));

// Find user
$user = User::where('email', $email)->first();

if (!$user) {
    echo "ERROR: User not found with email: {$email}\n";
    exit(1);
}

echo "\nUser found:\n";
echo "  ID: {$user->id}\n";
echo "  Name: {$user->name}\n";
echo "  Email: {$user->email}\n";
echo "  Phone: " . ($user->phone ?? 'NULL') . "\n";
echo "  Status: " . ($user->status ?? 'NULL') . "\n\n";

// Ask what to fix
echo "What would you like to do?\n";
echo "1. Reset password\n";
echo "2. Update phone number\n";
echo "3. Both\n";
echo "4. Exit\n";
echo "Choice: ";
$choice = trim(fgets(STDIN));

if ($choice == '1' || $choice == '3') {
    echo "\nEnter new password: ";
    system('stty -echo');
    $password = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
    
    echo "Confirm password: ";
    system('stty -echo');
    $confirmPassword = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
    
    if ($password !== $confirmPassword) {
        echo "ERROR: Passwords do not match!\n";
        exit(1);
    }
    
    try {
        $user->password = Hash::make($password);
        $user->save();
        echo "✓ Password updated successfully!\n";
    } catch (\Exception $e) {
        echo "ERROR: Failed to update password: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if ($choice == '2' || $choice == '3') {
    echo "\nEnter new phone number: ";
    $phone = trim(fgets(STDIN));
    
    try {
        $user->phone = $phone;
        $user->save();
        echo "✓ Phone number updated successfully!\n";
    } catch (\Exception $e) {
        echo "ERROR: Failed to update phone: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if ($choice == '4') {
    echo "Exiting...\n";
    exit(0);
}

echo "\n✓ User account updated successfully!\n";
echo "User can now login with the new credentials.\n";

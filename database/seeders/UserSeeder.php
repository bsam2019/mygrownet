<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        // Note: DB enum has 'admin', 'member', 'investor' but AccountType enum doesn't have 'admin'
        $admin = User::firstOrCreate(
            ['email' => 'admin@mygrownet.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@mygrownet.com',
                'email_verified_at' => now(),
                'password' => Hash::make('mygrownet@2025!'),
                'remember_token' => Str::random(10),
                'created_at' => now()->subYear(),
                'updated_at' => now()->subYear(),
                'referral_code' => 'ADMIN' . strtoupper(Str::random(5)),
                'account_type' => 'member', // Use 'member' since 'admin' not in AccountType enum
                'account_types' => ['member'], // Pass as array, not JSON string
            ]
        );

        // Assign admin role
        if (\Spatie\Permission\Models\Role::where('name', 'Administrator')->exists()) {
            $admin->assignRole('Administrator');
        }

        // Create one investment manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@mygrownet.com'],
            [
                'name' => 'Investment Manager',
                'email' => 'manager@mygrownet.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'created_at' => now()->subMonths(11),
                'updated_at' => now()->subMonths(11),
                'referral_code' => 'MGR' . strtoupper(Str::random(5)),
                'account_type' => 'member',
                'account_types' => ['member'], // Pass as array, not JSON string
            ]
        );

        // Assign manager role
        if (\Spatie\Permission\Models\Role::where('name', 'manager')->exists()) {
            $manager->assignRole('manager');
        }

        // Create support agents
        for ($i = 1; $i <= 2; $i++) {
            $support = User::firstOrCreate(
                ['email' => "support$i@mygrownet.com"],
                [
                    'name' => "Support Agent $i",
                    'email' => "support$i@mygrownet.com",
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    'created_at' => now()->subMonths(10 + $i),
                    'updated_at' => now()->subMonths(10 + $i),
                    'referral_code' => 'SUP' . strtoupper(Str::random(5)),
                    'account_type' => 'member',
                    'account_types' => ['member'], // Pass as array, not JSON string
                ]
            );

            // Assign support role (if exists)
            if (\Spatie\Permission\Models\Role::where('name', 'support')->exists()) {
                $support->assignRole('support');
            }
        }

        // Create a test member user for testing
        $testMember = User::firstOrCreate(
            ['email' => 'member@mygrownet.com'],
            [
                'name' => 'Test Member',
                'email' => 'member@mygrownet.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subMonths(6),
                'referral_code' => 'MEM' . strtoupper(Str::random(5)),
                'account_type' => 'member',
                'account_types' => ['member'], // Pass as array, not JSON string
            ]
        );

        // Assign member role
        if (\Spatie\Permission\Models\Role::where('name', 'Member')->exists()) {
            $testMember->assignRole('Member');
        }

        // Note: Regular users will register through the platform
        // and will be automatically assigned the Member role
    }
}

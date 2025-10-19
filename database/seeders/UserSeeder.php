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
        $admin = User::firstOrCreate(
            ['email' => 'admin@mygrownet.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@mygrownet.com',
                'email_verified_at' => now(),
                'password' => Hash::make('mygrownet@2025!'),
                'remember_token' => Str::random(10),
                'created_at' => now()->subYear(),
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
                ]
            );

            // Assign support role (if exists)
            if (\Spatie\Permission\Models\Role::where('name', 'support')->exists()) {
                $support->assignRole('support');
            }
        }

        // Create regular users spread across 12 months (reduced for memory efficiency)
        $startDate = now()->subMonths(12);

        // Cache the investor role to avoid repeated queries
        $investorRole = \Spatie\Permission\Models\Role::where('name', 'investor')->first();

        foreach (range(0, 11) as $month) {
            $date = $startDate->copy()->addMonths($month);

            // Reduced user count to prevent memory issues (was 8-12, now 3-5)
            $usersCount = rand(3, 5);

            for ($i = 0; $i < $usersCount; $i++) {
                $createdAt = $date->copy()->addDays(rand(0, 30));

                $user = User::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'email_verified_at' => $createdAt,
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Assign investor role if it exists
                if ($investorRole) {
                    $user->assignRole($investorRole);
                }
            }
        }
    }
}

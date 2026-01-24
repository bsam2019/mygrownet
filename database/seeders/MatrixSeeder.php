<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MatrixPosition;
use App\Services\MatrixService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $matrixService = app(MatrixService::class);

        // Create a root sponsor user if doesn't exist
        $rootSponsor = User::firstOrCreate(
            ['email' => 'sponsor@mygrownet.com'],
            [
                'name' => 'Root Sponsor',
                'email' => 'sponsor@mygrownet.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'referral_code' => 'ROOT001',
                'account_type' => 'member',
                'account_types' => ['member'],
            ]
        );

        // Assign investor role
        if (\Spatie\Permission\Models\Role::where('name', 'Investor')->exists()) {
            $rootSponsor->assignRole('Investor');
        }

        // Place root sponsor in matrix
        if (!$rootSponsor->getMatrixPosition()) {
            $matrixService->placeUserInMatrix($rootSponsor);
            $this->command->info("✓ Placed root sponsor in matrix");
        }

        // Create Level 1 users (3 direct referrals)
        $level1Users = [];
        for ($i = 1; $i <= 3; $i++) {
            $user = User::firstOrCreate(
                ['email' => "level1-user{$i}@mygrownet.com"],
                [
                    'name' => "Level 1 User {$i}",
                    'email' => "level1-user{$i}@mygrownet.com",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'referrer_id' => $rootSponsor->id,
                    'referral_code' => "L1U{$i}",
                    'current_professional_level' => 'associate',
                    'account_type' => 'member',
                    'account_types' => ['member'],
                ]
            );

            if (\Spatie\Permission\Models\Role::where('name', 'Investor')->exists()) {
                $user->assignRole('Investor');
            }

            if (!$user->getMatrixPosition()) {
                $matrixService->placeUserInMatrix($user, $rootSponsor);
                $this->command->info("✓ Placed Level 1 User {$i}");
            }

            $level1Users[] = $user;
        }

        // Create Level 2 users (9 total - 3 under each Level 1 user)
        $level2Users = [];
        foreach ($level1Users as $index => $sponsor) {
            for ($i = 1; $i <= 3; $i++) {
                $userNum = ($index * 3) + $i;
                $user = User::firstOrCreate(
                    ['email' => "level2-user{$userNum}@mygrownet.com"],
                    [
                        'name' => "Level 2 User {$userNum}",
                        'email' => "level2-user{$userNum}@mygrownet.com",
                        'password' => Hash::make('password'),
                        'email_verified_at' => now(),
                        'referrer_id' => $sponsor->id,
                        'referral_code' => "L2U{$userNum}",
                        'current_professional_level' => 'professional',
                        'account_type' => 'member',
                        'account_types' => ['member'],
                    ]
                );

                if (\Spatie\Permission\Models\Role::where('name', 'Investor')->exists()) {
                    $user->assignRole('Investor');
                }

                if (!$user->getMatrixPosition()) {
                    $matrixService->placeUserInMatrix($user, $sponsor);
                    $this->command->info("✓ Placed Level 2 User {$userNum}");
                }

                $level2Users[] = $user;
            }
        }

        // Create some Level 3 users (partial - 15 out of 27 possible)
        foreach (array_slice($level2Users, 0, 5) as $index => $sponsor) {
            for ($i = 1; $i <= 3; $i++) {
                $userNum = ($index * 3) + $i;
                $user = User::firstOrCreate(
                    ['email' => "level3-user{$userNum}@mygrownet.com"],
                    [
                        'name' => "Level 3 User {$userNum}",
                        'email' => "level3-user{$userNum}@mygrownet.com",
                        'password' => Hash::make('password'),
                        'email_verified_at' => now(),
                        'referrer_id' => $sponsor->id,
                        'referral_code' => "L3U{$userNum}",
                        'current_professional_level' => 'senior',
                        'account_type' => 'member',
                        'account_types' => ['member'],
                    ]
                );

                if (\Spatie\Permission\Models\Role::where('name', 'Investor')->exists()) {
                    $user->assignRole('Investor');
                }

                if (!$user->getMatrixPosition()) {
                    $matrixService->placeUserInMatrix($user, $sponsor);
                    $this->command->info("✓ Placed Level 3 User {$userNum}");
                }
            }
        }

        // Create a few users for spillover queue (without matrix positions)
        for ($i = 1; $i <= 3; $i++) {
            User::firstOrCreate(
                ['email' => "pending-user{$i}@mygrownet.com"],
                [
                    'name' => "Pending User {$i}",
                    'email' => "pending-user{$i}@mygrownet.com",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'referrer_id' => $rootSponsor->id,
                    'referral_code' => "PEND{$i}",
                    'current_professional_level' => 'associate',
                    'account_type' => 'member',
                    'account_types' => ['member'],
                ]
            );
            $this->command->info("✓ Created Pending User {$i} (for spillover queue)");
        }

        // Update professional level names in matrix positions
        MatrixPosition::whereNull('professional_level_name')->get()->each(function ($position) {
            $position->update([
                'professional_level_name' => MatrixPosition::LEVEL_NAMES[$position->level] ?? 'Unknown'
            ]);
        });

        $this->command->info("\n=== Matrix Seeding Complete ===");
        $this->command->info("Total matrix positions: " . MatrixPosition::count());
        $this->command->info("Users in spillover queue: 3");
        $this->command->info("\nTest Accounts:");
        $this->command->info("  Root Sponsor: sponsor@mygrownet.com / password");
        $this->command->info("  Level 1 Users: level1-user1@mygrownet.com / password (and user2, user3)");
        $this->command->info("  Level 2 Users: level2-user1@mygrownet.com / password (through user9)");
        $this->command->info("  Level 3 Users: level3-user1@mygrownet.com / password (through user15)");
        $this->command->info("  Pending Users: pending-user1@mygrownet.com / password (in spillover queue)");
    }
}

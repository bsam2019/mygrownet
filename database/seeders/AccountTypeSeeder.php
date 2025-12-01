<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\AccountType;
use Illuminate\Support\Facades\DB;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Migrates existing users from single account_type to account_types array
     */
    public function run(): void
    {
        $this->command->info('Migrating users to account_types array...');

        // Update existing users based on their current account_type
        $updated = 0;
        User::whereNotNull('account_type')->chunk(100, function ($users) use (&$updated) {
            foreach ($users as $user) {
                // Convert single account_type to array if not already done
                if (empty($user->account_types)) {
                    $user->account_types = [$user->account_type->value];
                    $user->save();
                    $updated++;
                }
            }
        });

        $this->command->info("Updated {$updated} users with existing account_type");

        // Set default account type for users without one
        $defaulted = 0;
        User::whereNull('account_type')
            ->where(function($query) {
                $query->whereNull('account_types')
                      ->orWhereRaw('JSON_LENGTH(account_types) = 0');
            })
            ->chunk(100, function ($users) use (&$defaulted) {
                foreach ($users as $user) {
                    // Users with referrer = MEMBER, without = CLIENT
                    $type = $user->referrer_id ? AccountType::MEMBER : AccountType::CLIENT;
                    $user->account_types = [$type->value];
                    $user->account_type = $type; // Also set single for backward compatibility
                    $user->save();
                    $defaulted++;
                }
            });

        $this->command->info("Set default account type for {$defaulted} users");
        $this->command->info('Account type migration complete!');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Artisan command to set a user's subscription tier for testing
 * 
 * Usage:
 *   php artisan user:set-tier {email} {module} {tier}
 * 
 * Examples:
 *   php artisan user:set-tier admin@example.com growbuilder free
 *   php artisan user:set-tier admin@example.com growbuilder starter
 *   php artisan user:set-tier admin@example.com growbuilder business
 *   php artisan user:set-tier admin@example.com growbuilder agency
 */
class SetUserTier extends Command
{
    protected $signature = 'user:set-tier 
                            {email : User email address}
                            {module : Module ID (e.g., growbuilder, growbiz, growfinance)}
                            {tier : Tier key (e.g., free, starter, business, agency)}';

    protected $description = 'Set a user\'s subscription tier for testing purposes';

    public function handle(): int
    {
        $email = $this->argument('email');
        $moduleId = $this->argument('module');
        $tierKey = $this->argument('tier');

        // Find user
        $user = DB::table('users')->where('email', $email)->first();
        if (!$user) {
            $this->error("User not found: {$email}");
            return 1;
        }

        // Validate tier exists
        $tier = DB::table('module_tiers')
            ->where('module_id', $moduleId)
            ->where('tier_key', $tierKey)
            ->where('is_active', true)
            ->first();

        if (!$tier) {
            $this->error("Tier not found: {$tierKey} for module {$moduleId}");
            $this->info("Available tiers:");
            $tiers = DB::table('module_tiers')
                ->where('module_id', $moduleId)
                ->where('is_active', true)
                ->pluck('tier_key');
            foreach ($tiers as $t) {
                $this->line("  - {$t}");
            }
            return 1;
        }

        // Update or create subscription
        DB::table('module_subscriptions')->updateOrInsert(
            [
                'user_id' => $user->id,
                'module_id' => $moduleId,
            ],
            [
                'subscription_tier' => $tierKey,
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'price_paid' => $tier->price_monthly ?? 0,
                'expires_at' => now()->addYear(), // Set far future expiry for testing
                'updated_at' => now(),
            ]
        );

        // Clear caches
        Cache::forget("user_subscription:{$user->id}:{$moduleId}");
        Cache::forget("module_tiers:{$moduleId}");
        Cache::forget("growbuilder_restrictions:{$user->id}:{$tierKey}");
        
        // Clear AI usage cache for current month
        $monthYear = now()->format('Y-m');
        Cache::forget("ai_usage:{$user->id}:{$monthYear}");

        $this->info("✓ Set {$user->name} ({$email}) to {$tierKey} tier for {$moduleId}");
        $this->table(
            ['Property', 'Value'],
            [
                ['User', $user->name],
                ['Email', $email],
                ['Module', $moduleId],
                ['Tier', $tierKey],
                ['Price', 'K' . number_format($tier->price_monthly ?? 0)],
                ['Expires', now()->addYear()->format('Y-m-d')],
            ]
        );

        return 0;
    }
}

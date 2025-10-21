<?php

namespace App\Services;

use App\Models\User;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Events\Points\UserRegistered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StarterKitService
{
    /**
     * Process starter kit for new member
     */
    public function processStarterKit(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Get the starter kit package
            $starterKit = Package::where('slug', 'starter-kit-associate')->first();
            
            if (!$starterKit) {
                Log::warning('Starter kit package not found for user: ' . $user->id);
                return;
            }

            // Create subscription for starter kit
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'package_id' => $starterKit->id,
                'amount' => $starterKit->price,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addMonths($starterKit->duration_months),
                'renewal_date' => now()->addMonths($starterKit->duration_months),
                'auto_renew' => false, // Starter kit is one-time
            ]);

            // Record transaction
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'subscription',
                'amount' => $starterKit->price,
                'status' => 'completed',
                'description' => 'Welcome Package - Starter Kit (Associate)',
                'notes' => 'Automatic starter kit assignment for new member',
                'processed_at' => now(),
            ]);

            // Fire event for points system (100 LP on registration)
            event(new UserRegistered($user));

            Log::info('Starter kit processed for user: ' . $user->id);
        });
    }

    /**
     * Check if user has received starter kit
     */
    public function hasStarterKit(User $user): bool
    {
        return Subscription::where('user_id', $user->id)
            ->whereHas('package', function ($query) {
                $query->where('slug', 'starter-kit-associate');
            })
            ->exists();
    }

    /**
     * Get starter kit details
     */
    public function getStarterKitDetails(): ?Package
    {
        return Package::where('slug', 'starter-kit-associate')->first();
    }
}

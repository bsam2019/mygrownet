<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DefaultSponsorService
{
    /**
     * Get the default sponsor for users without a referral code
     * This is typically the admin/company account
     */
    public function getDefaultSponsor(): ?User
    {
        // Check if default sponsor feature is enabled
        if (!config('referral.use_default_sponsor', true)) {
            return null;
        }

        return Cache::remember('default_sponsor', 3600, function () {
            // Try to find the configured default sponsor by email
            $defaultEmail = config('referral.default_sponsor_email', 'admin@mygrownet.com');
            $admin = User::where('email', $defaultEmail)->first();
            
            if ($admin) {
                return $admin;
            }
            
            // Fallback: Find the first user with Administrator role
            $admin = User::role('Administrator')->first();
            
            if ($admin) {
                return $admin;
            }
            
            // Fallback: Use first registered user if configured
            if (config('referral.use_first_user_as_default', false)) {
                return User::orderBy('created_at', 'asc')->first();
            }
            
            return null;
        });
    }
    
    /**
     * Check if default sponsor exists
     */
    public function hasDefaultSponsor(): bool
    {
        return $this->getDefaultSponsor() !== null;
    }
    
    /**
     * Get default sponsor's referral code
     */
    public function getDefaultReferralCode(): ?string
    {
        $sponsor = $this->getDefaultSponsor();
        return $sponsor?->referral_code;
    }
    
    /**
     * Clear the cached default sponsor
     */
    public function clearCache(): void
    {
        Cache::forget('default_sponsor');
    }
}

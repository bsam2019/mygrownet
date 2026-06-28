<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateReferralCounts extends Command
{
    protected $signature = 'referrals:update-counts';
    protected $description = 'Update referral_count field for all users based on actual referrals';

    public function handle()
    {
        $this->info('Updating referral counts for all users...');
        
        $users = User::all();
        $updated = 0;
        
        foreach ($users as $user) {
            $actualCount = User::where('referrer_id', $user->id)->count();
            
            if ($user->referral_count != $actualCount) {
                $user->referral_count = $actualCount;
                $user->save();
                $updated++;
                
                $this->line("Updated {$user->name}: {$actualCount} referrals");
            }
        }
        
        $this->info("✅ Updated {$updated} users");
        
        return 0;
    }
}

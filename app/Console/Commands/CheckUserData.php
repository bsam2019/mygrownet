<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckUserData extends Command
{
    protected $signature = 'user:check {email?}';
    protected $description = 'Check user data for debugging';

    public function handle()
    {
        $email = $this->argument('email');
        
        // If no email provided, list all users
        if (!$email) {
            $this->info("All users in database:");
            $users = User::select('id', 'name', 'email', 'phone', 'created_at')->orderBy('created_at', 'desc')->get();
            foreach ($users as $user) {
                $this->line("{$user->id}. {$user->name} - {$user->email} (Created: {$user->created_at->format('Y-m-d H:i')})");
            }
            $this->newLine();
            $this->info("Run: php artisan user:check <email> to see details");
            return 0;
        }
        
        $user = User::where('email', $email)->orWhere('phone', $email)->with('points', 'directReferrals')->first();
        
        if (!$user) {
            $this->error("User not found: $email");
            return 1;
        }
        
        $this->info("User: {$user->name} (ID: {$user->id})");
        $this->info("Email: {$user->email}");
        $this->info("Phone: {$user->phone}");
        $this->info("Referral Code: {$user->referral_code}");
        $this->info("Professional Level: {$user->current_professional_level}");
        $this->info("Created: {$user->created_at}");
        $this->newLine();
        
        if ($user->points) {
            $this->info("Points Record EXISTS");
            $this->info("  Lifetime Points: {$user->points->lifetime_points}");
            $this->info("  Monthly Points: {$user->points->monthly_points}");
        } else {
            $this->warn("No points record found");
        }
        $this->newLine();
        
        $directCount = $user->directReferrals()->count();
        $this->info("Direct Referrals: $directCount");
        
        if ($directCount > 0) {
            $this->info("Referrals:");
            foreach ($user->directReferrals as $referral) {
                $this->line("  - {$referral->name} ({$referral->email})");
            }
        }
        
        return 0;
    }
}

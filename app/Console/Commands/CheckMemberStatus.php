<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckMemberStatus extends Command
{
    protected $signature = 'check:member-status {userName?}';
    protected $description = 'Check member payment status and active subscription status';

    public function handle()
    {
        $userName = $this->argument('userName');
        
        if ($userName) {
            // Check specific user
            $user = User::where('name', 'like', "%{$userName}%")->first();
            
            if (!$user) {
                $this->error("User '{$userName}' not found!");
                return 1;
            }
            
            $this->checkUser($user);
        } else {
            // Check all users with referrals (team members)
            $this->info("Checking users with team members...\n");
            
            $usersWithReferrals = User::whereHas('referrals')->get();
            
            foreach ($usersWithReferrals as $user) {
                $this->info("=== USER: {$user->name} (ID: {$user->id}) ===");
                $this->info("Team Members:");
                
                foreach ($user->referrals as $member) {
                    $this->checkUser($member, true);
                    $this->line("");
                }
                
                $this->line("---\n");
            }
        }
        
        return 0;
    }
    
    private function checkUser(User $user, $isTeamMember = false)
    {
        $prefix = $isTeamMember ? "  → " : "";
        
        $this->line("{$prefix}Name: {$user->name}");
        $this->line("{$prefix}Email: {$user->email}");
        $this->line("{$prefix}ID: {$user->id}");
        
        // Check subscription status fields
        $this->line("{$prefix}Subscription Status: " . ($user->subscription_status ?? 'NULL'));
        $this->line("{$prefix}Subscription End Date: " . ($user->subscription_end_date ?? 'NULL'));
        
        // Check member payments
        $payments = $user->memberPayments;
        $this->line("{$prefix}Total Payments: " . $payments->count());
        
        if ($payments->count() > 0) {
            foreach ($payments as $payment) {
                $this->line("{$prefix}  Payment #{$payment->id}:");
                $this->line("{$prefix}    Amount: K{$payment->amount}");
                $this->line("{$prefix}    Type: {$payment->payment_type}");
                $this->line("{$prefix}    Status: {$payment->status}");
                $this->line("{$prefix}    Method: {$payment->payment_method}");
                $this->line("{$prefix}    Verified At: " . ($payment->verified_at ?? 'NULL'));
                $this->line("{$prefix}    Created At: {$payment->created_at}");
            }
        }
        
        // Check verified subscription payments
        $verifiedSubscriptions = $user->memberPayments()
            ->where('status', 'verified')
            ->where('payment_type', 'subscription')
            ->count();
            
        $this->line("{$prefix}Verified Subscription Payments: {$verifiedSubscriptions}");
        
        // Check hasActiveSubscription result
        $isActive = $user->hasActiveSubscription();
        $statusColor = $isActive ? 'info' : 'error';
        $this->{$statusColor}("{$prefix}Active Status: " . ($isActive ? 'ACTIVE ✓' : 'INACTIVE ✗'));
    }
}

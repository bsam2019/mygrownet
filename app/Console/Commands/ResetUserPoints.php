<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetUserPoints extends Command
{
    protected $signature = 'user:reset-points {email_or_phone}';
    protected $description = 'Reset user points to 0 (for cleaning up test data)';

    public function handle()
    {
        $identifier = $this->argument('email_or_phone');
        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->with('points')
            ->first();
        
        if (!$user) {
            $this->error("User not found: $identifier");
            return 1;
        }
        
        $this->info("User: {$user->name} (ID: {$user->id})");
        
        if (!$user->points) {
            $this->warn("No points record found. Creating one with 0 points...");
            $user->points()->create([
                'lifetime_points' => 0,
                'monthly_points' => 0,
            ]);
            $this->info("Points record created with 0 points");
            return 0;
        }
        
        $oldLP = $user->points->lifetime_points;
        $oldMAP = $user->points->monthly_points;
        
        if ($oldLP == 0 && $oldMAP == 0) {
            $this->info("Points are already 0. Nothing to reset.");
            return 0;
        }
        
        if (!$this->confirm("Reset points from LP:{$oldLP} MAP:{$oldMAP} to LP:0 MAP:0?")) {
            $this->info("Cancelled");
            return 0;
        }
        
        $user->points->update([
            'lifetime_points' => 0,
            'monthly_points' => 0,
            'last_month_points' => 0,
            'three_month_average' => 0,
            'current_streak_months' => 0,
        ]);
        
        $this->info("✓ Points reset to 0");
        $this->info("  Lifetime Points: {$oldLP} → 0");
        $this->info("  Monthly Points: {$oldMAP} → 0");
        
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\MLMCommissionService;

class TestCommissionProcessing extends Command
{
    protected $signature = 'test:commissions {user_id}';
    protected $description = 'Test commission processing for a user';

    public function handle(MLMCommissionService $mlmService)
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User not found!");
            return 1;
        }
        
        $this->info("Testing commission processing for: {$user->name} (ID: {$user->id})");
        $this->info("Referrer ID: " . ($user->referrer_id ?? 'None'));
        $this->info("Status: {$user->status}");
        
        try {
            $commissions = $mlmService->processMLMCommissions(
                $user,
                500.00,
                'registration'
            );
            
            $this->info("Commissions created: " . count($commissions));
            
            foreach ($commissions as $commission) {
                $this->line("  - Level {$commission->level}: K{$commission->amount} to User #{$commission->referrer_id} (Status: {$commission->status})");
            }
            
            $this->info("✅ Commission processing completed!");
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}

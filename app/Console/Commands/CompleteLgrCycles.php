<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrCycleModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CompleteLgrCycles extends Command
{
    protected $signature = 'lgr:complete-cycles';
    protected $description = 'Complete LGR cycles that have reached their end date';

    public function handle(): int
    {
        $this->info('Checking for LGR cycles to complete...');
        
        $today = Carbon::today();
        $completedCount = 0;
        
        // Find active cycles that have passed their end date
        $expiredCycles = LgrCycleModel::where('status', 'active')
            ->where('end_date', '<', $today)
            ->get();
        
        $this->info("Found {$expiredCycles->count()} cycles to complete");
        
        foreach ($expiredCycles as $cycle) {
            try {
                $cycle->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                
                $completedCount++;
                
                $this->line("  ✓ Completed cycle #{$cycle->id} for user #{$cycle->user_id}");
                $this->line("    - Active days: {$cycle->active_days}/70");
                $this->line("    - Total earned: K{$cycle->total_earned_lgc}");
                
                // TODO: Send completion notification to user
                // event(new LgrCycleCompleted($cycle));
                
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to complete cycle #{$cycle->id}: " . $e->getMessage());
                Log::error('LGR cycle completion failed', [
                    'cycle_id' => $cycle->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->info("\nCycles completed: {$completedCount}");
        
        Log::info('LGR cycles completed', [
            'date' => $today->toDateString(),
            'cycles_completed' => $completedCount,
        ]);
        
        return Command::SUCCESS;
    }
}


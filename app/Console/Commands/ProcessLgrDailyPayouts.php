<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrCycleModel;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrActivityModel;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrPoolModel;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrPayoutModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessLgrDailyPayouts extends Command
{
    protected $signature = 'lgr:process-daily-payouts';
    protected $description = 'Process daily LGR payouts for active cycles';

    public function handle(): int
    {
        $this->info('Processing LGR daily payouts...');
        
        $today = Carbon::today();
        $processedCount = 0;
        $totalPaid = 0;
        
        // Get all active cycles
        $activeCycles = LgrCycleModel::where('status', 'active')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();
        
        $this->info("Found {$activeCycles->count()} active cycles");
        
        // Get or create today's pool
        $pool = LgrPoolModel::firstOrCreate(
            ['pool_date' => $today],
            [
                'opening_balance' => 0,
                'contributions' => 0,
                'allocations' => 0,
                'closing_balance' => 0,
                'reserve_amount' => 0,
                'available_for_distribution' => 0,
            ]
        );
        
        foreach ($activeCycles as $cycle) {
            // Check if user has activity today
            $hasActivity = LgrActivityModel::where('lgr_cycle_id', $cycle->id)
                ->whereDate('activity_date', $today)
                ->where('verified', true)
                ->exists();
            
            if (!$hasActivity) {
                $this->line("  Skipping cycle #{$cycle->id} (user #{$cycle->user_id}) - no activity today");
                continue;
            }
            
            // Check if already paid today
            $alreadyPaid = LgrPayoutModel::where('lgr_cycle_id', $cycle->id)
                ->whereDate('payout_date', $today)
                ->exists();
            
            if ($alreadyPaid) {
                $this->line("  Skipping cycle #{$cycle->id} - already paid today");
                continue;
            }
            
            // Process payout
            try {
                DB::transaction(function () use ($cycle, $pool, $today, &$processedCount, &$totalPaid) {
                    $dailyRate = $cycle->daily_rate;
                    
                    // Create payout record
                    $payout = LgrPayoutModel::create([
                        'user_id' => $cycle->user_id,
                        'lgr_cycle_id' => $cycle->id,
                        'lgr_pool_id' => $pool->id,
                        'payout_date' => $today,
                        'lgc_amount' => $dailyRate,
                        'pool_balance_before' => $pool->available_for_distribution,
                        'pool_balance_after' => $pool->available_for_distribution - $dailyRate,
                        'proportional_adjustment' => false,
                        'status' => 'credited',
                    ]);
                    
                    // Update cycle stats
                    $cycle->increment('active_days');
                    $cycle->increment('total_earned_lgc', $dailyRate);
                    
                    // Credit user's loyalty points (LGC)
                    $user = User::find($cycle->user_id);
                    $user->increment('loyalty_points', $dailyRate);
                    
                    // Update pool
                    $pool->decrement('available_for_distribution', $dailyRate);
                    $pool->increment('allocations', $dailyRate);
                    
                    $processedCount++;
                    $totalPaid += $dailyRate;
                    
                    $this->line("  ✓ Paid K{$dailyRate} to user #{$cycle->user_id} (cycle #{$cycle->id})");
                });
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to process cycle #{$cycle->id}: " . $e->getMessage());
                Log::error('LGR daily payout failed', [
                    'cycle_id' => $cycle->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->info("\nDaily payouts processed:");
        $this->info("  Cycles processed: {$processedCount}");
        $this->info("  Total paid: K{$totalPaid}");
        
        Log::info('LGR daily payouts processed', [
            'date' => $today->toDateString(),
            'cycles_processed' => $processedCount,
            'total_paid' => $totalPaid,
        ]);
        
        return Command::SUCCESS;
    }
}


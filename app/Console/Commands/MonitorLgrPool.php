<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrPoolModel;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrSettingsModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorLgrPool extends Command
{
    protected $signature = 'lgr:monitor-pool';
    protected $description = 'Monitor LGR pool balance and send alerts if low';

    public function handle(): int
    {
        $this->info('Monitoring LGR pool balance...');
        
        $today = Carbon::today();
        
        // Get today's pool
        $pool = LgrPoolModel::where('pool_date', $today)->first();
        
        if (!$pool) {
            $this->warn('No pool found for today');
            return Command::SUCCESS;
        }
        
        // Get reserve requirement (default 30%)
        $reservePercentage = LgrSettingsModel::where('key', 'reserve_percentage')
            ->value('value') ?? 30;
        
        $totalBalance = $pool->opening_balance + $pool->contributions;
        $requiredReserve = $totalBalance * ($reservePercentage / 100);
        $currentAvailable = $pool->available_for_distribution;
        
        $this->info("Pool Status:");
        $this->info("  Total balance: K{$totalBalance}");
        $this->info("  Required reserve ({$reservePercentage}%): K{$requiredReserve}");
        $this->info("  Available for distribution: K{$currentAvailable}");
        $this->info("  Already allocated: K{$pool->allocations}");
        
        // Check if below reserve
        if ($currentAvailable < $requiredReserve) {
            $deficit = $requiredReserve - $currentAvailable;
            $this->warn("⚠️  Pool is below reserve requirement by K{$deficit}");
            
            Log::warning('LGR pool below reserve', [
                'date' => $today->toDateString(),
                'total_balance' => $totalBalance,
                'required_reserve' => $requiredReserve,
                'current_available' => $currentAvailable,
                'deficit' => $deficit,
            ]);
            
            // TODO: Send alert to admin
            // event(new LgrPoolLowBalance($pool, $deficit));
        } else {
            $this->info("✓ Pool balance is healthy");
        }
        
        // Check if pool is depleted
        if ($currentAvailable <= 0) {
            $this->error("❌ Pool is depleted! No funds available for distribution.");
            
            Log::critical('LGR pool depleted', [
                'date' => $today->toDateString(),
                'pool_id' => $pool->id,
            ]);
            
            // TODO: Send critical alert to admin
            // event(new LgrPoolDepleted($pool));
        }
        
        return Command::SUCCESS;
    }
}


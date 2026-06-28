<?php

namespace App\Console\Commands;

use App\Domain\Marketplace\Services\SellerTierService;
use App\Models\MarketplaceSeller;
use Illuminate\Console\Command;

class RecalculateSellerTiers extends Command
{
    protected $signature = 'marketplace:recalculate-tiers {--seller= : Specific seller ID to recalculate}';
    protected $description = 'Recalculate seller tiers based on performance metrics';

    public function handle(SellerTierService $tierService): int
    {
        $sellerId = $this->option('seller');

        if ($sellerId) {
            $seller = MarketplaceSeller::find($sellerId);
            if (!$seller) {
                $this->error("Seller with ID {$sellerId} not found.");
                return 1;
            }
            
            $this->info("Recalculating tier for seller: {$seller->business_name}");
            $oldTier = $seller->trust_level;
            $tierService->updateSellerMetrics($seller);
            $seller->refresh();
            
            $this->info("  Old tier: {$oldTier} -> New tier: {$seller->trust_level}");
            $this->info("  Commission rate: {$seller->effective_commission_rate}%");
            
            return 0;
        }

        $sellers = MarketplaceSeller::where('kyc_status', 'approved')->get();
        $this->info("Recalculating tiers for {$sellers->count()} sellers...");

        $bar = $this->output->createProgressBar($sellers->count());
        $bar->start();

        $upgrades = 0;
        $downgrades = 0;

        foreach ($sellers as $seller) {
            $oldTier = $seller->trust_level;
            $tierService->updateSellerMetrics($seller);
            $seller->refresh();
            
            if ($seller->trust_level !== $oldTier) {
                $tierOrder = ['new' => 0, 'verified' => 1, 'trusted' => 2, 'top' => 3];
                if (($tierOrder[$seller->trust_level] ?? 0) > ($tierOrder[$oldTier] ?? 0)) {
                    $upgrades++;
                } else {
                    $downgrades++;
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Tier recalculation complete!");
        $this->info("  Upgrades: {$upgrades}");
        $this->info("  Downgrades: {$downgrades}");

        return 0;
    }
}

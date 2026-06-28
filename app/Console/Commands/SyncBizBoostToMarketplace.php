<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Services\BizBoostMarketplaceSyncService;
use Illuminate\Console\Command;

class SyncBizBoostToMarketplace extends Command
{
    protected $signature = 'marketplace:sync-bizboost 
                            {--business-id= : Sync specific business by ID}
                            {--all : Sync all eligible businesses}
                            {--force : Force sync even if already synced}';

    protected $description = 'Sync BizBoost businesses and products to GrowNet Market';

    public function handle(BizBoostMarketplaceSyncService $syncService): int
    {
        $this->info('🔄 Starting BizBoost → GrowNet Market sync...');
        $this->newLine();

        // Sync specific business
        if ($businessId = $this->option('business-id')) {
            return $this->syncBusiness($businessId, $syncService);
        }

        // Sync all businesses
        if ($this->option('all')) {
            return $this->syncAllBusinesses($syncService);
        }

        $this->error('Please specify --business-id=X or --all');
        return self::FAILURE;
    }

    private function syncBusiness(int $businessId, BizBoostMarketplaceSyncService $syncService): int
    {
        $business = BizBoostBusinessModel::find($businessId);

        if (!$business) {
            $this->error("Business #{$businessId} not found");
            return self::FAILURE;
        }

        $this->info("Syncing: {$business->name}");

        // Check if should sync
        if (!$syncService->shouldSync($business) && !$this->option('force')) {
            $this->warn('Business does not meet sync criteria:');
            $this->line("  - Active: " . ($business->is_active ? '✓' : '✗'));
            $this->line("  - Sync Enabled: " . ($business->marketplace_sync_enabled ? '✓' : '✗'));
            $this->line("  - Has Products: " . ($business->products()->where('is_active', true)->count() > 0 ? '✓' : '✗'));
            $this->newLine();
            $this->info('Use --force to sync anyway');
            return self::FAILURE;
        }

        // Create seller
        $seller = $syncService->getOrCreateSeller($business);
        $this->info("✓ Seller created/updated: #{$seller->id}");

        // Sync products
        $result = $syncService->syncProducts($business);
        
        $this->info("✓ Products synced: {$result['synced']}/{$result['total']}");

        if (!empty($result['errors'])) {
            $this->warn("⚠ Errors: " . count($result['errors']));
            foreach ($result['errors'] as $error) {
                $this->line("  - {$error['product_name']}: {$error['error']}");
            }
        }

        $this->newLine();
        $this->info('✅ Sync complete!');

        return self::SUCCESS;
    }

    private function syncAllBusinesses(BizBoostMarketplaceSyncService $syncService): int
    {
        $query = BizBoostBusinessModel::where('is_active', true)
            ->where('marketplace_sync_enabled', true);

        // Only businesses with products
        $query->whereHas('products', function ($q) {
            $q->where('is_active', true);
        });

        $businesses = $query->get();

        if ($businesses->isEmpty()) {
            $this->warn('No eligible businesses found');
            return self::SUCCESS;
        }

        $this->info("Found {$businesses->count()} businesses to sync");
        $this->newLine();

        $bar = $this->output->createProgressBar($businesses->count());
        $bar->start();

        $stats = [
            'synced' => 0,
            'failed' => 0,
            'products_synced' => 0,
        ];

        foreach ($businesses as $business) {
            try {
                $seller = $syncService->getOrCreateSeller($business);
                $result = $syncService->syncProducts($business);

                $stats['synced']++;
                $stats['products_synced'] += $result['synced'];
            } catch (\Exception $e) {
                $stats['failed']++;
                $this->newLine();
                $this->error("Failed to sync {$business->name}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('📊 Sync Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Businesses Synced', $stats['synced']],
                ['Businesses Failed', $stats['failed']],
                ['Products Synced', $stats['products_synced']],
            ]
        );

        $this->newLine();
        $this->info('✅ Batch sync complete!');

        return self::SUCCESS;
    }
}

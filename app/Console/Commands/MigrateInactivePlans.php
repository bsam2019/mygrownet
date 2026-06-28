<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;

class MigrateInactivePlans extends Command
{
    protected $signature = 'storage:migrate-inactive-plans';
    protected $description = 'Migrate users from inactive storage plans to the free plan';

    public function handle()
    {
        $this->info('Migrating users from inactive storage plans...');
        
        // Get the free plan
        $freePlan = StoragePlan::where('slug', 'free')
            ->where('is_active', true)
            ->first();
        
        if (!$freePlan) {
            $this->error('Free plan not found! Please ensure the free plan exists and is active.');
            return 1;
        }
        
        // Find all subscriptions with inactive plans
        $subscriptions = UserStorageSubscription::whereHas('storagePlan', function ($query) {
            $query->where('is_active', false);
        })->with('storagePlan', 'user')->get();
        
        if ($subscriptions->isEmpty()) {
            $this->info('No users found on inactive plans.');
            return 0;
        }
        
        $this->info("Found {$subscriptions->count()} users on inactive plans.");
        
        $bar = $this->output->createProgressBar($subscriptions->count());
        $bar->start();
        
        $migrated = 0;
        foreach ($subscriptions as $subscription) {
            $oldPlan = $subscription->storagePlan->name;
            $subscription->update([
                'storage_plan_id' => $freePlan->id,
            ]);
            
            $this->newLine();
            $this->info("✓ Migrated {$subscription->user->name} from '{$oldPlan}' to '{$freePlan->name}'");
            
            $migrated++;
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info("Successfully migrated {$migrated} users to the free plan.");
        
        return 0;
    }
}

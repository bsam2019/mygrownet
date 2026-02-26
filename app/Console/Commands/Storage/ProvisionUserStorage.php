<?php

namespace App\Console\Commands\Storage;

use App\Models\User;
use App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;
use Illuminate\Console\Command;

class ProvisionUserStorage extends Command
{
    protected $signature = 'storage:provision-user {email} {--plan=starter}';
    
    protected $description = 'Provision storage subscription for a user';

    public function handle(): int
    {
        $email = $this->argument('email');
        $planSlug = $this->option('plan');

        // Find user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return 1;
        }

        // Find plan
        $plan = StoragePlan::where('slug', $planSlug)->first();
        
        if (!$plan) {
            $this->error("Storage plan '{$planSlug}' not found!");
            $this->info("Available plans: starter, basic, growth, pro");
            return 1;
        }

        // Check if user already has a subscription
        $existing = UserStorageSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            $this->warn("User already has an active storage subscription!");
            
            if (!$this->confirm('Do you want to update it?')) {
                return 0;
            }

            $existing->update([
                'storage_plan_id' => $plan->id,
                'start_at' => now(),
            ]);

            $this->info("✓ Updated storage subscription for {$user->name}");
        } else {
            UserStorageSubscription::create([
                'user_id' => $user->id,
                'storage_plan_id' => $plan->id,
                'status' => 'active',
                'start_at' => now(),
                'source' => 'manual',
            ]);

            $this->info("✓ Created storage subscription for {$user->name}");
        }

        $this->table(
            ['Plan', 'Quota', 'Max File Size'],
            [[$plan->name, $plan->formatted_quota, $plan->formatted_max_file_size]]
        );

        return 0;
    }
}

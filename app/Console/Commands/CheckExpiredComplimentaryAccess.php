<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use Illuminate\Console\Command;

class CheckExpiredComplimentaryAccess extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cms:check-expired-complimentary';

    /**
     * The console command description.
     */
    protected $description = 'Check and handle expired complimentary CMS access';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for expired complimentary access...');

        // Find companies with expired complimentary access
        $expired = CompanyModel::where('subscription_type', 'complimentary')
            ->where('status', 'active')
            ->whereNotNull('complimentary_until')
            ->where('complimentary_until', '<', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired complimentary accounts found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expired->count()} expired complimentary accounts.");

        foreach ($expired as $company) {
            $this->line("Processing: {$company->name} (ID: {$company->id})");

            // Update company status
            $company->update([
                'status' => 'suspended',
                'subscription_notes' => ($company->subscription_notes ?? '') . 
                    "\n[AUTO] Complimentary access expired on " . 
                    $company->complimentary_until->format('Y-m-d H:i:s'),
            ]);

            // TODO: Send notification to company owner
            // $company->users()->where('role', 'owner')->first()
            //     ->notify(new ComplimentaryAccessExpired($company));

            $this->info("  ✓ Suspended company: {$company->name}");
        }

        $this->info("\nProcessed {$expired->count()} expired complimentary accounts.");

        // Also check for accounts expiring soon (within 7 days)
        $expiringSoon = CompanyModel::where('subscription_type', 'complimentary')
            ->where('status', 'active')
            ->whereNotNull('complimentary_until')
            ->whereBetween('complimentary_until', [now(), now()->addDays(7)])
            ->get();

        if ($expiringSoon->isNotEmpty()) {
            $this->warn("\n{$expiringSoon->count()} accounts expiring within 7 days:");
            foreach ($expiringSoon as $company) {
                $daysLeft = now()->diffInDays($company->complimentary_until, false);
                $this->warn("  - {$company->name}: {$daysLeft} days remaining");
                
                // TODO: Send warning notification
                // $company->users()->where('role', 'owner')->first()
                //     ->notify(new ComplimentaryAccessExpiring($company, $daysLeft));
            }
        }

        return Command::SUCCESS;
    }
}

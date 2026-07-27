<?php

namespace App\Console\Commands;

use App\Domain\PlatformBilling\Services\BillingService;
use Illuminate\Console\Command;

class GenerateBillingInvoices extends Command
{
    protected $signature = 'billing:generate-invoices';
    protected $description = 'Generate platform billing invoices for expiring and overdue subscriptions';

    public function handle(BillingService $billing): int
    {
        $processed = $billing->processExpiringSubscriptions(withinDays: 7);
        $this->info("Found {$processed} expiring subscriptions");

        $overdue = $billing->processOverdueInvoices();
        $this->info("Marked {$overdue} overdue invoices");

        return Command::SUCCESS;
    }
}

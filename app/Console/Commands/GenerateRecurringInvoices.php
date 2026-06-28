<?php

namespace App\Console\Commands;

use App\Domain\CMS\Core\Services\RecurringInvoiceService;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\RecurringInvoiceModel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'cms:generate-recurring-invoices';
    protected $description = 'Generate invoices from recurring invoice templates';

    public function handle(RecurringInvoiceService $service): int
    {
        $this->info('Generating recurring invoices...');

        $companies = CompanyModel::all();
        $totalGenerated = 0;

        foreach ($companies as $company) {
            $this->info("Processing company: {$company->name}");

            $dueInvoices = RecurringInvoiceModel::where('company_id', $company->id)
                ->where('status', 'active')
                ->where('next_generation_date', '<=', Carbon::today())
                ->get();

            foreach ($dueInvoices as $recurringInvoice) {
                try {
                    $invoice = $service->generateInvoice($recurringInvoice);
                    
                    if ($invoice) {
                        $this->info("  ✓ Generated invoice #{$invoice->invoice_number} for {$recurringInvoice->customer->name}");
                        $totalGenerated++;
                    }
                } catch (\Exception $e) {
                    $this->error("  ✗ Failed to generate invoice for recurring invoice #{$recurringInvoice->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Total invoices generated: {$totalGenerated}");

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Domain\QuickInvoice\Services\PdfGeneratorService;
use Illuminate\Console\Command;

class CleanupQuickInvoiceTemp extends Command
{
    protected $signature = 'quick-invoice:cleanup-temp';

    protected $description = 'Clean up temporary Quick Invoice PDF files older than 1 hour';

    public function handle(): int
    {
        $deleted = PdfGeneratorService::cleanupTempFiles();
        
        $this->info("Cleaned up {$deleted} temporary PDF file(s).");
        
        return Command::SUCCESS;
    }
}

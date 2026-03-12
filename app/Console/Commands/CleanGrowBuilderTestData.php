<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;

class CleanGrowBuilderTestData extends Command
{
    protected $signature = 'growbuilder:clean-test-data {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Clean up test/mock data from GrowBuilder page views';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Checking GrowBuilder page views for test data...');
        
        // Check for suspicious patterns that indicate test data
        $suspiciousData = GrowBuilderPageView::where(function($query) {
            $query->where('country', 'LIKE', '%test%')
                  ->orWhere('country', 'LIKE', '%sample%')
                  ->orWhere('country', 'LIKE', '%mock%')
                  ->orWhere('ip_address', 'LIKE', '127.0.0.1')
                  ->orWhere('ip_address', 'LIKE', '192.168.%')
                  ->orWhere('ip_address', 'LIKE', '10.0.%')
                  ->orWhere('referrer', 'LIKE', '%test%')
                  ->orWhere('referrer', 'LIKE', '%sample%');
        })->get();

        if ($suspiciousData->count() > 0) {
            $this->warn("Found {$suspiciousData->count()} suspicious records:");
            
            foreach ($suspiciousData->take(10) as $record) {
                $this->line("ID: {$record->id}, IP: {$record->ip_address}, Country: {$record->country}, Referrer: {$record->referrer}");
            }
            
            if (!$dryRun) {
                if ($this->confirm('Delete these records?')) {
                    $deleted = GrowBuilderPageView::where(function($query) {
                        $query->where('country', 'LIKE', '%test%')
                              ->orWhere('country', 'LIKE', '%sample%')
                              ->orWhere('country', 'LIKE', '%mock%')
                              ->orWhere('ip_address', 'LIKE', '127.0.0.1')
                              ->orWhere('ip_address', 'LIKE', '192.168.%')
                              ->orWhere('ip_address', 'LIKE', '10.0.%')
                              ->orWhere('referrer', 'LIKE', '%test%')
                              ->orWhere('referrer', 'LIKE', '%sample%');
                    })->delete();
                    
                    $this->info("Deleted {$deleted} test records.");
                }
            }
        } else {
            $this->info('No obvious test data found.');
        }
        
        // Show current data summary
        $this->info("\nCurrent data summary:");
        $total = GrowBuilderPageView::count();
        $withIP = GrowBuilderPageView::whereNotNull('ip_address')->where('ip_address', '!=', '')->count();
        $withCountry = GrowBuilderPageView::whereNotNull('country')->where('country', '!=', '')->count();
        
        $this->line("Total page views: {$total}");
        $this->line("With IP address: {$withIP}");
        $this->line("With country data: {$withCountry}");
        
        return 0;
    }
}
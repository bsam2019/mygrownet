<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPaymentTransactionMigration extends Command
{
    protected $signature = 'fix:payment-migration';
    protected $description = 'Fix the payment transaction migration issue in production';

    public function handle()
    {
        $migrationName = '2025_12_29_000002_create_growbuilder_site_payment_transactions_table';
        
        // Check if migration already exists
        $exists = DB::table('migrations')
            ->where('migration', $migrationName)
            ->exists();
            
        if ($exists) {
            $this->info('Migration already marked as completed.');
            return 0;
        }
        
        // Get the current max batch
        $maxBatch = DB::table('migrations')->max('batch') ?? 0;
        
        // Insert the migration record
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $maxBatch + 1
        ]);
        
        $this->info('Migration marked as completed successfully!');
        $this->info('You can now run: php artisan migrate');
        
        return 0;
    }
}

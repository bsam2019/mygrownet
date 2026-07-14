<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MarkMigrationsComplete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:mark-complete {--dry-run : Show what would be marked without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all pending migrations as complete (for when schema already exists)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Get already run migrations
        $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();
        
        // Get all migration files
        $migrationFiles = File::glob(database_path('migrations/*.php'));
        
        // Get next batch number
        $nextBatch = DB::table('migrations')->max('batch') + 1;
        
        $toMark = [];
        
        foreach ($migrationFiles as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            
            if (!in_array($migrationName, $ranMigrations)) {
                $toMark[] = $migrationName;
            }
        }
        
        if (empty($toMark)) {
            $this->info('✅ All migrations are already marked as complete!');
            return 0;
        }
        
        $this->info("Found " . count($toMark) . " pending migrations to mark:");
        $this->newLine();
        
        foreach ($toMark as $migration) {
            $this->line("  • $migration");
        }
        
        $this->newLine();
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN: Would mark ' . count($toMark) . ' migrations as complete');
            $this->info('Run without --dry-run to actually mark them');
            return 0;
        }
        
        if (!$this->confirm('Mark these ' . count($toMark) . ' migrations as complete?', true)) {
            $this->warn('Cancelled');
            return 1;
        }
        
        $marked = 0;
        $failed = 0;
        
        foreach ($toMark as $migration) {
            try {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $nextBatch,
                ]);
                $this->info("✓ Marked: $migration");
                $marked++;
            } catch (\Exception $e) {
                $this->error("✗ Failed: $migration - " . $e->getMessage());
                $failed++;
            }
        }
        
        $this->newLine();
        $this->info("✅ Successfully marked $marked migrations as complete");
        
        if ($failed > 0) {
            $this->error("⚠️  $failed migrations failed");
            return 1;
        }
        
        $this->newLine();
        $this->info('💡 Tip: Run "php artisan migrate:status" to verify');
        
        return 0;
    }
}

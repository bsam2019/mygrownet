<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToWasabi extends Command
{
    protected $signature = 'storage:migrate-to-wasabi 
                            {--path= : Specific path to migrate}
                            {--batch-size=100 : Number of files per batch}
                            {--dry-run : Run without actually copying}
                            {--verify : Verify copied files}';

    protected $description = 'Migrate files from DigitalOcean Spaces to Wasabi';

    public function handle()
    {
        $path = $this->option('path') ?? '';
        $batchSize = (int) $this->option('batch-size');
        $dryRun = $this->option('dry-run');
        $verify = $this->option('verify');

        $this->info("Starting migration from DigitalOcean Spaces to Wasabi...");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No files will be copied");
        }

        $sourceDisk = Storage::disk('do_spaces');
        $targetDisk = Storage::disk('wasabi');

        // Get all files
        try {
            $files = $sourceDisk->allFiles($path);
        } catch (\Exception $e) {
            $this->error("Failed to list files: {$e->getMessage()}");
            return 1;
        }

        $total = count($files);
        $this->info("Found {$total} files to migrate");

        if ($total === 0) {
            $this->warn("No files found to migrate.");
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        foreach (array_chunk($files, $batchSize) as $batch) {
            foreach ($batch as $file) {
                try {
                    // Check if already exists on target
                    if ($targetDisk->exists($file)) {
                        if ($verify) {
                            // Verify file sizes match
                            $sourceSize = $sourceDisk->size($file);
                            $targetSize = $targetDisk->size($file);
                            
                            if ($sourceSize !== $targetSize) {
                                $this->newLine();
                                $this->warn("Size mismatch for {$file}: source={$sourceSize}, target={$targetSize}");
                                $errors++;
                            } else {
                                $skipped++;
                            }
                        } else {
                            $skipped++;
                        }
                        $bar->advance();
                        continue;
                    }

                    if (!$dryRun) {
                        // Copy file
                        $content = $sourceDisk->get($file);
                        $targetDisk->put($file, $content, [
                            'visibility' => 'public',
                            'CacheControl' => 'max-age=31536000',
                        ]);

                        if ($verify) {
                            // Verify the copy
                            $sourceSize = $sourceDisk->size($file);
                            $targetSize = $targetDisk->size($file);
                            
                            if ($sourceSize !== $targetSize) {
                                throw new \Exception("Verification failed: size mismatch (source={$sourceSize}, target={$targetSize})");
                            }
                        }
                    }

                    $migrated++;
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("Failed to migrate {$file}: {$e->getMessage()}");
                    $errors++;
                }

                $bar->advance();
            }

            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 seconds
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Migration completed!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Files', $total],
                ['Successfully Migrated', $migrated],
                ['Already Exists (Skipped)', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($dryRun) {
            $this->info("\nThis was a dry run. No files were actually copied.");
            $this->info("Run without --dry-run to perform the actual migration.");
        }

        return $errors === 0 ? 0 : 1;
    }
}

<?php

namespace App\Console\Commands;

use App\Domain\Storage\Services\StorageMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestStorageConnection extends Command
{
    protected $signature = 'storage:test-connection {disk?}';

    protected $description = 'Test storage connection to verify credentials';

    public function handle()
    {
        $disk = $this->argument('disk');

        if ($disk) {
            $this->testDisk($disk);
        } else {
            // Test all configured disks
            $this->info("Testing all storage connections...\n");
            
            $this->testDisk('do_spaces');
            $this->testDisk('wasabi');
            
            // Test migration service
            $this->newLine();
            $this->info("Testing StorageMigrationService...");
            $migrationService = app(StorageMigrationService::class);
            $this->info("Current mode: {$migrationService->getMode()}");
            $this->info("Primary disk: {$migrationService->getPrimaryDisk()}");
            $this->info("Secondary disk: " . ($migrationService->getSecondaryDisk() ?? 'none'));
            
            $results = $migrationService->testConnections();
            foreach ($results as $diskName => $result) {
                if ($result['status'] === 'success') {
                    $this->info("✓ {$diskName}: {$result['message']}");
                } else {
                    $this->error("✗ {$diskName}: {$result['message']}");
                }
            }
        }

        return 0;
    }

    protected function testDisk(string $diskName): void
    {
        $this->info("Testing {$diskName}...");

        try {
            $disk = Storage::disk($diskName);
            
            // Test write
            $testFile = 'test-connection-' . time() . '.txt';
            $testContent = 'Connection test successful at ' . now();
            
            $disk->put($testFile, $testContent);
            $this->line("  ✓ Write test passed");

            // Test read
            $retrieved = $disk->get($testFile);
            if ($retrieved === $testContent) {
                $this->line("  ✓ Read test passed");
            } else {
                $this->error("  ✗ Read test failed - content mismatch");
            }

            // Test URL generation
            $url = $disk->url($testFile);
            $this->line("  ✓ URL generation passed: {$url}");

            // Test delete
            $disk->delete($testFile);
            $this->line("  ✓ Delete test passed");

            $this->info("✓ {$diskName} connection successful!\n");

        } catch (\Exception $e) {
            $this->error("✗ {$diskName} connection failed!");
            $this->error("  Error: {$e->getMessage()}\n");
        }
    }
}

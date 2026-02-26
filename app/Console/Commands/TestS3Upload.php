<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Infrastructure\Storage\S3\S3StorageService;

class TestS3Upload extends Command
{
    protected $signature = 'storage:test-s3';
    protected $description = 'Test S3 configuration and generate a test presigned URL';

    public function handle(S3StorageService $s3Service): int
    {
        $this->info('Testing S3 Configuration...');
        $this->newLine();
        
        // Display configuration
        $this->info('Configuration:');
        $this->line('  Bucket: ' . config('filesystems.disks.s3.bucket'));
        $this->line('  Region: ' . config('filesystems.disks.s3.region'));
        $this->line('  Endpoint: ' . config('filesystems.disks.s3.endpoint'));
        $this->newLine();
        
        // Generate test presigned URL
        $testKey = 'storage/1/test-file.txt';
        $this->info('Generating presigned URL for: ' . $testKey);
        
        try {
            $url = $s3Service->generatePresignedUploadUrl($testKey, 'text/plain', 900);
            $this->newLine();
            $this->info('✓ Presigned URL generated successfully!');
            $this->newLine();
            $this->line('URL: ' . $url);
            $this->newLine();
            
            // Parse URL to check domain
            $parsed = parse_url($url);
            $this->info('URL Details:');
            $this->line('  Host: ' . $parsed['host']);
            $this->line('  Scheme: ' . $parsed['scheme']);
            $this->newLine();
            
            $this->warn('To test CORS, open browser console and run:');
            $this->line('fetch("' . $url . '", { method: "PUT", body: "test" })');
            $this->line('  .then(r => console.log("✓ CORS working!", r))');
            $this->line('  .catch(e => console.error("✗ CORS error:", e))');
            
        } catch (\Exception $e) {
            $this->error('✗ Failed to generate presigned URL');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}

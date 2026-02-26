<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestCorsConfiguration extends Command
{
    protected $signature = 'storage:test-cors';
    protected $description = 'Test CORS configuration on DigitalOcean Space';

    public function handle()
    {
        $this->info('Testing CORS Configuration...');
        $this->newLine();

        // Get S3 configuration
        $bucket = config('filesystems.disks.s3.bucket');
        $endpoint = config('filesystems.disks.s3.endpoint');
        $region = config('filesystems.disks.s3.region');

        $this->info("Bucket: {$bucket}");
        $this->info("Endpoint: {$endpoint}");
        $this->info("Region: {$region}");
        $this->newLine();

        try {
            $client = Storage::disk('s3')->getClient();
            
            // Try to get CORS configuration
            $this->info('Checking current CORS configuration...');
            
            try {
                $result = $client->getBucketCors([
                    'Bucket' => $bucket,
                ]);
                
                $this->info('✓ CORS is configured!');
                $this->newLine();
                $this->info('Current CORS Rules:');
                $this->line(json_encode($result['CORSRules'], JSON_PRETTY_PRINT));
                
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'NoSuchCORSConfiguration') !== false) {
                    $this->error('✗ CORS is NOT configured on this Space!');
                    $this->newLine();
                    $this->warn('To fix this, you need to apply CORS configuration.');
                    $this->warn('Run: php artisan storage:apply-cors');
                } else {
                    throw $e;
                }
            }
            
        } catch (\Exception $e) {
            $this->error('Failed to check CORS: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

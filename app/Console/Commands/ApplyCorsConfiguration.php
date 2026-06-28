<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ApplyCorsConfiguration extends Command
{
    protected $signature = 'storage:apply-cors';
    protected $description = 'Apply CORS configuration to DigitalOcean Space';

    public function handle()
    {
        $this->info('Applying CORS Configuration...');
        $this->newLine();

        $bucket = config('filesystems.disks.s3.bucket');
        $appUrl = config('app.url');

        $this->info("Bucket: {$bucket}");
        $this->info("App URL: {$appUrl}");
        $this->newLine();

        // Parse URL to get origin
        $parsedUrl = parse_url($appUrl);
        $origin = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        if (isset($parsedUrl['port'])) {
            $origin .= ':' . $parsedUrl['port'];
        }

        $this->info("Configuring CORS for origin: {$origin}");
        $this->newLine();

        try {
            $client = Storage::disk('s3')->getClient();
            
            // CORS configuration
            $corsConfig = [
                'CORSRules' => [
                    [
                        'AllowedOrigins' => [
                            $origin,
                            'http://127.0.0.1:8001',
                            'http://localhost:8001',
                            'http://127.0.0.1:8000',
                            'http://localhost:8000',
                        ],
                        'AllowedMethods' => ['GET', 'PUT', 'POST', 'DELETE', 'HEAD'],
                        'AllowedHeaders' => ['*'],
                        'ExposeHeaders' => ['ETag'],
                        'MaxAgeSeconds' => 3000,
                    ],
                ],
            ];

            $result = $client->putBucketCors([
                'Bucket' => $bucket,
                'CORSConfiguration' => $corsConfig,
            ]);

            $this->info('✓ CORS configuration applied successfully!');
            $this->newLine();
            $this->info('Allowed Origins:');
            foreach ($corsConfig['CORSRules'][0]['AllowedOrigins'] as $allowedOrigin) {
                $this->line("  - {$allowedOrigin}");
            }
            $this->newLine();
            $this->info('You can now upload files from your browser.');
            
        } catch (\Exception $e) {
            $this->error('Failed to apply CORS: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Make sure your AWS credentials have permission to modify bucket CORS settings.');
            return 1;
        }

        return 0;
    }
}

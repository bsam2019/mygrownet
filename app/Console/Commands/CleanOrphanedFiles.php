<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageFile;
use App\Infrastructure\Storage\S3\S3StorageService;

class CleanOrphanedFiles extends Command
{
    protected $signature = 'storage:clean-orphaned';
    protected $description = 'Remove file records that don\'t exist in S3';

    public function handle(S3StorageService $s3Service): int
    {
        $this->info('Checking for orphaned files...');
        
        $files = StorageFile::whereNull('deleted_at')->get();
        $orphanedCount = 0;
        
        foreach ($files as $file) {
            if (!$s3Service->fileExists($file->s3_key)) {
                $this->warn("Orphaned: {$file->original_name} (not in S3)");
                $file->delete();
                $orphanedCount++;
            }
        }
        
        if ($orphanedCount > 0) {
            $this->info("Cleaned up {$orphanedCount} orphaned file(s)");
        } else {
            $this->info('No orphaned files found');
        }
        
        return 0;
    }
}

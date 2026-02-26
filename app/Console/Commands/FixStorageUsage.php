<?php

namespace App\Console\Commands;

use App\Infrastructure\Storage\Persistence\Eloquent\StorageUsage;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixStorageUsage extends Command
{
    protected $signature = 'storage:fix-usage {--user-id= : Fix usage for specific user}';
    protected $description = 'Recalculate storage usage from actual files';

    public function handle()
    {
        $userId = $this->option('user-id');
        
        if ($userId) {
            $this->fixUserUsage($userId);
        } else {
            $this->fixAllUsage();
        }
        
        $this->info('Storage usage fixed successfully!');
    }
    
    private function fixUserUsage(int $userId)
    {
        $this->info("Fixing usage for user {$userId}...");
        
        $stats = StorageFile::where('user_id', $userId)
            ->whereNull('deleted_at')
            ->select([
                DB::raw('COUNT(*) as files_count'),
                DB::raw('SUM(size_bytes) as used_bytes')
            ])
            ->first();
        
        StorageUsage::updateOrCreate(
            ['user_id' => $userId],
            [
                'files_count' => $stats->files_count ?? 0,
                'used_bytes' => $stats->used_bytes ?? 0,
            ]
        );
        
        $this->info("User {$userId}: {$stats->files_count} files, " . number_format($stats->used_bytes) . " bytes");
    }
    
    private function fixAllUsage()
    {
        $this->info('Fixing usage for all users...');
        
        $users = StorageFile::select('user_id')
            ->distinct()
            ->pluck('user_id');
        
        foreach ($users as $userId) {
            $this->fixUserUsage($userId);
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeDeletedSites extends Command
{
    protected $signature = 'growbuilder:purge-deleted-sites 
                            {--force : Force immediate deletion without grace period}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Permanently delete sites that have passed their 30-day grace period';

    public function handle(): int
    {
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        $query = GrowBuilderSite::where('status', 'deleted');
        
        if (!$force) {
            $query->where('scheduled_deletion_at', '<=', now());
        }

        $sites = $query->get();

        if ($sites->isEmpty()) {
            $this->info('No sites to purge.');
            return Command::SUCCESS;
        }

        $this->info("Found {$sites->count()} site(s) to purge.");

        foreach ($sites as $site) {
            $this->line("Processing: {$site->name} ({$site->subdomain})");
            
            if ($dryRun) {
                $this->warn("  [DRY RUN] Would delete site ID: {$site->id}");
                continue;
            }

            try {
                // Delete associated media files
                $mediaPath = "growbuilder/{$site->id}";
                if (Storage::disk('public')->exists($mediaPath)) {
                    Storage::disk('public')->deleteDirectory($mediaPath);
                    $this->line("  Deleted media files");
                }

                // Delete related records (cascade should handle most, but be explicit)
                $site->pages()->delete();
                $site->media()->delete();
                $site->forms()->delete();
                $site->pageViews()->delete();
                $site->siteUsers()->delete();
                $site->siteRoles()->delete();
                $site->sitePosts()->delete();
                
                // Finally delete the site (force delete to bypass soft delete)
                $site->forceDelete();
                
                $this->info("  ✓ Permanently deleted");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to delete: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('Purge complete.');

        return Command::SUCCESS;
    }
}

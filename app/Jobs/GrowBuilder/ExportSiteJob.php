<?php

namespace App\Jobs\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\User;
use App\Services\GrowBuilder\StaticExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ExportSiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GrowBuilderSite $site,
        public User $user,
        public string $notificationEmail
    ) {}

    /**
     * Execute the job.
     */
    public function handle(StaticExportService $exportService): void
    {
        try {
            Log::info('Starting site export', [
                'site_id' => $this->site->id,
                'user_id' => $this->user->id,
            ]);

            // Generate export
            $zipPath = $exportService->exportSite($this->site);

            // Store export info in database for download link
            \DB::table('site_exports')->insert([
                'site_id' => $this->site->id,
                'user_id' => $this->user->id,
                'file_path' => $zipPath,
                'file_size' => filesize($zipPath),
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Site export completed', [
                'site_id' => $this->site->id,
                'file_size' => filesize($zipPath),
            ]);

            // TODO: Send email notification with download link
            // Notification::route('mail', $this->notificationEmail)
            //     ->notify(new SiteExportReady($this->site, $zipPath));

        } catch (\Exception $e) {
            Log::error('Site export failed', [
                'site_id' => $this->site->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // TODO: Send failure notification
            // Notification::route('mail', $this->notificationEmail)
            //     ->notify(new SiteExportFailed($this->site, $e->getMessage()));

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Site export job failed permanently', [
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

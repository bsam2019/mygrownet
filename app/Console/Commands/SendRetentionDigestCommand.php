<?php

namespace App\Console\Commands;

use App\Services\GrowBuilder\RetentionDigestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendRetentionDigestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'growbuilder:retention-digest {--site= : Specific site ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send monthly business performance digest and AI re-engagement suggestions for GrowBuilder sites';

    /**
     * Execute the console command.
     */
    public function handle(RetentionDigestService $digestService): int
    {
        $this->info('Generating monthly GrowBuilder retention digests...');

        $siteId = $this->option('site');

        $query = DB::table('growbuilder_sites')->whereNull('deleted_at');

        if ($siteId) {
            $query->where('id', $siteId);
        }

        $sites = $query->get(['id', 'name', 'user_id']);

        $start = now()->subDays(30)->toDateString();
        $end   = now()->toDateString();

        $processed = 0;

        foreach ($sites as $site) {
            try {
                $digest = $digestService->generateDigest($site->id, $start, $end);

                // Queue in-app notification for site owner
                DB::table('notifications')->insert([
                    'id'              => (string) \Illuminate\Support\Str::uuid(),
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id'   => $site->user_id,
                    'module'          => 'growbuilder',
                    'type'            => 'retention_digest',
                    'category'        => 'analytics',
                    'title'           => "Monthly Performance Report for \"{$site->name}\"",
                    'message'         => "Your site had {$digest['analytics']['unique_visitors']} unique visitors and {$digest['analytics']['enquiries']} enquiries this month.",
                    'action_url'      => "/dashboard/sites/{$site->id}/seo",
                    'action_text'     => 'View Health & Insights',
                    'priority'        => 'normal',
                    'read_at'         => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $processed++;
                $this->line("  ✓ Generated digest for site #{$site->id} ({$site->name})");
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed for site #{$site->id}: {$e->getMessage()}");
                Log::error('GrowBuilder digest command failed', ['site_id' => $site->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Completed sending digests for {$processed} site(s).");

        return Command::SUCCESS;
    }
}

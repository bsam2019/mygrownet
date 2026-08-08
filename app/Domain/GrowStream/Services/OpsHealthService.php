<?php

namespace App\Domain\GrowStream\Services;

use App\Domain\Core\Services\AlertService;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Operational health checks for GrowStream, consumed by `growstream:check-ops`.
 *
 * Covers:
 * - Cloudflare Stream storage quota (stored minutes vs. configured limit)
 * - PawaPay webhook freshness (heartbeat recorded on each verified webhook)
 * - Failed job backlog (failed_jobs table)
 *
 * All checks return a normalized alert array (or null when healthy) so the
 * console command and AlertService can surface them consistently.
 */
class OpsHealthService
{
    public function __construct(
        private AlertService $alerts,
    ) {}

    /**
     * Run every enabled check and fire alerts for any that trip.
     *
     * @return array<int, array<string, mixed>>
     */
    public function checkAll(): array
    {
        $alerts = array_filter([
            $this->checkCloudflareQuota(),
            $this->checkPawaPayWebhook(),
            $this->checkFailedJobs(),
        ]);

        foreach ($alerts as $alert) {
            $this->alerts->fire($alert);
        }

        return array_values($alerts);
    }

    /**
     * Cloudflare Stream stored-minute quota.
     */
    public function checkCloudflareQuota(): ?array
    {
        $limit = config('growstream.ops_monitoring.cloudflare_stored_minutes_limit');

        if (! $limit) {
            return null; // not configured — skip
        }

        $storedMinutes = (int) Video::query()
            ->whereNotNull('duration')
            ->sum(DB::raw('duration / 60'));

        $severity = $storedMinutes > $limit * 1.5 ? 'critical' : 'warning';
        $percent = $limit > 0 ? round(($storedMinutes / $limit) * 100) : 0;

        if ($storedMinutes >= $limit) {
            return $this->alert(
                type: 'cloudflare_stream_storage_limit',
                severity: $severity,
                message: "Cloudflare Stream storage at {$percent}% ({$storedMinutes}/{$limit} stored minutes)",
                value: $storedMinutes,
                threshold: $limit,
            );
        }

        return null;
    }

    /**
     * PawaPay webhook freshness. Only meaningful once payments are live —
     * skipped when automated payments are disabled.
     */
    public function checkPawaPayWebhook(): ?array
    {
        if (! config('payment.automated_payments_enabled', false)) {
            return null;
        }

        $staleMinutes = (int) config('growstream.ops_monitoring.pawapay_webhook_stale_minutes', 120);
        $lastWebhookAt = Cache::get('ops.pawapay.last_webhook_at');

        if (! $lastWebhookAt) {
            return $this->alert(
                type: 'pawapay_webhook_no_heartbeat',
                severity: 'warning',
                message: 'No PawaPay webhook received since monitoring began',
                value: 0,
                threshold: $staleMinutes,
            );
        }

        $lastWebhook = \Illuminate\Support\Carbon::parse($lastWebhookAt);
        $minutesSince = (int) $lastWebhook->diffInMinutes(now());

        if ($minutesSince > $staleMinutes) {
            return $this->alert(
                type: 'pawapay_webhook_stale',
                severity: $minutesSince > $staleMinutes * 3 ? 'critical' : 'warning',
                message: "No PawaPay webhook for {$minutesSince} minutes (threshold: {$staleMinutes})",
                value: $minutesSince,
                threshold: $staleMinutes,
            );
        }

        return null;
    }

    /**
     * Failed jobs backlog (failed_jobs table).
     */
    public function checkFailedJobs(): ?array
    {
        $warning = (int) config('growstream.ops_monitoring.failed_jobs_warning', 5);
        $critical = (int) config('growstream.ops_monitoring.failed_jobs_critical', 20);

        $count = DB::table('failed_jobs')->count();

        if ($count >= $critical) {
            return $this->alert(
                type: 'growstream_failed_jobs',
                severity: 'critical',
                message: "{$count} failed jobs in queue (critical threshold: {$critical})",
                value: $count,
                threshold: $critical,
            );
        }

        if ($count >= $warning) {
            return $this->alert(
                type: 'growstream_failed_jobs',
                severity: 'warning',
                message: "{$count} failed jobs in queue (warning threshold: {$warning})",
                value: $count,
                threshold: $warning,
            );
        }

        return null;
    }

    /**
     * Optional live check that the Cloudflare Stream API token still works
     * and the account is reachable. Network-only; not run in checkAll by
     * default to avoid rate limits. Call explicitly from the command.
     */
    public function verifyCloudflareReachability(): ?array
    {
        $accountId = (string) config('growstream.providers.cloudflare.account_id', '');
        $apiToken = (string) config('growstream.providers.cloudflare.api_token', '');

        if (! $accountId || ! $apiToken) {
            return $this->alert(
                type: 'cloudflare_stream_unconfigured',
                severity: 'critical',
                message: 'Cloudflare Stream credentials missing in config',
                value: 0,
                threshold: 1,
            );
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiToken,
                'Accept' => 'application/json',
            ])->timeout(10)->get(
                "https://api.cloudflare.com/client/v4/accounts/{$accountId}/stream",
                ['per_page' => 1],
            );

            if (! $response->successful()) {
                return $this->alert(
                    type: 'cloudflare_stream_unreachable',
                    severity: 'critical',
                    message: 'Cloudflare Stream API unreachable: '.($response->json('errors.0.message') ?? $response->status()),
                    value: $response->status(),
                    threshold: 200,
                );
            }
        } catch (\Throwable $e) {
            Log::warning('OpsHealthService: Cloudflare reachability check failed', ['error' => $e->getMessage()]);

            return $this->alert(
                type: 'cloudflare_stream_unreachable',
                severity: 'critical',
                message: 'Cloudflare Stream API connection failed: '.$e->getMessage(),
                value: 0,
                threshold: 1,
            );
        }

        return null;
    }

    private function alert(string $type, string $severity, string $message, mixed $value, mixed $threshold): array
    {
        return [
            'type' => $type,
            'severity' => $severity,
            'message' => $message,
            'value' => $value,
            'threshold' => $threshold,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

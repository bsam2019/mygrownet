<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Core\Services\MetricsService;
use App\Domain\Core\Services\DeadLetterService;
use App\Domain\Core\Services\HealthServiceImpl;
use App\Domain\Core\Services\OutboxService;
use App\Domain\Core\Services\InboxService;
use Illuminate\Support\Facades\Queue;
use Inertia\Inertia;

class IntegrationDashboardController extends Controller
{
    public function __construct(
        private MetricsService $metrics,
        private DeadLetterService $deadLetter,
        private HealthServiceImpl $health,
        private OutboxService $outbox,
        private InboxService $inbox,
    ) {}

    public function index()
    {
        $deadLetterStats = $this->deadLetter->countByStatus();
        $metricsSummary = $this->metrics->dashboard();
        $healthAll = $this->health->all();

        $dashboard = [
            'events' => [
                'published_24h' => $metricsSummary['events_published'] ?? 0,
                'failed_24h' => $metricsSummary['events_failed'] ?? 0,
                'failure_rate' => $this->calculateRate(
                    $metricsSummary['events_failed'] ?? 0,
                    $metricsSummary['events_published'] ?? 0
                ),
            ],
            'queue' => [
                'default_depth' => $this->safeQueueSize('default'),
                'events_queue_depth' => $this->safeQueueSize('events'),
                'integrations_depth' => $this->safeQueueSize('integrations'),
            ],
            'dead_letter' => [
                'pending' => $deadLetterStats['pending'] ?? 0,
                'replayed' => $deadLetterStats['replayed'] ?? 0,
                'failed' => $deadLetterStats['failed'] ?? 0,
                'total' => array_sum($deadLetterStats),
            ],
            'outbox' => [
                'pending' => $this->outbox->pendingCount(),
                'failed' => $this->outbox->failedCount(),
            ],
            'inbox' => $this->inbox->stats(),
            'contracts' => [
                'success_rate' => $metricsSummary['contract_success_rate'] ?? 100,
                'slowest_calls' => $metricsSummary['slowest_contracts'] ?? [],
                'total_calls' => $metricsSummary['contract_calls'] ?? 0,
            ],
            'applications' => $healthAll,
            'timestamp' => now()->toIso8601String(),
        ];

        return Inertia::render('Admin/IntegrationDashboard', [
            'dashboard' => $dashboard,
        ]);
    }

    private function calculateRate(int $failed, int $total): float
    {
        if ($total === 0) return 0;
        return round(($failed / $total) * 100, 2);
    }

    private function safeQueueSize(string $queue): int
    {
        try {
            return Queue::size($queue);
        } catch (\Throwable) {
            return -1;
        }
    }
}

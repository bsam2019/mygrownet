<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\QuickInvoice;

use App\Domain\QuickInvoice\Repositories\UsageTrackingRepositoryInterface;
use App\Models\QuickInvoice\UsageTracking;

class EloquentUsageTrackingRepository implements UsageTrackingRepositoryInterface
{
    public function track(string $documentType, string $template, ?int $userId = null, ?string $sessionId = null, string $integrationSource = 'standalone'): void
    {
        UsageTracking::track($documentType, $template, $userId, $sessionId, $integrationSource);
    }

    public function getStats(string $startDate, string $endDate): array
    {
        return UsageTracking::getStats($startDate, $endDate);
    }

    public function getUserMonthlyUsage(int $userId, ?string $month = null): int
    {
        return UsageTracking::getUserMonthlyUsage($userId, $month);
    }

    public function getRecentActivity(int $limit = 10): array
    {
        return UsageTracking::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user_name' => $activity->user?->name ?? 'Guest',
                    'document_type' => $activity->document_type,
                    'template_used' => $activity->template_used,
                    'integration_source' => $activity->integration_source,
                    'created_at' => $activity->created_at->format('M j, Y H:i'),
                ];
            })
            ->toArray();
    }

    public function getTopUsers(string $startDate, string $endDate, int $limit = 10): array
    {
        return UsageTracking::whereNotNull('user_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->join('users', 'user_id', '=', 'users.id')
            ->groupBy('user_id', 'users.name', 'users.email')
            ->selectRaw('user_id, users.name, users.email, COUNT(*) as document_count')
            ->orderByDesc('document_count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getDailyUsage(string $startDate, string $endDate): array
    {
        $days = (int) ceil((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
        $result = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayStats = UsageTracking::getStats($date, $date);

            $result[] = [
                'date' => $date,
                'documents' => $dayStats['total_documents'],
                'users' => $dayStats['unique_users'],
                'sessions' => $dayStats['unique_sessions'],
            ];
        }

        return $result;
    }

    public function getOverallStats(string $startDate, string $endDate): array
    {
        return UsageTracking::getStats($startDate, $endDate);
    }
}
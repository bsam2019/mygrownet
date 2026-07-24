<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Repositories;

interface UsageTrackingRepositoryInterface
{
    public function track(string $documentType, string $template, ?int $userId = null, ?string $sessionId = null, string $integrationSource = 'standalone'): void;

    public function getStats(string $startDate, string $endDate): array;

    public function getUserMonthlyUsage(int $userId, ?string $month = null): int;

    public function getRecentActivity(int $limit = 10): array;

    public function getTopUsers(string $startDate, string $endDate, int $limit = 10): array;

    public function getDailyUsage(string $startDate, string $endDate): array;

    public function getOverallStats(string $startDate, string $endDate): array;
}
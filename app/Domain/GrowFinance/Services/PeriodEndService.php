<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class PeriodEndService
{
    private const STANDARD_TASKS = [
        'Post all draft journals',
        'Reconcile bank accounts',
        'Run depreciation',
        'Compute FX revaluation',
        'Generate VAT return',
        'Snapshot financial reports',
        'Close accounting period',
    ];

    public function __construct(
        private FixedAssetService $fixedAssetService,
        private ReportSnapshotService $reportSnapshotService,
        private AccountingService $accountingService,
        private AccountingPeriodService $accountingPeriodService,
    ) {}

    public function generateChecklist(int $businessId, string $periodStart, string $periodEnd, int $createdBy): array
    {
        $start = new DateTimeImmutable($periodStart);
        $end = new DateTimeImmutable($periodEnd);
        $label = $start->format('F Y');

        $existing = DB::table('growfinance_period_end_checklists')
            ->where('business_id', $businessId)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->first();

        if ($existing) {
            return json_decode(json_encode($existing), true);
        }

        $items = array_map(fn(string $task) => [
            'task' => $task,
            'completed' => false,
            'completed_by' => null,
            'completed_at' => null,
        ], self::STANDARD_TASKS);

        $id = DB::table('growfinance_period_end_checklists')->insertGetId([
            'business_id' => $businessId,
            'period_label' => $label,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'items' => json_encode($items),
            'status' => 'in_progress',
            'created_by' => $createdBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('growfinance_period_end_checklists')->find($id);
        return json_decode(json_encode($row), true);
    }

    public function completeTask(int $checklistId, string $taskName, int $completedBy): void
    {
        $row = DB::table('growfinance_period_end_checklists')->find($checklistId);
        if (!$row) {
            throw new \RuntimeException('Checklist not found');
        }

        $items = json_decode($row->items, true);
        $found = false;
        foreach ($items as &$item) {
            if ($item['task'] === $taskName && !$item['completed']) {
                $item['completed'] = true;
                $item['completed_by'] = $completedBy;
                $item['completed_at'] = now()->toDateTimeString();
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new \RuntimeException("Task '{$taskName}' not found or already completed");
        }

        DB::table('growfinance_period_end_checklists')
            ->where('id', $checklistId)
            ->update([
                'items' => json_encode($items),
                'updated_at' => now(),
            ]);
    }

    public function runDepreciation(int $businessId): array
    {
        $periodDate = new DateTimeImmutable('now');
        $results = $this->fixedAssetService->runAllDepreciation($businessId, $periodDate);
        return [
            'assets_depreciated' => count($results),
            'total_amount' => array_sum(array_map(fn($e) => $e->depreciationAmount, $results)),
        ];
    }

    public function snapshotReports(int $businessId, string $periodStart, string $periodEnd): array
    {
        $start = new DateTimeImmutable($periodStart);
        $end = new DateTimeImmutable($periodEnd);
        return $this->reportSnapshotService->snapshotAll($businessId, $start, $end);
    }

    public function closePeriod(int $businessId, string $periodStart, string $periodEnd, int $closedBy): array
    {
        DB::transaction(function () use ($businessId, $periodStart, $periodEnd, $closedBy) {
            $this->accountingService->closePeriod($businessId, $periodStart, $periodEnd);

            $row = DB::table('growfinance_period_end_checklists')
                ->where('business_id', $businessId)
                ->where('period_start', $periodStart)
                ->where('period_end', $periodEnd)
                ->first();

            if ($row) {
                $items = json_decode($row->items, true);
                foreach ($items as &$item) {
                    if (!$item['completed']) {
                        $item['completed'] = true;
                        $item['completed_by'] = $closedBy;
                        $item['completed_at'] = now()->toDateTimeString();
                    }
                }

                DB::table('growfinance_period_end_checklists')
                    ->where('id', $row->id)
                    ->update([
                        'items' => json_encode($items),
                        'status' => 'completed',
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
        });

        return ['closed' => true, 'period_start' => $periodStart, 'period_end' => $periodEnd];
    }

    public function getChecklist(int $businessId, ?string $periodStart = null, ?string $periodEnd = null): ?array
    {
        $query = DB::table('growfinance_period_end_checklists')
            ->where('business_id', $businessId);

        if ($periodStart && $periodEnd) {
            $query->where('period_start', $periodStart)->where('period_end', $periodEnd);
        } else {
            $query->orderBy('created_at', 'desc')->limit(1);
        }

        $row = $query->first();
        if (!$row) {
            return null;
        }

        $row = json_decode(json_encode($row), true);
        $row['items'] = json_decode($row['items'], true);
        return $row;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\ReportRepositoryInterface;
use DateTimeImmutable;

class ReportSnapshotService
{
    public function __construct(
        private ReportingEngine $reportingEngine,
        private ReportRepositoryInterface $reportRepo,
    ) {}

    public function snapshotTrialBalance(int $businessId, ?DateTimeImmutable $asOf = null): int
    {
        $asOf ??= new DateTimeImmutable('now');
        $data = $this->reportingEngine->getTrialBalance($businessId, $asOf);
        return $this->reportRepo->saveSnapshot(
            $businessId, 'trial_balance', $asOf->format('Y-m-d'), $data
        );
    }

    public function snapshotProfitAndLoss(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        $data = $this->reportingEngine->getProfitAndLoss($businessId, $from, $to);
        return $this->reportRepo->saveSnapshot(
            $businessId, 'profit_loss', $to->format('Y-m-d'), $data
        );
    }

    public function snapshotBalanceSheet(int $businessId, ?DateTimeImmutable $asOf = null): int
    {
        $asOf ??= new DateTimeImmutable('now');
        $data = $this->reportingEngine->getBalanceSheet($businessId, $asOf);
        return $this->reportRepo->saveSnapshot(
            $businessId, 'balance_sheet', $asOf->format('Y-m-d'), $data
        );
    }

    public function snapshotCashFlow(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        $data = $this->reportingEngine->getCashFlow($businessId, $from, $to);
        return $this->reportRepo->saveSnapshot(
            $businessId, 'cash_flow', $to->format('Y-m-d'), $data
        );
    }

    public function snapshotAll(int $businessId, DateTimeImmutable $periodStart, DateTimeImmutable $periodEnd): array
    {
        return [
            'trial_balance' => $this->snapshotTrialBalance($businessId, $periodEnd),
            'profit_loss' => $this->snapshotProfitAndLoss($businessId, $periodStart, $periodEnd),
            'balance_sheet' => $this->snapshotBalanceSheet($businessId, $periodEnd),
            'cash_flow' => $this->snapshotCashFlow($businessId, $periodStart, $periodEnd),
        ];
    }

    private function takeSnapshot(int $businessId, string $reportType, DateTimeImmutable $asOfDate): array
    {
        $data = match ($reportType) {
            'trial_balance' => $this->reportingEngine->getTrialBalance($businessId, $asOfDate),
            'balance_sheet' => $this->reportingEngine->getBalanceSheet($businessId, $asOfDate),
            'profit_and_loss' => $this->reportingEngine->getProfitAndLoss(
                $businessId,
                $asOfDate->modify('-1 year'),
                $asOfDate,
            ),
            'cash_flow' => $this->reportingEngine->getCashFlow(
                $businessId,
                $asOfDate->modify('-1 year'),
                $asOfDate,
            ),
            default => throw new \InvalidArgumentException("Unsupported report type: {$reportType}"),
        };

        $snapshotId = $this->reportRepo->saveSnapshot(
            $businessId, $reportType, $asOfDate->format('Y-m-d'), $data
        );

        return ['id' => $snapshotId, 'data' => $data];
    }

    public function takeSnapshotWithHash(int $businessId, string $reportType, DateTimeImmutable $asOfDate): array
    {
        $snapshot = $this->takeSnapshot($businessId, $reportType, $asOfDate);
        $hash = hash('sha256', serialize($snapshot['data']));
        $this->reportRepo->updateSnapshotHash($snapshot['id'], $hash);

        $saved = $this->reportRepo->findSnapshotById($snapshot['id']);
        return $saved ?? ['id' => $snapshot['id'], 'integrity_hash' => $hash];
    }

    public function verifySnapshot(int $id): array
    {
        $snapshot = $this->reportRepo->findSnapshotById($id);
        if (!$snapshot) {
            return ['valid' => false, 'error' => 'Snapshot not found'];
        }

        $originalHash = $snapshot['integrity_hash'] ?? null;
        if (!$originalHash) {
            return ['valid' => false, 'error' => 'No integrity hash found for this snapshot', 'original_hash' => null, 'computed_hash' => null];
        }

        $reportData = $snapshot['report_data'] ?? [];
        $computedHash = hash('sha256', serialize($reportData));
        $valid = hash_equals($originalHash, $computedHash);

        return [
            'valid' => $valid,
            'original_hash' => $originalHash,
            'computed_hash' => $computedHash,
            'snapshot_id' => $id,
            'report_type' => $snapshot['report_type'] ?? null,
            'as_of_date' => $snapshot['as_of_date'] ?? null,
            'locked_at' => $snapshot['locked_at'] ?? null,
        ];
    }

    public function lockSnapshot(int $id): array
    {
        $snapshot = $this->reportRepo->findSnapshotById($id);
        if (!$snapshot) {
            throw new \DomainException('Snapshot not found');
        }

        if (!empty($snapshot['locked_at'])) {
            throw new \DomainException('Snapshot is already locked');
        }

        $this->reportRepo->lockSnapshot($id);
        return $this->reportRepo->findSnapshotById($id) ?? [];
    }

    public function getSnapshotsByType(int $businessId, string $reportType): array
    {
        return $this->reportRepo->findSnapshotsByType($businessId, $reportType, 50);
    }
}

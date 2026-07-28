<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\DepreciationEntry;
use App\Domain\GrowFinance\Entities\FixedAsset;
use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Entities\JournalLine;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\DepreciationScheduleRepositoryInterface;
use App\Domain\GrowFinance\Repositories\FixedAssetRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\AssetStatus;
use App\Domain\GrowFinance\ValueObjects\DepreciationMethod;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use DateTimeImmutable;

class FixedAssetService
{
    public function __construct(
        private FixedAssetRepositoryInterface $assetRepo,
        private DepreciationScheduleRepositoryInterface $scheduleRepo,
        private DepreciationEngine $depreciationEngine,
        private PostingEngine $postingEngine,
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalRepo,
        private JournalLineRepositoryInterface $lineRepo,
    ) {}

    public function acquire(int $businessId, array $data): FixedAsset
    {
        $asset = new FixedAsset(
            id: null,
            businessId: $businessId,
            name: $data['name'],
            category: $data['category'] ?? null,
            purchaseDate: new DateTimeImmutable($data['purchase_date']),
            cost: (float) ($data['cost'] ?? 0),
            residualValue: (float) ($data['residual_value'] ?? 0),
            usefulLifeMonths: (int) ($data['useful_life_months'] ?? 0),
            depreciationMethod: DepreciationMethod::from($data['depreciation_method'] ?? 'straight_line'),
            depreciationRate: isset($data['depreciation_rate']) ? (float) $data['depreciation_rate'] : null,
            accumulatedDepreciation: 0,
            status: AssetStatus::ACTIVE,
            location: $data['location'] ?? null,
            serialNumber: $data['serial_number'] ?? null,
            notes: $data['notes'] ?? null,
        );

        $saved = $this->assetRepo->save($asset);

        $schedule = $this->depreciationEngine->generateSchedule($saved);
        foreach ($schedule as $entry) {
            $this->scheduleRepo->save(new DepreciationEntry(
                id: null,
                assetId: $saved->id,
                periodDate: new DateTimeImmutable($entry['period_date']),
                depreciationAmount: $entry['depreciation_amount'],
                accumulatedDepreciation: $entry['accumulated_depreciation'],
                netBookValue: $entry['net_book_value'],
            ));
        }

        return $saved;
    }

    public function runDepreciation(int $assetId, DateTimeImmutable $periodDate): ?DepreciationEntry
    {
        $asset = $this->assetRepo->findById($assetId);
        if (!$asset || $asset->status->value !== 'active') return null;

        $existingEntry = $this->scheduleRepo->findUnposted($assetId);
        $target = null;
        foreach ($existingEntry as $entry) {
            if ($entry->periodDate->format('Y-m') === $periodDate->format('Y-m')) {
                $target = $entry;
                break;
            }
        }

        if (!$target) {
            $amount = $this->depreciationEngine->computePeriodDepreciation($asset, $periodDate);
            if ($amount <= 0) return null;

            $newAccumulated = $asset->accumulatedDepreciation + $amount;
            $target = $this->scheduleRepo->save(new DepreciationEntry(
                id: null,
                assetId: $asset->id,
                periodDate: $periodDate,
                depreciationAmount: $amount,
                accumulatedDepreciation: $newAccumulated,
                netBookValue: max(0, $asset->cost - $newAccumulated),
            ));
        }

        $jeId = $this->createDepreciationJournal($asset, $target);
        if ($jeId) {
            $this->scheduleRepo->save(new DepreciationEntry(
                id: $target->id,
                assetId: $target->assetId,
                periodDate: $target->periodDate,
                depreciationAmount: $target->depreciationAmount,
                accumulatedDepreciation: $target->accumulatedDepreciation,
                netBookValue: $target->netBookValue,
                journalEntryId: $jeId,
            ));

            $newAccum = $asset->accumulatedDepreciation + $target->depreciationAmount;
            $this->assetRepo->save(new FixedAsset(
                id: $asset->id,
                businessId: $asset->businessId,
                name: $asset->name,
                category: $asset->category,
                purchaseDate: $asset->purchaseDate,
                cost: $asset->cost,
                residualValue: $asset->residualValue,
                usefulLifeMonths: $asset->usefulLifeMonths,
                depreciationMethod: $asset->depreciationMethod,
                depreciationRate: $asset->depreciationRate,
                accumulatedDepreciation: $newAccum,
                status: $asset->isFullyDepreciated() ? AssetStatus::FULLY_DEPRECIATED : $asset->status,
                location: $asset->location,
                serialNumber: $asset->serialNumber,
                notes: $asset->notes,
            ));
        }

        return $target;
    }

    public function runAllDepreciation(int $businessId, DateTimeImmutable $periodDate): array
    {
        $assets = $this->assetRepo->findActive($businessId);
        $results = [];
        foreach ($assets as $asset) {
            $entry = $this->runDepreciation($asset->id, $periodDate);
            if ($entry) $results[] = $entry;
        }
        return $results;
    }

    public function dispose(int $assetId, DateTimeImmutable $disposalDate, ?float $proceeds = null): FixedAsset
    {
        $asset = $this->assetRepo->findById($assetId);
        if (!$asset) throw new \RuntimeException('Asset not found');

        $saved = $this->assetRepo->save(new FixedAsset(
            id: $asset->id,
            businessId: $asset->businessId,
            name: $asset->name,
            category: $asset->category,
            purchaseDate: $asset->purchaseDate,
            cost: $asset->cost,
            residualValue: $asset->residualValue,
            usefulLifeMonths: $asset->usefulLifeMonths,
            depreciationMethod: $asset->depreciationMethod,
            depreciationRate: $asset->depreciationRate,
            accumulatedDepreciation: $asset->accumulatedDepreciation,
            status: AssetStatus::DISPOSED,
            disposalDate: $disposalDate,
            disposalProceeds: $proceeds,
            location: $asset->location,
            serialNumber: $asset->serialNumber,
            notes: $asset->notes,
        ));

        $this->createDisposalJournal($asset, $disposalDate, $proceeds);
        return $saved;
    }

    public function getSchedule(int $assetId): array
    {
        return array_map(fn(DepreciationEntry $e) => $e->toArray(), $this->scheduleRepo->findByAsset($assetId));
    }

    public function findById(int $id): ?FixedAsset
    {
        return $this->assetRepo->findById($id);
    }

    public function getAll(int $businessId): array
    {
        return array_map(fn(FixedAsset $a) => $a->toArray(), $this->assetRepo->findByBusiness($businessId));
    }

    public function delete(int $assetId): void
    {
        $asset = $this->assetRepo->findById($assetId);
        if ($asset) {
            $this->scheduleRepo->deleteForAsset($assetId);
            $this->assetRepo->delete($asset);
        }
    }

    private function createDepreciationJournal(FixedAsset $asset, DepreciationEntry $entry): ?int
    {
        if ($entry->depreciationAmount <= 0) return null;

        $accounts = $this->accountRepo->findByBusiness($asset->businessId);
        $expenseAcct = $this->findAccountByCode($accounts, '5280');
        $accumAcct = $this->findAccountByCode($accounts, '1520');
        if (!$expenseAcct || !$accumAcct) return null;

        $savedEntry = $this->journalRepo->save(new JournalEntry(
            id: null,
            businessId: $asset->businessId,
            journalNumber: null,
            date: $entry->periodDate,
            description: "Depreciation - {$asset->name} ({$entry->periodDate->format('M Y')})",
            reference: null,
            status: JournalStatus::DRAFT,
        ));

        $this->lineRepo->save(new JournalLine(
            id: null,
            journalEntryId: $savedEntry->id,
            accountId: $expenseAcct->id,
            debitAmount: $entry->depreciationAmount,
            creditAmount: 0,
            description: "Depreciation expense - {$asset->name}",
            dimensions: null,
        ));

        $this->lineRepo->save(new JournalLine(
            id: null,
            journalEntryId: $savedEntry->id,
            accountId: $accumAcct->id,
            debitAmount: 0,
            creditAmount: $entry->depreciationAmount,
            description: "Accumulated depreciation - {$asset->name}",
            dimensions: null,
        ));

        $posted = $this->postingEngine->post($savedEntry->id);
        return $posted->id;
    }

    private function createDisposalJournal(FixedAsset $asset, DateTimeImmutable $date, ?float $proceeds): void
    {
        $accounts = $this->accountRepo->findByBusiness($asset->businessId);
        $fixedAssetAcct = $this->findAccountByCode($accounts, '1510');
        $accumAcct = $this->findAccountByCode($accounts, '1520');
        if (!$fixedAssetAcct || !$accumAcct) return;

        $description = "Disposal of {$asset->name}";
        $savedEntry = $this->journalRepo->save(new JournalEntry(
            id: null,
            businessId: $asset->businessId,
            journalNumber: null,
            date: $date,
            description: $description,
            reference: null,
            status: JournalStatus::DRAFT,
        ));

        $lines = [
            new JournalLine(null, $savedEntry->id, $accumAcct->id, $asset->accumulatedDepreciation, 0, "Remove accumulated depreciation", null),
            new JournalLine(null, $savedEntry->id, $fixedAssetAcct->id, 0, $asset->cost, "Remove asset cost", null),
        ];

        $nbv = $asset->getNetBookValue();
        if ($proceeds !== null && $proceeds != $nbv) {
            $gainLossAcct = $this->findAccountByCode($accounts, '4300');
            if ($gainLossAcct) {
                if ($proceeds > $nbv) {
                    $lines[] = new JournalLine(null, $savedEntry->id, $gainLossAcct->id, 0, $proceeds - $nbv, "Gain on disposal", null);
                } else {
                    $lossAcct = $this->findAccountByCode($accounts, '5300');
                    if ($lossAcct) {
                        $lines[] = new JournalLine(null, $savedEntry->id, $lossAcct->id, $nbv - $proceeds, 0, "Loss on disposal", null);
                    }
                }
            }
        }

        foreach ($lines as $line) {
            $this->lineRepo->save($line);
        }

        $this->postingEngine->post($savedEntry->id);
    }

    private function findAccountByCode(array $accounts, string $code): ?object
    {
        foreach ($accounts as $acct) {
            if (isset($acct->code) && $acct->code === $code) return $acct;
            if (is_array($acct) && ($acct['code'] ?? null) === $code) return (object) $acct;
        }
        return null;
    }
}

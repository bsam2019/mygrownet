<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\OrgGroupRepositoryInterface;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use DateTimeImmutable;

class ProfitabilityService
{
    public function __construct(
        private OrgGroupRepositoryInterface $orgGroupRepo,
        private ReportingEngine $reportingEngine,
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
    ) {}

    /**
     * Get profitability P&L filtered by org group (branch/department).
     */
    public function getProfitabilityByGroup(int $parentOrgId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $groups = $this->orgGroupRepo->findByParent($parentOrgId);
        $results = [];

        foreach ($groups as $group) {
            $pnl = $this->reportingEngine->getProfitAndLoss($group->childOrgId, $from, $to);
            $results[] = [
                'org_id' => $group->childOrgId,
                'relationship_type' => $group->relationshipType,
                'profit_and_loss' => $pnl,
                'net_income' => $pnl['total_income'] - $pnl['total_expenses'],
            ];
        }

        return $results;
    }

    /**
     * Get profitability by account dimension (cost centre tags).
     * Filters journal lines by dimensions_json for the given dimension key.
     */
    public function getProfitabilityByDimension(int $businessId, string $dimensionKey, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $entries = $this->journalEntryRepo->findByDateRange($businessId, $from, $to);
        $accounts = $this->accountRepo->findActive($businessId);
        $accountMap = [];
        foreach ($accounts as $a) {
            $accountMap[$a->id] = $a;
        }

        $dimensions = [];

        foreach ($entries as $entry) {
            if ($entry->status->value !== 'posted') continue;

            $entryDimensions = $entry->dimensions ?? [];
            $dimValue = $entryDimensions[$dimensionKey] ?? null;
            if (!$dimValue) continue;

            if (!isset($dimensions[$dimValue])) {
                $dimensions[$dimValue] = ['income' => [], 'expenses' => [], 'total_income' => 0.0, 'total_expenses' => 0.0];
            }

            $lines = $this->journalLineRepo->findByJournalEntry($entry->id);
            foreach ($lines as $line) {
                $account = $accountMap[$line->accountId] ?? null;
                if (!$account) continue;

                $netAmount = $line->creditAmount - $line->debitAmount;
                $sign = $account->normalBalance === 'credit' ? 1 : -1;
                $amount = $netAmount * $sign;

                if ($account->type === AccountType::INCOME) {
                    $dimensions[$dimValue]['total_income'] += $amount;
                    $dimensions[$dimValue]['income'][] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'amount' => $amount,
                    ];
                } elseif ($account->type === AccountType::EXPENSE) {
                    $dimensions[$dimValue]['total_expenses'] += abs($amount);
                    $dimensions[$dimValue]['expenses'][] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'amount' => abs($amount),
                    ];
                }
            }
        }

        return $dimensions;
    }
}

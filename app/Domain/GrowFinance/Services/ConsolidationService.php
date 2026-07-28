<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\GroupConsolidation;
use App\Domain\GrowFinance\Repositories\GroupConsolidationRepositoryInterface;
use App\Domain\GrowFinance\Repositories\OrgGroupRepositoryInterface;
use DateTimeImmutable;

class ConsolidationService
{
    /** @var array<string, string> orgId => functional currency code */
    private array $functionalCurrencies;

    public function __construct(
        private OrgGroupRepositoryInterface $orgGroupRepo,
        private GroupConsolidationRepositoryInterface $consolidationRepo,
        private ReportingEngine $reportingEngine,
        private CurrencyConversionService $currencyConversionService,
        private IntercompanyEliminationService $eliminationService,
        array $functionalCurrencies = ['ZMW' => 'ZMW'],
    ) {
        $this->functionalCurrencies = $functionalCurrencies;
    }

    /**
     * Consolidate a parent org and all its subsidiaries for a given period.
     * Returns the saved GroupConsolidation entity.
     */
    public function consolidate(
        int $parentOrgId,
        string $period,  // YYYY-MM
        string $reportingCurrency = 'ZMW',
    ): GroupConsolidation {
        $subsidiaries = $this->orgGroupRepo->findSubsidiaries($parentOrgId);
        $orgIds = array_merge([$parentOrgId], array_map(fn($g) => $g->childOrgId, $subsidiaries));

        // Build consolidated data per org
        $periodStart = DateTimeImmutable::createFromFormat('Y-m-d', $period . '-01');
        $periodEnd = DateTimeImmutable::createFromFormat('Y-m-d', $period . '-' . date('t', strtotime($period . '-01')));

        $orgData = [];
        $aggregatedTrialBalance = [];
        $aggregatedPnl = ['income' => [], 'expenses' => [], 'total_income' => 0.0, 'total_expenses' => 0.0];
        $aggregatedBalanceSheet = ['assets' => [], 'liabilities' => [], 'equity' => [], 'total_assets' => 0.0, 'total_liabilities' => 0.0, 'total_equity' => 0.0];

        foreach ($orgIds as $orgId) {
            $tb = $this->reportingEngine->getTrialBalance($orgId, $periodEnd);
            $pnl = $this->reportingEngine->getProfitAndLoss($orgId, $periodStart, $periodEnd);
            $bs = $this->reportingEngine->getBalanceSheet($orgId, $periodEnd);

            // Apply currency conversion if needed
            $functionalCurrency = $this->resolveFunctionalCurrency($orgId);
            if (strtoupper($functionalCurrency) !== strtoupper($reportingCurrency)) {
                $rate = $this->currencyConversionService->getRate($functionalCurrency, $reportingCurrency, $periodEnd);
                $tb = $this->convertTrialBalance($tb, $rate);
                $pnl = $this->convertPnl($pnl, $rate);
                $bs = $this->convertBalanceSheet($bs, $rate);
            }

            $orgData[$orgId] = [
                'trial_balance' => $tb,
                'profit_and_loss' => $pnl,
                'balance_sheet' => $bs,
                'functional_currency' => $functionalCurrency,
            ];

            // Aggregate
            $aggregatedTrialBalance = $this->mergeTrialBalances($aggregatedTrialBalance, $tb);
            $aggregatedPnl = $this->mergePnl($aggregatedPnl, $pnl);
            $aggregatedBalanceSheet = $this->mergeBalanceSheets($aggregatedBalanceSheet, $bs);
        }

        // Run elimination entries
        $eliminationEntryIds = [];
        foreach ($orgIds as $orgId) {
            try {
                $ids = $this->eliminationService->eliminateForPeriod($orgId, $period);
                $eliminationEntryIds = array_merge($eliminationEntryIds, $ids);
            } catch (\Throwable) {
                // Skip — may have no IC transactions
            }
        }

        // Recompute aggregated data post-elimination
        $netIncome = $aggregatedPnl['total_income'] - $aggregatedPnl['total_expenses'];
        $aggregatedBalanceSheet['equity'][] = [
            'account_code' => '3005',
            'account_name' => 'Retained Earnings (Current Period)',
            'amount' => $netIncome,
        ];
        $aggregatedBalanceSheet['total_equity'] += $netIncome;

        $data = [
            'trial_balance' => $aggregatedTrialBalance,
            'profit_and_loss' => $aggregatedPnl,
            'balance_sheet' => $aggregatedBalanceSheet,
        ];

        // Save consolidation
        $consolidation = GroupConsolidation::create(
            groupId: $this->resolveGroupId($parentOrgId),
            businessId: $parentOrgId,
            period: $period,
            functionalCurrency: $this->resolveFunctionalCurrency($parentOrgId),
            reportingCurrency: $reportingCurrency,
            exchangeRate: 1.0,
        );

        $consolidation = new GroupConsolidation(
            id: $consolidation->id,
            groupId: $consolidation->groupId,
            businessId: $consolidation->businessId,
            period: $consolidation->period,
            consolidatedData: $data,
            functionalCurrency: $consolidation->functionalCurrency,
            reportingCurrency: $consolidation->reportingCurrency,
            exchangeRate: $consolidation->exchangeRate,
            eliminationEntries: array_map(fn($id) => ['journal_entry_id' => $id], $eliminationEntryIds),
            status: $consolidation->status,
            consolidatedAt: $consolidation->consolidatedAt,
            createdAt: $consolidation->createdAt,
            updatedAt: $consolidation->updatedAt,
        );

        return $this->consolidationRepo->save($consolidation);
    }

    public function getConsolidatedStatements(int $parentOrgId, string $period): ?GroupConsolidation
    {
        return $this->consolidationRepo->findByBusinessAndPeriod($parentOrgId, $period);
    }

    public function listConsolidations(int $parentOrgId): array
    {
        return $this->consolidationRepo->findByGroup($this->resolveGroupId($parentOrgId));
    }

    public function setFunctionalCurrency(int $orgId, string $currency): void
    {
        $this->functionalCurrencies[(string) $orgId] = strtoupper($currency);
    }

    private function resolveFunctionalCurrency(int $orgId): string
    {
        $key = (string) $orgId;
        if (isset($this->functionalCurrencies[$key])) {
            return strtoupper($this->functionalCurrencies[$key]);
        }
        return 'ZMW';
    }

    private function resolveGroupId(int $parentOrgId): int
    {
        $groups = $this->orgGroupRepo->findByParent($parentOrgId);
        if (empty($groups)) {
            throw new \RuntimeException("No org group found for parent org: {$parentOrgId}");
        }
        return $groups[0]->id;
    }

    private function convertTrialBalance(array $tb, float $rate): array
    {
        return array_map(fn($row) => array_merge($row, [
            'debit' => round(($row['debit'] ?? 0) * $rate, 2),
            'credit' => round(($row['credit'] ?? 0) * $rate, 2),
            'balance' => round(($row['balance'] ?? 0) * $rate, 2),
        ]), $tb);
    }

    private function convertPnl(array $pnl, float $rate): array
    {
        $pnl['income'] = array_map(fn($i) => array_merge($i, ['amount' => round($i['amount'] * $rate, 2)]), $pnl['income']);
        $pnl['expenses'] = array_map(fn($e) => array_merge($e, ['amount' => round($e['amount'] * $rate, 2)]), $pnl['expenses']);
        $pnl['total_income'] = round($pnl['total_income'] * $rate, 2);
        $pnl['total_expenses'] = round($pnl['total_expenses'] * $rate, 2);
        return $pnl;
    }

    private function convertBalanceSheet(array $bs, float $rate): array
    {
        foreach (['assets', 'liabilities', 'equity'] as $section) {
            $bs[$section] = array_map(fn($i) => array_merge($i, ['amount' => round($i['amount'] * $rate, 2)]), $bs[$section]);
        }
        $bs['total_assets'] = round($bs['total_assets'] * $rate, 2);
        $bs['total_liabilities'] = round($bs['total_liabilities'] * $rate, 2);
        $bs['total_equity'] = round($bs['total_equity'] * $rate, 2);
        return $bs;
    }

    private function mergeTrialBalances(array $acc, array $tb): array
    {
        foreach ($tb as $row) {
            $code = $row['account_code'] ?? $row['code'] ?? null;
            if ($code === null) continue;
            if (!isset($acc[$code])) {
                $acc[$code] = $row;
            } else {
                $acc[$code]['debit'] = ($acc[$code]['debit'] ?? 0) + ($row['debit'] ?? 0);
                $acc[$code]['credit'] = ($acc[$code]['credit'] ?? 0) + ($row['credit'] ?? 0);
                $acc[$code]['balance'] = ($acc[$code]['balance'] ?? 0) + ($row['balance'] ?? 0);
            }
        }
        return array_values($acc);
    }

    private function mergePnl(array $acc, array $pnl): array
    {
        $mergeSection = function (array $existing, array $incoming, string $key) {
            foreach ($incoming[$key] as $item) {
                $code = $item['account_code'] ?? null;
                if ($code === null) continue;
                $found = false;
                foreach ($existing[$key] as &$e) {
                    if (($e['account_code'] ?? null) === $code) {
                        $e['amount'] += $item['amount'];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $existing[$key][] = $item;
                }
            }
            return $existing;
        };

        $acc = $mergeSection($acc, $pnl, 'income');
        $acc = $mergeSection($acc, $pnl, 'expenses');
        $acc['total_income'] += $pnl['total_income'];
        $acc['total_expenses'] += $pnl['total_expenses'];
        return $acc;
    }

    private function mergeBalanceSheets(array $acc, array $bs): array
    {
        $mergeSection = function (array $existing, array $incoming, string $key) {
            foreach ($incoming[$key] as $item) {
                $code = $item['account_code'] ?? null;
                if ($code === null) continue;
                $found = false;
                foreach ($existing[$key] as &$e) {
                    if (($e['account_code'] ?? null) === $code) {
                        $e['amount'] += $item['amount'];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $existing[$key][] = $item;
                }
            }
            return $existing;
        };

        $acc = $mergeSection($acc, $bs, 'assets');
        $acc = $mergeSection($acc, $bs, 'liabilities');
        $acc = $mergeSection($acc, $bs, 'equity');
        $acc['total_assets'] += $bs['total_assets'];
        $acc['total_liabilities'] += $bs['total_liabilities'];
        $acc['total_equity'] += $bs['total_equity'];
        return $acc;
    }
}

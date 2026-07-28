<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use DateTimeImmutable;

class FinancialRatioService
{
    public function __construct(
        private ReportingEngine $reportingEngine,
        private GeneralLedgerEngine $generalLedgerEngine,
        private AccountRepositoryInterface $accountRepo,
    ) {}

    public function getCurrentRatio(int $orgId, DateTimeImmutable $asOf): float
    {
        $bs = $this->reportingEngine->getBalanceSheet($orgId, $asOf);
        $currentAssets = $this->sumAccountsByCodePrefix($orgId, ['1']);
        $currentLiabilities = $this->sumAccountsByCodePrefix($orgId, ['2']);
        if ($currentLiabilities == 0) return 0.0;
        return round($currentAssets / $currentLiabilities, 2);
    }

    public function getQuickRatio(int $orgId, DateTimeImmutable $asOf): float
    {
        $currentAssets = $this->sumAccountsByCodePrefix($orgId, ['1']);
        $inventory = $this->sumAccountsByCodePrefix($orgId, ['12']);
        $currentLiabilities = $this->sumAccountsByCodePrefix($orgId, ['2']);
        if ($currentLiabilities == 0) return 0.0;
        return round(($currentAssets - $inventory) / $currentLiabilities, 2);
    }

    public function getReturnOnEquity(int $orgId, DateTimeImmutable $from, DateTimeImmutable $to): float
    {
        $pnl = $this->reportingEngine->getProfitAndLoss($orgId, $from, $to);
        $netIncome = $pnl['total_income'] - $pnl['total_expenses'];
        $avgEquity = $this->getAvgEquity($orgId, $from);
        if ($avgEquity == 0) return 0.0;
        return round(($netIncome / $avgEquity) * 100, 2);
    }

    public function getReturnOnAssets(int $orgId, DateTimeImmutable $from, DateTimeImmutable $to): float
    {
        $pnl = $this->reportingEngine->getProfitAndLoss($orgId, $from, $to);
        $netIncome = $pnl['total_income'] - $pnl['total_expenses'];
        $avgTotalAssets = $this->getAvgTotalAssets($orgId, $from);
        if ($avgTotalAssets == 0) return 0.0;
        return round(($netIncome / $avgTotalAssets) * 100, 2);
    }

    public function getProfitMargin(int $orgId, DateTimeImmutable $from, DateTimeImmutable $to): float
    {
        $pnl = $this->reportingEngine->getProfitAndLoss($orgId, $from, $to);
        $netIncome = $pnl['total_income'] - $pnl['total_expenses'];
        $totalRevenue = $pnl['total_income'];
        if ($totalRevenue == 0) return 0.0;
        return round(($netIncome / $totalRevenue) * 100, 2);
    }

    public function getDebtToEquity(int $orgId, DateTimeImmutable $asOf): float
    {
        $bs = $this->reportingEngine->getBalanceSheet($orgId, $asOf);
        $totalLiabilities = $bs['total_liabilities'];
        $totalEquity = $bs['total_equity'];
        if ($totalEquity == 0) return 0.0;
        return round($totalLiabilities / $totalEquity, 2);
    }

    public function getAllRatios(int $orgId, DateTimeImmutable $from, DateTimeImmutable $to, ?DateTimeImmutable $asOf = null): array
    {
        $asOf = $asOf ?? $to;

        return [
            'current_ratio' => $this->getCurrentRatio($orgId, $asOf),
            'quick_ratio' => $this->getQuickRatio($orgId, $asOf),
            'return_on_equity' => $this->getReturnOnEquity($orgId, $from, $to),
            'return_on_assets' => $this->getReturnOnAssets($orgId, $from, $to),
            'profit_margin' => $this->getProfitMargin($orgId, $from, $to),
            'debt_to_equity' => $this->getDebtToEquity($orgId, $asOf),
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'as_of' => $asOf->format('Y-m-d'),
            ],
        ];
    }

    public function getTrend(int $orgId, int $months = 12): array
    {
        $now = new DateTimeImmutable();
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = $now->modify("-{$i} months")->modify('first day of this month');
            $monthEnd = $now->modify("-{$i} months")->modify('last day of this month');

            $pnl = $this->reportingEngine->getProfitAndLoss($orgId, $monthStart, $monthEnd);
            $netIncome = $pnl['total_income'] - $pnl['total_expenses'];

            $trend[] = [
                'month' => $monthStart->format('Y-m'),
                'total_income' => $pnl['total_income'],
                'total_expenses' => $pnl['total_expenses'],
                'net_income' => $netIncome,
            ];
        }

        return $trend;
    }

    private function sumAccountsByCodePrefix(int $orgId, array $prefixes): float
    {
        $accounts = $this->accountRepo->findActive($orgId);
        $total = 0.0;

        foreach ($accounts as $account) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($account->code, $prefix)) {
                    $total += $account->currentBalance;
                    break;
                }
            }
        }

        return $total;
    }

    private function getAvgEquity(int $orgId, DateTimeImmutable $from): float
    {
        $midDate = $from->modify('+15 days');
        $startBs = $this->reportingEngine->getBalanceSheet($orgId, $from);
        $midBs = $this->reportingEngine->getBalanceSheet($orgId, $midDate);

        return ($startBs['total_equity'] + $midBs['total_equity']) / 2;
    }

    private function getAvgTotalAssets(int $orgId, DateTimeImmutable $from): float
    {
        $midDate = $from->modify('+15 days');
        $startBs = $this->reportingEngine->getBalanceSheet($orgId, $from);
        $midBs = $this->reportingEngine->getBalanceSheet($orgId, $midDate);

        return ($startBs['total_assets'] + $midBs['total_assets']) / 2;
    }
}

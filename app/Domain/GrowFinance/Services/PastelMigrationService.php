<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class PastelMigrationService
{
    private const PASTEL_MAP = [
        '1000' => '1000', '1100' => '1100', '1200' => '1200',
        '2000' => '2000', '2100' => '2100', '2200' => '2200',
        '3000' => '3000', '3100' => '3100',
        '4000' => '4000', '4100' => '4100',
        '5000' => '5000', '5100' => '5100',
        '6000' => '5200', '6100' => '5210',
        '7000' => '5230', '7100' => '5240',
    ];

    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private AccountingService $accountingService,
    ) {}

    public function mapPastelCode(string $pastelCode): ?string
    {
        return self::PASTEL_MAP[$pastelCode] ?? null;
    }

    public function importChartOfAccounts(int $businessId, array $pastelAccounts): array
    {
        $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($pastelAccounts as $acc) {
            try {
                $mappedCode = $this->mapPastelCode($acc['code'] ?? '');
                if (!$mappedCode) {
                    $results['skipped']++;
                    continue;
                }

                $existing = $this->accountRepo->findByCode($businessId, $mappedCode);
                if ($existing) {
                    $results['skipped']++;
                    continue;
                }

                $type = $this->inferAccountType($mappedCode);
                $normalBalance = $type->isDebitNormal() ? 'debit' : 'credit';

                $this->accountRepo->save(new Account(
                    id: null,
                    businessId: $businessId,
                    code: $mappedCode,
                    name: $acc['name'] ?? "Account {$mappedCode}",
                    type: $type,
                    normalBalance: $normalBalance,
                    level: $acc['level'] ?? 2,
                    isSystem: false,
                    description: $acc['description'] ?? null,
                ));

                $results['imported']++;
            } catch (\Throwable $e) {
                $results['errors'][] = $acc['code'] . ': ' . $e->getMessage();
            }
        }

        return $results;
    }

    public function importOpeningBalances(int $businessId, array $balances, string $asOfDate): array
    {
        $results = ['posted' => 0, 'errors' => []];

        $lines = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($balances as $bal) {
            $mappedCode = $this->mapPastelCode($bal['code'] ?? '');
            if (!$mappedCode) continue;

            $account = $this->accountRepo->findByCode($businessId, $mappedCode);
            if (!$account) {
                $results['errors'][] = "Account {$mappedCode} not found";
                continue;
            }

            $amount = (float) ($bal['balance'] ?? 0);
            if (abs($amount) < 0.01) continue;

            if ($amount > 0) {
                $lines[] = ['account_id' => $account->id, 'debit_amount' => $amount, 'credit_amount' => 0];
                $totalDebit += $amount;
            } else {
                $lines[] = ['account_id' => $account->id, 'debit_amount' => 0, 'credit_amount' => abs($amount)];
                $totalCredit += abs($amount);
            }
        }

        if (empty($lines)) {
            return $results;
        }

        $diff = round($totalDebit - $totalCredit, 2);
        if (abs($diff) > 0.01) {
            $suspenseCode = '3100';
            $suspenseAccount = $this->accountRepo->findByCode($businessId, $suspenseCode);
            if ($suspenseAccount) {
                if ($diff > 0) {
                    $lines[] = ['account_id' => $suspenseAccount->id, 'debit_amount' => 0, 'credit_amount' => $diff];
                } else {
                    $lines[] = ['account_id' => $suspenseAccount->id, 'debit_amount' => abs($diff), 'credit_amount' => 0];
                }
            }
        }

        try {
            $entry = $this->accountingService->createJournalEntry(
                businessId: $businessId,
                description: 'Pastel Opening Balances as of ' . $asOfDate,
                lines: $lines,
                date: new DateTimeImmutable($asOfDate),
            );
            $results['posted'] = count($lines);
        } catch (\Throwable $e) {
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    public function verifyTrialBalance(int $businessId): array
    {
        $accounts = $this->accountRepo->findActive($businessId);
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accounts as $account) {
            $balance = $account->currentBalance;
            if (abs($balance) < 0.01) continue;

            $normalIsDebit = $account->type->isDebitNormal();
            $isContra = $account->isContraAccount();
            $effectiveDebit = $normalIsDebit ? $balance : ($isContra ? $balance : -$balance);

            if ($effectiveDebit > 0) {
                $totalDebit += $effectiveDebit;
            } else {
                $totalCredit += abs($effectiveDebit);
            }
        }

        $difference = round($totalDebit - $totalCredit, 2);

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => $difference,
            'is_balanced' => abs($difference) < 0.01,
        ];
    }

    private function inferAccountType(string $code): AccountType
    {
        $prefix = substr($code, 0, 1);
        return match ($prefix) {
            '1' => AccountType::ASSET,
            '2' => AccountType::LIABILITY,
            '3' => AccountType::EQUITY,
            '4' => AccountType::INCOME,
            '5', '6', '7' => AccountType::EXPENSE,
            default => AccountType::EXPENSE,
        };
    }
}

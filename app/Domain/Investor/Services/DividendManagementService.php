<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\InvestorDividend;
use App\Domain\Investor\Entities\InvestorPaymentMethod;
use App\Domain\Investor\Repositories\InvestorDividendRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorPaymentMethodRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use DateTimeImmutable;

class DividendManagementService
{
    public function __construct(
        private readonly InvestorDividendRepositoryInterface $dividendRepository,
        private readonly InvestorPaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly InvestorAccountRepositoryInterface $accountRepository
    ) {}

    public function getInvestorDividends(int $investorId): array
    {
        return $this->dividendRepository->findByInvestor($investorId);
    }

    public function getUpcomingDistributions(int $investorId): array
    {
        return [];
    }

    public function getTotalDividendsEarned(int $investorId): float
    {
        $dividends = $this->dividendRepository->findByInvestor($investorId);
        return array_sum(array_map(fn($d) => $d->getNetAmount(), $dividends));
    }

    public function getDividendHistory(int $investorId, ?string $year = null): array
    {
        $dividends = $this->dividendRepository->findByInvestor($investorId);

        if ($year) {
            $dividends = array_filter($dividends, function ($d) use ($year) {
                $pd = $d->getPaymentDate();
                return $pd && $pd->format('Y') === $year;
            });
        }

        return array_map(function ($dividend) {
            return [
                'id' => $dividend->getId(),
                'period' => $dividend->getDividendPeriod(),
                'gross_amount' => $dividend->getGrossAmount(),
                'tax_withheld' => $dividend->getTaxWithheld(),
                'net_amount' => $dividend->getNetAmount(),
                'declaration_date' => $dividend->getDeclarationDate()?->format('Y-m-d'),
                'declaration_date_formatted' => $dividend->getDeclarationDate()?->format('M j, Y'),
                'payment_date' => $dividend->getPaymentDate()?->format('Y-m-d'),
                'payment_date_formatted' => $dividend->getPaymentDate()?->format('M j, Y'),
                'status' => $dividend->getStatus(),
                'status_label' => $this->getStatusLabel($dividend->getStatus()),
                'payment_method' => $dividend->getPaymentMethod(),
                'payment_reference' => $dividend->getPaymentReference(),
            ];
        }, array_values($dividends));
    }

    public function getDividendSummary(int $investorId): array
    {
        $dividends = $this->dividendRepository->findByInvestor($investorId);

        $totalGross = array_sum(array_map(fn($d) => $d->getGrossAmount(), $dividends));
        $totalTax = array_sum(array_map(fn($d) => $d->getTaxWithheld(), $dividends));
        $totalNet = array_sum(array_map(fn($d) => $d->getNetAmount(), $dividends));

        $paidDividends = array_filter($dividends, fn($d) => $d->getStatus() === 'paid');
        $pendingDividends = array_filter($dividends, fn($d) => in_array($d->getStatus(), ['declared', 'pending']));

        $totalPaid = array_sum(array_map(fn($d) => $d->getNetAmount(), $paidDividends));
        $totalPending = array_sum(array_map(fn($d) => $d->getNetAmount(), $pendingDividends));

        $lastDividend = !empty($paidDividends)
            ? array_values(array_slice($paidDividends, 0, 1))[0]
            : null;

        $nextDividend = !empty($pendingDividends)
            ? array_values(array_slice($pendingDividends, 0, 1))[0]
            : null;

        return [
            'total_gross' => $totalGross,
            'total_tax' => $totalTax,
            'total_net' => $totalNet,
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending,
            'dividend_count' => count($dividends),
            'last_dividend' => $lastDividend ? [
                'amount' => $lastDividend->getNetAmount(),
                'period' => $lastDividend->getDividendPeriod(),
                'date' => $lastDividend->getPaymentDate()?->format('M j, Y'),
            ] : null,
            'next_dividend' => $nextDividend ? [
                'amount' => $nextDividend->getNetAmount(),
                'period' => $nextDividend->getDividendPeriod(),
                'expected_date' => $nextDividend->getPaymentDate()?->format('M j, Y'),
            ] : null,
        ];
    }

    public function declareDividend(
        string $period,
        float $totalDividendPool,
        \DateTime $declarationDate,
        ?\DateTime $paymentDate = null
    ): int {
        $investors = $this->accountRepository->all();
        $investors = array_filter($investors, fn($i) => $i->getStatus()->value() === 'shareholder');
        $totalEquity = array_sum(array_map(fn($i) => $i->getEquityPercentage(), $investors));

        $dividendsCreated = 0;

        foreach ($investors as $investor) {
            $investorShare = $totalEquity > 0
                ? ($investor->getEquityPercentage() / $totalEquity) * $totalDividendPool
                : 0;
            $taxRate = 0.15;
            $taxWithheld = $investorShare * $taxRate;
            $netAmount = $investorShare - $taxWithheld;

            $dividend = InvestorDividend::create(
                investorAccountId: $investor->getId(),
                dividendPeriod: $period,
                grossAmount: $investorShare,
                taxWithheld: $taxWithheld,
                netAmount: $netAmount,
                declarationDate: new DateTimeImmutable($declarationDate->format('Y-m-d')),
                paymentDate: $paymentDate ? new DateTimeImmutable($paymentDate->format('Y-m-d')) : null
            );

            $this->dividendRepository->save($dividend);
            $dividendsCreated++;
        }

        return $dividendsCreated;
    }

    public function markDividendAsPaid(
        int $dividendId,
        string $paymentMethod,
        string $paymentReference
    ): bool {
        $dividend = $this->dividendRepository->findById($dividendId);

        if (!$dividend) {
            return false;
        }

        $dividend->markAsPaid($paymentMethod, $paymentReference);
        $this->dividendRepository->save($dividend);
        return true;
    }

    public function getPaymentMethod(int $investorId): ?InvestorPaymentMethod
    {
        return $this->paymentMethodRepository->findPrimaryByInvestor($investorId);
    }

    public function updatePaymentMethod(int $investorId, array $data): InvestorPaymentMethod
    {
        $this->paymentMethodRepository->setAllNonPrimary($investorId);

        return $this->paymentMethodRepository->updateOrCreate(
            $investorId,
            $data['method_type'],
            [
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'account_name' => $data['account_name'] ?? null,
                'branch_code' => $data['branch_code'] ?? null,
                'mobile_provider' => $data['mobile_provider'] ?? null,
                'mobile_number' => $data['mobile_number'] ?? null,
                'is_primary' => true,
                'is_verified' => false,
            ]
        );
    }

    public function calculateTaxWithholding(float $grossAmount): array
    {
        $taxRate = 0.15;
        $taxWithheld = $grossAmount * $taxRate;
        $netAmount = $grossAmount - $taxWithheld;

        return [
            'gross_amount' => $grossAmount,
            'tax_rate' => $taxRate * 100,
            'tax_withheld' => $taxWithheld,
            'net_amount' => $netAmount,
        ];
    }

    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'declared' => 'Declared',
            'pending' => 'Pending Payment',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }
}

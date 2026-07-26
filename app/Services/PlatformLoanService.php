<?php

namespace App\Services;

use App\Domain\BMS\Core\Services\BmsDataService;
use App\Domain\BMS\Core\Services\LoanAccountingService;
use App\Infrastructure\Persistence\Eloquent\BMS\LoanReceivableModel;
use App\Infrastructure\Persistence\Eloquent\BMS\LoanRepaymentModel;
use App\Models\User;
use Carbon\Carbon;

class PlatformLoanService
{
    private const PLATFORM_COMPANY_NAME = 'MyGrowNet Platform';

    public function __construct(
        private LoanAccountingService $loanAccountingService,
        private BmsDataService $bmsData,
    ) {}

    private function getPlatformCompanyId(): int
    {
        $company = $this->bmsData->findCompanyByName(self::PLATFORM_COMPANY_NAME);

        if (!$company) {
            throw new \Exception('MyGrowNet Platform company not found. Please run MyGrowNetPlatformCompanySeeder.');
        }

        return $company->id;
    }

    public function disburseLoan(
        User $user,
        float $principalAmount,
        float $interestRate = 0,
        int $termMonths = 12,
        string $loanType = 'member_loan',
        ?string $purpose = null,
        ?int $approvedBy = null
    ): LoanReceivableModel {
        $companyId = $this->getPlatformCompanyId();

        return $this->loanAccountingService->disburseLoan(
            companyId: $companyId,
            user: $user,
            principalAmount: $principalAmount,
            interestRate: $interestRate,
            termMonths: $termMonths,
            loanType: $loanType,
            purpose: $purpose,
            approvedBy: $approvedBy
        );
    }

    public function recordRepayment(
        LoanReceivableModel $loan,
        float $paymentAmount,
        ?string $paymentMethod = 'wallet',
        ?string $notes = null
    ): LoanRepaymentModel {
        if ($loan->company_id !== $this->getPlatformCompanyId()) {
            throw new \Exception('Loan does not belong to MyGrowNet Platform.');
        }

        return $this->loanAccountingService->recordRepayment(
            loan: $loan,
            paymentAmount: $paymentAmount,
            paymentMethod: $paymentMethod,
            notes: $notes
        );
    }

    public function getAllLoans(string $status = 'all'): \Illuminate\Database\Eloquent\Collection
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->bmsData->getLoansForCompany($companyId);
    }

    public function getLoansQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->bmsData->getLoansQueryForCompany($companyId);
    }

    public function getTotalLoansCount(): int
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->bmsData->getLoansQueryForCompany($companyId)->count();
    }

    public function getActiveLoansCount(): int
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->bmsData->getLoansQueryForCompany($companyId)
            ->where('status', 'active')
            ->count();
    }

    public function getTotalOutstanding(): float
    {
        $companyId = $this->getPlatformCompanyId();

        return (float) $this->bmsData->getLoansQueryForCompany($companyId)
            ->where('status', 'active')
            ->sum('outstanding_balance');
    }

    public function getOverdueLoansCount(): int
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->bmsData->getLoansQueryForCompany($companyId)
            ->overdue()
            ->count();
    }

    public function getDefaultedLoansCount(): int
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->bmsData->getLoansQueryForCompany($companyId)
            ->where('status', 'defaulted')
            ->count();
    }

    public function getLoanById(int $loanId): ?LoanReceivableModel
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->bmsData->findLoanById($companyId, $loanId);
    }

    public function getUserLoans(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->bmsData->getUserLoansForCompany($companyId, $userId);
    }

    public function getBalanceSheetData(?Carbon $asOfDate = null): array
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->loanAccountingService->getBalanceSheetData($companyId, $asOfDate);
    }

    public function getCashFlowData(Carbon $startDate, Carbon $endDate): array
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->loanAccountingService->getCashFlowData($companyId, $startDate, $endDate);
    }

    public function getAgingReport(): array
    {
        $companyId = $this->getPlatformCompanyId();

        $loans = $this->bmsData->getLoansForCompany($companyId)
            ->where('status', 'active');

        $aging = [
            'current' => ['count' => 0, 'amount' => 0, 'loans' => []],
            '30_days' => ['count' => 0, 'amount' => 0, 'loans' => []],
            '60_days' => ['count' => 0, 'amount' => 0, 'loans' => []],
            '90_days' => ['count' => 0, 'amount' => 0, 'loans' => []],
            'default' => ['count' => 0, 'amount' => 0, 'loans' => []],
        ];

        foreach ($loans as $loan) {
            $category = $loan->risk_category;
            $aging[$category]['count']++;
            $aging[$category]['amount'] += $loan->outstanding_balance;
            $aging[$category]['loans'][] = [
                'id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'user_name' => $loan->user->name,
                'outstanding_balance' => $loan->outstanding_balance,
                'days_overdue' => $loan->days_overdue,
                'next_payment_date' => $loan->next_payment_date?->format('Y-m-d'),
            ];
        }

        return $aging;
    }

    public function getPortfolioSummary(): array
    {
        $companyId = $this->getPlatformCompanyId();

        $allLoans = $this->bmsData->getLoansForCompany($companyId);
        $activeLoans = $allLoans->where('status', 'active');

        $totalDisbursed = $allLoans->sum('principal_amount');
        $totalOutstanding = $activeLoans->sum('outstanding_balance');
        $totalRepaid = $allLoans->sum('amount_paid');
        $totalInterestEarned = $allLoans->sum('interest_paid');

        return [
            'total_loans' => $allLoans->count(),
            'active_loans' => $activeLoans->count(),
            'paid_loans' => $allLoans->where('status', 'paid')->count(),
            'defaulted_loans' => $allLoans->where('status', 'defaulted')->count(),
            'total_disbursed' => $totalDisbursed,
            'total_outstanding' => $totalOutstanding,
            'total_repaid' => $totalRepaid,
            'total_interest_earned' => $totalInterestEarned,
            'repayment_rate' => $totalDisbursed > 0 ? ($totalRepaid / $totalDisbursed) * 100 : 0,
            'default_rate' => $allLoans->count() > 0
                ? ($allLoans->where('status', 'defaulted')->count() / $allLoans->count()) * 100
                : 0,
        ];
    }

    public function updateAllRiskCategories(): void
    {
        $companyId = $this->getPlatformCompanyId();

        $this->loanAccountingService->updateAllRiskCategories($companyId);
    }

    public function getOverdueLoans(): \Illuminate\Database\Eloquent\Collection
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->bmsData->getOverdueLoansForCompany($companyId);
    }

    public function getLoansByRisk(string $riskCategory): \Illuminate\Database\Eloquent\Collection
    {
        $companyId = $this->getPlatformCompanyId();

        return $this->bmsData->getLoansByRiskForCompany($companyId, $riskCategory);
    }
}

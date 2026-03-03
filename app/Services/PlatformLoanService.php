<?php

namespace App\Services;

use App\Domain\CMS\Core\Services\LoanAccountingService;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LoanReceivableModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LoanRepaymentModel;
use App\Models\User;
use Carbon\Carbon;

/**
 * Platform Loan Service
 * 
 * Wrapper service for admin dashboard to access CMS loan functionality
 * specifically for MyGrowNet Platform company loans.
 * 
 * This ensures admin dashboard uses CMS infrastructure without affecting
 * other CMS clients.
 */
class PlatformLoanService
{
    private const PLATFORM_COMPANY_NAME = 'MyGrowNet Platform';
    
    public function __construct(
        private LoanAccountingService $loanAccountingService
    ) {}
    
    /**
     * Get MyGrowNet Platform company ID
     */
    private function getPlatformCompanyId(): int
    {
        $company = CompanyModel::where('name', self::PLATFORM_COMPANY_NAME)->first();
        
        if (!$company) {
            throw new \Exception('MyGrowNet Platform company not found. Please run MyGrowNetPlatformCompanySeeder.');
        }
        
        return $company->id;
    }
    
    /**
     * Disburse loan to platform member
     */
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
    
    /**
     * Record loan repayment
     */
    public function recordRepayment(
        LoanReceivableModel $loan,
        float $paymentAmount,
        ?string $paymentMethod = 'wallet',
        ?string $notes = null
    ): LoanRepaymentModel {
        // Verify loan belongs to platform company
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
    
    /**
     * Get all platform loans
     */
    public function getAllLoans(string $status = 'all'): \Illuminate\Database\Eloquent\Collection
    {
        $companyId = $this->getPlatformCompanyId();
        
        $query = LoanReceivableModel::forCompany($companyId)
            ->with(['user', 'repayments', 'schedules'])
            ->orderBy('created_at', 'desc');
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        return $query->get();
    }
    
    /**
     * Get loans query builder (for filtering and pagination)
     */
    public function getLoansQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $companyId = $this->getPlatformCompanyId();
        
        return LoanReceivableModel::forCompany($companyId);
    }
    
    /**
     * Get total loans count
     */
    public function getTotalLoansCount(): int
    {
        $companyId = $this->getPlatformCompanyId();
        
        return LoanReceivableModel::forCompany($companyId)->count();
    }
    
    /**
     * Get active loans count
     */
    public function getActiveLoansCount(): int
    {
        $companyId = $this->getPlatformCompanyId();
        
        return LoanReceivableModel::forCompany($companyId)
            ->where('status', 'active')
            ->count();
    }
    
    /**
     * Get total outstanding balance
     */
    public function getTotalOutstanding(): float
    {
        $companyId = $this->getPlatformCompanyId();
        
        return (float) LoanReceivableModel::forCompany($companyId)
            ->where('status', 'active')
            ->sum('outstanding_balance');
    }
    
    /**
     * Get overdue loans count
     */
    public function getOverdueLoansCount(): int
    {
        $companyId = $this->getPlatformCompanyId();
        
        return LoanReceivableModel::forCompany($companyId)
            ->overdue()
            ->count();
    }
    
    /**
     * Get defaulted loans count
     */
    public function getDefaultedLoansCount(): int
    {
        $companyId = $this->getPlatformCompanyId();
        
        return LoanReceivableModel::forCompany($companyId)
            ->where('status', 'defaulted')
            ->count();
    }
    
    /**
     * Get loan by ID (platform loans only)
     */
    public function getLoanById(int $loanId): ?LoanReceivableModel
    {
        $companyId = $this->getPlatformCompanyId();
        
        return LoanReceivableModel::forCompany($companyId)
            ->with(['user', 'repayments', 'schedules'])
            ->find($loanId);
    }
    
    /**
     * Get loans for specific user
     */
    public function getUserLoans(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        $companyId = $this->getPlatformCompanyId();
        
        return LoanReceivableModel::forCompany($companyId)
            ->where('user_id', $userId)
            ->with(['repayments', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get balance sheet data for platform
     */
    public function getBalanceSheetData(?Carbon $asOfDate = null): array
    {
        $companyId = $this->getPlatformCompanyId();
        
        return $this->loanAccountingService->getBalanceSheetData($companyId, $asOfDate);
    }
    
    /**
     * Get cash flow data for platform
     */
    public function getCashFlowData(Carbon $startDate, Carbon $endDate): array
    {
        $companyId = $this->getPlatformCompanyId();
        
        return $this->loanAccountingService->getCashFlowData($companyId, $startDate, $endDate);
    }
    
    /**
     * Get loan aging report
     */
    public function getAgingReport(): array
    {
        $companyId = $this->getPlatformCompanyId();
        
        $loans = LoanReceivableModel::forCompany($companyId)
            ->where('status', 'active')
            ->get();
        
        $aging = [
            'current' => [
                'count' => 0,
                'amount' => 0,
                'loans' => [],
            ],
            '30_days' => [
                'count' => 0,
                'amount' => 0,
                'loans' => [],
            ],
            '60_days' => [
                'count' => 0,
                'amount' => 0,
                'loans' => [],
            ],
            '90_days' => [
                'count' => 0,
                'amount' => 0,
                'loans' => [],
            ],
            'default' => [
                'count' => 0,
                'amount' => 0,
                'loans' => [],
            ],
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
    
    /**
     * Get loan portfolio summary
     */
    public function getPortfolioSummary(): array
    {
        $companyId = $this->getPlatformCompanyId();
        
        $allLoans = LoanReceivableModel::forCompany($companyId)->get();
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
    
    /**
     * Update risk categories for all platform loans
     */
    public function updateAllRiskCategories(): void
    {
        $companyId = $this->getPlatformCompanyId();
        
        $this->loanAccountingService->updateAllRiskCategories($companyId);
    }
    
    /**
     * Get overdue loans
     */
    public function getOverdueLoans(): \Illuminate\Database\Eloquent\Collection
    {
        $companyId = $this->getPlatformCompanyId();
        
        return LoanReceivableModel::forCompany($companyId)
            ->overdue()
            ->with(['user'])
            ->orderBy('days_overdue', 'desc')
            ->get();
    }
    
    /**
     * Get loans by risk category
     */
    public function getLoansByRisk(string $riskCategory): \Illuminate\Database\Eloquent\Collection
    {
        $companyId = $this->getPlatformCompanyId();
        
        return LoanReceivableModel::forCompany($companyId)
            ->byRisk($riskCategory)
            ->with(['user'])
            ->orderBy('outstanding_balance', 'desc')
            ->get();
    }
}

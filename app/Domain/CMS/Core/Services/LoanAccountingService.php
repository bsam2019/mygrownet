<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\LoanReceivableModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LoanRepaymentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LoanScheduleModel;
use App\Models\Transaction;
use App\Models\User;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * CMS Loan Accounting Service
 * 
 * Manages loans within the CMS system with proper balance sheet accounting.
 * This is for CMS companies giving loans, NOT related to GrowFinance module.
 * 
 * Separates principal from interest for accurate financial reporting.
 */
class LoanAccountingService
{
    /**
     * Disburse a new loan (create asset on balance sheet)
     */
    public function disburseLoan(
        int $companyId,
        User $user,
        float $principalAmount,
        float $interestRate = 0,
        int $termMonths = 12,
        string $loanType = 'member_loan',
        ?string $purpose = null,
        ?int $approvedBy = null
    ): LoanReceivableModel {
        DB::beginTransaction();
        
        try {
            // Calculate total amount with interest
            $interestAmount = ($principalAmount * $interestRate * $termMonths) / (12 * 100);
            $totalAmount = $principalAmount + $interestAmount;
            $monthlyPayment = $termMonths > 0 ? $totalAmount / $termMonths : $totalAmount;
            
            // Generate loan number
            $loanNumber = $this->generateLoanNumber($companyId);
            
            // Create loan record (balance sheet asset)
            $loan = LoanReceivableModel::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'loan_number' => $loanNumber,
                'loan_type' => $loanType,
                'principal_amount' => $principalAmount,
                'interest_rate' => $interestRate,
                'total_amount' => $totalAmount,
                'outstanding_balance' => $totalAmount,
                'term_months' => $termMonths,
                'monthly_payment' => $monthlyPayment,
                'disbursement_date' => now(),
                'due_date' => now()->addMonths($termMonths),
                'next_payment_date' => now()->addMonth(),
                'status' => 'active',
                'risk_category' => 'current',
                'purpose' => $purpose,
                'approved_by' => $approvedBy,
                'disbursed_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            
            // Generate payment schedule
            $this->generatePaymentSchedule($loan, $termMonths, $monthlyPayment, $principalAmount, $interestAmount);
            
            // Create transaction for cash disbursement (for cash flow tracking)
            Transaction::create([
                'user_id' => $user->id,
                'transaction_type' => TransactionType::LOAN_DISBURSEMENT->value,
                'transaction_source' => 'cms',
                'cms_reference_type' => 'loan',
                'cms_reference_id' => $loan->id,
                'amount' => $principalAmount,
                'status' => TransactionStatus::COMPLETED->value,
                'description' => "Loan disbursement: {$loanNumber}",
                'reference_number' => $loanNumber,
                'metadata' => json_encode([
                    'company_id' => $companyId,
                    'loan_id' => $loan->id,
                    'loan_number' => $loanNumber,
                    'principal' => $principalAmount,
                    'interest_rate' => $interestRate,
                    'term_months' => $termMonths,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            return $loan;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Record loan repayment (reduce asset, recognize interest revenue)
     */
    public function recordRepayment(
        LoanReceivableModel $loan,
        float $paymentAmount,
        ?string $paymentMethod = 'wallet',
        ?string $notes = null
    ): LoanRepaymentModel {
        DB::beginTransaction();
        
        try {
            // Calculate how much goes to principal vs interest
            $remainingInterest = ($loan->total_amount - $loan->principal_amount) - $loan->interest_paid;
            $interestPortion = min($paymentAmount, $remainingInterest);
            $principalPortion = $paymentAmount - $interestPortion;
            
            // Generate payment reference
            $paymentReference = $this->generatePaymentReference($loan);
            
            // Create repayment record
            $repayment = LoanRepaymentModel::create([
                'loan_id' => $loan->id,
                'user_id' => $loan->user_id,
                'payment_reference' => $paymentReference,
                'payment_amount' => $paymentAmount,
                'principal_portion' => $principalPortion,
                'interest_portion' => $interestPortion,
                'payment_date' => now(),
                'payment_method' => $paymentMethod,
                'notes' => $notes,
            ]);
            
            // Create transaction for principal repayment (reduces asset, NOT revenue)
            if ($principalPortion > 0) {
                $principalTransaction = Transaction::create([
                    'user_id' => $loan->user_id,
                    'transaction_type' => TransactionType::LOAN_REPAYMENT->value,
                    'transaction_source' => 'cms',
                    'cms_reference_type' => 'loan_repayment',
                    'cms_reference_id' => $repayment->id,
                    'amount' => -$principalPortion,
                    'status' => TransactionStatus::COMPLETED->value,
                    'description' => "Loan repayment (principal): {$loan->loan_number}",
                    'reference_number' => $paymentReference,
                    'metadata' => json_encode([
                        'loan_id' => $loan->id,
                        'repayment_id' => $repayment->id,
                        'type' => 'principal',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $repayment->update(['transaction_id' => $principalTransaction->id]);
            }
            
            // Create separate transaction for interest (THIS IS REVENUE)
            if ($interestPortion > 0) {
                Transaction::create([
                    'user_id' => $loan->user_id,
                    'transaction_type' => 'interest_income',
                    'transaction_source' => 'cms',
                    'cms_reference_type' => 'loan_repayment',
                    'cms_reference_id' => $repayment->id,
                    'amount' => -$interestPortion,
                    'status' => TransactionStatus::COMPLETED->value,
                    'description' => "Loan interest payment: {$loan->loan_number}",
                    'reference_number' => $paymentReference . '-INT',
                    'metadata' => json_encode([
                        'loan_id' => $loan->id,
                        'repayment_id' => $repayment->id,
                        'type' => 'interest',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Update loan balances
            $loan->update([
                'amount_paid' => $loan->amount_paid + $paymentAmount,
                'principal_paid' => $loan->principal_paid + $principalPortion,
                'interest_paid' => $loan->interest_paid + $interestPortion,
                'outstanding_balance' => $loan->outstanding_balance - $paymentAmount,
                'last_payment_date' => now(),
            ]);
            
            // Update payment schedule
            $this->updatePaymentSchedule($loan, $paymentAmount);
            
            // Check if loan is fully paid
            if ($loan->outstanding_balance <= 0.01) {
                $loan->update([
                    'status' => 'paid',
                    'fully_paid_at' => now(),
                    'outstanding_balance' => 0,
                ]);
            } else {
                // Update next payment date
                $nextSchedule = $loan->schedules()
                    ->where('status', 'pending')
                    ->orderBy('due_date')
                    ->first();
                    
                if ($nextSchedule) {
                    $loan->update(['next_payment_date' => $nextSchedule->due_date]);
                }
            }
            
            // Update risk category
            $this->updateRiskCategory($loan);
            
            DB::commit();
            
            return $repayment;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Generate payment schedule
     */
    private function generatePaymentSchedule(
        LoanReceivableModel $loan,
        int $termMonths,
        float $monthlyPayment,
        float $principalAmount,
        float $interestAmount
    ): void {
        $principalPerMonth = $principalAmount / $termMonths;
        $interestPerMonth = $interestAmount / $termMonths;
        
        for ($i = 1; $i <= $termMonths; $i++) {
            LoanScheduleModel::create([
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'due_date' => now()->addMonths($i),
                'installment_amount' => $monthlyPayment,
                'principal_portion' => $principalPerMonth,
                'interest_portion' => $interestPerMonth,
                'status' => 'pending',
            ]);
        }
    }
    
    /**
     * Update payment schedule after payment
     */
    private function updatePaymentSchedule(LoanReceivableModel $loan, float $paymentAmount): void
    {
        $remainingAmount = $paymentAmount;
        
        $pendingSchedules = $loan->schedules()
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->get();
        
        foreach ($pendingSchedules as $schedule) {
            if ($remainingAmount <= 0) {
                break;
            }
            
            $amountForThisSchedule = min($remainingAmount, $schedule->installment_amount - $schedule->amount_paid);
            
            $schedule->update([
                'amount_paid' => $schedule->amount_paid + $amountForThisSchedule,
                'status' => ($schedule->amount_paid + $amountForThisSchedule >= $schedule->installment_amount) 
                    ? 'paid' 
                    : 'partial',
                'paid_date' => ($schedule->amount_paid + $amountForThisSchedule >= $schedule->installment_amount) 
                    ? now() 
                    : null,
            ]);
            
            $remainingAmount -= $amountForThisSchedule;
        }
    }
    
    /**
     * Update loan risk category based on overdue status
     */
    public function updateRiskCategory(LoanReceivableModel $loan): void
    {
        if ($loan->status !== 'active') {
            return;
        }
        
        $daysOverdue = 0;
        
        if ($loan->next_payment_date && $loan->next_payment_date->isPast()) {
            $daysOverdue = now()->diffInDays($loan->next_payment_date);
        }
        
        $riskCategory = match(true) {
            $daysOverdue === 0 => 'current',
            $daysOverdue <= 30 => '30_days',
            $daysOverdue <= 60 => '60_days',
            $daysOverdue <= 90 => '90_days',
            default => 'default',
        };
        
        $loan->update([
            'days_overdue' => $daysOverdue,
            'risk_category' => $riskCategory,
        ]);
        
        // Mark as defaulted if over 90 days
        if ($daysOverdue > 90 && $loan->status === 'active') {
            $loan->update([
                'status' => 'defaulted',
                'defaulted_at' => now(),
            ]);
        }
    }
    
    /**
     * Get balance sheet data for a company (loans as assets)
     */
    public function getBalanceSheetData(int $companyId, ?Carbon $asOfDate = null): array
    {
        $date = $asOfDate ?? now();
        
        $activeLoans = LoanReceivableModel::forCompany($companyId)
            ->where('status', 'active')
            ->where('disbursement_date', '<=', $date)
            ->get();
        
        $totalLoansReceivable = $activeLoans->sum('outstanding_balance');
        $currentLoans = $activeLoans->where('risk_category', 'current')->sum('outstanding_balance');
        $overdueLoans = $activeLoans->whereIn('risk_category', ['30_days', '60_days'])->sum('outstanding_balance');
        $defaultedLoans = $activeLoans->whereIn('risk_category', ['90_days', 'default'])->sum('outstanding_balance');
        
        return [
            'total_loans_receivable' => $totalLoansReceivable,
            'current_loans' => $currentLoans,
            'overdue_loans' => $overdueLoans,
            'defaulted_loans' => $defaultedLoans,
            'loan_count' => $activeLoans->count(),
            'breakdown_by_risk' => [
                'current' => $currentLoans,
                '30_days' => $activeLoans->where('risk_category', '30_days')->sum('outstanding_balance'),
                '60_days' => $activeLoans->where('risk_category', '60_days')->sum('outstanding_balance'),
                '90_days' => $activeLoans->where('risk_category', '90_days')->sum('outstanding_balance'),
                'default' => $activeLoans->where('risk_category', 'default')->sum('outstanding_balance'),
            ],
        ];
    }
    
    /**
     * Get cash flow data for a company (separate from P&L)
     */
    public function getCashFlowData(int $companyId, Carbon $startDate, Carbon $endDate): array
    {
        // Cash out (loans disbursed)
        $loansDisbursed = LoanReceivableModel::forCompany($companyId)
            ->whereBetween('disbursement_date', [$startDate, $endDate])
            ->sum('principal_amount');
        
        // Cash in (principal repayments only)
        $principalRepayments = LoanRepaymentModel::whereHas('loan', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('principal_portion');
        
        // Interest received (this goes to P&L as revenue)
        $interestReceived = LoanRepaymentModel::whereHas('loan', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('interest_portion');
        
        return [
            'loans_disbursed' => $loansDisbursed,
            'principal_repayments' => $principalRepayments,
            'interest_received' => $interestReceived,
            'net_cash_flow' => $principalRepayments - $loansDisbursed,
        ];
    }
    
    /**
     * Generate unique loan number for company
     */
    private function generateLoanNumber(int $companyId): string
    {
        $year = now()->year;
        $lastLoan = LoanReceivableModel::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastLoan ? (int) substr($lastLoan->loan_number, -5) + 1 : 1;
        
        return sprintf('LOAN-%d-%05d', $year, $sequence);
    }
    
    /**
     * Generate unique payment reference
     */
    private function generatePaymentReference(LoanReceivableModel $loan): string
    {
        $count = $loan->repayments()->count() + 1;
        return sprintf('PAY-%s-%03d', $loan->loan_number, $count);
    }
    
    /**
     * Update all loan risk categories for a company (run daily via cron)
     */
    public function updateAllRiskCategories(int $companyId): void
    {
        $activeLoans = LoanReceivableModel::forCompany($companyId)
            ->where('status', 'active')
            ->get();
        
        foreach ($activeLoans as $loan) {
            $this->updateRiskCategory($loan);
        }
    }
}

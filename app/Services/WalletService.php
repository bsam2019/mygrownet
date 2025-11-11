<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Centralized Wallet Service
 * 
 * This service handles all wallet balance calculations and operations.
 * The wallet balance is calculated dynamically from multiple sources.
 * 
 * USES: EarningsService for all earnings calculations (no duplication)
 */
class WalletService
{
    protected EarningsService $earningsService;
    
    public function __construct(EarningsService $earningsService)
    {
        $this->earningsService = $earningsService;
    }
    
    /**
     * Calculate user's wallet balance
     * 
     * Balance = Total Credits - Total Debits
     * 
     * @param User $user
     * @return float
     */
    public function calculateBalance(User $user): float
    {
        $credits = $this->calculateTotalCredits($user);
        $debits = $this->calculateTotalDebits($user);
        
        return $credits - $debits;
    }
    
    /**
     * Calculate total credits (money IN to wallet)
     * 
     * @param User $user
     * @return float
     */
    private function calculateTotalCredits(User $user): float
    {
        // Get ALL earnings from EarningsService (no duplication!)
        $earnings = $this->earningsService->calculateTotalEarnings($user);
        
        // Add deposits/topups
        $deposits = $this->getDeposits($user);
        
        // Add loan disbursements
        $loans = $this->getLoanDisbursements($user);
        
        return $earnings + $deposits + $loans;
    }
    
    /**
     * Calculate total debits (money OUT from wallet)
     * 
     * @param User $user
     * @return float
     */
    private function calculateTotalDebits(User $user): float
    {
        return $this->getWithdrawals($user)
             + $this->getExpenses($user)
             + $this->getLoanRepayments($user);
    }
    
    /**
     * Get deposits/topups (external money added to wallet)
     * 
     * @param User $user
     * @return float
     */
    private function getDeposits(User $user): float
    {
        // Verified wallet topups from member_payments
        $memberPaymentTopups = (float) \App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->sum('amount');
        
        // Wallet topups from transactions table (e.g., LGR transfers)
        $transactionTopups = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'wallet_topup')
            ->where('status', 'completed')
            ->sum('amount');
        
        return $memberPaymentTopups + $transactionTopups;
    }
    
    /**
     * Get loan disbursements
     * 
     * @param User $user
     * @return float
     */
    private function getLoanDisbursements(User $user): float
    {
        return (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'loan_disbursement')
            ->where('status', 'completed')
            ->sum('amount');
    }
    
    /**
     * Get withdrawals
     * 
     * @param User $user
     * @return float
     */
    private function getWithdrawals(User $user): float
    {
        // Approved withdrawals from withdrawals table
        $withdrawalsTable = (float) $user->withdrawals()
            ->where('status', 'approved')
            ->sum('amount');
        
        // Withdrawals from transactions table
        $transactionWithdrawals = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('transaction_type', 'withdrawal')
            ->sum('amount');
        
        return $withdrawalsTable + abs($transactionWithdrawals);
    }
    
    /**
     * Get expenses (workshops, products, etc.)
     * 
     * @param User $user
     * @return float
     */
    private function getExpenses(User $user): float
    {
        // Workshop expenses
        $workshopExpenses = (float) \App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
            ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
            ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
            ->sum('workshops.price');
        
        // Starter kit purchases from transactions
        $starterKitExpenses = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'LIKE', '%starter_kit%')
            ->where('status', 'completed')
            ->sum('amount');
        
        return $workshopExpenses + abs($starterKitExpenses);
    }
    
    /**
     * Get loan repayments
     * 
     * @param User $user
     * @return float
     */
    private function getLoanRepayments(User $user): float
    {
        return (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'loan_repayment')
            ->where('status', 'completed')
            ->sum('amount');
    }
    
    /**
     * Get wallet breakdown for display
     * Uses EarningsService for earnings breakdown (no duplication!)
     * 
     * @param User $user
     * @return array
     */
    public function getWalletBreakdown(User $user): array
    {
        // Get earnings breakdown from EarningsService
        $earningsBreakdown = $this->earningsService->getEarningsBreakdown($user);
        
        return [
            'credits' => [
                'earnings' => $earningsBreakdown, // From EarningsService
                'deposits' => $this->getDeposits($user),
                'loans' => $this->getLoanDisbursements($user),
                'total' => $this->calculateTotalCredits($user),
            ],
            'debits' => [
                'withdrawals' => $this->getWithdrawals($user),
                'expenses' => $this->getExpenses($user),
                'loan_repayments' => $this->getLoanRepayments($user),
                'total' => $this->calculateTotalDebits($user),
            ],
            'balance' => $this->calculateBalance($user),
        ];
    }
}

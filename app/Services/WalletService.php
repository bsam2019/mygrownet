<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Centralized Wallet Service
 * 
 * This service handles all wallet balance calculations and operations.
 * The wallet balance is calculated dynamically from multiple sources.
 */
class WalletService
{
    /**
     * Calculate user's wallet balance
     * 
     * Formula:
     * Balance = (Commissions + Profit Shares + Wallet Topups) 
     *         - (Approved Withdrawals + Workshop Expenses + Transaction Expenses + Starter Kit Purchases)
     * 
     * @param User $user
     * @return float
     */
    public function calculateBalance(User $user): float
    {
        $earnings = $this->calculateTotalEarnings($user);
        $expenses = $this->calculateTotalExpenses($user);
        
        return $earnings - $expenses;
    }
    
    /**
     * Calculate total earnings (credits to wallet)
     * 
     * Sources:
     * 1. Referral commissions (paid status)
     * 2. Profit shares
     * 3. Wallet topups (verified payments)
     * 4. Wallet topups from transactions table (LGR transfers, etc.)
     * 5. Loan disbursements
     * 
     * @param User $user
     * @return float
     */
    public function calculateTotalEarnings(User $user): float
    {
        $commissionEarnings = (float) $user->referralCommissions()
            ->where('status', 'paid')
            ->sum('amount');
        
        $profitEarnings = (float) $user->profitShares()
            ->sum('amount');
        
        $walletTopups = (float) \App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->sum('amount');
        
        // Include wallet topups from transactions table (e.g., LGR transfers)
        $transactionTopups = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'wallet_topup')
            ->where('status', 'completed')
            ->sum('amount');
        
        // Include loan disbursements
        $loanDisbursements = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'loan_disbursement')
            ->where('status', 'completed')
            ->sum('amount');
        
        return $commissionEarnings + $profitEarnings + $walletTopups + $transactionTopups + $loanDisbursements;
    }
    
    /**
     * Calculate total expenses (debits from wallet)
     * 
     * Sources:
     * 1. Approved withdrawals
     * 2. Workshop registrations paid via wallet
     * 3. Transaction expenses (from transactions table)
     * 4. Starter kit purchases paid via wallet
     * 5. Loan repayments
     * 
     * @param User $user
     * @return float
     */
    public function calculateTotalExpenses(User $user): float
    {
        // Approved withdrawals
        $totalWithdrawals = (float) $user->withdrawals()
            ->where('status', 'approved')
            ->sum('amount');
        
        // Workshop expenses
        $workshopExpenses = (float) \App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
            ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
            ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
            ->sum('workshops.price');
        
        // Transaction expenses
        $transactionExpenses = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('transaction_type', 'withdrawal')
            ->sum('amount');
        
        // Starter kit purchases via wallet
        $starterKitExpenses = (float) DB::table('starter_kit_purchases')
            ->where('user_id', $user->id)
            ->where('payment_method', 'wallet')
            ->where('status', 'completed')
            ->sum('amount');
        
        // Loan repayments
        $loanRepayments = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'loan_repayment')
            ->where('status', 'completed')
            ->sum('amount');
        
        return $totalWithdrawals + $workshopExpenses + $transactionExpenses + $starterKitExpenses + $loanRepayments;
    }
    
    /**
     * Get wallet breakdown for display
     * 
     * @param User $user
     * @return array
     */
    public function getWalletBreakdown(User $user): array
    {
        $commissionEarnings = (float) $user->referralCommissions()->where('status', 'paid')->sum('amount');
        $profitEarnings = (float) $user->profitShares()->sum('amount');
        $walletTopups = (float) \App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->sum('amount');
        $transactionTopups = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'wallet_topup')
            ->where('status', 'completed')
            ->sum('amount');
        $loanDisbursements = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'loan_disbursement')
            ->where('status', 'completed')
            ->sum('amount');
        
        $totalWithdrawals = (float) $user->withdrawals()->where('status', 'approved')->sum('amount');
        $workshopExpenses = (float) \App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
            ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
            ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
            ->sum('workshops.price');
        $starterKitExpenses = (float) DB::table('starter_kit_purchases')
            ->where('user_id', $user->id)
            ->where('payment_method', 'wallet')
            ->where('status', 'completed')
            ->sum('amount');
        $loanRepayments = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'loan_repayment')
            ->where('status', 'completed')
            ->sum('amount');
        
        return [
            'earnings' => [
                'commissions' => $commissionEarnings,
                'profit_shares' => $profitEarnings,
                'topups' => $walletTopups + $transactionTopups,
                'loans' => $loanDisbursements,
                'total' => $commissionEarnings + $profitEarnings + $walletTopups + $transactionTopups + $loanDisbursements,
            ],
            'expenses' => [
                'withdrawals' => $totalWithdrawals,
                'workshops' => $workshopExpenses,
                'starter_kits' => $starterKitExpenses,
                'loan_repayments' => $loanRepayments,
                'total' => $totalWithdrawals + $workshopExpenses + $starterKitExpenses + $loanRepayments,
            ],
            'balance' => $this->calculateBalance($user),
        ];
    }
}

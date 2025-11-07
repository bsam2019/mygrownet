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
     * PRIMARY SOURCE: transactions table (single source of truth)
     * EXCLUDES: LGR awards (they stay in LGR balance until transferred)
     * 
     * @param User $user
     * @return float
     */
    public function calculateBalance(User $user): float
    {
        // Get balance from transactions table, EXCLUDING LGR transactions
        // LGR awards stay in LGR balance until user transfers them to wallet
        $transactionsBalance = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('transaction_type', 'NOT LIKE', '%lgr%')
            ->sum('amount');
        
        // Add old system data (commissions, profit shares) if not in transactions
        $commissionEarnings = (float) $user->referralCommissions()->where('status', 'paid')->sum('amount');
        $profitEarnings = (float) $user->profitShares()->sum('amount');
        
        // Workshop expenses (if not in transactions)
        $workshopExpenses = (float) \App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
            ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
            ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
            ->sum('workshops.price');
        
        return $transactionsBalance + $commissionEarnings + $profitEarnings - $workshopExpenses;
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
     * 1. Approved withdrawals (includes starter kit purchases via wallet_payment method)
     * 2. Workshop registrations paid via wallet
     * 3. Transaction expenses (from transactions table)
     * 4. Loan repayments
     * 
     * NOTE: Starter kit purchases are NOT counted separately because they are already
     * recorded as withdrawals with withdrawal_method='wallet_payment'
     * 
     * @param User $user
     * @return float
     */
    public function calculateTotalExpenses(User $user): float
    {
        // Approved withdrawals (includes starter kit wallet payments)
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
        
        // Loan repayments
        $loanRepayments = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'loan_repayment')
            ->where('status', 'completed')
            ->sum('amount');
        
        return $totalWithdrawals + $workshopExpenses + $transactionExpenses + $loanRepayments;
    }
    
    /**
     * Get wallet breakdown for display
     * 
     * Uses transactions table as primary source
     * 
     * @param User $user
     * @return array
     */
    public function getWalletBreakdown(User $user): array
    {
        // Get breakdown from transactions table
        $deposits = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->whereIn('transaction_type', ['deposit', 'wallet_topup'])
            ->where('status', 'completed')
            ->sum('amount');
        
        $lgrAwards = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'LIKE', '%lgr%')
            ->where('status', 'completed')
            ->sum('amount');
        
        $loanDisbursements = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'loan_disbursement')
            ->where('status', 'completed')
            ->sum('amount');
        
        $withdrawals = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'completed')
            ->sum('amount');
        
        $starterKits = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'LIKE', '%starter_kit%')
            ->where('status', 'completed')
            ->sum('amount');
        
        $loanRepayments = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'loan_repayment')
            ->where('status', 'completed')
            ->sum('amount');
        
        // Old system data (not yet in transactions)
        $commissionEarnings = (float) $user->referralCommissions()->where('status', 'paid')->sum('amount');
        $profitEarnings = (float) $user->profitShares()->sum('amount');
        
        $workshopExpenses = (float) \App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
            ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
            ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
            ->sum('workshops.price');
        
        return [
            'earnings' => [
                'commissions' => $commissionEarnings,
                'profit_shares' => $profitEarnings,
                'topups' => $deposits,
                'lgr' => $lgrAwards,
                'loans' => $loanDisbursements,
                'total' => $commissionEarnings + $profitEarnings + $deposits + $lgrAwards + $loanDisbursements,
            ],
            'expenses' => [
                'withdrawals' => abs($withdrawals),
                'starter_kits' => abs($starterKits),
                'workshops' => $workshopExpenses,
                'loan_repayments' => abs($loanRepayments),
                'total' => abs($withdrawals) + abs($starterKits) + $workshopExpenses + abs($loanRepayments),
            ],
            'balance' => $this->calculateBalance($user),
        ];
    }
}

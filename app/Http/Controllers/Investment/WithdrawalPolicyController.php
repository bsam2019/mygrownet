<?php

namespace App\Http\Controllers\Investment;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalPolicy;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WithdrawalPolicyController extends Controller
{
    protected const LOCK_IN_PERIOD = 12; // months
    protected const EARLY_WITHDRAWAL_PENALTY = 0.50; // 50% penalty
    protected const MAX_PARTIAL_WITHDRAWAL = 0.50; // 50% of profits

    public function requestWithdrawal(Request $request)
    {
        try {
            $request->validate([
                'investment_id' => 'required|exists:investments,id',
                'withdrawal_type' => 'required|in:early,full,partial',
                'amount' => 'required|numeric|min:0'
            ]);

            $investment = Investment::findOrFail($request->investment_id);
            
            // Validate withdrawal eligibility
            $this->validateWithdrawalEligibility($investment, $request->withdrawal_type, $request->amount);

            DB::beginTransaction();

            $withdrawalPolicy = WithdrawalPolicy::create([
                'investment_id' => $investment->id,
                'user_id' => auth()->id(),
                'withdrawal_type' => $request->withdrawal_type,
                'amount' => $request->amount,
                'penalty_amount' => $this->calculatePenalty($investment, $request->withdrawal_type, $request->amount),
                'status' => 'pending',
                'approval_status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Withdrawal request submitted successfully',
                'withdrawal' => $withdrawalPolicy
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function approveWithdrawal(Request $request, $withdrawalId)
    {
        try {
            $withdrawal = WithdrawalPolicy::findOrFail($withdrawalId);
            
            if (!auth()->user()->hasRole('admin')) {
                throw new \Exception('Unauthorized to approve withdrawals');
            }

            DB::beginTransaction();

            $withdrawal->update([
                'approval_status' => 'approved',
                'approved_by' => auth()->id(),
                'processed_at' => now()
            ]);

            // Process the withdrawal based on type
            $this->processWithdrawal($withdrawal);

            DB::commit();

            return response()->json([
                'message' => 'Withdrawal approved and processed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    protected function validateWithdrawalEligibility(Investment $investment, string $type, float $amount)
    {
        $investmentAge = Carbon::parse($investment->investment_date)->diffInMonths(now());
        $isLocked = $investmentAge < self::LOCK_IN_PERIOD;

        if ($type !== 'early' && $isLocked) {
            throw new \Exception('Investment is still within lock-in period');
        }

        if ($type === 'partial') {
            $maxPartialAmount = $this->calculateTotalProfits($investment) * self::MAX_PARTIAL_WITHDRAWAL;
            if ($amount > $maxPartialAmount) {
                throw new \Exception('Partial withdrawal amount exceeds maximum allowed');
            }
        }

        if ($type === 'full' && $amount !== $investment->amount) {
            throw new \Exception('Full withdrawal amount must equal investment amount');
        }
    }

    protected function calculatePenalty(Investment $investment, string $type, float $amount): float
    {
        if ($type === 'early') {
            return $amount * self::EARLY_WITHDRAWAL_PENALTY;
        }
        return 0;
    }

    protected function calculateTotalProfits(Investment $investment): float
    {
        return $investment->profitShares()
            ->sum(DB::raw('fixed_profit_amount + performance_bonus'));
    }

    protected function processWithdrawal(WithdrawalPolicy $withdrawal)
    {
        $investment = $withdrawal->investment;

        switch ($withdrawal->withdrawal_type) {
            case 'early':
            case 'full':
                $investment->update(['status' => 'withdrawn']);
                break;
            case 'partial':
                // Only reduce the investment amount for partial withdrawals
                $investment->update([
                    'amount' => $investment->amount - $withdrawal->amount
                ]);
                break;
        }

        $withdrawal->update(['status' => 'processed']);
    }

    public function getWithdrawalHistory($userId)
    {
        return response()->json(
            WithdrawalPolicy::where('user_id', $userId)
                ->with(['investment', 'approver'])
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }
}
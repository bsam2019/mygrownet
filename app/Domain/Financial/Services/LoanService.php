<?php

namespace App\Domain\Financial\Services;

use App\Domain\Financial\ValueObjects\LoanAmount;
use App\Models\User;
use App\Application\Notification\UseCases\SendNotificationUseCase;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * Issue a loan to a member
     */
    public function issueLoan(
        User $member,
        LoanAmount $amount,
        User $issuedBy,
        ?string $notes = null
    ): void {
        DB::transaction(function () use ($member, $amount, $issuedBy, $notes) {
            // Add to loan balance
            $member->loan_balance += $amount->value();
            $member->total_loan_issued += $amount->value();
            $member->loan_issued_at = now();
            $member->loan_issued_by = $issuedBy->id;
            $member->loan_notes = $notes;
            $member->save();

            // Credit wallet immediately via transaction
            // This will be included in wallet balance calculation
            DB::table('transactions')->insert([
                'user_id' => $member->id,
                'transaction_type' => 'wallet_topup',
                'amount' => $amount->value(),
                'status' => 'completed',
                'reference_number' => 'LOAN-' . strtoupper(uniqid()),
                'description' => "Loan issued by {$issuedBy->name}" . ($notes ? ": {$notes}" : ''),
                'processed_by' => $issuedBy->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Send notification to member
            try {
                $notificationService = app(SendNotificationUseCase::class);
                $notificationService->execute(
                    userId: $member->id,
                    type: 'wallet.loan.issued',
                    data: [
                        'title' => 'Loan Approved',
                        'message' => "💰 Your loan of K" . number_format($amount->value(), 2) . " has been approved and credited to your wallet. Automatic repayment will begin from future earnings.",
                        'amount' => 'K' . number_format($amount->value(), 2),
                        'issued_by' => $issuedBy->name,
                        'notes' => $notes,
                        'action_url' => route('mygrownet.wallet.index'),
                        'action_text' => 'View Wallet',
                        'priority' => 'high'
                    ]
                );
            } catch (\Exception $e) {
                \Log::warning('Failed to send loan notification', [
                    'user_id' => $member->id,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    /**
     * Repay loan from earnings
     */
    public function repayFromEarnings(User $member, LoanAmount $earningsAmount): LoanAmount
    {
        $loanBalance = LoanAmount::fromFloat($member->loan_balance);

        if ($loanBalance->isZero()) {
            return $earningsAmount; // No loan, return full earnings
        }

        // Calculate repayment amount (minimum of earnings or loan balance)
        $repaymentAmount = $earningsAmount->min($loanBalance);

        DB::transaction(function () use ($member, $repaymentAmount) {
            $member->loan_balance -= $repaymentAmount->value();
            $member->total_loan_repaid += $repaymentAmount->value();
            $member->save();

            // Log the repayment transaction
            DB::table('transactions')->insert([
                'user_id' => $member->id,
                'transaction_type' => 'loan_repayment',
                'amount' => $repaymentAmount->value(),
                'status' => 'completed',
                'reference_number' => 'REPAY-' . strtoupper(uniqid()),
                'description' => 'Automatic loan repayment from earnings',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        // Return remaining earnings after repayment
        try {
            return $earningsAmount->subtract($repaymentAmount);
        } catch (\InvalidArgumentException $e) {
            return LoanAmount::zero();
        }
    }

    /**
     * Check if member has outstanding loan
     */
    public function hasOutstandingLoan(User $member): bool
    {
        return $member->loan_balance > 0;
    }

    /**
     * Get loan repayment progress percentage
     */
    public function getRepaymentProgress(User $member): float
    {
        if ($member->total_loan_issued == 0) {
            return 100;
        }

        return ($member->total_loan_repaid / $member->total_loan_issued) * 100;
    }

    /**
     * Can member withdraw funds?
     */
    public function canWithdraw(User $member): bool
    {
        return !$this->hasOutstandingLoan($member);
    }

    /**
     * Get loan summary for member
     */
    public function getLoanSummary(User $member): array
    {
        return [
            'has_loan' => $this->hasOutstandingLoan($member),
            'loan_balance' => $member->loan_balance,
            'total_issued' => $member->total_loan_issued,
            'total_repaid' => $member->total_loan_repaid,
            'repayment_progress' => $this->getRepaymentProgress($member),
            'issued_at' => $member->loan_issued_at,
            'issued_by' => $member->loanIssuedBy,
            'notes' => $member->loan_notes,
            'can_withdraw' => $this->canWithdraw($member),
        ];
    }
}

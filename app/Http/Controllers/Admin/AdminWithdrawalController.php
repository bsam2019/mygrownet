<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalPolicy;
use App\Application\Notification\UseCases\SendNotificationUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalController extends Controller
{
    public function getPendingWithdrawals()
    {
        return response()->json(
            WithdrawalPolicy::where('approval_status', 'pending')
                ->with(['user', 'investment'])
                ->orderBy('created_at', 'asc')
                ->paginate(10)
        );
    }

    public function batchApproveWithdrawals(Request $request)
    {
        $request->validate([
            'withdrawal_ids' => 'required|array',
            'withdrawal_ids.*' => 'exists:withdrawal_policies,id'
        ]);

        try {
            DB::beginTransaction();

            $withdrawals = WithdrawalPolicy::whereIn('id', $request->withdrawal_ids)
                ->where('approval_status', 'pending')
                ->get();

            foreach ($withdrawals as $withdrawal) {
                $withdrawal->update([
                    'approval_status' => 'approved',
                    'approved_by' => auth()->id(),
                    'processed_at' => now()
                ]);

                // Process the withdrawal
                $this->processWithdrawal($withdrawal);
                
                // Send notification
                app(SendNotificationUseCase::class)->execute(
                    userId: $withdrawal->user_id,
                    type: 'wallet.withdrawal.approved',
                    data: [
                        'title' => 'Withdrawal Approved',
                        'message' => 'Your withdrawal request has been approved and will be processed within 24-48 hours',
                        'amount' => 'K' . number_format($withdrawal->amount, 2),
                        'action_url' => route('withdrawals.index'),
                        'action_text' => 'View Status'
                    ]
                );
            }

            DB::commit();
            return response()->json(['message' => 'Withdrawals processed successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function rejectWithdrawal(Request $request, $withdrawalId)
    {
        $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        try {
            $withdrawal = WithdrawalPolicy::findOrFail($withdrawalId);
            
            $withdrawal->update([
                'approval_status' => 'rejected',
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'processed_at' => now()
            ]);
            
            // Send notification
            app(SendNotificationUseCase::class)->execute(
                userId: $withdrawal->user_id,
                type: 'wallet.withdrawal.rejected',
                data: [
                    'title' => 'Withdrawal Rejected',
                    'message' => 'Your withdrawal request has been rejected',
                    'amount' => 'K' . number_format($withdrawal->amount, 2),
                    'reason' => $request->rejection_reason,
                    'action_url' => route('withdrawals.index'),
                    'action_text' => 'View Details'
                ]
            );

            return response()->json(['message' => 'Withdrawal rejected successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getWithdrawalStatistics()
    {
        $stats = [
            'pending_count' => WithdrawalPolicy::where('approval_status', 'pending')->count(),
            'pending_amount' => WithdrawalPolicy::where('approval_status', 'pending')->sum('amount'),
            'today_processed' => WithdrawalPolicy::whereDate('processed_at', today())
                ->where('status', 'processed')
                ->count(),
            'today_amount' => WithdrawalPolicy::whereDate('processed_at', today())
                ->where('status', 'processed')
                ->sum('amount'),
        ];

        return response()->json($stats);
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
                $investment->update([
                    'amount' => $investment->amount - $withdrawal->amount
                ]);
                break;
        }

        $withdrawal->update(['status' => 'processed']);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ActivityLog;
use App\Notifications\WithdrawalApproved;
use App\Notifications\WithdrawalRejected;
use Illuminate\Support\Facades\DB;

class WithdrawalApprovalController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Withdrawals/Index', [
            'withdrawals' => Transaction::where('transaction_type', 'withdrawal')
                ->with(['user', 'investment'])
                ->latest()
                ->paginate(15),
            'summary' => [
                'pending' => Transaction::where('transaction_type', 'withdrawal')
                    ->where('status', 'pending')
                    ->sum('amount'),
                'completed' => Transaction::where('transaction_type', 'withdrawal')
                    ->where('status', 'completed')
                    ->sum('amount'),
                'cancelled' => Transaction::where('transaction_type', 'withdrawal')
                    ->where('status', 'cancelled')
                    ->sum('amount')
            ]
        ]);
    }

    public function show(Transaction $withdrawal)
    {
        return Inertia::render('Admin/Withdrawals/Show', [
            'withdrawal' => $withdrawal->load([
                'user',
                'user.profile',
                'investment',
                'investment.category'
            ])
        ]);
    }

    public function approve($id)
    {
        try {
            $withdrawal = Transaction::findOrFail($id);
            
            if ($withdrawal->status !== 'pending') {
                return back()->with('error', 'This withdrawal cannot be approved.');
            }

            DB::beginTransaction();

            try {
                // Update withdrawal status
                $withdrawal->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'processed_by' => auth()->id()
                ]);

                // Update investment amount if investment exists
                if ($withdrawal->investment) {
                    $withdrawal->investment->update([
                        'amount' => $withdrawal->investment->amount - $withdrawal->amount
                    ]);
                }

                // Log activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'activity_type' => 'withdrawal_approval',
                    'action' => 'approve',
                    'description' => 'Approved withdrawal #' . $withdrawal->reference_number,
                    'data' => json_encode([
                        'withdrawal_id' => $withdrawal->id,
                        'amount' => $withdrawal->amount,
                        'user_id' => $withdrawal->user_id
                    ])
                ]);

                // Notify user
                $withdrawal->user->notify(new WithdrawalApproved($withdrawal));

                DB::commit();
                return back()->with('success', 'Withdrawal approved successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Withdrawal approval failed: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Withdrawal approval error: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve withdrawal: ' . $e->getMessage());
        }
    }

    public function reject($id, Request $request)
    {
        try {
            $withdrawal = Transaction::findOrFail($id);
            
            if ($withdrawal->status !== 'pending') {
                return back()->with('error', 'This withdrawal cannot be rejected.');
            }

            $request->validate([
                'reason' => 'required|string|max:255'
            ]);

            DB::beginTransaction();

            try {
                // Update withdrawal status
                $withdrawal->update([
                    'status' => 'rejected',
                    'notes' => $request->reason,
                    'processed_at' => now(),
                    'processed_by' => auth()->id()
                ]);

                // Log activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'activity_type' => 'withdrawal_rejection',
                    'action' => 'reject',
                    'description' => 'Rejected withdrawal #' . $withdrawal->reference_number,
                    'data' => json_encode([
                        'withdrawal_id' => $withdrawal->id,
                        'amount' => $withdrawal->amount,
                        'user_id' => $withdrawal->user_id,
                        'reason' => $request->reason
                    ])
                ]);

                // Notify user
                $withdrawal->user->notify(new WithdrawalRejected($withdrawal, $request->reason));

                DB::commit();
                return back()->with('success', 'Withdrawal rejected successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Withdrawal rejection failed: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Withdrawal rejection error: ' . $e->getMessage());
            return back()->with('error', 'Failed to reject withdrawal: ' . $e->getMessage());
        }
    }

    public function processMultiple(Request $request)
    {
        $request->validate([
            'withdrawals' => 'required|array',
            'withdrawals.*' => 'exists:transactions,id',
            'action' => 'required|in:approve,reject',
            'reason' => 'required_if:action,reject|string|max:255'
        ]);

        $successCount = 0;
        $failCount = 0;

        foreach ($request->withdrawals as $id) {
            $withdrawal = Transaction::find($id);

            if (!$withdrawal || $withdrawal->transaction_type !== 'withdrawal'
                || $withdrawal->status !== 'pending') {
                $failCount++;
                continue;
            }

            try {
                DB::beginTransaction();

                if ($request->action === 'approve') {
                    $withdrawal->update([
                        'status' => 'completed',
                        'processed_at' => now(),
                        'processed_by' => auth()->id()
                    ]);

                    $withdrawal->investment->update([
                        'amount' => $withdrawal->investment->amount - $withdrawal->amount
                    ]);

                    $withdrawal->user->notify(new WithdrawalApproved($withdrawal));
                } else {
                    $withdrawal->update([
                        'status' => 'rejected',
                        'notes' => $request->reason,
                        'processed_at' => now(),
                        'processed_by' => auth()->id()
                    ]);

                    $withdrawal->user->notify(new WithdrawalRejected($withdrawal));
                }

                DB::commit();
                $successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $failCount++;
            }
        }

        return back()->with('success', "Processed {$successCount} withdrawals successfully. Failed: {$failCount}");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
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
            'withdrawals' => Withdrawal::with('user')
                ->latest()
                ->paginate(15),
            'summary' => [
                'pending' => Withdrawal::where('status', 'pending')->sum('amount'),
                'approved' => Withdrawal::where('status', 'approved')->sum('amount'),
                'rejected' => Withdrawal::where('status', 'rejected')->sum('amount'),
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
            $withdrawal = Withdrawal::findOrFail($id);
            
            if ($withdrawal->status !== 'pending') {
                return back()->with('error', 'This withdrawal cannot be approved.');
            }

            DB::beginTransaction();

            try {
                // Update withdrawal status
                $withdrawal->update([
                    'status' => 'approved',
                    'processed_at' => now(),
                ]);

                // Log activity
                ActivityLog::create([
                    'loggable_type' => Withdrawal::class,
                    'loggable_id' => $withdrawal->id,
                    'user_id' => auth()->id(),
                    'activity_type' => 'withdrawal_approval',
                    'action' => 'approved',
                    'description' => 'Approved withdrawal of K' . number_format($withdrawal->amount, 2) . ' for ' . $withdrawal->user->name,
                ]);

                // Notify user (if notification exists)
                // $withdrawal->user->notify(new WithdrawalApproved($withdrawal));

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
            $withdrawal = Withdrawal::findOrFail($id);
            
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
                    'reason' => $request->reason,
                    'processed_at' => now(),
                ]);

                // Log activity
                ActivityLog::create([
                    'loggable_type' => Withdrawal::class,
                    'loggable_id' => $withdrawal->id,
                    'user_id' => auth()->id(),
                    'activity_type' => 'withdrawal_rejection',
                    'action' => 'rejected',
                    'description' => 'Rejected withdrawal of K' . number_format($withdrawal->amount, 2) . ' for ' . $withdrawal->user->name . '. Reason: ' . $request->reason,
                ]);

                // Notify user (if notification exists)
                // $withdrawal->user->notify(new WithdrawalRejected($withdrawal, $request->reason));

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

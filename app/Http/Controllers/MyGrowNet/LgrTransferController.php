<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Models\LgrSetting;
use App\Application\Notification\UseCases\SendNotificationUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LgrTransferController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check if user is blocked from LGR withdrawals
        if ($user->lgr_withdrawal_blocked) {
            return redirect()->back()->withErrors([
                'amount' => 'LGR withdrawals are currently restricted for your account. ' . 
                           ($user->lgr_restriction_reason ? 'Reason: ' . $user->lgr_restriction_reason : 'Please contact support.')
            ]);
        }
        
        $minAmount = LgrSetting::get('lgr_transfer_min_amount', 10);
        $maxAmount = LgrSetting::get('lgr_transfer_max_amount', 10000);
        $feePercentage = LgrSetting::get('lgr_transfer_fee_percentage', 0);

        $validated = $request->validate([
            'amount' => "required|numeric|min:{$minAmount}|max:{$maxAmount}",
        ]);

        $amount = $validated['amount'];

        if ($user->loyalty_points < $amount) {
            return redirect()->back()->withErrors(['amount' => 'Insufficient LGR balance']);
        }
        
        // Check against withdrawable limit
        $lgrWithdrawablePercentage = $user->lgr_custom_withdrawable_percentage 
            ?? LgrSetting::get('lgr_max_cash_conversion', 40);
        $lgrAwardedTotal = (float) ($user->loyalty_points_awarded_total ?? 0);
        $lgrWithdrawnTotal = (float) ($user->loyalty_points_withdrawn_total ?? 0);
        $lgrMaxWithdrawable = ($lgrAwardedTotal * $lgrWithdrawablePercentage / 100) - $lgrWithdrawnTotal;
        
        if ($amount > $lgrMaxWithdrawable) {
            return redirect()->back()->withErrors([
                'amount' => "You can only transfer up to K" . number_format($lgrMaxWithdrawable, 2) . 
                           " ({$lgrWithdrawablePercentage}% of awarded LGR)"
            ]);
        }

        DB::beginTransaction();
        try {
            $fee = ($amount * $feePercentage) / 100;
            $netAmount = $amount - $fee;

            $baseReference = 'LGR-' . strtoupper(uniqid('', true)) . '-' . time();

            // Deduct from loyalty points and track withdrawal
            $user->decrement('loyalty_points', $amount);
            $user->increment('loyalty_points_withdrawn_total', $amount);

            // Record LGR deduction
            DB::table('transactions')->insert([
                'user_id' => $user->id,
                'transaction_type' => 'lgr_transfer_out',
                'amount' => -$amount,
                'reference_number' => $baseReference . '-OUT',
                'description' => "LGR Transfer to Wallet",
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add to wallet as a credit (wallet balance is calculated from transactions)
            DB::table('transactions')->insert([
                'user_id' => $user->id,
                'transaction_type' => 'wallet_topup',
                'amount' => $netAmount,
                'reference_number' => $baseReference . '-IN',
                'description' => "LGR Transfer to Wallet" . ($fee > 0 ? " (Fee: K{$fee})" : ""),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            try {
                $notificationService = app(SendNotificationUseCase::class);
                $notificationService->execute(
                    userId: $user->id,
                    type: 'wallet.lgr_transfer.completed',
                    data: [
                        'title' => 'LGR Transfer Completed',
                        'message' => "Successfully transferred K{$netAmount} from LGR to your wallet",
                        'amount' => 'K' . number_format($netAmount, 2),
                        'action_url' => route('mygrownet.wallet.index'),
                        'action_text' => 'View Wallet',
                        'priority' => 'normal'
                    ]
                );
            } catch (\Exception $e) {
                \Log::warning('Failed to send notification', ['error' => $e->getMessage()]);
            }

            return redirect()->back()
                ->with('success', "Successfully transferred K{$netAmount} to your wallet");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('LGR Transfer Failed', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }
}

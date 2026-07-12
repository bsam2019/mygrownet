<?php

namespace App\Http\Controllers\Investment;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Transaction;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
        $this->middleware('auth');
    }
    public function index()
    {
        return Inertia::render('Withdrawals/Index', [
            'withdrawals' => auth()->user()->transactions()
                ->where('transaction_type', 'withdrawal')
                ->with('investment')
                ->latest()
                ->paginate(10),
            'investments' => auth()->user()->investments()
                ->where('status', 'active')
                ->with('category')
                ->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'investment_id' => 'required|exists:investments,id',
            'amount' => 'required|numeric|min:100',
            'withdrawal_method' => 'required|string|in:bank_transfer,mobile_money',
            'bank_name' => 'required_if:withdrawal_method,bank_transfer',
            'account_number' => 'required_if:withdrawal_method,bank_transfer',
            'mobile_number' => 'required_if:withdrawal_method,mobile_money',
            'mobile_provider' => 'required_if:withdrawal_method,mobile_money',
            'otp_code' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'otp_type' => 'required|in:email,sms'
        ]);

        $user = auth()->user();
        $investment = $user->investments()
            ->where('id', $request->investment_id)
            ->where('status', 'active')
            ->firstOrFail();

        // Verify OTP before processing withdrawal
        $otpResult = $this->otpService->verifyOtp(
            $user,
            $request->otp_code,
            $request->otp_type,
            'withdrawal'
        );

        if (!$otpResult['success']) {
            return back()->withErrors(['otp_code' => $otpResult['message']]);
        }

        // Check minimum investment duration (e.g., 30 days)
        if ($investment->created_at->diffInDays(now()) < 30) {
            return back()->with('error', 'Investment must be at least 30 days old to withdraw');
        }

        // Check available balance
        if ($investment->amount < $request->amount) {
            return back()->with('error', 'Insufficient investment balance');
        }

        // Create withdrawal transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'investment_id' => $investment->id,
            'amount' => $request->amount,
            'transaction_type' => 'withdrawal',
            'status' => 'pending',
            'reference_number' => 'WDR-' . strtoupper(Str::random(10)),
            'description' => 'Withdrawal request from investment #' . $investment->id,
            'payment_method' => $request->withdrawal_method,
            'payment_details' => array_filter([
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'mobile_number' => $request->mobile_number,
                'mobile_provider' => $request->mobile_provider
            ]),
            'metadata' => [
                'otp_verified' => true,
                'otp_token_id' => $otpResult['token_id'],
                'verification_ip' => $request->ip(),
                'verification_time' => now()->toISOString()
            ]
        ]);

        // Log the withdrawal request
        $user->recordActivity(
            'withdrawal_requested',
            "Withdrawal request of K{$request->amount} from investment #{$investment->id}"
        );

        return back()->with('success', 'Withdrawal request submitted successfully and is pending approval');
    }

    public function show(Transaction $withdrawal)
    {
        $this->authorize('view', $withdrawal);

        return Inertia::render('Withdrawals/Show', [
            'withdrawal' => $withdrawal->load(['investment', 'investment.category'])
        ]);
    }

    public function cancel(Transaction $withdrawal)
    {
        $this->authorize('cancel', $withdrawal);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Only pending withdrawals can be cancelled');
        }

        $withdrawal->update([
            'status' => 'cancelled',
            'notes' => 'Cancelled by user'
        ]);

        return back()->with('success', 'Withdrawal request cancelled successfully');
    }
}

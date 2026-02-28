<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Domain\Wallet\Services\UnifiedWalletService;
use App\Domain\Payment\Services\PaymentService;
use App\Domain\Payment\DTOs\CollectionRequest;
use App\Domain\Payment\DTOs\DisbursementRequest;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * General Wallet Controller
 * 
 * Provides wallet functionality for ALL user types:
 * - Clients (app users without MLM)
 * - Business accounts (SME users)
 * - Members (MLM participants - redirects to MyGrowNet wallet)
 * 
 * This is a simplified wallet focused on:
 * - Balance viewing
 * - Top-ups
 * - Withdrawals
 * - Transaction history
 */
class GeneralWalletController extends Controller
{
    public function __construct(
        private UnifiedWalletService $unifiedWalletService,
        private PaymentService $paymentService
    ) {}

    /**
     * Display the wallet dashboard
     * Now unified for ALL user types (members, clients, business)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Reset daily withdrawal limit if needed
        $this->resetDailyWithdrawalIfNeeded($user);
        
        // Get wallet breakdown using unified service for better account type support
        $breakdown = $this->unifiedWalletService->getWalletBreakdown($user);
        
        // Get verification limits
        $limits = $this->getVerificationLimits($user->verification_level ?? 'basic');
        $remainingDailyLimit = $limits['daily_withdrawal'] - ($user->daily_withdrawal_used ?? 0);
        
        // Get recent transactions using unified service
        $recentTransactions = $this->unifiedWalletService->getRecentTransactions($user, 15);
        
        return Inertia::render('Wallet/Dashboard', [
            'balance' => $breakdown['balance'],
            'bonusBalance' => (float) ($user->bonus_balance ?? 0),
            'totalDeposits' => $breakdown['credits']['deposits'],
            'totalWithdrawals' => $breakdown['debits']['withdrawals'],
            'totalExpenses' => $breakdown['debits']['expenses'],
            'ventureDividends' => $breakdown['credits']['venture_dividends'] ?? 0,
            'businessRevenue' => $breakdown['credits']['business_revenue'] ?? 0,
            'commissions' => $breakdown['credits']['commissions'] ?? 0, // MLM earnings
            'profitShares' => $breakdown['credits']['profit_shares'] ?? 0, // MLM earnings
            'recentTransactions' => $recentTransactions,
            'pendingWithdrawals' => (float) ($user->withdrawals()->where('status', 'pending')->sum('amount') ?? 0),
            'verificationLevel' => $user->verification_level ?? 'basic',
            'verificationLimits' => $limits,
            'remainingDailyLimit' => max(0, $remainingDailyLimit),
            'policyAccepted' => (bool) ($user->wallet_policy_accepted ?? false),
            'accountType' => $user->getPrimaryAccountType()?->value ?? 'client',
        ]);
    }

    /**
     * Accept wallet policy
     */
    public function acceptPolicy(Request $request)
    {
        $user = $request->user();
        
        $user->update([
            'wallet_policy_accepted' => true,
            'wallet_policy_accepted_at' => now(),
        ]);
        
        return back()->with('success', 'Wallet policy accepted successfully.');
    }
    
    /**
     * Get recent transactions for the user
     */
    private function getRecentTransactions($user): array
    {
        $transactions = collect();
        
        // Get wallet top-ups
        $topups = \App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($topup) {
                return [
                    'id' => 'topup_' . $topup->id,
                    'type' => 'deposit',
                    'amount' => (float) $topup->amount,
                    'status' => 'completed',
                    'date' => $topup->created_at->format('M d, Y'),
                    'description' => 'Wallet Top-up',
                    'created_at' => $topup->created_at,
                ];
            });
        
        // Get withdrawals
        $withdrawals = $user->withdrawals()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($withdrawal) {
                return [
                    'id' => 'withdrawal_' . $withdrawal->id,
                    'type' => 'withdrawal',
                    'amount' => (float) $withdrawal->amount,
                    'status' => $withdrawal->status,
                    'date' => $withdrawal->created_at->format('M d, Y'),
                    'description' => 'Withdrawal to ' . ($withdrawal->payment_method ?? 'Mobile Money'),
                    'created_at' => $withdrawal->created_at,
                ];
            });
        
        // Get general transactions
        $generalTransactions = $user->transactions()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($transaction) {
                $isCredit = in_array($transaction->transaction_type, ['deposit', 'credit', 'refund']);
                return [
                    'id' => 'txn_' . $transaction->id,
                    'type' => $isCredit ? 'credit' : 'debit',
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'date' => $transaction->created_at->format('M d, Y'),
                    'description' => $transaction->description ?? ucfirst($transaction->transaction_type),
                    'created_at' => $transaction->created_at,
                ];
            });
        
        return $transactions
            ->concat($topups)
            ->concat($withdrawals)
            ->concat($generalTransactions)
            ->sortByDesc('created_at')
            ->take(15)
            ->values()
            ->toArray();
    }
    
    /**
     * Get verification limits based on level
     */
    private function getVerificationLimits(string $level): array
    {
        return match($level) {
            'basic' => [
                'daily_withdrawal' => 1000,
                'monthly_withdrawal' => 10000,
                'single_transaction' => 500,
            ],
            'enhanced' => [
                'daily_withdrawal' => 5000,
                'monthly_withdrawal' => 50000,
                'single_transaction' => 2000,
            ],
            'premium' => [
                'daily_withdrawal' => 20000,
                'monthly_withdrawal' => 200000,
                'single_transaction' => 10000,
            ],
            default => [
                'daily_withdrawal' => 1000,
                'monthly_withdrawal' => 10000,
                'single_transaction' => 500,
            ],
        };
    }
    
    /**
     * Reset daily withdrawal limit if needed
     */
    private function resetDailyWithdrawalIfNeeded($user): void
    {
        $today = now()->toDateString();
        $resetDate = $user->daily_withdrawal_reset_date;
        
        if (!$resetDate || $resetDate !== $today) {
            $user->update([
                'daily_withdrawal_used' => 0,
                'daily_withdrawal_reset_date' => $today,
            ]);
        }
    }
    
    /**
     * Check withdrawal limit
     */
    public function checkWithdrawalLimit(Request $request)
    {
        $user = $request->user();
        $amount = (float) $request->input('amount', 0);
        
        $this->resetDailyWithdrawalIfNeeded($user);
        
        $limits = $this->getVerificationLimits($user->verification_level ?? 'basic');
        $remainingDaily = $limits['daily_withdrawal'] - ($user->daily_withdrawal_used ?? 0);
        
        $canWithdraw = $amount <= $remainingDaily && $amount <= $limits['single_transaction'];
        
        return response()->json([
            'canWithdraw' => $canWithdraw,
            'limits' => $limits,
            'remainingDaily' => max(0, $remainingDaily),
            'message' => $canWithdraw 
                ? 'Withdrawal amount is within limits.' 
                : 'Withdrawal amount exceeds your current limits.',
        ]);
    }

    /**
     * Show the top-up page
     * Unified for all user types
     */
    public function showTopUp(Request $request)
    {
        $user = $request->user();
        
        $breakdown = $this->unifiedWalletService->getWalletBreakdown($user);
        
        return Inertia::render('Wallet/TopUp', [
            'balance' => $breakdown['balance'],
            'paymentMethods' => [
                ['id' => 'mtn', 'name' => 'MTN Mobile Money', 'type' => 'mobile_money', 'provider' => 'mtn'],
                ['id' => 'airtel', 'name' => 'Airtel Money', 'type' => 'mobile_money', 'provider' => 'airtel'],
                ['id' => 'zamtel', 'name' => 'Zamtel Kwacha', 'type' => 'mobile_money', 'provider' => 'zamtel'],
            ],
        ]);
    }

    /**
     * Process a wallet top-up
     */
    public function processTopUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:50000',
            'payment_method' => 'required|string|in:mtn,airtel,zamtel',
            'phone_number' => 'required|string|regex:/^(09[567]\d{7}|07[567]\d{7})$/',
        ]);

        $user = $request->user();
        $amount = (float) $request->input('amount');
        $provider = $request->input('payment_method');
        $phoneNumber = $request->input('phone_number');
        
        // Generate unique reference
        $reference = 'TOPUP-' . strtoupper(Str::random(8)) . '-' . time();
        
        try {
            // Create collection request
            $collectionRequest = new CollectionRequest(
                phoneNumber: $phoneNumber,
                amount: $amount,
                currency: 'ZMW',
                provider: $provider,
                reference: $reference,
                description: 'Wallet Top-up',
                customerName: $user->name,
                customerEmail: $user->email,
                metadata: [
                    'user_id' => $user->id,
                    'type' => 'wallet_topup',
                ]
            );
            
            // Process collection via payment gateway
            $response = $this->paymentService->collect($collectionRequest);
            
            // Record the pending transaction
            DB::table('transactions')->insert([
                'user_id' => $user->id,
                'transaction_type' => 'wallet_topup',
                'amount' => $amount,
                'currency' => 'ZMW',
                'status' => $response->status->value,
                'reference' => $reference,
                'gateway_reference' => $response->transactionId,
                'payment_method' => $provider,
                'description' => 'Wallet Top-up via ' . strtoupper($provider),
                'metadata' => json_encode([
                    'phone_number' => $phoneNumber,
                    'gateway' => $this->paymentService->getDefaultGateway(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            if ($response->success) {
                // Clear wallet cache (will be refreshed when payment completes)
                $this->unifiedWalletService->clearCache($user);
                return back()->with('success', 'Top-up initiated! Please confirm the payment on your phone.');
            } else {
                return back()->with('error', $response->message ?? 'Failed to initiate top-up. Please try again.');
            }
            
        } catch (\Exception $e) {
            Log::error('Wallet top-up failed', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'An error occurred while processing your top-up. Please try again.');
        }
    }

    /**
     * Show the withdrawal page
     * Unified for all user types
     */
    public function showWithdraw(Request $request)
    {
        $user = $request->user();
        
        $this->resetDailyWithdrawalIfNeeded($user);
        
        $breakdown = $this->unifiedWalletService->getWalletBreakdown($user);
        $limits = $this->getVerificationLimits($user->verification_level ?? 'basic');
        $remainingDailyLimit = $limits['daily_withdrawal'] - ($user->daily_withdrawal_used ?? 0);
        
        return Inertia::render('Wallet/Withdraw', [
            'balance' => $breakdown['balance'],
            'remainingDailyLimit' => max(0, $remainingDailyLimit),
            'verificationLevel' => $user->verification_level ?? 'basic',
            'verificationLimits' => $limits,
            'withdrawalMethods' => [
                ['id' => 'mtn', 'name' => 'MTN Mobile Money', 'type' => 'mobile_money'],
                ['id' => 'airtel', 'name' => 'Airtel Money', 'type' => 'mobile_money'],
                ['id' => 'zamtel', 'name' => 'Zamtel Kwacha', 'type' => 'mobile_money'],
            ],
        ]);
    }

    /**
     * Process a withdrawal request
     */
    public function processWithdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'payment_method' => 'required|string|in:mtn,airtel,zamtel',
            'phone_number' => 'required|string|regex:/^(09[567]\d{7}|07[567]\d{7})$/',
            'account_name' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $amount = (float) $request->input('amount');
        $provider = $request->input('payment_method');
        $phoneNumber = $request->input('phone_number');
        $accountName = $request->input('account_name', $user->name);
        
        // Check balance
        $balance = $this->unifiedWalletService->calculateBalance($user);
        if ($amount > $balance) {
            return back()->with('error', 'Insufficient balance.');
        }
        
        // Check limits
        $this->resetDailyWithdrawalIfNeeded($user);
        $limits = $this->getVerificationLimits($user->verification_level ?? 'basic');
        $remainingDaily = $limits['daily_withdrawal'] - ($user->daily_withdrawal_used ?? 0);
        
        if ($amount > $remainingDaily) {
            return back()->with('error', "Amount exceeds your remaining daily limit of K{$remainingDaily}.");
        }
        
        if ($amount > $limits['single_transaction']) {
            return back()->with('error', "Amount exceeds single transaction limit of K{$limits['single_transaction']}.");
        }
        
        // Generate unique reference
        $reference = 'WD-' . strtoupper(Str::random(8)) . '-' . time();
        
        try {
            DB::beginTransaction();
            
            // Create withdrawal record
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'withdrawal_method' => $provider,
                'phone_number' => $phoneNumber,
                'reference' => $reference,
                'status' => 'pending',
                'reason' => 'Wallet withdrawal - ' . $accountName,
            ]);
            
            // Update daily withdrawal used
            $user->update([
                'daily_withdrawal_used' => ($user->daily_withdrawal_used ?? 0) + $amount,
            ]);
            
            // Record the transaction
            DB::table('transactions')->insert([
                'user_id' => $user->id,
                'transaction_type' => 'withdrawal',
                'amount' => -$amount, // Negative for debit
                'currency' => 'ZMW',
                'status' => 'pending',
                'reference' => $reference,
                'payment_method' => $provider,
                'description' => 'Withdrawal to ' . strtoupper($provider),
                'metadata' => json_encode([
                    'phone_number' => $phoneNumber,
                    'account_name' => $accountName,
                    'withdrawal_id' => $withdrawal->id,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            // Clear wallet cache after transaction
            $this->unifiedWalletService->clearCache($user);
            
            return redirect()->route('wallet.index')->with('success', 'Withdrawal request submitted successfully! It will be processed within 24 hours.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Wallet withdrawal failed', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'An error occurred while processing your withdrawal. Please try again.');
        }
    }

    /**
     * Show transaction history
     * Unified for all user types
     */
    public function history(Request $request)
    {
        $user = $request->user();
        
        $transactions = $this->unifiedWalletService->getRecentTransactions($user, 50);
        $breakdown = $this->unifiedWalletService->getWalletBreakdown($user);
        
        return Inertia::render('Wallet/History', [
            'transactions' => $transactions,
            'balance' => $breakdown['balance'],
            'totalDeposits' => $breakdown['credits']['deposits'],
            'totalWithdrawals' => $breakdown['debits']['withdrawals'],
        ]);
    }
}

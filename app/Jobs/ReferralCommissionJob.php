<?php

namespace App\Jobs;

use App\Domain\Reward\Services\ReferralMatrixService;
use App\Models\Investment;
use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\CommissionClawback;
use App\Models\WithdrawalRequest;
use App\Notifications\ReferralCommissionNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReferralCommissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes
    public $backoff = [30, 60, 120]; // Retry after 30s, 1min, 2min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $jobType,
        public ?int $investmentId = null,
        public ?int $withdrawalRequestId = null,
        public array $additionalData = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ReferralMatrixService $matrixService): void
    {
        try {
            Log::info("Starting {$this->jobType} referral commission processing", [
                'job_type' => $this->jobType,
                'investment_id' => $this->investmentId,
                'withdrawal_request_id' => $this->withdrawalRequestId
            ]);

            $result = match ($this->jobType) {
                'process_investment_commissions' => $this->processInvestmentCommissions($matrixService),
                'process_clawback' => $this->processCommissionClawback(),
                'batch_process_pending' => $this->batchProcessPendingCommissions(),
                default => throw new Exception("Invalid job type: {$this->jobType}")
            };

            if ($result['success']) {
                $this->sendSuccessNotifications($result);
                Log::info("Successfully completed {$this->jobType} processing", $result);
            } else {
                throw new Exception($result['error'] ?? 'Unknown error occurred during processing');
            }

        } catch (Exception $e) {
            Log::error("Failed to process {$this->jobType} referral commissions", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'investment_id' => $this->investmentId,
                'withdrawal_request_id' => $this->withdrawalRequestId
            ]);

            $this->sendFailureNotifications($e);
            throw $e;
        }
    }

    /**
     * Process referral commissions for a new investment
     */
    protected function processInvestmentCommissions(ReferralMatrixService $matrixService): array
    {
        if (!$this->investmentId) {
            throw new Exception('Investment ID is required for processing investment commissions');
        }

        $investment = Investment::with(['user.currentInvestmentTier', 'user.referrer'])->find($this->investmentId);
        
        if (!$investment) {
            throw new Exception("Investment not found: {$this->investmentId}");
        }

        try {
            DB::beginTransaction();

            $processedCommissions = [];
            $totalCommissionAmount = 0;

            // Process multi-level referral commissions
            $multiLevelResult = $this->processMultiLevelCommissions($investment);
            $processedCommissions = array_merge($processedCommissions, $multiLevelResult['commissions']);
            $totalCommissionAmount += $multiLevelResult['total_amount'];

            // Process matrix-based commissions
            $matrixResult = $matrixService->processMatrixCommissions($investment);
            if ($matrixResult['success']) {
                $processedCommissions = array_merge($processedCommissions, $matrixResult['commissions']);
                $totalCommissionAmount += $matrixResult['total_amount'];
            }

            DB::commit();

            return [
                'success' => true,
                'investment_id' => $this->investmentId,
                'commissions_processed' => count($processedCommissions),
                'total_commission_amount' => $totalCommissionAmount,
                'multi_level_commissions' => $multiLevelResult['commissions_count'],
                'matrix_commissions' => $matrixResult['commissions_processed'] ?? 0,
                'processed_commissions' => $processedCommissions
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process multi-level referral commissions (up to 3 levels)
     */
    protected function processMultiLevelCommissions(Investment $investment): array
    {
        $user = $investment->user;
        $commissions = [];
        $totalAmount = 0;

        // Start with direct referrer
        $currentReferrer = $user->referrer;
        $level = 1;

        while ($currentReferrer && $level <= 3) {
            $tier = $currentReferrer->currentInvestmentTier;
            
            if ($tier && $tier->isEligibleForReferralLevel($level)) {
                $commissionAmount = $tier->calculateMultiLevelReferralCommission($investment->amount, $level);
                
                if ($commissionAmount > 0) {
                    $commission = ReferralCommission::create([
                        'referrer_id' => $currentReferrer->id,
                        'referred_id' => $user->id,
                        'investment_id' => $investment->id,
                        'level' => $level,
                        'amount' => $commissionAmount,
                        'percentage' => $tier->getReferralRateForLevel($level),
                        'status' => 'pending',
                        'commission_type' => 'multi_level',
                        'tier_name' => $tier->name,
                        'created_at' => now()
                    ]);

                    $commissions[] = [
                        'commission_id' => $commission->id,
                        'referrer_id' => $currentReferrer->id,
                        'referrer_name' => $currentReferrer->name,
                        'level' => $level,
                        'amount' => $commissionAmount,
                        'tier' => $tier->name,
                        'status' => 'pending'
                    ];

                    $totalAmount += $commissionAmount;

                    // Record activity for referrer
                    $currentReferrer->recordActivity(
                        'referral_commission_earned',
                        "Earned Level {$level} referral commission: K{$commissionAmount} from {$user->name}"
                    );

                    // Update referrer's total referral earnings (pending)
                    $currentReferrer->increment('total_referral_earnings', $commissionAmount);
                }
            }

            // Move to next level
            $currentReferrer = $currentReferrer->referrer;
            $level++;
        }

        return [
            'commissions' => $commissions,
            'commissions_count' => count($commissions),
            'total_amount' => $totalAmount
        ];
    }

    /**
     * Process commission clawback for early withdrawals
     */
    protected function processCommissionClawback(): array
    {
        if (!$this->withdrawalRequestId) {
            throw new Exception('Withdrawal request ID is required for processing clawback');
        }

        $withdrawalRequest = WithdrawalRequest::with(['user', 'investment'])->find($this->withdrawalRequestId);
        
        if (!$withdrawalRequest) {
            throw new Exception("Withdrawal request not found: {$this->withdrawalRequestId}");
        }

        try {
            DB::beginTransaction();

            $clawbackResult = $this->calculateAndProcessClawback($withdrawalRequest);

            DB::commit();

            return [
                'success' => true,
                'withdrawal_request_id' => $this->withdrawalRequestId,
                'clawbacks_processed' => $clawbackResult['clawbacks_count'],
                'total_clawback_amount' => $clawbackResult['total_amount'],
                'affected_users' => $clawbackResult['affected_users'],
                'clawback_details' => $clawbackResult['clawbacks']
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate and process commission clawback
     */
    protected function calculateAndProcessClawback(WithdrawalRequest $withdrawalRequest): array
    {
        $user = $withdrawalRequest->user;
        $investment = $withdrawalRequest->investment;
        $withdrawalDate = $withdrawalRequest->created_at;
        $investmentDate = $investment->created_at;
        
        // Calculate time since investment
        $monthsSinceInvestment = $investmentDate->diffInMonths($withdrawalDate);
        
        // Determine clawback percentage based on withdrawal timing
        $clawbackPercentage = $this->getClawbackPercentage($monthsSinceInvestment);
        
        if ($clawbackPercentage === 0) {
            return [
                'clawbacks' => [],
                'clawbacks_count' => 0,
                'total_amount' => 0,
                'affected_users' => []
            ];
        }

        // Get all commissions paid for this user's investments
        $commissions = ReferralCommission::where('referred_id', $user->id)
            ->where('investment_id', $investment->id)
            ->where('status', 'paid')
            ->with('referrer')
            ->get();

        $clawbacks = [];
        $totalClawbackAmount = 0;
        $affectedUsers = [];

        foreach ($commissions as $commission) {
            $clawbackAmount = $commission->amount * ($clawbackPercentage / 100);
            
            if ($clawbackAmount > 0) {
                // Create clawback record
                $clawback = CommissionClawback::create([
                    'referral_commission_id' => $commission->id,
                    'user_id' => $commission->referrer_id,
                    'original_amount' => $commission->amount,
                    'clawback_amount' => $clawbackAmount,
                    'clawback_percentage' => $clawbackPercentage,
                    'reason' => "Early withdrawal within {$monthsSinceInvestment} months",
                    'withdrawal_request_id' => $withdrawalRequest->id,
                    'processed_at' => now()
                ]);

                // Deduct from referrer's earnings
                $referrer = $commission->referrer;
                $referrer->decrement('total_referral_earnings', $clawbackAmount);

                // Create debt record if referrer has insufficient balance
                if ($referrer->total_referral_earnings < 0) {
                    $referrer->update(['referral_debt' => abs($referrer->total_referral_earnings)]);
                    $referrer->update(['total_referral_earnings' => 0]);
                }

                $clawbacks[] = [
                    'clawback_id' => $clawback->id,
                    'referrer_id' => $commission->referrer_id,
                    'referrer_name' => $referrer->name,
                    'original_amount' => $commission->amount,
                    'clawback_amount' => $clawbackAmount,
                    'level' => $commission->level
                ];

                $totalClawbackAmount += $clawbackAmount;
                $affectedUsers[] = $commission->referrer_id;

                // Record activity for affected referrer
                $referrer->recordActivity(
                    'commission_clawback_processed',
                    "Commission clawback: K{$clawbackAmount} due to early withdrawal by {$user->name}"
                );
            }
        }

        return [
            'clawbacks' => $clawbacks,
            'clawbacks_count' => count($clawbacks),
            'total_amount' => $totalClawbackAmount,
            'affected_users' => array_unique($affectedUsers)
        ];
    }

    /**
     * Get clawback percentage based on withdrawal timing
     */
    protected function getClawbackPercentage(int $monthsSinceInvestment): float
    {
        return match (true) {
            $monthsSinceInvestment <= 1 => 50.0, // 50% clawback for 0-1 month
            $monthsSinceInvestment <= 3 => 25.0, // 25% clawback for 1-3 months
            default => 0.0 // No clawback after 3 months
        };
    }

    /**
     * Batch process pending commissions
     */
    protected function batchProcessPendingCommissions(): array
    {
        $batchSize = $this->additionalData['batch_size'] ?? 100;
        $maxAge = $this->additionalData['max_age_days'] ?? 7; // Process commissions older than 7 days
        
        $cutoffDate = Carbon::now()->subDays($maxAge);
        
        $pendingCommissions = ReferralCommission::where('status', 'pending')
            ->where('created_at', '<=', $cutoffDate)
            ->with('referrer')
            ->limit($batchSize)
            ->get();

        if ($pendingCommissions->isEmpty()) {
            return [
                'success' => true,
                'processed_count' => 0,
                'total_amount' => 0,
                'message' => 'No pending commissions to process'
            ];
        }

        try {
            DB::beginTransaction();

            $processedCount = 0;
            $totalAmount = 0;
            $processedCommissions = [];

            foreach ($pendingCommissions as $commission) {
                // Update commission status
                $commission->update([
                    'status' => 'paid',
                    'processed_at' => now()
                ]);

                $processedCommissions[] = [
                    'commission_id' => $commission->id,
                    'referrer_id' => $commission->referrer_id,
                    'amount' => $commission->amount,
                    'level' => $commission->level
                ];

                $processedCount++;
                $totalAmount += $commission->amount;

                // Record activity for referrer
                $commission->referrer?->recordActivity(
                    'commission_payment_processed',
                    "Commission payment processed: K{$commission->amount}"
                );
            }

            DB::commit();

            return [
                'success' => true,
                'processed_count' => $processedCount,
                'total_amount' => $totalAmount,
                'processed_commissions' => $processedCommissions
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Send success notifications
     */
    protected function sendSuccessNotifications(array $result): void
    {
        try {
            match ($this->jobType) {
                'process_investment_commissions' => $this->notifyInvestmentCommissionSuccess($result),
                'process_clawback' => $this->notifyClawbackSuccess($result),
                'batch_process_pending' => $this->notifyBatchProcessSuccess($result),
            };
        } catch (Exception $e) {
            Log::warning("Failed to send success notifications for {$this->jobType}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify about investment commission success
     */
    protected function notifyInvestmentCommissionSuccess(array $result): void
    {
        if (empty($result['processed_commissions'])) {
            return;
        }

        foreach ($result['processed_commissions'] as $commission) {
            $referrer = User::find($commission['referrer_id']);
            if ($referrer) {
                $referrer->notify(new ReferralCommissionNotification([
                    'type' => 'commission_earned',
                    'amount' => $commission['amount'],
                    'level' => $commission['level'],
                    'investment_id' => $this->investmentId,
                    'commission_id' => $commission['commission_id'] ?? null
                ]));
            }
        }
    }

    /**
     * Notify about clawback success
     */
    protected function notifyClawbackSuccess(array $result): void
    {
        foreach ($result['affected_users'] as $userId) {
            $user = User::find($userId);
            if ($user) {
                $userClawbacks = collect($result['clawback_details'])
                    ->where('referrer_id', $userId);
                
                $totalClawback = $userClawbacks->sum('clawback_amount');
                
                $user->notify(new ReferralCommissionNotification([
                    'type' => 'commission_clawback',
                    'clawback_amount' => $totalClawback,
                    'withdrawal_request_id' => $this->withdrawalRequestId,
                    'clawback_count' => $userClawbacks->count()
                ]));
            }
        }
    }

    /**
     * Notify about batch process success
     */
    protected function notifyBatchProcessSuccess(array $result): void
    {
        // Notify admins about batch processing completion
        $admins = User::role('admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new ReferralCommissionNotification([
                'type' => 'batch_process_complete',
                'processed_count' => $result['processed_count'],
                'total_amount' => $result['total_amount']
            ]));
        }
    }

    /**
     * Send failure notifications
     */
    protected function sendFailureNotifications(Exception $exception): void
    {
        try {
            $admins = User::role('admin')->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new ReferralCommissionNotification([
                    'type' => 'processing_failure',
                    'job_type' => $this->jobType,
                    'error_message' => $exception->getMessage(),
                    'investment_id' => $this->investmentId,
                    'withdrawal_request_id' => $this->withdrawalRequestId
                ]));
            }
        } catch (Exception $e) {
            Log::error("Failed to send failure notifications for {$this->jobType}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::critical("ReferralCommissionJob failed permanently", [
            'job_type' => $this->jobType,
            'investment_id' => $this->investmentId,
            'withdrawal_request_id' => $this->withdrawalRequestId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Send critical failure notification
        try {
            $admins = User::role('admin')->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new ReferralCommissionNotification([
                    'type' => 'critical_failure',
                    'job_type' => $this->jobType,
                    'error_message' => $exception->getMessage(),
                    'investment_id' => $this->investmentId,
                    'withdrawal_request_id' => $this->withdrawalRequestId,
                    'attempts' => $this->attempts()
                ]));
            }
        } catch (Exception $e) {
            Log::error("Failed to send critical failure notification", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        $tags = ['referral-commission', $this->jobType];
        
        if ($this->investmentId) {
            $tags[] = "investment:{$this->investmentId}";
        }
        
        if ($this->withdrawalRequestId) {
            $tags[] = "withdrawal:{$this->withdrawalRequestId}";
        }
        
        return $tags;
    }
}
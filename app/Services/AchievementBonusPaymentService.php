<?php

namespace App\Services;

use App\Models\User;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\InvestmentTier;
use App\Models\PaymentTransaction;
use App\Models\AchievementBonus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class AchievementBonusPaymentService
{
    protected MobileMoneyService $mobileMoneyService;
    
    public function __construct(MobileMoneyService $mobileMoneyService)
    {
        $this->mobileMoneyService = $mobileMoneyService;
    }

    /**
     * Process all pending achievement bonus payments
     */
    public function processAllPendingBonuses(): array
    {
        $results = [
            'total_processed' => 0,
            'successful_payments' => 0,
            'failed_payments' => 0,
            'total_amount' => 0,
            'errors' => []
        ];

        try {
            // Get all pending achievement bonuses
            $pendingBonuses = $this->getPendingAchievementBonuses();
            
            foreach ($pendingBonuses as $bonus) {
                $result = $this->processAchievementBonusPayment($bonus);
                
                $results['total_processed']++;
                $results['total_amount'] += $result['amount'];
                
                if ($result['success']) {
                    $results['successful_payments']++;
                } else {
                    $results['failed_payments']++;
                    if (!empty($result['error'])) {
                        $results['errors'][] = $result['error'];
                    }
                }
            }

            Log::info('Achievement bonus payment batch processing completed', $results);

        } catch (Exception $e) {
            Log::error('Achievement bonus payment batch processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $results['errors'][] = 'Batch processing failed: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Process achievement bonus payment for a specific bonus
     */
    public function processAchievementBonusPayment(AchievementBonus $bonus): array
    {
        $user = $bonus->user;
        $achievement = $bonus->achievement;
        
        $result = [
            'success' => false,
            'amount' => $bonus->amount,
            'error' => null
        ];

        try {
            DB::beginTransaction();

            // Validate bonus eligibility
            if (!$this->validateBonusEligibility($bonus)) {
                $result['error'] = 'Bonus is not eligible for payment';
                DB::rollBack();
                return $result;
            }

            // Try balance payment first, then mobile money
            if ($this->shouldUseDirectDeposit($bonus)) {
                $success = $this->processDirectDepositPayment($bonus);
                $result['payment_method'] = 'direct_deposit';
            } else {
                $paymentResult = $this->processMobileMoneyBonusPayment($bonus);
                $success = $paymentResult['success'];
                $result['payment_method'] = 'mobile_money';
                
                if (!$success) {
                    $result['error'] = $paymentResult['error'] ?? 'Mobile money payment failed';
                }
            }

            if ($success) {
                // Mark bonus as paid
                $bonus->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $result['payment_method']
                ]);

                // Update user balance and earnings
                $user->increment('balance', $bonus->amount);
                $user->increment('total_earnings', $bonus->amount);

                // Record achievement bonus activity
                $user->recordActivity(
                    'achievement_bonus_received',
                    "Received K{$bonus->amount} bonus for {$achievement->name} achievement"
                );

                $result['success'] = true;
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            
            $result['error'] = "Achievement bonus payment exception: " . $e->getMessage();
            
            Log::error('Achievement bonus payment processing failed', [
                'bonus_id' => $bonus->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Process tier advancement bonuses
     */
    public function processTierAdvancementBonuses(User $user, InvestmentTier $newTier, InvestmentTier $oldTier = null): array
    {
        $results = [
            'bonuses_created' => 0,
            'total_amount' => 0,
            'errors' => []
        ];

        try {
            DB::beginTransaction();

            // Get tier advancement achievements
            $tierAchievements = Achievement::where('category', 'tier_advancement')
                ->where('tier_requirement', $newTier->name)
                ->get();

            foreach ($tierAchievements as $achievement) {
                // Check if user already has this achievement
                $existingAchievement = UserAchievement::where('user_id', $user->id)
                    ->where('achievement_id', $achievement->id)
                    ->first();

                if (!$existingAchievement) {
                    // Award achievement
                    $userAchievement = UserAchievement::create([
                        'user_id' => $user->id,
                        'achievement_id' => $achievement->id,
                        'earned_at' => now(),
                        'tier_at_earning' => $newTier->name,
                        'progress' => 100,
                        'times_earned' => 1
                    ]);

                    // Create achievement bonus
                    $bonus = $this->createAchievementBonus($user, $achievement, 'tier_advancement');
                    
                    if ($bonus) {
                        $results['bonuses_created']++;
                        $results['total_amount'] += $bonus->amount;
                    }
                }
            }

            // Process tier-specific bonuses
            $tierBonus = $this->calculateTierAdvancementBonus($newTier, $oldTier);
            if ($tierBonus > 0) {
                $bonus = AchievementBonus::create([
                    'user_id' => $user->id,
                    'achievement_id' => null, // Direct tier bonus
                    'bonus_type' => 'tier_advancement',
                    'amount' => $tierBonus,
                    'status' => 'pending',
                    'earned_at' => now(),
                    'tier_at_earning' => $newTier->name,
                    'description' => "Tier advancement bonus for reaching {$newTier->name}"
                ]);

                $results['bonuses_created']++;
                $results['total_amount'] += $tierBonus;
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            
            $results['errors'][] = "Tier advancement bonus processing failed: " . $e->getMessage();
            
            Log::error('Tier advancement bonus processing failed', [
                'user_id' => $user->id,
                'new_tier' => $newTier->name,
                'old_tier' => $oldTier?->name,
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Process performance bonuses based on team volume
     */
    public function processPerformanceBonuses(User $user, float $teamVolume, string $period = 'monthly'): array
    {
        $results = [
            'bonuses_created' => 0,
            'total_amount' => 0,
            'errors' => []
        ];

        try {
            DB::beginTransaction();

            // Get performance achievements based on team volume
            $performanceAchievements = Achievement::where('category', 'performance')
                ->where('volume_requirement', '<=', $teamVolume)
                ->orderBy('volume_requirement', 'desc')
                ->get();

            foreach ($performanceAchievements as $achievement) {
                // Check if user earned this achievement in the current period
                $periodStart = $this->getPeriodStart($period);
                
                $existingBonus = AchievementBonus::where('user_id', $user->id)
                    ->where('achievement_id', $achievement->id)
                    ->where('earned_at', '>=', $periodStart)
                    ->first();

                if (!$existingBonus) {
                    // Create performance bonus
                    $bonusAmount = $this->calculatePerformanceBonusAmount($achievement, $teamVolume);
                    
                    if ($bonusAmount > 0) {
                        $bonus = AchievementBonus::create([
                            'user_id' => $user->id,
                            'achievement_id' => $achievement->id,
                            'bonus_type' => 'performance',
                            'amount' => $bonusAmount,
                            'status' => 'pending',
                            'earned_at' => now(),
                            'tier_at_earning' => $user->membershipTier?->name,
                            'team_volume_at_earning' => $teamVolume,
                            'description' => "Performance bonus for {$achievement->name} (K{$teamVolume} team volume)"
                        ]);

                        $results['bonuses_created']++;
                        $results['total_amount'] += $bonusAmount;
                    }
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            
            $results['errors'][] = "Performance bonus processing failed: " . $e->getMessage();
            
            Log::error('Performance bonus processing failed', [
                'user_id' => $user->id,
                'team_volume' => $teamVolume,
                'period' => $period,
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Process leadership bonuses
     */
    public function processLeadershipBonuses(User $user, int $activeReferrals, float $teamVolume): array
    {
        $results = [
            'bonuses_created' => 0,
            'total_amount' => 0,
            'errors' => []
        ];

        try {
            DB::beginTransaction();

            // Determine leadership level
            $leadershipLevel = $this->determineLeadershipLevel($activeReferrals, $teamVolume);
            
            if ($leadershipLevel) {
                // Get leadership achievements
                $leadershipAchievements = Achievement::where('category', 'leadership')
                    ->where('leadership_level', $leadershipLevel)
                    ->get();

                foreach ($leadershipAchievements as $achievement) {
                    // Check if user already has this leadership achievement
                    $existingAchievement = UserAchievement::where('user_id', $user->id)
                        ->where('achievement_id', $achievement->id)
                        ->first();

                    if (!$existingAchievement) {
                        // Award leadership achievement
                        UserAchievement::create([
                            'user_id' => $user->id,
                            'achievement_id' => $achievement->id,
                            'earned_at' => now(),
                            'tier_at_earning' => $user->membershipTier?->name,
                            'progress' => 100,
                            'times_earned' => 1
                        ]);

                        // Create leadership bonus
                        $bonusAmount = $this->calculateLeadershipBonusAmount($leadershipLevel, $teamVolume);
                        
                        if ($bonusAmount > 0) {
                            $bonus = AchievementBonus::create([
                                'user_id' => $user->id,
                                'achievement_id' => $achievement->id,
                                'bonus_type' => 'leadership',
                                'amount' => $bonusAmount,
                                'status' => 'pending',
                                'earned_at' => now(),
                                'tier_at_earning' => $user->membershipTier?->name,
                                'team_volume_at_earning' => $teamVolume,
                                'active_referrals_at_earning' => $activeReferrals,
                                'description' => "Leadership bonus for {$leadershipLevel} level"
                            ]);

                            $results['bonuses_created']++;
                            $results['total_amount'] += $bonusAmount;
                        }
                    }
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            
            $results['errors'][] = "Leadership bonus processing failed: " . $e->getMessage();
            
            Log::error('Leadership bonus processing failed', [
                'user_id' => $user->id,
                'active_referrals' => $activeReferrals,
                'team_volume' => $teamVolume,
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Get pending achievement bonuses
     */
    protected function getPendingAchievementBonuses(): Collection
    {
        return AchievementBonus::with(['user', 'achievement'])
            ->where('status', 'pending')
            ->where('earned_at', '<=', now()->subHours(24)) // 24-hour delay like commissions
            ->whereHas('user', function ($query) {
                $query->where('is_blocked', false);
            })
            ->get();
    }

    /**
     * Validate bonus eligibility
     */
    protected function validateBonusEligibility(AchievementBonus $bonus): bool
    {
        $user = $bonus->user;

        // Check if user is active and not blocked
        if ($user->is_blocked || !$user->hasActiveSubscription()) {
            return false;
        }

        // Check if bonus is still pending
        if ($bonus->status !== 'pending') {
            return false;
        }

        // Check if bonus amount is valid
        if ($bonus->amount <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Determine if should use direct deposit (balance) vs mobile money
     */
    protected function shouldUseDirectDeposit(AchievementBonus $bonus): bool
    {
        // For achievement bonuses, always use direct deposit to user balance
        // This is faster and more reliable than mobile money for bonuses
        return true;
    }

    /**
     * Process direct deposit payment (add to user balance)
     */
    protected function processDirectDepositPayment(AchievementBonus $bonus): bool
    {
        try {
            // Create payment transaction record
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $bonus->user_id,
                'type' => 'bonus_payment',
                'amount' => $bonus->amount,
                'status' => 'completed',
                'payment_method' => 'wallet',
                'payment_details' => [
                    'bonus_id' => $bonus->id,
                    'bonus_type' => $bonus->bonus_type,
                    'achievement_id' => $bonus->achievement_id,
                    'achievement_name' => $bonus->achievement?->name
                ],
                'reference' => $this->generateBonusReference($bonus->id),
                'completed_at' => now()
            ]);

            // Update bonus with payment transaction
            $bonus->update([
                'payment_transaction_id' => $paymentTransaction->id
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Direct deposit payment failed', [
                'bonus_id' => $bonus->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Process mobile money bonus payment
     */
    protected function processMobileMoneyBonusPayment(AchievementBonus $bonus): array
    {
        $user = $bonus->user;

        // Validate user payment details
        if (!$this->validateUserPaymentDetails($user)) {
            return [
                'success' => false,
                'error' => 'Invalid payment details'
            ];
        }

        try {
            // Create payment transaction record
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'type' => 'bonus_payment',
                'amount' => $bonus->amount,
                'status' => 'pending',
                'payment_method' => 'mobile_money',
                'payment_details' => [
                    'phone_number' => $user->phone_number,
                    'bonus_id' => $bonus->id,
                    'bonus_type' => $bonus->bonus_type,
                    'achievement_id' => $bonus->achievement_id
                ],
                'reference' => $this->generateBonusReference($bonus->id)
            ]);

            // Process mobile money payment
            $paymentResult = $this->mobileMoneyService->sendPayment([
                'phone_number' => $user->phone_number,
                'amount' => $bonus->amount,
                'reference' => $paymentTransaction->reference,
                'description' => "MyGrowNet achievement bonus - K{$bonus->amount}",
                'recipient_name' => $user->name
            ]);

            if ($paymentResult['success']) {
                // Update payment transaction
                $paymentTransaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'external_reference' => $paymentResult['external_reference'] ?? null,
                    'payment_response' => $paymentResult
                ]);

                // Update bonus with payment transaction
                $bonus->update([
                    'payment_transaction_id' => $paymentTransaction->id
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $paymentTransaction->id,
                    'external_reference' => $paymentResult['external_reference'] ?? null
                ];
            } else {
                // Mark payment as failed
                $paymentTransaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $paymentResult['error'] ?? 'Unknown error',
                    'payment_response' => $paymentResult
                ]);

                return [
                    'success' => false,
                    'error' => $paymentResult['error'] ?? 'Mobile money payment failed',
                    'transaction_id' => $paymentTransaction->id
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Payment processing exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create achievement bonus
     */
    protected function createAchievementBonus(User $user, Achievement $achievement, string $bonusType): ?AchievementBonus
    {
        $bonusAmount = $this->calculateAchievementBonusAmount($achievement, $user);
        
        if ($bonusAmount <= 0) {
            return null;
        }

        return AchievementBonus::create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'bonus_type' => $bonusType,
            'amount' => $bonusAmount,
            'status' => 'pending',
            'earned_at' => now(),
            'tier_at_earning' => $user->membershipTier?->name,
            'description' => "Achievement bonus for {$achievement->name}"
        ]);
    }

    /**
     * Calculate achievement bonus amount
     */
    protected function calculateAchievementBonusAmount(Achievement $achievement, User $user): float
    {
        // Base bonus amount from achievement
        $baseAmount = $achievement->bonus_amount ?? 0;
        
        // Apply tier multiplier
        $tierMultiplier = $this->getTierBonusMultiplier($user->membershipTier);
        
        return $baseAmount * $tierMultiplier;
    }

    /**
     * Calculate tier advancement bonus
     */
    protected function calculateTierAdvancementBonus(InvestmentTier $newTier, ?InvestmentTier $oldTier): float
    {
        // Tier advancement bonuses from config
        $tierBonuses = config('mygrownet.tier_advancement_bonuses', [
            'Bronze' => 100.00,
            'Silver' => 250.00,
            'Gold' => 500.00,
            'Diamond' => 1000.00,
            'Elite' => 2500.00
        ]);

        return $tierBonuses[$newTier->name] ?? 0;
    }

    /**
     * Calculate performance bonus amount
     */
    protected function calculatePerformanceBonusAmount(Achievement $achievement, float $teamVolume): float
    {
        $baseAmount = $achievement->bonus_amount ?? 0;
        $volumeMultiplier = min(2.0, $teamVolume / 100000); // Max 2x multiplier at K100,000
        
        return $baseAmount * $volumeMultiplier;
    }

    /**
     * Calculate leadership bonus amount
     */
    protected function calculateLeadershipBonusAmount(string $leadershipLevel, float $teamVolume): float
    {
        $leadershipBonuses = config('mygrownet.leadership_bonuses', [
            'developing_leader' => 0.5, // 0.5% of team volume
            'gold_leader' => 1.0,       // 1.0% of team volume
            'diamond_leader' => 1.5,    // 1.5% of team volume
            'elite_leader' => 2.0       // 2.0% of team volume
        ]);

        $percentage = $leadershipBonuses[$leadershipLevel] ?? 0;
        return $teamVolume * ($percentage / 100);
    }

    /**
     * Determine leadership level based on performance
     */
    protected function determineLeadershipLevel(int $activeReferrals, float $teamVolume): ?string
    {
        if ($activeReferrals >= 50 && $teamVolume >= 500000) {
            return 'elite_leader';
        } elseif ($activeReferrals >= 25 && $teamVolume >= 250000) {
            return 'diamond_leader';
        } elseif ($activeReferrals >= 10 && $teamVolume >= 100000) {
            return 'gold_leader';
        } elseif ($activeReferrals >= 5 && $teamVolume >= 25000) {
            return 'developing_leader';
        }

        return null;
    }

    /**
     * Get tier bonus multiplier
     */
    protected function getTierBonusMultiplier(?InvestmentTier $tier): float
    {
        if (!$tier) {
            return 1.0;
        }

        $multipliers = [
            'Bronze' => 1.0,
            'Silver' => 1.2,
            'Gold' => 1.5,
            'Diamond' => 2.0,
            'Elite' => 2.5
        ];

        return $multipliers[$tier->name] ?? 1.0;
    }

    /**
     * Get period start date
     */
    protected function getPeriodStart(string $period): Carbon
    {
        return match($period) {
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'quarterly' => now()->startOfQuarter(),
            'yearly' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
    }

    /**
     * Validate user payment details
     */
    protected function validateUserPaymentDetails(User $user): bool
    {
        // Check if user has valid phone number for mobile money
        if (empty($user->phone_number)) {
            return false;
        }

        // Validate phone number format (Zambian format)
        if (!preg_match('/^(\+260|0)?[79]\d{8}$/', $user->phone_number)) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique bonus payment reference
     */
    protected function generateBonusReference(int $bonusId): string
    {
        return 'MGN-BONUS-' . $bonusId . '-' . now()->format('YmdHis') . '-' . rand(1000, 9999);
    }

    /**
     * Get achievement bonus statistics
     */
    public function getBonusStatistics(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStart($period);

        $bonuses = AchievementBonus::where('earned_at', '>=', $startDate)->get();
        $payments = PaymentTransaction::where('type', 'bonus_payment')
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_bonuses' => $bonuses->count(),
            'paid_bonuses' => $bonuses->where('status', 'paid')->count(),
            'pending_bonuses' => $bonuses->where('status', 'pending')->count(),
            'total_amount' => $bonuses->sum('amount'),
            'paid_amount' => $payments->where('status', 'completed')->sum('amount'),
            'success_rate' => $bonuses->count() > 0 
                ? ($bonuses->where('status', 'paid')->count() / $bonuses->count()) * 100 
                : 0,
            'by_type' => $bonuses->groupBy('bonus_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                    'paid' => $group->where('status', 'paid')->count()
                ];
            }),
            'by_tier' => $bonuses->groupBy('tier_at_earning')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount')
                ];
            })
        ];
    }
}
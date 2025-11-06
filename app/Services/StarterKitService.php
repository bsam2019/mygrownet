<?php

namespace App\Services;

use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel;
use App\Models\StarterKitUnlock;
use App\Models\MemberAchievement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StarterKitService
{
    public function __construct(
        private readonly \App\Application\Notification\UseCases\SendNotificationUseCase $notificationService,
        private readonly WalletService $walletService
    ) {}
    
    /**
     * Starter Kit tiers
     */
    public const TIER_BASIC = 'basic';
    public const TIER_PREMIUM = 'premium';
    
    /**
     * Starter Kit prices in Kwacha.
     */
    public const PRICE_BASIC = 500.00;
    public const PRICE_PREMIUM = 1000.00;
    
    /**
     * Legacy price constant (for backward compatibility)
     */
    public const PRICE = self::PRICE_BASIC;

    /**
     * Shop credit amounts in Kwacha.
     */
    public const SHOP_CREDIT_BASIC = 100.00;
    public const SHOP_CREDIT_PREMIUM = 200.00;

    /**
     * Shop credit expiry days.
     */
    public const CREDIT_EXPIRY_DAYS = 90;

    /**
     * Purchase starter kit for a user.
     */
    public function purchaseStarterKit(
        User $user,
        string $paymentMethod,
        string $paymentReference = null,
        string $tier = self::TIER_BASIC
    ): StarterKitPurchaseModel {
        Log::info('StarterKitService::purchaseStarterKit called', [
            'user_id' => $user->id,
            'tier' => $tier,
            'payment_method' => $paymentMethod,
        ]);
        
        return DB::transaction(function () use ($user, $paymentMethod, $paymentReference, $tier) {
            // Get price based on tier
            $price = $tier === self::TIER_PREMIUM ? self::PRICE_PREMIUM : self::PRICE_BASIC;
            $shopCredit = $tier === self::TIER_PREMIUM ? self::SHOP_CREDIT_PREMIUM : self::SHOP_CREDIT_BASIC;
            
            Log::info('Tier pricing calculated', [
                'tier' => $tier,
                'price' => $price,
                'shop_credit' => $shopCredit,
            ]);
            // Handle wallet payment
            if ($paymentMethod === 'wallet') {
                // Calculate current wallet balance using WalletService (includes loan transactions)
                $walletBalance = $this->walletService->calculateBalance($user);
                
                if ($walletBalance < $price) {
                    throw new \Exception('Insufficient wallet balance');
                }
                
                // Generate payment reference for wallet transaction
                $paymentReference = 'WALLET-' . now()->format('YmdHis') . '-' . $user->id;
                
                // Create withdrawal record to deduct from wallet
                // Note: Using withdrawals table for backward compatibility with existing data
                DB::table('withdrawals')->insert([
                    'user_id' => $user->id,
                    'amount' => $price,
                    'status' => 'approved',
                    'withdrawal_method' => 'wallet_payment',
                    'reason' => 'Starter Kit Purchase - Wallet Payment',
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                Log::info('Wallet payment validated and transaction created', [
                    'user_id' => $user->id,
                    'amount' => $price,
                    'wallet_balance' => $walletBalance,
                    'new_balance' => $walletBalance - $price,
                ]);
            }
            
            // Create purchase record
            $purchase = StarterKitPurchaseModel::create([
                'user_id' => $user->id,
                'tier' => $tier,
                'amount' => $price,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference ?? 'PENDING',
                'status' => $paymentMethod === 'wallet' ? 'completed' : 'pending',
                'invoice_number' => StarterKitPurchaseModel::generateInvoiceNumber(),
            ]);

            Log::info('Starter Kit purchase created', [
                'user_id' => $user->id,
                'invoice' => $purchase->invoice_number,
                'payment_method' => $paymentMethod,
                'tier' => $tier,
                'amount' => $price,
            ]);
            
            // For wallet payments, complete the purchase immediately
            if ($paymentMethod === 'wallet') {
                $this->completePurchase($purchase);
                Log::info('Wallet purchase completed immediately', [
                    'user_id' => $user->id,
                    'invoice' => $purchase->invoice_number,
                ]);
            }

            return $purchase;
        });
    }

    /**
     * Complete starter kit purchase and grant access.
     */
    public function completePurchase(StarterKitPurchaseModel $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $user = $purchase->user;

            // Mark purchase as completed
            $purchase->update([
                'status' => 'completed',
                'purchased_at' => now(),
            ]);

            // Get tier with fallback to basic
            $tier = $purchase->tier ?? self::TIER_BASIC;
            
            // Update user record
            $user->update([
                'has_starter_kit' => true,
                'starter_kit_tier' => $tier,
                'starter_kit_purchased_at' => now(),
                'library_access_until' => now()->addDays(30), // 30 days free library access
            ]);

            // Add shop credit to wallet
            $this->addShopCredit($user, $tier);

            // Create progressive unlock schedule
            $this->createUnlockSchedule($user);

            // Award registration bonus points
            $this->awardRegistrationBonus($user);
            
            // Process MLM commissions for uplines (7 levels)
            // Only uplines who have purchased starter kit will receive commissions
            $this->processStarterKitCommissions($user, $purchase->amount);
            
            // Generate receipt
            $this->generateStarterKitReceipt($user, $purchase->payment_method, $purchase->payment_reference);
            
            // Send notification
            $this->sendPurchaseNotification($user, $tier);
            
            // Update LGR qualification status
            $this->updateLgrQualification($user);
            
            // Update referrer's LGR qualification (network building)
            $this->updateReferrerLgrQualification($user);

            // Send welcome email (implement separately)
            // event(new StarterKitPurchased($user, $purchase));

            Log::info('Starter Kit purchase completed', [
                'user_id' => $user->id,
                'invoice' => $purchase->invoice_number,
            ]);
        });
    }
    
    /**
     * Update LGR qualification after starter kit purchase
     */
    protected function updateLgrQualification(User $user): void
    {
        try {
            $lgrQualificationService = app(\App\Application\Services\LoyaltyReward\LgrQualificationService::class);
            $lgrQualificationService->checkQualification($user->id);
            
            Log::info('LGR qualification updated after starter kit purchase', [
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the purchase if LGR update fails
            Log::error('Failed to update LGR qualification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);
        }
    }
    
    /**
     * Update referrer's LGR qualification when member purchases starter kit
     */
    protected function updateReferrerLgrQualification(User $user): void
    {
        try {
            if (!$user->referrer_id) {
                return;
            }
            
            $lgrQualificationService = app(\App\Application\Services\LoyaltyReward\LgrQualificationService::class);
            $lgrQualificationService->updateReferrerQualification($user->id);
            
            Log::info('Referrer LGR qualification updated after member starter kit purchase', [
                'member_id' => $user->id,
                'referrer_id' => $user->referrer_id,
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the purchase if LGR update fails
            Log::error('Failed to update referrer LGR qualification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);
        }
    }
    
    /**
     * Process MLM commissions for starter kit purchase
     * 
     * IMPORTANT: Commissions are always based on K500, regardless of tier.
     * - Basic tier (K500): Commission on K500
     * - Premium tier (K1000): Commission on K500 only (extra K500 is for content, not commissionable)
     */
    protected function processStarterKitCommissions(User $user, float $amount): void
    {
        try {
            // Always use K500 as the commission base, regardless of actual purchase amount
            $commissionableAmount = 500.00;
            
            $mlmService = app(MLMCommissionService::class);
            $commissions = $mlmService->processMLMCommissions($user, $commissionableAmount, 'starter_kit');
            
            Log::info('Starter kit commissions processed', [
                'user_id' => $user->id,
                'purchase_amount' => $amount,
                'commissionable_amount' => $commissionableAmount,
                'commissions_count' => count($commissions),
                'total_commission' => collect($commissions)->sum('amount'),
                'note' => 'Commissions based on K500 base amount only',
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the purchase if commission processing fails
            Log::error('Failed to process starter kit commissions: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);
        }
    }
    
    /**
     * Generate receipt for starter kit purchase
     */
    private function generateStarterKitReceipt(User $user, string $paymentMethod, ?string $transactionRef): void
    {
        try {
            $receiptService = app(\App\Services\ReceiptService::class);
            $receipt = $receiptService->generateStarterKitReceipt($user, 500, $paymentMethod, $transactionRef);
            
            // Email receipt to user
            $receiptService->emailReceipt(
                $user,
                $receipt->pdf_path,
                'MyGrowNet - Starter Kit Purchase Receipt'
            );
            
            $receipt->update([
                'emailed' => true,
                'emailed_at' => now(),
            ]);
            
            Log::info('Starter Kit receipt generated and emailed', [
                'receipt_id' => $receipt->id,
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the purchase if receipt generation fails
            Log::error('Failed to generate starter kit receipt: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Add shop credit to user record.
     */
    protected function addShopCredit(User $user, string $tier = self::TIER_BASIC): void
    {
        $creditAmount = $tier === self::TIER_PREMIUM ? self::SHOP_CREDIT_PREMIUM : self::SHOP_CREDIT_BASIC;
        
        $user->update([
            'starter_kit_shop_credit' => $creditAmount,
            'starter_kit_credit_expiry' => now()->addDays(self::CREDIT_EXPIRY_DAYS),
        ]);

        Log::info('Shop credit added', [
            'user_id' => $user->id,
            'tier' => $tier,
            'amount' => $creditAmount,
            'expiry' => $user->starter_kit_credit_expiry,
        ]);
    }

    /**
     * Create progressive unlock schedule for user.
     */
    protected function createUnlockSchedule(User $user): void
    {
        $purchaseDate = Carbon::now();

        $schedule = [
            // Day 1: Immediate access
            [
                'content_item' => 'Module 1: Business Fundamentals',
                'content_category' => 'course',
                'unlock_date' => $purchaseDate,
            ],
            [
                'content_item' => 'eBook: Success Guide',
                'content_category' => 'ebook',
                'unlock_date' => $purchaseDate,
            ],
            [
                'content_item' => 'Video 1: Platform Navigation',
                'content_category' => 'video',
                'unlock_date' => $purchaseDate,
            ],

            // Day 8: Second wave
            [
                'content_item' => 'Module 2: Network Building Strategies',
                'content_category' => 'course',
                'unlock_date' => $purchaseDate->copy()->addDays(8),
            ],
            [
                'content_item' => 'eBook: Network Building Mastery',
                'content_category' => 'ebook',
                'unlock_date' => $purchaseDate->copy()->addDays(8),
            ],
            [
                'content_item' => 'Video 2: Building Your Network',
                'content_category' => 'video',
                'unlock_date' => $purchaseDate->copy()->addDays(8),
            ],

            // Day 15: Third wave
            [
                'content_item' => 'Module 3: Financial Success Planning',
                'content_category' => 'course',
                'unlock_date' => $purchaseDate->copy()->addDays(15),
            ],
            [
                'content_item' => 'eBook: Financial Freedom Blueprint',
                'content_category' => 'ebook',
                'unlock_date' => $purchaseDate->copy()->addDays(15),
            ],
            [
                'content_item' => 'Video 3: Maximizing Earnings',
                'content_category' => 'video',
                'unlock_date' => $purchaseDate->copy()->addDays(15),
            ],

            // Day 22: Marketing tools
            [
                'content_item' => 'Marketing Templates',
                'content_category' => 'tool',
                'unlock_date' => $purchaseDate->copy()->addDays(22),
            ],
            [
                'content_item' => 'Pitch Deck',
                'content_category' => 'tool',
                'unlock_date' => $purchaseDate->copy()->addDays(22),
            ],
            [
                'content_item' => 'Social Media Content Pack',
                'content_category' => 'tool',
                'unlock_date' => $purchaseDate->copy()->addDays(22),
            ],

            // Day 30: Library access
            [
                'content_item' => 'Digital Library Access (50+ eBooks)',
                'content_category' => 'library',
                'unlock_date' => $purchaseDate->copy()->addDays(30),
            ],
        ];

        foreach ($schedule as $item) {
            StarterKitUnlock::create([
                'user_id' => $user->id,
                ...$item,
            ]);
        }

        // Unlock Day 1 items immediately
        StarterKitUnlock::where('user_id', $user->id)
            ->whereDate('unlock_date', '<=', Carbon::today())
            ->update([
                'is_unlocked' => true,
                'unlocked_at' => now(),
            ]);

        Log::info('Unlock schedule created', ['user_id' => $user->id]);
    }

    /**
     * Award registration bonus points.
     * This includes starter kit bonus + retroactive referral bonuses.
     * 
     * IMPORTANT: 
     * - Basic tier (K500): 25 LP
     * - Premium tier (K1000): 50 LP (member gets full points for their investment)
     * - BUT referral commissions to uplines are ALWAYS based on K500 only
     */
    protected function awardRegistrationBonus(User $user): void
    {
        // 1. Award LP for starter kit purchase based on tier
        // Basic (K500) = 25 LP, Premium (K1000) = 50 LP
        // Member gets full points for their investment
        $lpAmount = $user->starter_kit_tier === 'premium' ? 50 : 25;
        
        DB::table('point_transactions')->insert([
            'user_id' => $user->id,
            'lp_amount' => $lpAmount,
            'bp_amount' => 0,
            'source' => 'starter_kit_purchase',
            'description' => 'Starter Kit Purchase Bonus (' . ucfirst($user->starter_kit_tier) . ')',
            'reference_type' => 'starter_kit',
            'reference_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Starter kit bonus awarded', [
            'user_id' => $user->id,
            'tier' => $user->starter_kit_tier,
            'lp_awarded' => $lpAmount,
            'note' => 'Member receives full LP for their tier, but upline commissions based on K500 only',
        ]);

        // 2. Award retroactive points for existing verified referrals
        $verifiedReferrals = $user->directReferrals()
            ->whereHas('memberPayments', function($query) {
                $query->where('status', 'verified');
            })
            ->get();

        foreach ($verifiedReferrals as $referral) {
            DB::table('point_transactions')->insert([
                'user_id' => $user->id,
                'lp_amount' => 25,
                'bp_amount' => 37.5,
                'source' => 'direct_referral',
                'description' => "Direct referral: {$referral->name} verified (Retroactive)",
                'reference_type' => 'user',
                'reference_id' => $referral->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Retroactive referral bonus awarded', [
                'user_id' => $user->id,
                'referral_id' => $referral->id,
                'referral_name' => $referral->name,
                'lp_awarded' => 25,
                'bp_awarded' => 37.5,
            ]);
        }

        // 3. Recalculate and update cached totals
        $totalLP = DB::table('point_transactions')
            ->where('user_id', $user->id)
            ->sum('lp_amount');
        $totalBP = DB::table('point_transactions')
            ->where('user_id', $user->id)
            ->sum('bp_amount');

        $user->update([
            'life_points' => $totalLP,
            'bonus_points' => $totalBP,
        ]);

        // Also update user_points table if exists
        if ($user->points) {
            $user->points->update([
                'lifetime_points' => $totalLP,
                'monthly_points' => $totalBP,
            ]);
        }

        Log::info('Registration bonus complete', [
            'user_id' => $user->id,
            'total_lp' => $totalLP,
            'total_bp' => $totalBP,
            'verified_referrals' => $verifiedReferrals->count(),
        ]);
    }

    /**
     * Award achievement to user.
     */
    public function awardAchievement(User $user, string $achievementType): ?MemberAchievement
    {
        // Check if achievement already exists
        if (MemberAchievement::where('user_id', $user->id)
            ->where('achievement_type', $achievementType)
            ->exists()) {
            return null;
        }

        $details = MemberAchievement::ACHIEVEMENTS[$achievementType] ?? null;

        if (!$details) {
            Log::warning('Unknown achievement type', ['type' => $achievementType]);
            return null;
        }

        $achievement = MemberAchievement::create([
            'user_id' => $user->id,
            'achievement_type' => $achievementType,
            'achievement_name' => $details['name'],
            'description' => $details['description'],
            'badge_icon' => $details['icon'],
            'badge_color' => $details['color'],
            'earned_at' => now(),
        ]);

        // Award points if specified
        if (isset($details['lp_reward'])) {
            $user->increment('life_points', $details['lp_reward']);
        }

        if (isset($details['bp_reward'])) {
            $user->increment('bonus_points', $details['bp_reward']);
        }

        Log::info('Achievement awarded', [
            'user_id' => $user->id,
            'achievement' => $achievementType,
        ]);

        return $achievement;
    }

    /**
     * Process daily unlock checks.
     */
    public function processUnlocks(): int
    {
        $unlocked = 0;

        $readyToUnlock = StarterKitUnlock::readyToUnlock()->get();

        foreach ($readyToUnlock as $unlock) {
            $unlock->unlock();
            $unlocked++;

            // Notify user (implement separately)
            // event(new ContentUnlocked($unlock->user, $unlock));
        }

        Log::info('Daily unlocks processed', ['count' => $unlocked]);

        return $unlocked;
    }

    /**
     * Check and expire shop credits.
     */
    public function expireShopCredits(): int
    {
        $expired = User::where('starter_kit_shop_credit', '>', 0)
            ->whereDate('starter_kit_credit_expiry', '<', Carbon::today())
            ->update([
                'starter_kit_shop_credit' => 0,
                'starter_kit_credit_expiry' => null,
            ]);

        Log::info('Shop credits expired', ['count' => $expired]);

        return $expired;
    }

    /**
     * Get user's starter kit progress.
     */
    public function getUserProgress(User $user): array
    {
        if (!$user->has_starter_kit) {
            return [
                'has_starter_kit' => false,
            ];
        }

        $purchase = StarterKitPurchaseModel::where('user_id', $user->id)
            ->completed()
            ->first();

        $unlocks = StarterKitUnlock::where('user_id', $user->id)->get();
        $achievements = MemberAchievement::where('user_id', $user->id)
            ->displayed()
            ->get();

        return [
            'has_starter_kit' => true,
            'purchased_at' => $purchase?->purchased_at,
            'days_since_purchase' => $purchase?->purchased_at?->diffInDays(now()),
            'shop_credit' => [
                'amount' => $user->starter_kit_shop_credit ?? 0,
                'expiry' => $user->starter_kit_credit_expiry,
                'days_until_expiry' => $user->starter_kit_credit_expiry?->diffInDays(now(), false),
            ],
            'unlocks' => [
                'total' => $unlocks->count(),
                'unlocked' => $unlocks->where('is_unlocked', true)->count(),
                'locked' => $unlocks->where('is_unlocked', false)->count(),
                'next_unlock' => $unlocks->where('is_unlocked', false)
                    ->sortBy('unlock_date')
                    ->first(),
            ],
            'achievements' => [
                'total' => $achievements->count(),
                'recent' => $achievements->sortByDesc('earned_at')->take(5),
            ],
        ];
    }
    
    /**
     * Send purchase notification to user
     */
    protected function sendPurchaseNotification(User $user, string $tier): void
    {
        try {
            $tierName = $tier === self::TIER_PREMIUM ? 'Premium' : 'Basic';
            $shopCredit = $tier === self::TIER_PREMIUM ? self::SHOP_CREDIT_PREMIUM : self::SHOP_CREDIT_BASIC;
            
            $message = "Welcome to MyGrowNet! Your {$tierName} Starter Kit is now active. ";
            $message .= "You've received K{$shopCredit} shop credit and access to all content.";
            
            if ($tier === self::TIER_PREMIUM) {
                $message .= " You're now qualified for LGR quarterly profit sharing!";
            }
            
            $this->notificationService->execute(
                userId: $user->id,
                type: 'subscriptions.starter_kit_purchased',
                data: [
                    'title' => '🎉 Starter Kit Activated!',
                    'message' => $message,
                    'tier' => $tierName,
                    'shop_credit' => $shopCredit,
                    'action_url' => route('mygrownet.starter-kit.show'),
                    'action_text' => 'View Starter Kit'
                ]
            );
            
            Log::info('Starter Kit purchase notification sent', [
                'user_id' => $user->id,
                'tier' => $tier,
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the purchase if notification fails
            Log::error('Failed to send starter kit notification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);
        }
    }
}

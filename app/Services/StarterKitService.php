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
    /**
     * Starter Kit price in Kwacha.
     */
    public const PRICE = 500.00;

    /**
     * Shop credit amount in Kwacha.
     */
    public const SHOP_CREDIT = 100.00;

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
        string $paymentReference = null
    ): StarterKitPurchaseModel {
        return DB::transaction(function () use ($user, $paymentMethod, $paymentReference) {
            // Handle wallet payment
            if ($paymentMethod === 'wallet') {
                // Calculate current wallet balance
                $commissionEarnings = (float) ($user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0);
                $profitEarnings = (float) ($user->profitShares()->sum('amount') ?? 0);
                $walletTopups = (float) (\App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
                    ->where('payment_type', 'wallet_topup')
                    ->where('status', 'verified')
                    ->sum('amount') ?? 0);
                $totalEarnings = $commissionEarnings + $profitEarnings + $walletTopups;
                $totalWithdrawals = (float) ($user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0);
                $workshopExpenses = (float) (\App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
                    ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
                    ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
                    ->sum('workshops.price') ?? 0);
                $walletBalance = $totalEarnings - $totalWithdrawals - $workshopExpenses;
                
                if ($walletBalance < self::PRICE) {
                    throw new \Exception('Insufficient wallet balance');
                }
                
                // Generate payment reference for wallet transaction
                $paymentReference = 'WALLET-' . now()->format('YmdHis') . '-' . $user->id;
                
                // Create withdrawal record to deduct from wallet
                DB::table('withdrawals')->insert([
                    'user_id' => $user->id,
                    'amount' => self::PRICE,
                    'status' => 'approved',
                    'withdrawal_method' => 'wallet_payment',
                    'reason' => 'Starter Kit Purchase - Wallet Payment',
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                Log::info('Wallet payment validated and transaction created', [
                    'user_id' => $user->id,
                    'amount' => self::PRICE,
                    'wallet_balance' => $walletBalance,
                    'new_balance' => $walletBalance - self::PRICE,
                ]);
            }
            
            // Create purchase record
            $purchase = StarterKitPurchaseModel::create([
                'user_id' => $user->id,
                'amount' => self::PRICE,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference ?? 'PENDING',
                'status' => $paymentMethod === 'wallet' ? 'completed' : 'pending',
                'invoice_number' => StarterKitPurchaseModel::generateInvoiceNumber(),
            ]);

            Log::info('Starter Kit purchase created', [
                'user_id' => $user->id,
                'invoice' => $purchase->invoice_number,
                'payment_method' => $paymentMethod,
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

            // Update user record
            $user->update([
                'has_starter_kit' => true,
                'starter_kit_purchased_at' => now(),
                'library_access_until' => now()->addDays(30), // 30 days free library access
            ]);

            // Add shop credit to wallet
            $this->addShopCredit($user);

            // Create progressive unlock schedule
            $this->createUnlockSchedule($user);

            // Award registration bonus points
            $this->awardRegistrationBonus($user);
            
            // Process MLM commissions for uplines (7 levels)
            // Only uplines who have purchased starter kit will receive commissions
            $this->processStarterKitCommissions($user, $purchase->amount);
            
            // Generate receipt
            $this->generateStarterKitReceipt($user, $purchase->payment_method, $purchase->payment_reference);

            // Send welcome email (implement separately)
            // event(new StarterKitPurchased($user, $purchase));

            Log::info('Starter Kit purchase completed', [
                'user_id' => $user->id,
                'invoice' => $purchase->invoice_number,
            ]);
        });
    }
    
    /**
     * Process MLM commissions for starter kit purchase
     */
    protected function processStarterKitCommissions(User $user, float $amount): void
    {
        try {
            $mlmService = app(MLMCommissionService::class);
            $commissions = $mlmService->processMLMCommissions($user, $amount, 'starter_kit');
            
            Log::info('Starter kit commissions processed', [
                'user_id' => $user->id,
                'commissions_count' => count($commissions),
                'total_commission' => collect($commissions)->sum('amount'),
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
    protected function addShopCredit(User $user): void
    {
        $user->update([
            'starter_kit_shop_credit' => self::SHOP_CREDIT,
            'starter_kit_credit_expiry' => Carbon::now()->addDays(self::CREDIT_EXPIRY_DAYS),
            'starter_kit_credit_expiry' => now()->addDays(self::CREDIT_EXPIRY_DAYS),
        ]);

        Log::info('Shop credit added', [
            'user_id' => $user->id,
            'amount' => self::SHOP_CREDIT,
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
     */
    protected function awardRegistrationBonus(User $user): void
    {
        // 1. Award 25 LP for starter kit purchase
        DB::table('point_transactions')->insert([
            'user_id' => $user->id,
            'lp_amount' => 25,
            'bp_amount' => 0,
            'source' => 'starter_kit_purchase',
            'description' => 'Starter Kit Purchase Bonus',
            'reference_type' => 'starter_kit',
            'reference_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Starter kit bonus awarded', [
            'user_id' => $user->id,
            'lp_awarded' => 25,
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
}

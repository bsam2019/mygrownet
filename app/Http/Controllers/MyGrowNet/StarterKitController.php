<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Application\StarterKit\UseCases\PurchaseStarterKitUseCase;
use App\Http\Controllers\Controller;
use App\Services\StarterKitService;
use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel;
use App\Infrastructure\Persistence\Eloquent\StarterKit\ContentItemModel;
use App\Models\StarterKitUnlock;
use App\Models\MemberAchievement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StarterKitController extends Controller
{
    public function __construct(
        protected StarterKitService $starterKitService,
        protected PurchaseStarterKitUseCase $purchaseUseCase
    ) {}

    /**
     * Display member's starter kit information
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        // Refresh user to get latest data (especially after upgrade)
        $user->refresh();
        
        // Check if user has purchased starter kit
        if (!$user->has_starter_kit) {
            // Check for pending payment (K500 product payment)
            $pendingPayment = \App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
                ->where('payment_type', 'product')
                ->where('amount', StarterKitService::PRICE)
                ->where('status', 'pending')
                ->latest()
                ->first();
            
            if ($pendingPayment) {
                // Load content items for preview
                $contentItems = ContentItemModel::active()
                    ->ordered()
                    ->get()
                    ->groupBy('category');
                
                return Inertia::render('MyGrowNet/StarterKit', [
                    'hasStarterKit' => false,
                    'hasPendingPayment' => true,
                    'pendingPayment' => [
                        'amount' => $pendingPayment->amount,
                        'payment_method' => $pendingPayment->payment_method,
                        'payment_reference' => $pendingPayment->payment_reference,
                        'submitted_at' => $pendingPayment->created_at->format('M j, Y H:i'),
                    ],
                    'tiers' => [
                        'lite' => [
                            'price' => StarterKitService::PRICE_LITE,
                            'shopCredit' => StarterKitService::SHOP_CREDIT_LITE,
                            'lgrDailyRate' => 12.50,
                            'lpAward' => 15,
                        ],
                        'basic' => [
                            'price' => StarterKitService::PRICE_BASIC,
                            'shopCredit' => StarterKitService::SHOP_CREDIT_BASIC,
                            'lgrDailyRate' => 25.00,
                            'lpAward' => 25,
                        ],
                        'growth_plus' => [
                            'price' => StarterKitService::PRICE_GROWTH_PLUS,
                            'shopCredit' => StarterKitService::SHOP_CREDIT_GROWTH_PLUS,
                            'lgrDailyRate' => 37.50,
                            'lpAward' => 50,
                        ],
                        'pro' => [
                            'price' => StarterKitService::PRICE_PRO,
                            'shopCredit' => StarterKitService::SHOP_CREDIT_PRO,
                            'lgrDailyRate' => 62.50,
                            'lpAward' => 100,
                        ],
                    ],
                    'contentItems' => $contentItems,
                ]);
            }
            
            // Load content items for preview
            $contentItems = ContentItemModel::active()
                ->ordered()
                ->get()
                ->groupBy('category');
            
            return Inertia::render('MyGrowNet/StarterKit', [
                'hasStarterKit' => false,
                'hasPendingPayment' => false,
                'tiers' => [
                    'lite' => [
                        'price' => StarterKitService::PRICE_LITE,
                        'shopCredit' => StarterKitService::SHOP_CREDIT_LITE,
                        'lgrDailyRate' => 12.50,
                        'lpAward' => 15,
                    ],
                    'basic' => [
                        'price' => StarterKitService::PRICE_BASIC,
                        'shopCredit' => StarterKitService::SHOP_CREDIT_BASIC,
                        'lgrDailyRate' => 25.00,
                        'lpAward' => 25,
                    ],
                    'growth_plus' => [
                        'price' => StarterKitService::PRICE_GROWTH_PLUS,
                        'shopCredit' => StarterKitService::SHOP_CREDIT_GROWTH_PLUS,
                        'lgrDailyRate' => 37.50,
                        'lpAward' => 50,
                    ],
                    'pro' => [
                        'price' => StarterKitService::PRICE_PRO,
                        'shopCredit' => StarterKitService::SHOP_CREDIT_PRO,
                        'lgrDailyRate' => 62.50,
                        'lpAward' => 100,
                    ],
                ],
                'purchaseUrl' => route('mygrownet.starter-kit.purchase'),
                'contentItems' => $contentItems,
            ]);
        }
        
        // Get starter kit purchase
        $purchase = StarterKitPurchaseModel::where('user_id', $user->id)
            ->completed()
            ->first();
        
        // Get progress
        $progress = $this->starterKitService->getUserProgress($user);
        
        // Get unlocked content
        $unlockedContent = StarterKitUnlock::where('user_id', $user->id)
            ->unlocked()
            ->get()
            ->groupBy('content_category');
        
        // Get achievements
        $achievements = MemberAchievement::where('user_id', $user->id)
            ->displayed()
            ->orderByDesc('earned_at')
            ->get();
        
        return Inertia::render('MyGrowNet/StarterKit', [
            'hasStarterKit' => true,
            'purchase' => [
                'invoice_number' => $purchase->invoice_number,
                'purchased_at' => $purchase->purchased_at ? $purchase->purchased_at->format('M j, Y') : ($purchase->created_at ? $purchase->created_at->format('M j, Y') : 'N/A'),
                'amount' => $purchase->amount,
                'days_since_purchase' => $purchase->purchased_at ? (int) $purchase->purchased_at->diffInDays(now()) : ($purchase->created_at ? (int) $purchase->created_at->diffInDays(now()) : 0),
            ],
            'shopCredit' => [
                'amount' => (float) ($user->starter_kit_shop_credit ?? 0),
                'expiry' => $user->starter_kit_credit_expiry?->format('M j, Y'),
                'days_remaining' => $user->starter_kit_credit_expiry ? (int) now()->diffInDays($user->starter_kit_credit_expiry, false) : 0,
            ],
            'progress' => [
                'total_unlocks' => $progress['unlocks']['total'] ?? 0,
                'unlocked' => $progress['unlocks']['unlocked'] ?? 0,
                'locked' => $progress['unlocks']['locked'] ?? 0,
                'next_unlock' => $progress['unlocks']['next_unlock'] ?? null,
            ],
            'content' => [
                'courses' => $unlockedContent->get('course', collect())->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->content_item,
                    'unlocked_at' => $item->unlocked_at?->format('M j, Y'),
                    'viewed' => $item->viewed_at !== null,
                ]),
                'videos' => $unlockedContent->get('video', collect())->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->content_item,
                    'unlocked_at' => $item->unlocked_at?->format('M j, Y'),
                    'viewed' => $item->viewed_at !== null,
                ]),
                'ebooks' => $unlockedContent->get('ebook', collect())->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->content_item,
                    'unlocked_at' => $item->unlocked_at?->format('M j, Y'),
                    'viewed' => $item->viewed_at !== null,
                ]),
                'tools' => $unlockedContent->get('tool', collect())->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->content_item,
                    'unlocked_at' => $item->unlocked_at?->format('M j, Y'),
                    'viewed' => $item->viewed_at !== null,
                ]),
                'library' => $unlockedContent->get('library', collect())->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->content_item,
                    'unlocked_at' => $item->unlocked_at?->format('M j, Y'),
                    'viewed' => $item->viewed_at !== null,
                ]),
            ],
            'achievements' => $achievements->map(fn($achievement) => [
                'id' => $achievement->id,
                'name' => $achievement->achievement_name,
                'description' => $achievement->description,
                'icon' => $achievement->badge_icon,
                'color' => $achievement->badge_color,
                'earned_at' => $achievement->earned_at->format('M j, Y'),
            ]),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'joined_at' => $user->created_at->format('M j, Y'),
                'starter_kit_tier' => $user->starter_kit_tier,
            ],
        ]);
    }
    
    /**
     * Show purchase page
     */
    public function purchase(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        
        if ($user->has_starter_kit) {
            return redirect()->route('mygrownet.starter-kit.show')
                ->with('info', 'You already have the Starter Kit!');
        }
        
        // Calculate wallet balance using WalletService for consistency
        $walletService = app(\App\Services\WalletService::class);
        $walletBalance = $walletService->calculateBalance($user);
        
        // Load content items from database
        $contentItems = ContentItemModel::active()
            ->ordered()
            ->get()
            ->groupBy('category');
        
        return Inertia::render('MyGrowNet/StarterKitPurchase', [
            'tiers' => [
                'lite' => [
                    'name' => 'Lite Knowledge Pack',
                    'price' => StarterKitService::PRICE_LITE,
                    'shopCredit' => StarterKitService::SHOP_CREDIT_LITE,
                    'lgrDailyRate' => 12.50,
                    'lgrMaxEarnings' => 875.00,
                    'lpAward' => 15,
                ],
                'basic' => [
                    'name' => 'Basic Growth Pack',
                    'price' => StarterKitService::PRICE_BASIC,
                    'shopCredit' => StarterKitService::SHOP_CREDIT_BASIC,
                    'lgrDailyRate' => 25.00,
                    'lgrMaxEarnings' => 1750.00,
                    'lpAward' => 25,
                ],
                'growth_plus' => [
                    'name' => 'Growth Plus Pack',
                    'price' => StarterKitService::PRICE_GROWTH_PLUS,
                    'shopCredit' => StarterKitService::SHOP_CREDIT_GROWTH_PLUS,
                    'lgrDailyRate' => 37.50,
                    'lgrMaxEarnings' => 2625.00,
                    'lpAward' => 50,
                ],
                'pro' => [
                    'name' => 'Pro Access Pack',
                    'price' => StarterKitService::PRICE_PRO,
                    'shopCredit' => StarterKitService::SHOP_CREDIT_PRO,
                    'lgrDailyRate' => 62.50,
                    'lgrMaxEarnings' => 4375.00,
                    'lpAward' => 100,
                ],
            ],
            'walletBalance' => $walletBalance,
            'paymentMethods' => $this->getPaymentMethods(),
            'contentItems' => $contentItems,
        ]);
    }
    
    /**
     * Process purchase
     */
    public function storePurchase(Request $request)
    {
        $validated = $request->validate([
            'tier' => 'required|string|in:lite,basic,growth_plus,pro',
            'payment_method' => 'required|string|in:mobile_money,bank_transfer,wallet',
            'terms_accepted' => 'required|accepted',
        ]);
        
        $user = $request->user();
        
        \Log::info('Starter Kit purchase request', [
            'user_id' => $user->id,
            'tier' => $validated['tier'],
            'payment_method' => $validated['payment_method'],
        ]);
        
        try {
            // Update terms acceptance
            $user->update([
                'starter_kit_terms_accepted' => true,
                'starter_kit_terms_accepted_at' => now(),
            ]);
            
            // Use the Purchase Use Case (DDD)
            $result = $this->purchaseUseCase->execute(
                $user,
                $validated['payment_method'],
                null,
                $validated['tier']
            );
            
            // Check if this is a mobile request (AJAX/Inertia)
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                // For mobile, return success without redirect
                return back()->with('success', $result['message']);
            }
            
            // For desktop, redirect as normal
            return redirect($result['redirect'])
                ->with('success', $result['message']);
                
        } catch (\InvalidArgumentException $e) {
            \Log::error('Starter Kit purchase validation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Starter Kit purchase failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', 'Purchase failed. Please try again.');
        }
    }
    
    /**
     * Show upgrade page for Basic to Premium
     */
    public function showUpgrade(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        
        // Check if user has basic tier
        if (!$user->has_starter_kit || $user->starter_kit_tier !== 'basic') {
            return redirect()->route('mygrownet.starter-kit.show')
                ->with('info', 'Upgrade is only available for Basic tier members.');
        }
        
        // Calculate wallet balance
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
        
        $upgradeCost = StarterKitService::PRICE_PREMIUM - StarterKitService::PRICE_BASIC; // K500
        
        return Inertia::render('MyGrowNet/StarterKitUpgrade', [
            'currentTier' => 'basic',
            'upgradeCost' => $upgradeCost,
            'walletBalance' => $walletBalance,
            'premiumBenefits' => [
                'Double shop credit (K200 instead of K100)',
                'LGR Qualification - Quarterly profit sharing',
                'Priority support access',
                'Enhanced earning potential',
            ],
        ]);
    }
    
    /**
     * Process upgrade from Basic to Premium
     */
    public function processUpgrade(Request $request)
    {
        $validated = $request->validate([
            'terms_accepted' => 'required|accepted',
        ]);
        
        $user = $request->user();
        
        // Verify user has basic tier
        if (!$user->has_starter_kit || $user->starter_kit_tier !== 'basic') {
            return back()->with('error', 'Upgrade is only available for Basic tier members.');
        }
        
        $upgradeCost = StarterKitService::PRICE_PREMIUM - StarterKitService::PRICE_BASIC;
        
        // Calculate wallet balance
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
        
        // Check sufficient balance
        if ($walletBalance < $upgradeCost) {
            return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
        }
        
        try {
            \DB::transaction(function () use ($user, $upgradeCost) {
                // Create transaction record to deduct from wallet
                \DB::table('transactions')->insert([
                    'user_id' => $user->id,
                    'transaction_type' => 'starter_kit_upgrade',
                    'amount' => -$upgradeCost, // Negative for debit
                    'reference_number' => 'SK-UPGRADE-' . strtoupper(uniqid()),
                    'description' => 'Starter Kit Upgrade: Basic to Premium',
                    'status' => 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Update user tier and shop credit in one update
                $currentCredit = $user->starter_kit_shop_credit ?? 0;
                $user->update([
                    'starter_kit_tier' => 'premium',
                    'starter_kit_shop_credit' => $currentCredit + 100,
                ]);
                
                // Award 25 LP for upgrade (member gets additional points)
                // BUT NO referral commissions to uplines (upgrade is for content only)
                \DB::table('point_transactions')->insert([
                    'user_id' => $user->id,
                    'lp_amount' => 25,
                    'bp_amount' => 0,
                    'source' => 'starter_kit_upgrade',
                    'description' => 'Starter Kit Upgrade: Basic to Premium (+25 LP)',
                    'reference_type' => 'starter_kit',
                    'reference_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Recalculate user's total points
                $totalLP = \DB::table('point_transactions')
                    ->where('user_id', $user->id)
                    ->sum('lp_amount');
                $totalBP = \DB::table('point_transactions')
                    ->where('user_id', $user->id)
                    ->sum('bp_amount');
                
                $user->update([
                    'life_points' => $totalLP,
                    'bonus_points' => $totalBP,
                ]);
                
                // Update user_points table if exists
                $userPoints = \DB::table('user_points')->where('user_id', $user->id)->first();
                if ($userPoints) {
                    \DB::table('user_points')
                        ->where('user_id', $user->id)
                        ->update([
                            'lifetime_points' => $totalLP,
                            'monthly_points' => $totalBP,
                            'updated_at' => now(),
                        ]);
                }
                
                // Update LGR qualification
                $lgrService = app(\App\Application\Services\LoyaltyReward\LgrQualificationService::class);
                $lgrService->checkQualification($user->id);
                
                // Send upgrade notification
                $notificationService = app(\App\Application\Notification\UseCases\SendNotificationUseCase::class);
                $notificationService->execute(
                    userId: $user->id,
                    type: 'starter_kit.upgraded',
                    data: [
                        'title' => '⭐ Upgraded to Premium!',
                        'message' => 'Congratulations! You\'ve been upgraded to Premium tier. You now have access to LGR quarterly profit sharing and received an additional K100 shop credit!',
                        'action_url' => '/mygrownet/my-starter-kit',
                        'action_text' => 'View Benefits'
                    ]
                );
                
                \Log::info('Starter Kit upgraded to Premium', [
                    'user_id' => $user->id,
                    'upgrade_cost' => $upgradeCost,
                    'lp_awarded' => 25,
                    'note' => 'Member receives 25 LP, but NO referral commissions to uplines',
                ]);
            });
            
            // Refresh the user model to get updated data
            $user->refresh();
            
            return redirect()->route('mygrownet.starter-kit.show')
                ->with('success', 'Congratulations! You have been upgraded to Premium tier with LGR access!');
                
        } catch (\Exception $e) {
            \Log::error('Starter Kit upgrade failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Upgrade failed. Please try again.');
        }
    }
    
    /**
     * Get payment methods
     */
    protected function getPaymentMethods(): array
    {
        return [
            [
                'id' => 'wallet',
                'name' => 'MyGrowNet Wallet',
                'description' => 'Use your wallet balance',
                'icon' => '💳',
            ],
            [
                'id' => 'mobile_money',
                'name' => 'Mobile Money',
                'description' => 'MTN MoMo or Airtel Money',
                'icon' => '📱',
            ],
            [
                'id' => 'bank_transfer',
                'name' => 'Bank Transfer',
                'description' => 'Direct bank transfer',
                'icon' => '🏦',
            ],
        ];
    }
}

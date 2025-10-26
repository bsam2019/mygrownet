<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Services\StarterKitService;
use App\Models\StarterKitPurchase;
use App\Models\StarterKitUnlock;
use App\Models\MemberAchievement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StarterKitController extends Controller
{
    public function __construct(
        protected StarterKitService $starterKitService
    ) {}

    /**
     * Display member's starter kit information
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        
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
                return Inertia::render('MyGrowNet/StarterKit', [
                    'hasStarterKit' => false,
                    'hasPendingPayment' => true,
                    'pendingPayment' => [
                        'amount' => $pendingPayment->amount,
                        'payment_method' => $pendingPayment->payment_method,
                        'payment_reference' => $pendingPayment->payment_reference,
                        'submitted_at' => $pendingPayment->created_at->format('M j, Y H:i'),
                    ],
                    'price' => StarterKitService::PRICE,
                    'shopCredit' => StarterKitService::SHOP_CREDIT,
                ]);
            }
            
            return Inertia::render('MyGrowNet/StarterKit', [
                'hasStarterKit' => false,
                'hasPendingPayment' => false,
                'price' => StarterKitService::PRICE,
                'shopCredit' => StarterKitService::SHOP_CREDIT,
                'purchaseUrl' => route('mygrownet.starter-kit.purchase'),
            ]);
        }
        
        // Get starter kit purchase
        $purchase = StarterKitPurchase::where('user_id', $user->id)
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
                'purchased_at' => $purchase->purchased_at->format('M j, Y'),
                'amount' => $purchase->amount,
                'days_since_purchase' => (int) $purchase->purchased_at->diffInDays(now()),
            ],
            'shopCredit' => [
                'amount' => $user->starter_kit_shop_credit ?? 0,
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
            ],
        ]);
    }
    
    /**
     * Show purchase page
     */
    public function purchase(Request $request): Response
    {
        $user = $request->user();
        
        if ($user->has_starter_kit) {
            return redirect()->route('mygrownet.starter-kit.show')
                ->with('info', 'You already have the Starter Kit!');
        }
        
        // Calculate wallet balance (same logic as WalletController)
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
        
        return Inertia::render('MyGrowNet/StarterKitPurchase', [
            'price' => StarterKitService::PRICE,
            'shopCredit' => StarterKitService::SHOP_CREDIT,
            'walletBalance' => $walletBalance,
            'paymentMethods' => $this->getPaymentMethods(),
        ]);
    }
    
    /**
     * Process purchase
     */
    public function storePurchase(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:mobile_money,bank_transfer,wallet',
            'terms_accepted' => 'required|accepted',
        ]);
        
        $user = $request->user();
        
        if ($user->has_starter_kit) {
            return back()->with('error', 'You already have the Starter Kit!');
        }
        
        try {
            // Update terms acceptance
            $user->update([
                'starter_kit_terms_accepted' => true,
                'starter_kit_terms_accepted_at' => now(),
            ]);
            
            // Handle wallet payment - instant access
            if ($validated['payment_method'] === 'wallet') {
                $purchase = $this->starterKitService->purchaseStarterKit(
                    $user,
                    'wallet',
                    null
                );
                
                $this->starterKitService->completePurchase($purchase);
                
                return redirect()->route('mygrownet.starter-kit.show')
                    ->with('success', 'Welcome! Your Starter Kit is ready. K100 shop credit has been added.');
            }
            
            // For other payment methods, redirect to Submit Payment page
            return redirect()->route('mygrownet.payments.create')
                ->with('payment_context', [
                    'type' => 'starter_kit',
                    'amount' => StarterKitService::PRICE,
                    'description' => 'MyGrowNet Starter Kit Purchase',
                ])
                ->with('info', 'Please submit your payment details for verification. You will receive access once confirmed.');
        } catch (\Exception $e) {
            \Log::error('Starter Kit purchase failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', $e->getMessage() === 'Insufficient wallet balance' 
                ? 'Insufficient wallet balance. Please deposit funds or use another payment method.'
                : 'Purchase failed. Please try again.');
        }
    }
    
    /**
     * Get payment methods
     */
    protected function getPaymentMethods(): array
    {
        return [
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

<?php

namespace App\Http\Controllers;

use App\Models\StarterKitPurchase;
use App\Models\StarterKitContentAccess;
use App\Models\StarterKitUnlock;
use App\Models\MemberAchievement;
use App\Services\StarterKitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StarterKitController extends Controller
{
    public function __construct(
        protected StarterKitService $starterKitService
    ) {}

    /**
     * Show starter kit landing page.
     */
    public function index(): Response
    {
        $user = Auth::user();

        return Inertia::render('StarterKit/Index', [
            'hasStarterKit' => $user->has_starter_kit ?? false,
            'price' => StarterKitService::PRICE,
            'shopCredit' => StarterKitService::SHOP_CREDIT,
            'valueBreakdown' => $this->getValueBreakdown(),
        ]);
    }

    /**
     * Show purchase page.
     */
    public function purchase(): Response
    {
        $user = Auth::user();

        if ($user->has_starter_kit) {
            return redirect()->route('starter-kit.dashboard')
                ->with('info', 'You already have the Starter Kit!');
        }

        return Inertia::render('StarterKit/Purchase', [
            'price' => StarterKitService::PRICE,
            'valueBreakdown' => $this->getValueBreakdown(),
            'paymentMethods' => $this->getPaymentMethods(),
        ]);
    }

    /**
     * Process starter kit purchase.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:mobile_money,bank_transfer,wallet',
            'payment_reference' => 'required|string|max:100',
            'terms_accepted' => 'required|accepted',
        ]);

        $user = Auth::user();

        if ($user->has_starter_kit) {
            return back()->with('error', 'You already have the Starter Kit!');
        }

        try {
            // Create purchase
            $purchase = $this->starterKitService->purchaseStarterKit(
                $user,
                $validated['payment_method'],
                $validated['payment_reference']
            );

            // Update terms acceptance
            $user->update([
                'starter_kit_terms_accepted' => true,
                'starter_kit_terms_accepted_at' => now(),
            ]);

            // For now, auto-complete (in production, wait for payment confirmation)
            $this->starterKitService->completePurchase($purchase);

            return redirect()->route('starter-kit.dashboard')
                ->with('success', 'Welcome to MyGrowNet! Your Starter Kit is ready.');
        } catch (\Exception $e) {
            \Log::error('Starter Kit purchase failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Purchase failed. Please try again.');
        }
    }

    /**
     * Show starter kit dashboard.
     */
    public function dashboard(): Response
    {
        $user = Auth::user();

        if (!$user->has_starter_kit) {
            return redirect()->route('starter-kit.index')
                ->with('info', 'Please purchase the Starter Kit first.');
        }

        $progress = $this->starterKitService->getUserProgress($user);

        return Inertia::render('StarterKit/Dashboard', [
            'progress' => $progress,
            'unlockedContent' => $this->getUnlockedContent($user),
            'achievements' => $this->getUserAchievements($user),
        ]);
    }

    /**
     * Show content library.
     */
    public function library(): Response
    {
        $user = Auth::user();

        if (!$user->has_starter_kit) {
            return redirect()->route('starter-kit.index');
        }

        $unlocks = StarterKitUnlock::where('user_id', $user->id)
            ->unlocked()
            ->get()
            ->groupBy('content_category');

        return Inertia::render('StarterKit/Library', [
            'content' => [
                'courses' => $unlocks->get('course', collect()),
                'videos' => $unlocks->get('video', collect()),
                'ebooks' => $unlocks->get('ebook', collect()),
                'tools' => $unlocks->get('tool', collect()),
                'webinars' => $unlocks->get('webinar', collect()),
            ],
        ]);
    }

    /**
     * Track content access.
     */
    public function trackAccess(Request $request)
    {
        $validated = $request->validate([
            'content_type' => 'required|string',
            'content_id' => 'required|string',
            'content_name' => 'nullable|string',
        ]);

        $user = Auth::user();

        $access = StarterKitContentAccess::firstOrCreate(
            [
                'user_id' => $user->id,
                'content_type' => $validated['content_type'],
                'content_id' => $validated['content_id'],
            ],
            [
                'content_name' => $validated['content_name'] ?? null,
            ]
        );

        $access->trackAccess();

        // Award achievements based on content type
        if ($validated['content_type'] === 'video' && !$access->wasRecentlyCreated) {
            $this->starterKitService->awardAchievement($user, 'first_video_watched');
        }

        return response()->json([
            'success' => true,
            'access' => $access,
        ]);
    }

    /**
     * Update content progress.
     */
    public function updateProgress(Request $request)
    {
        $validated = $request->validate([
            'content_type' => 'required|string',
            'content_id' => 'required|string',
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $user = Auth::user();

        $access = StarterKitContentAccess::where('user_id', $user->id)
            ->where('content_type', $validated['content_type'])
            ->where('content_id', $validated['content_id'])
            ->firstOrFail();

        $access->updateProgress($validated['progress']);

        // Check for completion achievements
        if ($validated['progress'] >= 100) {
            $this->checkCompletionAchievements($user);
        }

        return response()->json([
            'success' => true,
            'access' => $access,
        ]);
    }

    /**
     * Get value breakdown for display.
     */
    protected function getValueBreakdown(): array
    {
        return [
            [
                'category' => 'Learning & Training',
                'items' => [
                    ['name' => 'Entrepreneurship Course (3 modules)', 'value' => 200],
                    ['name' => 'MyGrowNet Success Guide (eBook)', 'value' => 50],
                    ['name' => 'Video Tutorial Series (3 videos)', 'value' => 50],
                    ['name' => 'Digital Library Access (30 days)', 'value' => 100],
                ],
                'subtotal' => 400,
            ],
            [
                'category' => 'Product Credits',
                'items' => [
                    ['name' => 'MyGrow Shop Credit', 'value' => 100],
                ],
                'subtotal' => 100,
            ],
            [
                'category' => 'Platform Access',
                'items' => [
                    ['name' => 'Member Dashboard (lifetime)', 'value' => 50],
                    ['name' => 'Referral System Tools', 'value' => 50],
                    ['name' => 'Weekly Webinars (1 month)', 'value' => 100],
                    ['name' => 'Points System Activation', 'value' => 50],
                ],
                'subtotal' => 250,
            ],
            [
                'category' => 'Business Tools',
                'items' => [
                    ['name' => 'Digital Marketing Toolkit', 'value' => 100],
                    ['name' => 'Pitch Deck & Presentations', 'value' => 50],
                    ['name' => 'Pre-Written Content Library', 'value' => 50],
                ],
                'subtotal' => 200,
            ],
            [
                'category' => 'Bonus Features',
                'items' => [
                    ['name' => 'Progressive Unlocking System', 'value' => 30],
                    ['name' => 'Achievement Badges & Certificates', 'value' => 20],
                    ['name' => 'Community Access (lifetime)', 'value' => 50],
                ],
                'subtotal' => 100,
            ],
        ];
    }

    /**
     * Get available payment methods.
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
            [
                'id' => 'wallet',
                'name' => 'MyGrowNet Wallet',
                'description' => 'Use your wallet balance',
                'icon' => '💳',
            ],
        ];
    }

    /**
     * Get unlocked content for user.
     */
    protected function getUnlockedContent($user): array
    {
        return StarterKitUnlock::where('user_id', $user->id)
            ->unlocked()
            ->get()
            ->groupBy('content_category')
            ->map(fn($items) => $items->values())
            ->toArray();
    }

    /**
     * Get user achievements.
     */
    protected function getUserAchievements($user): array
    {
        return MemberAchievement::where('user_id', $user->id)
            ->displayed()
            ->orderByDesc('earned_at')
            ->get()
            ->toArray();
    }

    /**
     * Check for completion achievements.
     */
    protected function checkCompletionAchievements($user): void
    {
        // Check if all modules completed
        $completedModules = StarterKitContentAccess::where('user_id', $user->id)
            ->ofType('course')
            ->completed()
            ->count();

        if ($completedModules >= 3) {
            $this->starterKitService->awardAchievement($user, 'starter_graduate');
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LgrManualAward;
use App\Models\User;
use App\Application\Notification\UseCases\SendNotificationUseCase;
use App\Services\IdempotencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class LgrManualAwardController extends Controller
{
    public function __construct(
        private readonly IdempotencyService $idempotencyService
    ) {}
    
    public function index(): Response
    {
        $awards = LgrManualAward::with(['user', 'awardedBy'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_awarded' => LgrManualAward::sum('amount'),
            'total_recipients' => LgrManualAward::distinct('user_id')->count(),
            'this_month' => LgrManualAward::whereMonth('created_at', now()->month)->sum('amount'),
        ];

        // Get premium members for the modal
        $eligibleMembers = User::where('starter_kit_tier', 'premium')
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'phone', 'loyalty_points')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                // Count referrals safely
                $referralsCount = 0;
                try {
                    $referralsCount = User::where('referrer_id', $user->id)->count();
                } catch (\Exception $e) {
                    // Ignore if relationship doesn't exist
                }
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? 'N/A',
                    'referrals_count' => $referralsCount,
                    'loyalty_points' => (float) ($user->loyalty_points ?? 0),
                ];
            });

        return Inertia::render('Admin/LGR/ManualAwards', [
            'awards' => $awards,
            'stats' => $stats,
            'eligibleMembers' => $eligibleMembers,
        ]);
    }

    public function create(): Response
    {
        // Get premium members who are active
        $eligibleMembers = User::where('starter_kit_tier', 'premium')
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'phone', 'starter_kit_tier')
            ->withCount('referrals')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'tier' => $user->starter_kit_tier,
                    'referrals_count' => $user->referrals_count,
                    'loyalty_points' => $user->loyalty_points ?? 0,
                ];
            });

        return Inertia::render('Admin/LGR/AwardBonus', [
            'eligibleMembers' => $eligibleMembers,
        ]);
    }

    public function store(Request $request)
    {
        Log::info('LGR Award Request Received', $request->all());
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:10|max:2100',
            'award_type' => 'required|in:early_adopter,performance,special,marketing',
            'reason' => 'required|string|min:10|max:500',
        ]);

        Log::info('LGR Award Validated', $validated);

        // Verify user is premium
        $user = User::findOrFail($validated['user_id']);
        Log::info('User Found', ['id' => $user->id, 'tier' => $user->starter_kit_tier]);
        
        if ($user->starter_kit_tier !== 'premium') {
            Log::warning('User not premium', ['user_id' => $user->id, 'tier' => $user->starter_kit_tier]);
            return back()->withErrors(['user_id' => 'Only premium members are eligible for LGR awards']);
        }

        // Generate idempotency key based on admin, user, amount, and timestamp
        $timestamp = floor(time() / 60) * 60; // Round to nearest minute
        $idempotencyKey = $this->idempotencyService->generateKey(
            auth()->id(),
            'lgr_manual_award',
            [
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'timestamp' => $timestamp
            ]
        );
        
        // Check if this exact award was already processed recently
        if ($this->idempotencyService->wasCompleted($idempotencyKey)) {
            Log::warning('Duplicate LGR award attempt detected', [
                'admin_id' => auth()->id(),
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'idempotency_key' => $idempotencyKey,
            ]);
            return redirect()
                ->route('admin.lgr.awards.index')
                ->with('info', 'This award was already processed. Please check the awards list.');
        }
        
        // Check if award is currently in progress
        if ($this->idempotencyService->isInProgress($idempotencyKey)) {
            Log::warning('LGR award already in progress', [
                'admin_id' => auth()->id(),
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'idempotency_key' => $idempotencyKey,
            ]);
            return back()->withErrors(['error' => 'Award is already being processed. Please wait.']);
        }

        try {
            // Execute award with idempotency protection
            $result = $this->idempotencyService->execute(
                $idempotencyKey,
                function () use ($user, $validated) {
                    return $this->executeAward($user, $validated);
                },
                lockDuration: 30, // 30 seconds lock
                keyTtl: 300 // Remember for 5 minutes
            );
            
            return redirect()
                ->route('admin.lgr.awards.index')
                ->with('success', $result['message']);
                
        } catch (\Exception $e) {
            Log::error('LGR Award Failed', [
                'admin_id' => auth()->id(),
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to process award: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Execute the actual award operation
     */
    private function executeAward(User $user, array $validated): array
    {
        return DB::transaction(function () use ($user, $validated) {
            Log::info('Starting award transaction');
            
            // Create award record
            $award = LgrManualAward::create([
                'user_id' => $validated['user_id'],
                'awarded_by' => auth()->id(),
                'amount' => $validated['amount'],
                'award_type' => $validated['award_type'],
                'reason' => $validated['reason'],
                'credited' => true,
                'credited_at' => now(),
            ]);

            Log::info('Award created', ['award_id' => $award->id]);

            // Credit to user's loyalty points and track total awarded
            $oldBalance = $user->loyalty_points;
            $user->increment('loyalty_points', $validated['amount']);
            $user->increment('loyalty_points_awarded_total', $validated['amount']);
            $user->refresh();
            
            Log::info('User balance updated', [
                'user_id' => $user->id,
                'old_balance' => $oldBalance,
                'new_balance' => $user->loyalty_points,
                'amount_added' => $validated['amount']
            ]);

            // Create transaction record
            $transactionId = DB::table('transactions')->insertGetId([
                'user_id' => $user->id,
                'transaction_type' => 'lgr_manual_award',
                'amount' => $validated['amount'],
                'reference_number' => 'LGR-' . strtoupper(uniqid()),
                'description' => "LGR Manual Award: {$validated['reason']}",
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Transaction created', ['transaction_id' => $transactionId]);

            // Send notification to user
            try {
                $notificationService = app(SendNotificationUseCase::class);
                $notificationService->execute(
                    userId: $user->id,
                    type: 'wallet.lgr_award.received',
                    data: [
                        'title' => 'LGR Award Received',
                        'message' => "🎉 You've been awarded K{$validated['amount']} in Loyalty Growth Credits! " . ucwords(str_replace('_', ' ', $validated['award_type'])) . ": {$validated['reason']}",
                        'amount' => 'K' . number_format($validated['amount'], 2),
                        'award_type' => ucwords(str_replace('_', ' ', $validated['award_type'])),
                        'reason' => $validated['reason'],
                        'action_url' => route('mygrownet.wallet.index'),
                        'action_text' => 'View Wallet',
                        'priority' => 'high'
                    ]
                );
                Log::info('Notification sent to user', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                Log::warning('Failed to send notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the award if notification fails
            }

            Log::info('Award transaction committed successfully');
            
            return [
                'success' => true,
                'message' => "Successfully awarded K{$validated['amount']} to {$user->name}",
                'award_id' => $award->id,
            ];
        });
    }

    public function show(LgrManualAward $award): Response
    {
        $award->load(['user', 'awardedBy']);

        return Inertia::render('Admin/LGR/AwardDetails', [
            'award' => $award,
        ]);
    }
}

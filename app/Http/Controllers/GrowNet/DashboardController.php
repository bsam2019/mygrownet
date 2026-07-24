<?php

declare(strict_types=1);

namespace App\Http\Controllers\GrowNet;

use App\Domain\GrowNet\Services\DashboardService;
use App\Domain\GrowNet\Services\MemberService;
use App\Domain\GrowNet\Services\TierAdvancementService;
use App\Domain\Support\Repositories\TicketRepository;
use App\Domain\GrowNet\Wallet\Services\WalletService;
use App\Domain\Messaging\Services\MessagingService;
use App\Application\UseCases\Announcement\GetUserAnnouncementsUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel;
use App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopModel;
use App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel;
use App\Models\Community\CommunityProject;
use App\Models\ProfessionalLevel;
use App\Models\WithdrawalRequest;
use App\Domain\Support\ValueObjects\UserId as SupportUserId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private MemberService $memberService,
        private TierAdvancementService $tierAdvancementService,
        private WalletService $walletService,
        private GetUserAnnouncementsUseCase $getUserAnnouncementsUseCase,
        private MessagingService $messagingService,
        private TicketRepository $ticketRepository,
    ) {}

    public function mobileIndex(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) return redirect()->route('login');

            $member = $this->memberService->getOrCreateMember($user->id);

            $data = $this->prepareBaseData($user, $member);

            $data['walletBalance'] = $this->walletService->calculateBalance($user);

            $limits = $this->dashboardService->getVerificationLimits($user->verification_level ?? 'basic');
            $data['verificationLimits'] = $limits;
            $data['remainingDailyLimit'] = $limits['daily_withdrawal'] - ($user->daily_withdrawal_used ?? 0);

            $data['pendingWithdrawals'] = WithdrawalRequest::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])->sum('amount');

            $data['loanSummary'] = $this->getLoanSummary($user);
            $data['recentTopups'] = $this->getRecentTopups($user);
            $data['earningsBreakdown'] = $this->getEarningsBreakdown($user);
            $data['loyaltyData'] = $this->getLoyaltyData($user);
            $data['announcements'] = $this->getUserAnnouncementsUseCase->execute(
                $user->id, $user->currentMembershipTier->name ?? 'Associate'
            );
            $data['supportTickets'] = $this->getSupportTickets($user);
            $data['isMobileDashboard'] = true;

            return Inertia::render('GrowNet/GrowNet', $data);

        } catch (\Exception $e) {
            Log::error('Dashboard error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->view('errors.500', ['message' => 'Unable to load dashboard.'], 500);
        }
    }

    private function prepareBaseData($user, $member): array
    {
        $dashboardData = $this->dashboardService->getDashboardData($user->id);

        $membershipProgress = $this->getProfessionalLevelProgress($user);
        $starterKitInfo = $this->getStarterKitInfo($user);

        return array_merge($dashboardData, [
            'user' => $user,
            'membershipProgress' => $membershipProgress,
            'starterKit' => $starterKitInfo,
            'subscription' => $user->subscriptions()->where('status', 'active')->first(),
            'learningProgress' => $this->getLearningProgress($user),
            'leaderboardPosition' => $user->leaderboard_position,
            'recentAchievements' => $user->achievements()->latest()->limit(5)->get(),
            'availableProjects' => CommunityProject::where('status', 'funding')->latest()->limit(3)->get(),
            'userInvestments' => $user->projectInvestions()
                ->where('status', 'confirmed')->latest()->limit(5)->get(),
            'upcomingWorkshops' => $this->getUpcomingWorkshops(),
            'myWorkshops' => $this->getMyWorkshops($user),
            'monthlyStats' => $this->getMonthlyStats($user),
            'notifications' => $this->getNotifications($user, $membershipProgress),
            'stats' => $this->getStats($user),
        ]);
    }

    public function getDashboardStats(Request $request)
    {
        $user = $request->user();
        $member = $this->memberService->getOrCreateMember($user->id);
        $commissionSummary = $this->dashboardService->getCommissionSummary($member->id());

        return response()->json([
            'total_earnings' => $member->totalEarnings()->amount(),
            'team_size' => $member->referralCount(),
            'pending_commissions' => $commissionSummary['pending_commissions'],
            'level_breakdown' => $commissionSummary['level_breakdown'],
        ]);
    }

    public function getNetworkData(Request $request)
    {
        $user = $request->user();
        $member = $this->memberService->getOrCreateMember($user->id);
        return response()->json($this->dashboardService->getNetworkData($member->id()));
    }

    public function getCommissionSummary(Request $request)
    {
        $user = $request->user();
        $member = $this->memberService->getOrCreateMember($user->id);
        return response()->json($this->dashboardService->getCommissionSummary($member->id()));
    }

    private function getProfessionalLevelProgress($user): array
    {
        $lifetimePoints = (int) DB::table('point_transactions')
            ->where('user_id', $user->id)->sum('lp_amount');

        $currentLevel = ProfessionalLevel::where('slug', $user->current_professional_level ?? 'associate')->first();

        if (!$currentLevel) {
            return ['current_tier' => null, 'current_level' => null, 'next_tier' => null, 'lifetime_points' => 0, 'progress_percentage' => 0];
        }

        $nextLevel = ProfessionalLevel::where('level', '>', $currentLevel->level)->orderBy('level')->first();

        $progress = $nextLevel && $nextLevel->lp_required > 0
            ? min(100, ($lifetimePoints / $nextLevel->lp_required) * 100)
            : 100;

        return [
            'current_tier' => $currentLevel,
            'current_level' => $currentLevel,
            'next_tier' => $nextLevel,
            'lifetime_points' => $lifetimePoints,
            'progress_percentage' => round($progress, 1),
            'eligibility' => $nextLevel ? $lifetimePoints >= $nextLevel->lp_required : false,
        ];
    }

    private function getStarterKitInfo($user): ?array
    {
        $kit = \App\Models\Subscription::with('package')
            ->where('user_id', $user->id)
            ->whereHas('package', fn($q) => $q->where('slug', 'starter-kit-associate'))
            ->first();

        return $kit ? [
            'has_kit' => true,
            'tier' => $user->starter_kit_tier ?? 'associate',
            'purchased_at' => $user->starter_kit_purchased_at?->format('M d, Y'),
            'status' => $kit->status,
        ] : null;
    }

    private function getLearningProgress($user): array
    {
        return [
            'courses_completed' => $user->courses_completed ?? 0,
            'certificates_earned' => $user->certificates_earned ?? 0,
            'webinars_attended' => $user->webinars_attended ?? 0,
        ];
    }

    private function getUpcomingWorkshops(): array
    {
        return WorkshopModel::where('status', 'published')
            ->where('start_date', '>', now())
            ->orderBy('start_date')->limit(3)
            ->get()->map(fn($w) => [
                'id' => $w->id, 'title' => $w->title,
                'start_date' => $w->start_date->format('M d, Y'),
                'price' => $w->price, 'lp_reward' => $w->lp_reward, 'bp_reward' => $w->bp_reward,
            ])->toArray();
    }

    private function getMyWorkshops($user): array
    {
        return WorkshopRegistrationModel::with('workshop')
            ->where('user_id', $user->id)
            ->whereIn('status', ['registered', 'attended'])
            ->latest()->limit(3)
            ->get()->map(fn($r) => [
                'id' => $r->id,
                'workshop_title' => $r->workshop->title,
                'status' => $r->status,
                'attended_at' => $r->attended_at?->format('M d, Y'),
            ])->toArray();
    }

    private function getMonthlyStats($user): array
    {
        $stats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $stats[] = [
                'month' => $month->format('M Y'),
                'direct_referrals' => $user->directReferrals()
                    ->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count(),
                'commissions_earned' => $user->referralCommissions()
                    ->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('amount'),
            ];
        }
        return $stats;
    }

    private function getNotifications($user, array $membershipProgress): array
    {
        $notifications = collect();

        $newProjects = CommunityProject::where('created_at', '>=', now()->subDays(7))->count();
        if ($newProjects > 0) {
            $notifications->push(['type' => 'new_projects', 'title' => 'New Community Projects', 'message' => "$newProjects new projects available", 'priority' => 'medium']);
        }

        if (!empty($membershipProgress['eligibility'])) {
            $notifications->push(['type' => 'tier_upgrade', 'title' => 'Tier Upgrade Available', 'message' => 'You\'re eligible to upgrade!', 'priority' => 'high']);
        }

        return $notifications->values()->toArray();
    }

    private function getStats($user): array
    {
        return [
            'total_earnings' => $user->calculateTotalEarnings(),
            'achievement_count' => $user->achievements_count ?? 0,
            'courses_completed' => $user->courses_completed ?? 0,
            'lifetime_points' => (int) DB::table('point_transactions')->where('user_id', $user->id)->sum('lp_amount'),
            'business_points' => (int) DB::table('point_transactions')->where('user_id', $user->id)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('bp_amount'),
            'lifetime_referrals' => $user->referral_count ?? 0,
            'active_referrals' => $user->directReferrals()->whereHas('subscriptions', fn($q) => $q->where('status', 'active'))->count(),
            'this_month_referrals' => $user->directReferrals()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }

    private function getLoanSummary($user): array
    {
        try {
            if (class_exists(\App\Domain\Financial\Services\LoanService::class)) {
                return app(\App\Domain\Financial\Services\LoanService::class)->getLoanSummary($user);
            }
        } catch (\Exception $e) {
            Log::error('Loan service error', ['error' => $e->getMessage()]);
        }
        return ['has_loan' => false, 'can_withdraw' => true];
    }

    private function getRecentTopups($user): array
    {
        return MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->whereIn('status', ['verified', 'pending', 'processing', 'rejected'])
            ->latest()->take(50)
            ->get()->map(fn($t) => [
                'id' => $t->id, 'amount' => (float) $t->amount,
                'status' => $t->status, 'payment_method' => $t->payment_method,
                'date' => $t->created_at->format('M d, Y'),
            ])->toArray();
    }

    private function getEarningsBreakdown($user): array
    {
        return [
            'referral_earnings' => (float) ($user->total_referral_earnings ?? 0),
            'bonus_earnings' => (float) ($user->bonus_balance ?? 0),
            'total_earnings' => (float) ($user->total_earnings ?? 0),
        ];
    }

    private function getLoyaltyData($user): array
    {
        $lgrWithdrawablePercentage = $user->lgr_custom_withdrawable_percentage
            ?? \App\Models\LGR\LgrSetting::get('lgr_max_cash_conversion', 40);
        $lgrAwardedTotal = (float) ($user->loyalty_points_awarded_total ?? 0);
        $lgrWithdrawnTotal = (float) ($user->loyalty_points_withdrawn_total ?? 0);
        $lgrMaxWithdrawable = ($lgrAwardedTotal * $lgrWithdrawablePercentage / 100) - $lgrWithdrawnTotal;
        $lgrWithdrawable = (float) min($user->loyalty_points ?? 0, max(0, $lgrMaxWithdrawable));

        if ($user->lgr_withdrawal_blocked ?? false) $lgrWithdrawable = 0;

        return [
            'loyaltyPoints' => (float) ($user->loyalty_points ?? 0),
            'lgrWithdrawable' => $lgrWithdrawable,
            'lgrWithdrawablePercentage' => (int) $lgrWithdrawablePercentage,
            'lgrWithdrawalBlocked' => (bool) ($user->lgr_withdrawal_blocked ?? false),
        ];
    }

    private function getSupportTickets($user): array
    {
        try {
            $tickets = $this->ticketRepository->findByUserId(SupportUserId::fromInt($user->id));
            return array_map(fn($t) => [
                'id' => $t->id()->value(), 'subject' => $t->subject(),
                'status' => $t->status()->value, 'createdAt' => $t->createdAt()->format('Y-m-d H:i:s'),
            ], $tickets);
        } catch (\Exception $e) {
            return [];
        }
    }
}

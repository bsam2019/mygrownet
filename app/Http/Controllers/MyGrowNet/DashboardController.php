<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use App\Models\TeamVolume;
use App\Models\UserNetwork;
use App\Models\Achievement;
use App\Models\CommunityProject;
use App\Models\PhysicalReward;
use App\Models\Course;
use App\Services\MLMCommissionService;
use App\Services\MyGrowNetTierAdvancementService;
use App\Services\AssetIncomeTrackingService;
use App\Services\CommunityProjectService;
use App\Models\PhysicalRewardAllocation;
use App\Models\ProjectContribution;
use App\Models\ProjectVote;
use App\Models\ProjectProfitDistribution;
use Carbon\Carbon;
use App\Application\UseCases\Announcement\GetUserAnnouncementsUseCase;
use App\Domain\Messaging\Services\MessagingService;
use App\Domain\Messaging\ValueObjects\UserId;

class DashboardController extends Controller
{
    protected MLMCommissionService $mlmCommissionService;
    protected MyGrowNetTierAdvancementService $tierAdvancementService;
    protected AssetIncomeTrackingService $assetIncomeTrackingService;
    protected CommunityProjectService $communityProjectService;
    protected \App\Services\WalletService $walletService;
    protected \App\Services\EarningsService $earningsService;
    protected GetUserAnnouncementsUseCase $getUserAnnouncementsUseCase;
    protected MessagingService $messagingService;

    public function __construct(
        MLMCommissionService $mlmCommissionService,
        MyGrowNetTierAdvancementService $tierAdvancementService,
        AssetIncomeTrackingService $assetIncomeTrackingService,
        CommunityProjectService $communityProjectService,
        \App\Services\WalletService $walletService,
        \App\Services\EarningsService $earningsService,
        GetUserAnnouncementsUseCase $getUserAnnouncementsUseCase,
        MessagingService $messagingService
    ) {
        $this->mlmCommissionService = $mlmCommissionService;
        $this->tierAdvancementService = $tierAdvancementService;
        $this->assetIncomeTrackingService = $assetIncomeTrackingService;
        $this->communityProjectService = $communityProjectService;
        $this->walletService = $walletService;
        $this->earningsService = $earningsService;
        $this->getUserAnnouncementsUseCase = $getUserAnnouncementsUseCase;
        $this->messagingService = $messagingService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        // Check if we should redirect based on dashboard preference
        $shouldRedirect = $this->shouldRedirectToDashboard($request, $user);
        if ($shouldRedirect) {
            return redirect()->route($shouldRedirect);
        }
        
        // Load user with necessary relationships
        $user = $user->load([
            'currentMembershipTier',
            'subscriptions' => function ($query) {
                $query->where('status', 'active')->with('package');
            },
            'achievements',
            'directReferrals',
            'referralCommissions',
            'teamVolume',
            'notificationPreferences'
        ]);

        // Get current subscription
        $currentSubscription = $user->subscriptions()->where('status', 'active')->first();
        
        // Get professional level progress
        $membershipProgress = $this->getProfessionalLevelProgress($user);
        
        // Get learning progress
        $learningProgress = $this->getLearningProgress($user);
        
        // Get referral stats with seven-level tracking
        $referralStats = $this->getSevenLevelReferralStats($user);
        
        // Get leaderboard position
        $leaderboardPosition = $this->getLeaderboardPosition($user);
        
        // Get recent achievements
        $recentAchievements = $user->achievements()
            ->with(['achievement'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Get upcoming workshops
        $upcomingWorkshops = \App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopModel::where('status', 'published')
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->limit(3)
            ->get()
            ->map(function ($workshop) {
                return [
                    'id' => $workshop->id,
                    'title' => $workshop->title,
                    'slug' => $workshop->slug,
                    'category' => $workshop->category,
                    'start_date' => $workshop->start_date->format('M j, Y'),
                    'lp_reward' => $workshop->lp_reward,
                    'bp_reward' => $workshop->bp_reward,
                    'price' => number_format($workshop->price, 2),
                    'available_slots' => $workshop->availableSlots(),
                ];
            });
        
        // Get user's workshop registrations
        $myWorkshops = \App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::with('workshop')
            ->where('user_id', $user->id)
            ->whereIn('status', ['registered', 'attended'])
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'workshop_title' => $registration->workshop->title,
                    'workshop_slug' => $registration->workshop->slug,
                    'start_date' => $registration->workshop->start_date->format('M j, Y'),
                    'status' => $registration->status,
                ];
            });
        
        // Get starter kit information
        $starterKit = \App\Models\Subscription::with('package')
            ->where('user_id', $user->id)
            ->whereHas('package', function ($query) {
                $query->where('slug', 'starter-kit-associate');
            })
            ->first();
        
        $starterKitInfo = null;
        if ($starterKit) {
            $starterKitInfo = [
                'received' => true,
                'package_name' => $starterKit->package->name,
                'received_date' => $starterKit->created_at->format('M j, Y'),
                'features' => $starterKit->package->features,
                'status' => $starterKit->status,
            ];
        }
        
        // Get available community projects
        $availableProjects = CommunityProject::funding()
            ->with(['creator', 'manager'])
            ->latest()
            ->limit(3)
            ->get();
        
        // Get user project investments
        $userInvestments = $user->projectInvestments()
            ->with(['project'])
            ->where('status', 'confirmed')
            ->latest()
            ->limit(5)
            ->get();
        
        // Get monthly stats for charts
        $monthlyStats = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $month->format('M Y'),
                'direct_referrals' => $user->directReferrals()
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'project_investments' => $user->projectInvestments()
                    ->whereMonth('invested_at', $month->month)
                    ->whereYear('invested_at', $month->year)
                    ->sum('amount'),
                'team_volume' => $this->getMonthlyTeamVolume($user, $month),
                'commissions_earned' => $user->referralCommissions()
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount')
            ];
        }

        // Get notifications
        $notifications = collect();
        
        // Check for new community projects
        $newProjects = CommunityProject::where('created_at', '>=', now()->subDays(7))->count();
        if ($newProjects > 0) {
            $notifications->push([
                'type' => 'new_projects',
                'title' => 'New Investment Opportunities',
                'message' => "{$newProjects} new community projects are now available for investment.",
                'action_url' => route('mygrownet.projects.index'),
                'priority' => 'medium'
            ]);
        }
        
        // Check for subscription renewal
        if ($currentSubscription && $currentSubscription->getDaysUntilNextBilling() <= 7) {
            $notifications->push([
                'type' => 'subscription_renewal',
                'title' => 'Subscription Renewal Due',
                'message' => "Your subscription renewal is in {$currentSubscription->getDaysUntilNextBilling()} days.",
                'action_url' => route('mygrownet.subscription.manage'),
                'priority' => 'high'
            ]);
        }
        
        // Check for tier upgrade eligibility
        if (isset($membershipProgress['eligibility']) && $membershipProgress['eligibility']) {
            $notifications->push([
                'type' => 'tier_upgrade',
                'title' => 'Tier Upgrade Available',
                'message' => "You're eligible to upgrade to {$membershipProgress['next_tier']['name']}!",
                'action_url' => route('mygrownet.membership.upgrade'),
                'priority' => 'high'
            ]);
        }

        // Get team volume visualization data
        $teamVolumeData = $this->getTeamVolumeVisualization($user);

        // Get network structure data
        $networkData = $this->getNetworkStructureData($user);

        // Get asset tracking data
        $assetData = $this->getAssetTrackingData($user);

        // Get community project data
        $communityProjectData = $this->getCommunityProjectData($user);

        // Get loan summary
        $loanService = app(\App\Domain\Financial\Services\LoanService::class);
        $loanSummary = $loanService->getLoanSummary($user);

        // Get messaging data
        $messagingData = $this->getMessagingData($user);

        return Inertia::render('MyGrowNet/Dashboard', [
            'user' => $user,
            'subscription' => $currentSubscription,
            'starterKit' => $starterKitInfo,
            'membershipProgress' => $membershipProgress,
            'learningProgress' => $learningProgress,
            'referralStats' => $referralStats,
            'leaderboardPosition' => $leaderboardPosition,
            'recentAchievements' => $recentAchievements,
            'availableProjects' => $availableProjects,
            'userInvestments' => $userInvestments,
            'upcomingWorkshops' => $upcomingWorkshops,
            'myWorkshops' => $myWorkshops,
            'monthlyStats' => $monthlyStats,
            'notifications' => $notifications->values(),
            'teamVolumeData' => $teamVolumeData,
            'networkData' => $networkData,
            'assetData' => $assetData,
            'communityProjectData' => $communityProjectData,
            'loanSummary' => $loanSummary,
            'stats' => [
                'total_earnings' => $user->calculateTotalEarnings(),
                'achievement_count' => $user->achievements_count ?? 0,
                'community_projects_count' => $user->community_projects_count ?? 0,
                'courses_completed' => $user->courses_completed ?? 0,
                'lifetime_investment_returns' => $user->total_project_returns ?? 0,
                'lifetime_points' => (int) \DB::table('point_transactions')->where('user_id', $user->id)->sum('lp_amount'),
                'business_points' => (int) \DB::table('point_transactions')->where('user_id', $user->id)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('bp_amount'),
                'lifetime_referrals' => $user->referral_count ?? 0,
                'total_referral_earnings' => $user->total_referral_earnings ?? 0,
                'active_referrals' => $user->directReferrals()
                    ->whereHas('subscriptions', function ($query) {
                        $query->where('status', 'active');
                    })->count(),
                'this_month_referrals' => $user->directReferrals()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count()
            ]
        ]);
    }

    /**
     * Mobile-optimized dashboard
     */
    public function mobileIndex(Request $request)
    {
        $user = $request->user();
        
        // Check if we should redirect based on dashboard preference
        $shouldRedirect = $this->shouldRedirectToDashboard($request, $user);
        if ($shouldRedirect) {
            return redirect()->route($shouldRedirect);
        }
        
        // DEBUG: Log that we're in mobile dashboard
        \Log::info('Mobile Dashboard Accessed', [
            'user_id' => $user->id,
            'url' => $request->fullUrl()
        ]);
        
        // Try to prepare data, catch any errors
        try {
            $data = $this->prepareIndexData($request);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Mobile Dashboard Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Show error to user
            dd('Error loading mobile dashboard', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ]);
        }
        
        // Add wallet balance using WalletService
        $data['walletBalance'] = $this->walletService->calculateBalance($user);
        
        // Get verification limits
        $limits = $this->getVerificationLimits($user->verification_level ?? 'basic');
        $data['verificationLimits'] = $limits;
        $data['remainingDailyLimit'] = $limits['daily_withdrawal'] - ($user->daily_withdrawal_used ?? 0);
        
        // Get pending withdrawals
        $data['pendingWithdrawals'] = \App\Models\WithdrawalRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');
        
        // Get loan summary (if exists)
        try {
            if (class_exists(\App\Domain\Financial\Services\LoanService::class)) {
                $loanService = app(\App\Domain\Financial\Services\LoanService::class);
                $data['loanSummary'] = $loanService->getLoanSummary($user);
            } else {
                $data['loanSummary'] = [
                    'has_loan' => false,
                    'can_withdraw' => true,
                ];
            }
        } catch (\Exception $e) {
            $data['loanSummary'] = [
                'has_loan' => false,
                'can_withdraw' => true,
            ];
        }
        
        // Get all wallet top-ups (including pending) for mobile
        $data['recentTopups'] = \App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->whereIn('status', ['verified', 'pending', 'processing', 'rejected'])
            ->latest()
            ->take(50) // Increased limit for mobile view
            ->get()
            ->map(function ($topup) {
                return [
                    'id' => $topup->id,
                    'amount' => (float) $topup->amount,
                    'status' => $topup->status,
                    'payment_method' => $topup->payment_method,
                    'payment_reference' => $topup->payment_reference,
                    'date' => $topup->created_at->format('M d, Y'),
                    'time' => $topup->created_at->format('h:i A'),
                ];
            })
            ->toArray();
        
        // Add earnings breakdown for mobile
        $data['earningsBreakdown'] = $this->getEarningsBreakdown($user);
        
        // Add LGR data for mobile
        $lgrWithdrawablePercentage = $user->lgr_custom_withdrawable_percentage 
            ?? \App\Models\LgrSetting::get('lgr_max_cash_conversion', 40);
        $lgrAwardedTotal = (float) ($user->loyalty_points_awarded_total ?? 0);
        $lgrWithdrawnTotal = (float) ($user->loyalty_points_withdrawn_total ?? 0);
        $lgrMaxWithdrawable = ($lgrAwardedTotal * $lgrWithdrawablePercentage / 100) - $lgrWithdrawnTotal;
        $lgrWithdrawable = (float) min($user->loyalty_points ?? 0, max(0, $lgrMaxWithdrawable));
        $lgrWithdrawalBlocked = (bool) ($user->lgr_withdrawal_blocked ?? false);
        if ($lgrWithdrawalBlocked) {
            $lgrWithdrawable = 0.0;
        }
        
        $data['loyaltyPoints'] = (float) ($user->loyalty_points ?? 0);
        $data['lgrWithdrawable'] = (float) $lgrWithdrawable;
        $data['lgrWithdrawablePercentage'] = (int) $lgrWithdrawablePercentage;
        $data['lgrWithdrawalBlocked'] = $lgrWithdrawalBlocked;
        
        // Get announcements for user
        $userTier = $user->currentMembershipTier->name ?? 'Associate';
        $data['announcements'] = $this->getUserAnnouncementsUseCase->execute($user->id, $userTier);
        
        // Get messaging data for mobile
        $data['messagingData'] = $this->getMessagingData($user);
        
        // DEBUG: Add a flag to identify mobile dashboard
        $data['isMobileDashboard'] = true;
        $data['debugInfo'] = [
            'component' => 'MyGrowNet/MobileDashboard',
            'timestamp' => now()->toDateTimeString(),
            'user' => $user->name,
            'walletBalance' => $data['walletBalance']
        ];
        
        \Log::info('Rendering Mobile Dashboard', [
            'component' => 'MyGrowNet/MobileDashboard',
            'data_keys' => array_keys($data),
            'walletBalance' => $data['walletBalance']
        ]);
        
        // Render the mobile dashboard component instead
        return Inertia::render('MyGrowNet/MobileDashboard', $data);
    }

    /**
     * Prepare dashboard data (shared between desktop and mobile)
     */
    private function prepareIndexData(Request $request): array
    {
        $user = $request->user();
        
        // Load user with necessary relationships
        $user = $user->load([
            'currentMembershipTier',
            'subscriptions' => function ($query) {
                $query->where('status', 'active')->with('package');
            },
            'achievements',
            'projectInvestments'
        ]);

        // Get current subscription
        $currentSubscription = $user->subscriptions()->where('status', 'active')->first();
        
        // Get professional level progress
        $membershipProgress = $this->getProfessionalLevelProgress($user);
        
        // Get learning progress
        $learningProgress = $this->getLearningProgress($user);
        
        // Get referral statistics
        $referralStats = $this->getSevenLevelReferralStats($user);
        
        // Get leaderboard position
        $leaderboardPosition = $this->getLeaderboardPosition($user);
        
        // Get recent achievements
        $recentAchievements = $user->achievements()
            ->latest()
            ->limit(5)
            ->get();
        
        // Get upcoming workshops
        $upcomingWorkshops = \App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopModel::where('status', 'published')
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->limit(3)
            ->get()
            ->map(function ($workshop) {
                return [
                    'id' => $workshop->id,
                    'title' => $workshop->title,
                    'start_date' => $workshop->start_date->format('M d, Y'),
                    'price' => $workshop->price,
                    'lp_reward' => $workshop->lp_reward,
                    'bp_reward' => $workshop->bp_reward,
                ];
            });
        
        // Get user's workshop registrations
        $myWorkshops = \App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel::with('workshop')
            ->where('user_id', $user->id)
            ->whereIn('status', ['registered', 'attended'])
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'workshop_title' => $registration->workshop->title,
                    'status' => $registration->status,
                    'attended_at' => $registration->attended_at?->format('M d, Y'),
                ];
            });
        
        // Get starter kit information
        $starterKit = \App\Models\Subscription::with('package')
            ->where('user_id', $user->id)
            ->whereHas('package', function ($query) {
                $query->where('slug', 'starter-kit-associate');
            })
            ->first();
        
        $starterKitInfo = null;
        if ($starterKit) {
            $starterKitInfo = [
                'has_kit' => true,
                'tier' => $user->starter_kit_tier ?? 'associate',
                'purchased_at' => $user->starter_kit_purchased_at?->format('M d, Y'),
                'status' => $starterKit->status,
            ];
        }
        
        // Get available community projects
        $availableProjects = CommunityProject::where('status', 'funding')
            ->latest()
            ->limit(3)
            ->get();
        
        // Get user project investments
        $userInvestments = $user->projectInvestments()
            ->with(['project'])
            ->where('status', 'confirmed')
            ->latest()
            ->limit(5)
            ->get();
        
        // Get monthly stats for charts
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $month->format('M Y'),
                'direct_referrals' => $user->directReferrals()
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'project_investments' => $user->projectInvestments()
                    ->whereMonth('invested_at', $month->month)
                    ->whereYear('invested_at', $month->year)
                    ->sum('amount'),
                'team_volume' => $this->getMonthlyTeamVolume($user, $month),
                'commissions_earned' => $user->referralCommissions()
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount')
            ];
        }
        
        // Prepare notifications
        $notifications = collect();
        
        // Check for new community projects
        $newProjects = CommunityProject::where('created_at', '>=', now()->subDays(7))->count();
        if ($newProjects > 0) {
            $notifications->push([
                'type' => 'new_projects',
                'title' => 'New Community Projects',
                'message' => "$newProjects new community projects available for investment",
                'action_url' => route('mygrownet.projects.index'),
                'priority' => 'medium'
            ]);
        }
        
        // Check for tier upgrade eligibility
        if (isset($membershipProgress['eligibility']) && $membershipProgress['eligibility']) {
            $notifications->push([
                'type' => 'tier_upgrade',
                'title' => 'Tier Upgrade Available',
                'message' => "You're eligible to upgrade to {$membershipProgress['next_tier']['name']}!",
                'action_url' => route('mygrownet.membership.upgrade'),
                'priority' => 'high'
            ]);
        }

        // Get team volume visualization data
        $teamVolumeData = $this->getTeamVolumeData($request);
        
        // Get network data (use direct method, not API endpoint)
        $networkData = $this->getNetworkStructureData($user);
        
        // Get asset data (use local method instead of service)
        $assetData = $this->getAssetTrackingData($user);
        
        // Get community project data
        $communityProjectData = $this->getCommunityProjectData($user);
        
        // Get loan summary (with fallback if service doesn't exist)
        try {
            if (class_exists(\App\Domain\Financial\Services\LoanService::class)) {
                $loanService = app(\App\Domain\Financial\Services\LoanService::class);
                $loanSummary = $loanService->getLoanSummary($user);
            } else {
                // Fallback: basic loan data from user model
                $loanSummary = [
                    'total_borrowed' => (float) ($user->loan_balance ?? 0),
                    'total_repaid' => (float) ($user->total_loan_repaid ?? 0),
                    'outstanding_balance' => (float) ($user->loan_balance ?? 0),
                    'loan_limit' => (float) ($user->loan_limit ?? 0),
                    'available_credit' => (float) max(0, ($user->loan_limit ?? 0) - ($user->loan_balance ?? 0)),
                    'loan_balance' => (float) ($user->loan_balance ?? 0),
                ];
            }
        } catch (\Exception $e) {
            // Fallback on error
            $loanSummary = [
                'total_borrowed' => 0.0,
                'total_repaid' => 0.0,
                'outstanding_balance' => 0.0,
                'loan_limit' => 0.0,
                'available_credit' => 0.0,
                'loan_balance' => 0.0,
            ];
        }

        return [
            'user' => $user,
            'subscription' => $currentSubscription,
            'starterKit' => $starterKitInfo,
            'membershipProgress' => $membershipProgress,
            'learningProgress' => $learningProgress,
            'referralStats' => $referralStats,
            'leaderboardPosition' => $leaderboardPosition,
            'recentAchievements' => $recentAchievements,
            'availableProjects' => $availableProjects,
            'userInvestments' => $userInvestments,
            'upcomingWorkshops' => $upcomingWorkshops,
            'myWorkshops' => $myWorkshops,
            'monthlyStats' => $monthlyStats,
            'notifications' => $notifications->values(),
            'teamVolumeData' => $teamVolumeData,
            'networkData' => $networkData,
            'assetData' => $assetData,
            'communityProjectData' => $communityProjectData,
            'loanSummary' => $loanSummary,
            'stats' => [
                'total_earnings' => $user->calculateTotalEarnings(),
                'achievement_count' => $user->achievements_count ?? 0,
                'community_projects_count' => $user->community_projects_count ?? 0,
                'courses_completed' => $user->courses_completed ?? 0,
                'lifetime_investment_returns' => $user->total_project_returns ?? 0,
                'lifetime_points' => (int) \DB::table('point_transactions')->where('user_id', $user->id)->sum('lp_amount'),
                'business_points' => (int) \DB::table('point_transactions')->where('user_id', $user->id)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('bp_amount'),
                'lifetime_referrals' => $user->referral_count ?? 0,
                'total_referral_earnings' => $user->total_referral_earnings ?? 0,
                'active_referrals' => $user->directReferrals()
                    ->whereHas('subscriptions', function ($query) {
                        $query->where('status', 'active');
                    })->count(),
                'this_month_referrals' => $user->directReferrals()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count()
            ]
        ];
    }

    /**
     * Get seven-level referral statistics with team volume tracking
     */
    private function getSevenLevelReferralStats(User $user): array
    {
        $stats = [
            'total_referrals' => $user->referral_count ?? 0,
            'active_referrals' => $user->directReferrals()
                ->whereHas('subscriptions', function ($query) {
                    $query->where('status', 'active');
                })->count(),
            'this_month_referrals' => $user->directReferrals()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'referral_earnings' => $user->total_referral_earnings ?? 0,
            'referral_code' => $user->referral_code,
            'levels' => []
        ];

        // Get seven-level commission breakdown
        $levelsData = [];
        for ($level = 1; $level <= 7; $level++) {
            $levelCommissions = $user->referralCommissions()
                ->where('level', $level)
                ->where('status', 'paid')
                ->get();

            $thisMonthCommissions = $user->referralCommissions()
                ->where('level', $level)
                ->where('status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->get();

            // Get actual users at this level
            $usersAtLevel = UserNetwork::where('referrer_id', $user->id)
                ->where('level', $level)
                ->with(['user' => function($query) {
                    $query->select('id', 'name', 'email', 'phone', 'created_at')
                        ->with(['currentMembershipTier:id,name', 'subscriptions' => function($q) {
                            $q->where('status', 'active')->select('id', 'user_id', 'status');
                        }]);
                }])
                ->get()
                ->map(function($network) {
                    $user = $network->user;
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'tier' => $user->currentMembershipTier->name ?? 'Associate',
                        'is_active' => $user->subscriptions->count() > 0,
                        'joined_date' => $user->created_at->format('M d, Y'),
                    ];
                })
                ->toArray();

            $levelsData[] = [
                'level' => $level,
                'count' => $this->getReferralCountAtLevel($user, $level),
                'total_earnings' => $levelCommissions->sum('amount'),
                'this_month_earnings' => $thisMonthCommissions->sum('amount'),
                'team_volume' => $this->getTeamVolumeAtLevel($user, $level),
                'members' => $usersAtLevel
            ];
        }
        
        $stats['levels'] = $levelsData;

        return $stats;
    }

    /**
     * Get team volume visualization data
     */
    private function getTeamVolumeVisualization(User $user): array
    {
        $currentMonth = now();
        $teamVolume = $user->teamVolume()
            ->whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->first();
        
        // If no current month data, get the latest available
        if (!$teamVolume) {
            $teamVolume = $user->teamVolume()->latest()->first();
        }
        
        return [
            'current_month' => [
                'personal_volume' => $teamVolume->personal_volume ?? 0,
                'team_volume' => $teamVolume->team_volume ?? 0,
                'left_leg_volume' => $teamVolume->left_leg_volume ?? 0,
                'right_leg_volume' => $teamVolume->right_leg_volume ?? 0,
                'total_volume' => $teamVolume->total_volume ?? 0
            ],
            'monthly_trend' => $this->getMonthlyVolumeHistory($user, 6),
            'volume_breakdown' => [
                'subscriptions' => $this->getVolumeBySource($user, 'subscriptions'),
                'upgrades' => $this->getVolumeBySource($user, 'upgrades'),
                'renewals' => $this->getVolumeBySource($user, 'renewals')
            ],
            'tier_requirements' => $this->getTierVolumeRequirements($user)
        ];
    }

    /**
     * Get network structure data for multilevel display
     */
    private function getNetworkStructureData(User $user): array
    {
        $directReferrals = $user->directReferrals()
            ->with(['currentMembershipTier', 'subscriptions', 'teamVolume'])
            ->get()
            ->map(function ($referral) {
                $latestTeamVolume = $referral->teamVolume()->latest()->first();
                
                return [
                    'id' => $referral->id,
                    'name' => $referral->name,
                    'tier' => $referral->currentMembershipTier->name ?? 'None',
                    'status' => $referral->hasActiveSubscription() ? 'active' : 'inactive',
                    'joined_date' => $referral->created_at->format('M Y'),
                    'personal_volume' => $latestTeamVolume->personal_volume ?? 0,
                    'team_size' => $this->getTeamSize($referral),
                    'level' => 1
                ];
            });

        return [
            'direct_referrals' => $directReferrals,
            'network_depth' => $this->getNetworkDepth($user),
            'total_network_size' => $this->getTotalNetworkSize($user),
            'active_members' => $this->getActiveNetworkMembers($user),
            'network_growth' => $this->getNetworkGrowthData($user),
            'level_breakdown' => $this->getNetworkLevelBreakdown($user)
        ];
    }

    /**
     * Get professional level progress (NEW - MyGrowNet Points System)
     */
    private function getProfessionalLevelProgress(User $user): array
    {
        // Calculate lifetime points from transactions
        $lifetimePoints = (int) \DB::table('point_transactions')
            ->where('user_id', $user->id)
            ->sum('lp_amount');
        
        // Get current level
        $currentLevelSlug = $user->current_professional_level ?? 'associate';
        $currentLevel = \App\Models\ProfessionalLevel::where('slug', $currentLevelSlug)->first();
        
        // If current level not found, return default
        if (!$currentLevel) {
            return [
                'current_tier' => null,
                'current_level' => null,
                'next_tier' => null,
                'next_level' => null,
                'lifetime_points' => 0,
                'progress_percentage' => 0,
                'points_needed' => 0,
                'eligibility' => false,
                'message' => 'Professional level not configured. Please contact support.'
            ];
        }
        
        // Get next level
        $nextLevel = \App\Models\ProfessionalLevel::where('level', '>', $currentLevel->level)
            ->orderBy('level')
            ->first();
        
        if (!$nextLevel) {
            return [
                'current_tier' => $currentLevel, // Use current_tier for Vue compatibility
                'current_level' => $currentLevel,
                'next_tier' => null,
                'next_level' => null,
                'lifetime_points' => $lifetimePoints,
                'progress_percentage' => 100,
                'points_needed' => 0,
                'eligibility' => false, // No next level to be eligible for
                'message' => 'Congratulations! You\'ve reached the highest level.'
            ];
        }
        
        // Calculate progress
        $pointsNeeded = $nextLevel->lp_required - $lifetimePoints;
        $progressPercentage = $nextLevel->lp_required > 0 
            ? min(100, ($lifetimePoints / $nextLevel->lp_required) * 100)
            : 0;
        
        return [
            'current_tier' => $currentLevel, // Use current_tier for Vue compatibility
            'current_level' => $currentLevel,
            'next_tier' => $nextLevel,
            'next_level' => $nextLevel,
            'lifetime_points' => $lifetimePoints,
            'progress_percentage' => round($progressPercentage, 1),
            'points_needed' => max(0, $pointsNeeded),
            'eligibility' => $pointsNeeded <= 0, // Eligible if enough points
        ];
    }

    /**
     * Get membership tier progress (OLD - VBIF System - Keep for backward compatibility)
     */
    private function getMembershipProgress(User $user): array
    {
        $currentTier = $user->currentMembershipTier;
        
        if (!$currentTier) {
            $firstTier = InvestmentTier::active()->ordered()->first();
            $eligibility = $firstTier ? $this->tierAdvancementService->checkEligibility($user) : null;
            
            return [
                'current_tier' => null,
                'next_tier' => $firstTier,
                'progress_percentage' => 0,
                'eligibility' => $eligibility
            ];
        }

        $nextTier = $currentTier->getNextTier();
        
        if (!$nextTier) {
            return [
                'current_tier' => $currentTier,
                'next_tier' => null,
                'progress_percentage' => 100,
                'eligibility' => null,
                'message' => 'Congratulations! You\'ve reached the highest tier.'
            ];
        }

        $progress = $this->tierAdvancementService->calculateTierProgress($user, $currentTier, $nextTier);
        $eligibility = $this->tierAdvancementService->checkEligibility($user);

        return [
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'progress_percentage' => $progress,
            'eligibility' => $eligibility
        ];
    }

    /**
     * Get learning progress
     */
    private function getLearningProgress(User $user): array
    {
        return [
            'courses_completed' => $user->courses_completed ?? 0,
            'current_courses' => [], // Would be populated from course completion table
            'recommended_courses' => [], // Based on user's tier and progress
            'certificates_earned' => $user->certificates_earned ?? 0,
            'webinars_attended' => $user->webinars_attended ?? 0
        ];
    }

    /**
     * Get leaderboard position
     */
    private function getLeaderboardPosition(User $user): ?int
    {
        return $user->leaderboard_position;
    }

    // Helper methods for data calculations

    private function getReferralCountAtLevel(User $user, int $level): int
    {
        if (method_exists($this->mlmCommissionService, 'getReferralCountAtLevel')) {
            return $this->mlmCommissionService->getReferralCountAtLevel($user, $level);
        }
        
        // Fallback implementation
        return UserNetwork::where('referrer_id', $user->id)
            ->where('level', $level)
            ->count();
    }

    private function getTeamVolumeAtLevel(User $user, int $level): float
    {
        if (method_exists($this->mlmCommissionService, 'getTeamVolumeAtLevel')) {
            return $this->mlmCommissionService->getTeamVolumeAtLevel($user, $level);
        }
        
        // Fallback implementation
        $networkMembers = UserNetwork::where('referrer_id', $user->id)
            ->where('level', $level)
            ->pluck('user_id');
            
        return TeamVolume::whereIn('user_id', $networkMembers)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('personal_volume');
    }

    private function getMonthlyTeamVolume(User $user, Carbon $month): float
    {
        return TeamVolume::where('user_id', $user->id)
            ->whereMonth('created_at', $month->month)
            ->whereYear('created_at', $month->year)
            ->sum('team_volume');
    }

    private function getMonthlyVolumeHistory(User $user, int $months): array
    {
        $history = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $history[] = [
                'month' => $month->format('M Y'),
                'volume' => $this->getMonthlyTeamVolume($user, $month)
            ];
        }
        return $history;
    }

    private function getVolumeBySource(User $user, string $source): float
    {
        $currentMonth = now();
        
        switch ($source) {
            case 'subscriptions':
                return $user->subscriptions()
                    ->where('status', 'active')
                    ->whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->sum('amount');
                    
            case 'upgrades':
                return $user->tierUpgrades()
                    ->whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->sum('total_investment_amount');
                    
            case 'renewals':
                return $user->subscriptions()
                    ->where('status', 'active')
                    ->whereMonth('renewal_date', $currentMonth->month)
                    ->whereYear('renewal_date', $currentMonth->year)
                    ->sum('amount');
                    
            default:
                return 0;
        }
    }

    private function getTierVolumeRequirements(User $user): array
    {
        $currentTier = $user->currentMembershipTier;
        $nextTier = $currentTier?->getNextTier();
        
        if (!$nextTier) {
            return [];
        }

        return [
            'current_requirement' => $currentTier->team_volume_requirement ?? 0,
            'next_requirement' => $nextTier->team_volume_requirement ?? 0,
            'progress' => $user->teamVolume->team_volume ?? 0
        ];
    }

    private function getNetworkDepth(User $user): int
    {
        return UserNetwork::where('referrer_id', $user->id)->max('level') ?? 0;
    }

    private function getTotalNetworkSize(User $user): int
    {
        return UserNetwork::where('referrer_id', $user->id)->count();
    }

    private function getActiveNetworkMembers(User $user): int
    {
        return UserNetwork::where('referrer_id', $user->id)
            ->whereHas('user.subscriptions', function ($query) {
                $query->where('status', 'active');
            })->count();
    }

    private function getTeamSize(User $user): int
    {
        return UserNetwork::where('referrer_id', $user->id)->count();
    }

    private function getNetworkGrowthData(User $user): array
    {
        $growth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $growth[] = [
                'month' => $month->format('M Y'),
                'new_members' => UserNetwork::where('referrer_id', $user->id)
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count()
            ];
        }
        return $growth;
    }

    private function getNetworkLevelBreakdown(User $user): array
    {
        $breakdown = [];
        for ($level = 1; $level <= 7; $level++) {
            $levelMembers = UserNetwork::where('referrer_id', $user->id)
                ->where('level', $level)
                ->count();
                
            $activeMembers = UserNetwork::where('referrer_id', $user->id)
                ->where('level', $level)
                ->whereHas('user.subscriptions', function ($query) {
                    $query->where('status', 'active');
                })
                ->count();
                
            $breakdown[$level] = [
                'level' => $level,
                'total_members' => $levelMembers,
                'active_members' => $activeMembers,
                'percentage_active' => $levelMembers > 0 ? round(($activeMembers / $levelMembers) * 100, 1) : 0
            ];
        }
        return $breakdown;
    }

    /**
     * API endpoint for dashboard statistics
     */
    public function getDashboardStats(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'total_earnings' => $this->earningsService->calculateTotalEarnings($user),
            'this_month_earnings' => $this->getThisMonthEarnings($user),
            'team_size' => $this->getTotalNetworkSize($user),
            'active_team_members' => $this->getActiveNetworkMembers($user),
            'current_tier' => $user->currentMembershipTier?->name,
            'tier_progress' => $this->getMembershipProgress($user)['progress_percentage'],
            'commission_levels' => $this->getFiveLevelCommissionSummary($user),
            'team_volume' => $user->teamVolume->team_volume ?? 0
        ]);
    }

    /**
     * API endpoint for network data
     */
    public function getNetworkData(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'network_structure' => $this->getNetworkStructureData($user),
            'referral_stats' => $this->getSevenLevelReferralStats($user),
            'growth_trend' => $this->getNetworkGrowthData($user)
        ]);
    }

    /**
     * API endpoint for commission summary
     */
    public function getCommissionSummary(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'total_commissions' => $user->total_referral_earnings ?? 0,
            'this_month_commissions' => $this->getThisMonthCommissions($user),
            'pending_commissions' => $this->getPendingCommissions($user),
            'commission_breakdown' => $this->getFiveLevelCommissionBreakdown($user),
            'payment_history' => $this->getCommissionPaymentHistory($user)
        ]);
    }

    /**
     * API endpoint for team volume data
     */
    public function getTeamVolumeData(Request $request)
    {
        $user = $request->user();
        
        return response()->json($this->getTeamVolumeVisualization($user));
    }

    /**
     * API endpoint for seven-level commission data
     */
    public function getFiveLevelCommissionData(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'referral_stats' => $this->getSevenLevelReferralStats($user),
            'commission_breakdown' => $this->getFiveLevelCommissionBreakdown($user),
            'level_performance' => $this->getLevelPerformanceMetrics($user)
        ]);
    }

    /**
     * API endpoint for network structure data
     */
    public function getNetworkStructure(Request $request)
    {
        $user = $request->user();
        $depth = $request->get('depth', 3); // Default to 3 levels deep
        
        return response()->json([
            'network_structure' => $this->getDetailedNetworkStructure($user, $depth),
            'level_breakdown' => $this->getNetworkLevelBreakdown($user),
            'growth_metrics' => $this->getNetworkGrowthMetrics($user)
        ]);
    }

    // Additional helper methods for API endpoints

    private function getThisMonthEarnings(User $user): float
    {
        return $this->earningsService->getThisMonthEarnings($user);
    }

    private function getThisMonthCommissions(User $user): float
    {
        return $this->earningsService->getThisMonthEarnings($user);
    }

    private function getPendingCommissions(User $user): float
    {
        return $this->earningsService->getPendingEarnings($user);
    }

    private function getFiveLevelCommissionSummary(User $user): array
    {
        $summary = [];
        for ($level = 1; $level <= 7; $level++) {
            $summary[$level] = [
                'level' => $level,
                'count' => $this->getReferralCountAtLevel($user, $level),
                'earnings' => $user->referralCommissions()
                    ->where('level', $level)
                    ->where('status', 'paid')
                    ->sum('amount')
            ];
        }
        return $summary;
    }

    private function getFiveLevelCommissionBreakdown(User $user): array
    {
        $breakdown = [];
        for ($level = 1; $level <= 7; $level++) {
            $commissions = $user->referralCommissions()
                ->where('level', $level)
                ->get();

            $breakdown[$level] = [
                'level' => $level,
                'total' => $commissions->sum('amount'),
                'paid' => $commissions->where('status', 'paid')->sum('amount'),
                'pending' => $commissions->where('status', 'pending')->sum('amount'),
                'this_month' => $commissions
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->sum('amount')
            ];
        }
        return $breakdown;
    }

    private function getCommissionPaymentHistory(User $user): array
    {
        return $user->referralCommissions()
            ->where('status', 'paid')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($commission) {
                return [
                    'id' => $commission->id,
                    'amount' => $commission->amount,
                    'level' => $commission->level,
                    'type' => $commission->type,
                    'paid_at' => $commission->updated_at->format('M d, Y'),
                    'referral_name' => $commission->referral?->name
                ];
            })
            ->toArray();
    }


    /**
     * Get detailed network structure with multiple levels
     */
    private function getDetailedNetworkStructure(User $user, int $maxDepth = 3): array
    {
        return $this->buildNetworkTree($user, 1, $maxDepth);
    }

    /**
     * Recursively build network tree structure
     */
    private function buildNetworkTree(User $user, int $currentLevel, int $maxDepth): array
    {
        if ($currentLevel > $maxDepth) {
            return [];
        }
        
        $directReferrals = $user->directReferrals()
            ->with(['currentMembershipTier', 'subscriptions', 'teamVolume'])
            ->get();
            
        return $directReferrals->map(function ($referral) use ($currentLevel, $maxDepth) {
            $latestTeamVolume = $referral->teamVolume()->latest()->first();
            
            $node = [
                'id' => $referral->id,
                'name' => $referral->name,
                'email' => $referral->email,
                'phone' => $referral->phone,
                'tier' => $referral->currentMembershipTier->name ?? 'None',
                'status' => $referral->hasActiveSubscription() ? 'active' : 'inactive',
                'joined_date' => $referral->created_at->format('M Y'),
                'personal_volume' => $latestTeamVolume->personal_volume ?? 0,
                'team_size' => $this->getTeamSize($referral),
                'has_starter_kit' => $referral->starterKitPurchases()->exists(),
                'direct_referrals' => $referral->directReferrals()->count(),
                'level' => $currentLevel,
                'children' => []
            ];
            
            // Add children if we haven't reached max depth
            if ($currentLevel < $maxDepth) {
                $node['children'] = $this->buildNetworkTree($referral, $currentLevel + 1, $maxDepth);
            }
            
            return $node;
        })->toArray();
    }

    /**
     * Get network growth metrics
     */
    private function getNetworkGrowthMetrics(User $user): array
    {
        $currentMonth = now();
        $lastMonth = now()->subMonth();
        
        $currentMonthGrowth = UserNetwork::where('referrer_id', $user->id)
            ->whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->count();
            
        $lastMonthGrowth = UserNetwork::where('referrer_id', $user->id)
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();
            
        $growthRate = $lastMonthGrowth > 0 
            ? (($currentMonthGrowth - $lastMonthGrowth) / $lastMonthGrowth) * 100 
            : ($currentMonthGrowth > 0 ? 100 : 0);
            
        return [
            'current_month_growth' => $currentMonthGrowth,
            'last_month_growth' => $lastMonthGrowth,
            'growth_rate_percentage' => round($growthRate, 1),
            'total_network_size' => $this->getTotalNetworkSize($user),
            'active_percentage' => $this->getActiveNetworkPercentage($user),
            'average_depth' => $this->getAverageNetworkDepth($user)
        ];
    }

    /**
     * Get active network percentage
     */
    private function getActiveNetworkPercentage(User $user): float
    {
        $totalMembers = $this->getTotalNetworkSize($user);
        $activeMembers = $this->getActiveNetworkMembers($user);
        
        return $totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100, 1) : 0;
    }

    /**
     * Get average network depth
     */
    private function getAverageNetworkDepth(User $user): float
    {
        $networkMembers = UserNetwork::where('referrer_id', $user->id)->get();
        
        if ($networkMembers->isEmpty()) {
            return 0;
        }
        
        $totalDepth = $networkMembers->sum('level');
        return round($totalDepth / $networkMembers->count(), 1);
    }

    /**
     * Get comprehensive asset tracking data
     */
    private function getAssetTrackingData(User $user): array
    {
        $allocations = PhysicalRewardAllocation::with(['physicalReward'])
            ->where('user_id', $user->id)
            ->get();

        if ($allocations->isEmpty()) {
            return [
                'summary' => [
                    'total_assets' => 0,
                    'active_assets' => 0,
                    'total_income_generated' => 0,
                    'monthly_income_average' => 0,
                    'assets_pending_ownership' => 0
                ],
                'assets' => [],
                'income_trends' => [],
                'maintenance_alerts' => [],
                'ownership_opportunities' => []
            ];
        }

        // Get comprehensive income report
        $incomeReport = $this->assetIncomeTrackingService->getUserIncomeReport($user);

        // Get individual asset progress
        $assetProgress = $allocations->map(function ($allocation) {
            return [
                'id' => $allocation->id,
                'name' => $allocation->physicalReward->name,
                'category' => $allocation->physicalReward->category,
                'status' => $allocation->status,
                'progress' => $allocation->getProgress(),
                'estimated_value' => $allocation->physicalReward->estimated_value,
                'total_income' => $allocation->total_income_generated,
                'monthly_average' => $allocation->monthly_income_average,
                'allocated_at' => $allocation->allocated_at->format('M d, Y'),
                'maintenance_compliant' => $allocation->maintenance_compliant,
                'ownership_eligible' => $allocation->isEligibleForOwnershipTransfer()
            ];
        });

        // Get maintenance alerts
        $maintenanceAlerts = $this->getMaintenanceAlerts($user);

        // Get ownership opportunities
        $ownershipOpportunities = $this->getOwnershipOpportunities($user);

        return [
            'summary' => [
                'total_assets' => $allocations->count(),
                'active_assets' => $allocations->whereIn('status', ['delivered', 'ownership_transferred'])->count(),
                'total_income_generated' => $incomeReport['summary']['total_income_generated'],
                'monthly_income_average' => $incomeReport['summary']['monthly_average'],
                'assets_pending_ownership' => $allocations->where('status', 'delivered')
                    ->filter(fn($a) => $a->isEligibleForOwnershipTransfer())->count(),
                'estimated_annual_income' => $incomeReport['summary']['estimated_annual_income'],
                'roi_percentage' => $incomeReport['summary']['roi_percentage']
            ],
            'assets' => $assetProgress,
            'income_breakdown' => $incomeReport['asset_breakdown'],
            'performance_metrics' => $incomeReport['performance_metrics'],
            'income_trends' => $this->getAssetIncomeTrends($user),
            'maintenance_alerts' => $maintenanceAlerts,
            'ownership_opportunities' => $ownershipOpportunities,
            'recommendations' => $this->assetIncomeTrackingService->getIncomeOptimizationRecommendations($user)
        ];
    }

    /**
     * Get maintenance alerts for user assets
     */
    private function getMaintenanceAlerts(User $user): array
    {
        $alerts = [];

        $maintenanceDue = PhysicalRewardAllocation::with('physicalReward')
            ->where('user_id', $user->id)
            ->maintenanceDue()
            ->get();

        foreach ($maintenanceDue as $allocation) {
            $alerts[] = [
                'type' => 'maintenance_due',
                'asset_name' => $allocation->physicalReward->name,
                'message' => 'Monthly maintenance check required',
                'priority' => 'high',
                'due_date' => $allocation->last_maintenance_check 
                    ? $allocation->last_maintenance_check->addMonth()->format('M d, Y')
                    : 'Overdue',
                'allocation_id' => $allocation->id
            ];
        }

        $nonCompliant = PhysicalRewardAllocation::with('physicalReward')
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->where('maintenance_compliant', false)
            ->get();

        foreach ($nonCompliant as $allocation) {
            $alerts[] = [
                'type' => 'maintenance_violation',
                'asset_name' => $allocation->physicalReward->name,
                'message' => 'Asset maintenance requirements not met - risk of forfeiture',
                'priority' => 'critical',
                'allocation_id' => $allocation->id
            ];
        }

        return $alerts;
    }

    /**
     * Get ownership transfer opportunities
     */
    private function getOwnershipOpportunities(User $user): array
    {
        $opportunities = [];

        $eligible = PhysicalRewardAllocation::with('physicalReward')
            ->where('user_id', $user->id)
            ->eligibleForOwnershipTransfer()
            ->get();

        foreach ($eligible as $allocation) {
            $opportunities[] = [
                'allocation_id' => $allocation->id,
                'asset_name' => $allocation->physicalReward->name,
                'category' => $allocation->physicalReward->category,
                'estimated_value' => $allocation->physicalReward->estimated_value,
                'maintenance_completed' => $allocation->maintenance_months_completed,
                'maintenance_required' => $allocation->physicalReward->maintenance_period_months,
                'eligible_since' => $allocation->delivered_at->addMonths($allocation->physicalReward->maintenance_period_months)->format('M d, Y')
            ];
        }

        return $opportunities;
    }

    /**
     * Get asset income trends
     */
    private function getAssetIncomeTrends(User $user): array
    {
        $trends = [];
        
        // Get last 6 months of income data
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            
            // This would ideally query detailed income records
            // For now, we'll calculate based on current averages
            $monthlyIncome = PhysicalRewardAllocation::where('user_id', $user->id)
                ->whereIn('status', ['delivered', 'ownership_transferred'])
                ->where('delivered_at', '<=', $month->endOfMonth())
                ->sum('monthly_income_average');

            $trends[] = [
                'month' => $month->format('M Y'),
                'income' => $monthlyIncome,
                'asset_count' => PhysicalRewardAllocation::where('user_id', $user->id)
                    ->whereIn('status', ['delivered', 'ownership_transferred'])
                    ->where('delivered_at', '<=', $month->endOfMonth())
                    ->count()
            ];
        }

        return $trends;
    }

    /**
     * API endpoint for asset tracking data
     */
    public function getAssetTrackingDataApi(Request $request)
    {
        $user = $request->user();
        
        return response()->json($this->getAssetTrackingData($user));
    }

    /**
     * API endpoint for asset performance analytics
     */
    public function getAssetPerformanceAnalytics(Request $request, PhysicalRewardAllocation $allocation)
    {
        // Ensure user owns this allocation
        if ($allocation->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $analytics = $this->assetIncomeTrackingService->getAssetPerformanceAnalytics($allocation);
        
        return response()->json($analytics);
    }

    /**
     * API endpoint for recording asset income
     */
    public function recordAssetIncome(Request $request, PhysicalRewardAllocation $allocation)
    {
        // Ensure user owns this allocation
        if ($allocation->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'source' => 'nullable|string|max:255',
            'date' => 'nullable|date'
        ]);

        $success = $this->assetIncomeTrackingService->recordIncome(
            $allocation,
            $request->amount,
            $request->source,
            $request->date ? Carbon::parse($request->date) : null
        );

        if ($success) {
            return response()->json([
                'message' => 'Income recorded successfully',
                'allocation' => $allocation->fresh()
            ]);
        }

        return response()->json(['error' => 'Failed to record income'], 500);
    }

    /**
     * API endpoint for updating asset maintenance status
     */
    public function updateAssetMaintenance(Request $request, PhysicalRewardAllocation $allocation)
    {
        // Ensure user owns this allocation
        if ($allocation->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'compliant' => 'required|boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        $allocation->updateMaintenanceStatus($request->compliant, $request->notes);

        return response()->json([
            'message' => 'Maintenance status updated successfully',
            'allocation' => $allocation->fresh()
        ]);
    }

    /**
     * Get comprehensive community project data for dashboard
     */
    private function getCommunityProjectData(User $user): array
    {
        // Get user's project portfolio
        $portfolio = $this->communityProjectService->getUserProjectPortfolio($user);
        
        // Get user's contributions
        $contributions = ProjectContribution::where('user_id', $user->id)
            ->with(['project'])
            ->orderBy('contributed_at', 'desc')
            ->get();

        // Get user's profit distributions
        $profitDistributions = ProjectProfitDistribution::where('user_id', $user->id)
            ->with(['project'])
            ->orderBy('paid_at', 'desc')
            ->get();

        // Get available projects for user's tier
        $availableProjects = $this->communityProjectService->getProjectsForUser($user)
            ->take(5)
            ->map(function ($project) use ($user) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'type' => $project->type,
                    'category' => $project->category,
                    'target_amount' => $project->target_amount,
                    'current_amount' => $project->current_amount,
                    'funding_progress' => $project->getFundingProgress(),
                    'expected_annual_return' => $project->expected_annual_return,
                    'risk_level' => $project->risk_level,
                    'days_remaining' => $project->getDaysRemainingForFunding(),
                    'minimum_contribution' => $project->minimum_contribution,
                    'user_can_contribute' => $project->canUserContribute($user),
                    'user_contribution_total' => $project->getUserContributionTotal($user),
                    'is_featured' => $project->is_featured
                ];
            });

        // Get pending votes
        $pendingVotes = $this->getPendingVotes($user);

        // Get investment performance trends
        $investmentTrends = $this->getInvestmentTrends($user);

        return [
            'portfolio_summary' => [
                'total_contributed' => $portfolio['total_contributed'],
                'total_returns_received' => $portfolio['total_returns_received'],
                'net_roi' => $portfolio['net_roi'],
                'active_projects' => $portfolio['active_projects'],
                'completed_projects' => $portfolio['completed_projects'],
                'total_projects' => $portfolio['total_projects']
            ],
            'recent_contributions' => $contributions->take(5)->map(function ($contribution) {
                return [
                    'id' => $contribution->id,
                    'project_name' => $contribution->project->name,
                    'amount' => $contribution->amount,
                    'status' => $contribution->status,
                    'contributed_at' => $contribution->contributed_at->format('M d, Y'),
                    'project_status' => $contribution->project->status,
                    'expected_returns' => $contribution->project->calculateExpectedReturns($contribution->user)
                ];
            }),
            'recent_distributions' => $profitDistributions->take(5)->map(function ($distribution) {
                return [
                    'id' => $distribution->id,
                    'project_name' => $distribution->project->name,
                    'amount' => $distribution->distribution_amount,
                    'distribution_type' => $distribution->distribution_type,
                    'status' => $distribution->status,
                    'distribution_date' => $distribution->paid_at ? $distribution->paid_at->format('M d, Y') : 'Pending',
                    'period_label' => $distribution->distribution_period_label ?? 'N/A'
                ];
            }),
            'available_projects' => $availableProjects,
            'pending_votes' => $pendingVotes,
            'investment_trends' => $investmentTrends,
            'voting_opportunities' => $this->getVotingOpportunities($user),
            'project_alerts' => $this->getProjectAlerts($user)
        ];
    }

    /**
     * Get pending votes for user
     */
    private function getPendingVotes(User $user): array
    {
        $pendingVotes = [];
        
        // Get projects where user has contributed and voting is active
        $userProjects = ProjectContribution::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->with(['project'])
            ->get()
            ->pluck('project')
            ->unique('id');

        foreach ($userProjects as $project) {
            if ($project->requires_voting && $project->canUserVote($user)) {
                // Check for active voting sessions
                $activeVotes = ProjectVote::where('community_project_id', $project->id)
                    ->where('vote_deadline', '>', now())
                    ->whereNotIn('vote_session_id', function ($query) use ($user, $project) {
                        $query->select('vote_session_id')
                            ->from('project_votes')
                            ->where('user_id', $user->id)
                            ->where('community_project_id', $project->id);
                    })
                    ->distinct('vote_session_id')
                    ->get();

                foreach ($activeVotes as $vote) {
                    $pendingVotes[] = [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'vote_session_id' => $vote->vote_session_id,
                        'vote_type' => $vote->vote_type,
                        'vote_subject' => $vote->vote_subject,
                        'vote_description' => $vote->vote_description,
                        'deadline' => $vote->vote_deadline,
                        'days_remaining' => now()->diffInDays($vote->vote_deadline, false),
                        'voting_power' => $project->getUserVotingPower($user)
                    ];
                }
            }
        }

        return $pendingVotes;
    }

    /**
     * Get investment performance trends
     */
    private function getInvestmentTrends(User $user): array
    {
        $trends = [];
        
        // Get last 6 months of investment data
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            
            $monthlyContributions = ProjectContribution::where('user_id', $user->id)
                ->where('status', 'confirmed')
                ->whereMonth('contributed_at', $month->month)
                ->whereYear('contributed_at', $month->year)
                ->sum('amount');

            $monthlyReturns = ProjectProfitDistribution::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereMonth('paid_at', $month->month)
                ->whereYear('paid_at', $month->year)
                ->sum('distribution_amount');

            $trends[] = [
                'month' => $month->format('M Y'),
                'contributions' => $monthlyContributions,
                'returns' => $monthlyReturns,
                'net_flow' => $monthlyReturns - $monthlyContributions
            ];
        }

        return $trends;
    }

    /**
     * Get voting opportunities
     */
    private function getVotingOpportunities(User $user): array
    {
        $opportunities = [];
        
        // Get projects where user can vote but hasn't participated in governance
        $eligibleProjects = $this->communityProjectService->getProjectsForUser($user)
            ->filter(function ($project) use ($user) {
                return $project->requires_voting && 
                       $project->canUserVote($user) &&
                       $project->getUserContributionTotal($user) > 0;
            });

        foreach ($eligibleProjects as $project) {
            $opportunities[] = [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'voting_power' => $project->getUserVotingPower($user),
                'contribution_amount' => $project->getUserContributionTotal($user),
                'governance_participation' => ProjectVote::where('user_id', $user->id)
                    ->where('community_project_id', $project->id)
                    ->count()
            ];
        }

        return $opportunities;
    }

    /**
     * Get project alerts and notifications
     */
    private function getProjectAlerts(User $user): array
    {
        $alerts = [];

        // Check for projects nearing funding deadline
        $userProjects = ProjectContribution::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->with(['project'])
            ->get()
            ->pluck('project')
            ->unique('id');

        foreach ($userProjects as $project) {
            if ($project->status === 'funding' && $project->getDaysRemainingForFunding() <= 7) {
                $alerts[] = [
                    'type' => 'funding_deadline',
                    'project_name' => $project->name,
                    'message' => 'Funding deadline approaching',
                    'days_remaining' => $project->getDaysRemainingForFunding(),
                    'priority' => $project->getDaysRemainingForFunding() <= 3 ? 'high' : 'medium',
                    'project_id' => $project->id
                ];
            }

            // Check for pending distributions
            $pendingDistributions = ProjectProfitDistribution::where('user_id', $user->id)
                ->where('community_project_id', $project->id)
                ->where('status', 'approved')
                ->count();

            if ($pendingDistributions > 0) {
                $alerts[] = [
                    'type' => 'pending_distribution',
                    'project_name' => $project->name,
                    'message' => "{$pendingDistributions} profit distribution(s) ready for payment",
                    'priority' => 'medium',
                    'project_id' => $project->id
                ];
            }
        }

        return $alerts;
    }

    /**
     * API endpoint for community project data
     */
    public function getCommunityProjectDataApi(Request $request)
    {
        $user = $request->user();
        
        return response()->json($this->getCommunityProjectData($user));
    }

    /**
     * API endpoint for project contribution
     */
    public function contributeToProject(Request $request, CommunityProject $project)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_details' => 'nullable|array'
        ]);

        $user = $request->user();

        try {
            $contribution = $this->communityProjectService->processContribution(
                $user,
                $project,
                $request->amount,
                $request->payment_method,
                $request->payment_details ?? []
            );

            return response()->json([
                'message' => 'Contribution processed successfully',
                'contribution' => $contribution,
                'project' => $project->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API endpoint for casting votes
     */
    public function castProjectVote(Request $request, CommunityProject $project)
    {
        $request->validate([
            'session_id' => 'required|string',
            'vote' => 'required|in:yes,no,abstain',
            'comments' => 'nullable|string|max:1000'
        ]);

        $user = $request->user();

        try {
            $vote = $this->communityProjectService->castVote(
                $user,
                $project,
                $request->session_id,
                $request->vote,
                $request->comments
            );

            return response()->json([
                'message' => 'Vote cast successfully',
                'vote' => $vote
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API endpoint for project analytics
     */
    public function getProjectAnalytics(Request $request, CommunityProject $project)
    {
        // Ensure user has contributed to this project
        $userContribution = ProjectContribution::where('user_id', $request->user()->id)
            ->where('community_project_id', $project->id)
            ->where('status', 'confirmed')
            ->first();

        if (!$userContribution) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $analytics = $this->communityProjectService->getProjectAnalytics($project);
        
        return response()->json($analytics);
    }

    /**
     * Get verification limits based on user's verification level
     */
    private function getVerificationLimits(string $level): array
    {
        $limits = [
            'basic' => [
                'daily_withdrawal' => 1000,
                'monthly_withdrawal' => 10000,
                'single_transaction' => 500,
            ],
            'verified' => [
                'daily_withdrawal' => 5000,
                'monthly_withdrawal' => 50000,
                'single_transaction' => 2000,
            ],
            'premium' => [
                'daily_withdrawal' => 20000,
                'monthly_withdrawal' => 200000,
                'single_transaction' => 10000,
            ],
        ];

        return $limits[$level] ?? $limits['basic'];
    }

    /**
     * Get earnings breakdown for mobile dashboard
     * Uses EarningsService for consistency
     */
    private function getEarningsBreakdown(User $user): array
    {
        // Get base earnings from service
        $breakdown = $this->earningsService->getEarningsBreakdown($user);
        
        // Team performance (purchases and subscriptions from team)
        // This includes commissions from team member purchases and subscriptions
        $teamPerformance = \DB::table('referral_commissions')
            ->where('referrer_id', $user->id)
            ->where('status', 'paid')
            ->whereIn('package_type', ['subscription', 'product_purchase', 'starter_kit'])
            ->sum('amount');

        // Get pending earnings from service
        $pendingEarnings = $this->earningsService->getPendingEarnings($user);
        
        // Get LGR info from service
        $lgrInfo = $this->earningsService->getLgrWithdrawableInfo($user);

        return [
            'referral_commissions' => (float) $breakdown['commissions'],
            'profit_shares' => (float) $breakdown['profit_shares'],
            'team_performance' => (float) $teamPerformance,
            'pending_earnings' => (float) $pendingEarnings,
            'lgr_daily_bonus' => (float) $lgrInfo['balance'],
            'lgr_withdrawable' => (float) $lgrInfo['withdrawable'],
            'lgr_percentage' => (float) $lgrInfo['percentage'],
            'lgr_blocked' => (bool) $lgrInfo['blocked'],
            'total_earnings' => (float) ($breakdown['commissions'] + $breakdown['profit_shares'] + $teamPerformance),
        ];
    }

    /**
     * Determine if user should be redirected to a different dashboard
     * 
     * @param Request $request
     * @param User $user
     * @return string|null Route name to redirect to, or null if no redirect needed
     */
    private function shouldRedirectToDashboard(Request $request, User $user): ?string
    {
        // Check for session override (temporary preference)
        if ($request->session()->has('dashboard_override')) {
            return null; // Don't redirect if user has temporary override
        }

        $preference = $user->preferred_dashboard ?? 'auto';
        $currentRoute = $request->route()->getName();
        $isMobile = $this->isMobileDevice($request);

        // Handle based on preference
        switch ($preference) {
            case 'mobile':
                // User prefers mobile, redirect if on desktop
                return ($currentRoute === 'mygrownet.dashboard') ? 'mygrownet.mobile' : null;
                
            case 'desktop':
                // User prefers desktop, redirect if on mobile
                return ($currentRoute === 'mygrownet.mobile') ? 'mygrownet.dashboard' : null;
                
            case 'auto':
            default:
                // Auto-detect based on device
                if ($isMobile && $currentRoute === 'mygrownet.dashboard') {
                    return 'mygrownet.mobile';
                } elseif (!$isMobile && $currentRoute === 'mygrownet.mobile') {
                    return 'mygrownet.dashboard';
                }
                return null;
        }
    }

    /**
     * Detect if request is from a mobile device
     * 
     * @param Request $request
     * @return bool
     */
    private function isMobileDevice(Request $request): bool
    {
        $userAgent = $request->header('User-Agent');
        
        // Check for mobile keywords in user agent
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod',
            'BlackBerry', 'Windows Phone', 'webOS', 'Opera Mini'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get messaging data for dashboard
     */
    private function getMessagingData(User $user): array
    {
        $userId = UserId::fromInt($user->id);
        
        // Get unread message count
        $unreadCount = $this->messagingService->getUnreadCount($userId);
        
        // Get recent messages (last 5)
        $recentMessages = $this->messagingService->getInbox($userId, 5);
        
        // Transform to simple array for frontend
        $messages = [];
        foreach ($recentMessages as $message) {
            $sender = User::find($message->senderId()->value());
            $messages[] = [
                'id' => $message->id()->value(),
                'sender_name' => $sender ? $sender->name : 'Unknown',
                'subject' => $message->content()->subject(),
                'preview' => $message->content()->preview(50),
                'is_read' => $message->isRead(),
                'created_at' => $message->createdAt()->format('M j, g:i A'),
                'created_at_human' => $this->getHumanReadableTime($message->createdAt()),
            ];
        }
        
        return [
            'unread_count' => $unreadCount,
            'recent_messages' => $messages,
            'has_messages' => count($messages) > 0,
        ];
    }

    /**
     * Get human readable time difference
     */
    private function getHumanReadableTime(\DateTimeImmutable $dateTime): string
    {
        $now = new \DateTimeImmutable();
        $diff = $now->getTimestamp() - $dateTime->getTimestamp();
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . 'm ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . 'h ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . 'd ago';
        } else {
            return $dateTime->format('M j');
        }
    }
}

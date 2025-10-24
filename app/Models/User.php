<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HasActivityLogs;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasActivityLogs, HasRoles;

    /**
     * Boot the model and auto-assign Member role on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Auto-generate referral code BEFORE saving
            if (!$user->referral_code) {
                do {
                    $code = 'MGN' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
                } while (User::where('referral_code', $code)->exists());
                
                $user->referral_code = $code;
            }
        });

        static::created(function ($user) {
            // Auto-assign Member role to new users
            if (!\Spatie\Permission\Models\Role::where('name', 'Member')->exists()) {
                return;
            }
            
            // Only assign if user doesn't already have a role
            if ($user->roles()->count() === 0) {
                $user->assignRole('Member');
            }
        });
    }

    /**
     * Find user by phone or email
     * 
     * @param string $identifier Phone number or email
     * @return User|null
     */
    public static function findByPhoneOrEmail(string $identifier): ?User
    {
        // Check if it's a phone number (contains only digits, +, -, spaces, or parentheses)
        if (preg_match('/^[\d\s\+\-\(\)]+$/', $identifier)) {
            // Normalize phone number (remove spaces, dashes, parentheses)
            $normalizedPhone = preg_replace('/[\s\-\(\)]/', '', $identifier);
            
            // Try to find by phone
            return static::where('phone', $normalizedPhone)
                ->orWhere('phone', $identifier)
                ->first();
        }
        
        // Otherwise treat as email
        return static::where('email', $identifier)->first();
    }

    /**
     * Normalize phone number for storage
     * Removes spaces, dashes, and parentheses
     * Ensures Zambian format (+260 or 0)
     * 
     * @param string $phone
     * @return string
     */
    public static function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters except +
        $normalized = preg_replace('/[^\d\+]/', '', $phone);
        
        // If starts with 260 (without +), add +
        if (preg_match('/^260\d{9}$/', $normalized)) {
            $normalized = '+' . $normalized;
        }
        
        // If starts with 0 and is 10 digits, convert to +260
        if (preg_match('/^0\d{9}$/', $normalized)) {
            $normalized = '+260' . substr($normalized, 1);
        }
        
        return $normalized;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'phone_verified_at',
        'referrer_id',
        'status',
        'last_login_at',
        'matrix_position',
        'total_investment_amount',
        'total_referral_earnings',
        'total_profit_earnings',
        'referral_code',
        'referral_count',
        'last_referral_at',
        'tier_upgraded_at',
        'tier_history',
        'current_investment_tier_id',
        'rank',
        'address',
        'balance',
        'total_earnings',
        'direct_referrals',
        // MyGrowNet team volume fields
        'current_team_volume',
        'current_personal_volume',
        'current_team_depth',
        'active_referrals_count',
        'monthly_subscription_fee',
        'subscription_start_date',
        'subscription_end_date',
        'subscription_status',
        'network_path',
        'network_level',
        // Security fields
        'is_blocked',
        'block_reason',
        'blocked_at',
        'blocked_by',
        'requires_id_verification',
        'is_id_verified',
        'id_verified_at',
        'failed_login_attempts',
        'last_failed_login_at',
        'security_flags',
        'risk_score',
        'risk_assessed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'matrix_position' => 'array',
        'tier_history' => 'array',
        'total_investment_amount' => 'decimal:2',
        'total_referral_earnings' => 'decimal:2',
        'total_profit_earnings' => 'decimal:2',
        'last_referral_at' => 'datetime',
        'tier_upgraded_at' => 'datetime',
        // Security casts
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
        'requires_id_verification' => 'boolean',
        'is_id_verified' => 'boolean',
        'id_verified_at' => 'datetime',
        'last_failed_login_at' => 'datetime',
        'security_flags' => 'array',
        'risk_score' => 'decimal:2',
        'risk_assessed_at' => 'datetime',
        // MyGrowNet team volume casts
        'current_team_volume' => 'decimal:2',
        'current_personal_volume' => 'decimal:2',
        'monthly_subscription_fee' => 'decimal:2',
        'subscription_start_date' => 'date',
        'subscription_end_date' => 'date',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->latest();
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function directReferrals(): HasMany
    {
        return $this->hasMany(User::class, 'referrer_id');
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function referralCommissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_id');
    }

    public function refereeCommissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referred_id');
    }

    // MyGrowNet team volume relationships
    public function teamVolumes(): HasMany
    {
        return $this->hasMany(TeamVolume::class);
    }

    public function networkMembers(): HasMany
    {
        return $this->hasMany(UserNetwork::class, 'referrer_id');
    }

    public function networkUpline(): HasMany
    {
        return $this->hasMany(UserNetwork::class, 'user_id');
    }

    // Points System Relationships
    public function points(): HasOne
    {
        return $this->hasOne(UserPoints::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function monthlyActivityStatuses(): HasMany
    {
        return $this->hasMany(MonthlyActivityStatus::class);
    }

    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Get current month activity status
     */
    public function currentMonthActivity(): HasOne
    {
        return $this->hasOne(MonthlyActivityStatus::class)
            ->where('year', now()->year)
            ->where('month', now()->month);
    }

    /**
     * Check if user is qualified for current month
     */
    public function isQualifiedThisMonth(): bool
    {
        return $this->points?->meetsMonthlyQualification() ?? false;
    }

    /**
     * Get account age in days
     */
    public function getAccountAgeDaysAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Get active direct referrals count
     */
    public function getActiveDirectReferralsCountAttribute(): int
    {
        return $this->directReferrals()
            ->where('is_currently_active', true)
            ->count();
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(WithdrawalPolicy::class);
    }

    public function profitShares(): HasMany
    {
        return $this->hasMany(ProfitShare::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Course relationships
    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function courseCompletions(): HasMany
    {
        return $this->hasMany(CourseCompletion::class);
    }

    public function lessonCompletions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_enrollments')
                    ->withPivot(['enrolled_at', 'tier_at_enrollment', 'progress_percentage', 'completed_at', 'status'])
                    ->withTimestamps();
    }

    public function completedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_completions')
                    ->withPivot(['completed_at', 'completion_time_minutes', 'final_score', 'tier_at_completion'])
                    ->withTimestamps();
    }

    // Community project relationships
    public function createdProjects(): HasMany
    {
        return $this->hasMany(CommunityProject::class, 'created_by');
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(CommunityProject::class, 'project_manager_id');
    }

    public function projectContributions(): HasMany
    {
        return $this->hasMany(ProjectContribution::class);
    }

    public function projectInvestments(): HasMany
    {
        return $this->hasMany(ProjectInvestment::class);
    }

    public function projectVotes(): HasMany
    {
        return $this->hasMany(ProjectVote::class);
    }

    public function projectDistributions(): HasMany
    {
        return $this->hasMany(ProjectProfitDistribution::class);
    }

    public function contributedProjects(): BelongsToMany
    {
        return $this->belongsToMany(CommunityProject::class, 'project_contributions')
                    ->withPivot(['amount', 'status', 'contributed_at', 'tier_at_contribution'])
                    ->withTimestamps();
    }

    // Achievement relationships
    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
                    ->withPivot(['earned_at', 'progress', 'times_earned', 'tier_at_earning'])
                    ->withTimestamps();
    }

    public function leaderboardEntries(): HasMany
    {
        return $this->hasMany(LeaderboardEntry::class);
    }

    // Achievement helper methods
    public function hasAchievement(int $achievementId): bool
    {
        return $this->userAchievements()->where('achievement_id', $achievementId)->exists();
    }

    public function getAchievementCount(int $achievementId): int
    {
        $userAchievement = $this->userAchievements()->where('achievement_id', $achievementId)->first();
        return $userAchievement ? $userAchievement->times_earned : 0;
    }

    public function getTotalAchievementPoints(): int
    {
        return $this->userAchievements()
                    ->with('achievement')
                    ->get()
                    ->sum(fn($ua) => $ua->achievement->points);
    }

    public function recordActivity(string $action, ?string $description = null)
    {
        return $this->activityLogs()->create([
            'action' => $action,
            'description' => $description,
            'ip_address' => app()->runningInConsole() ? '127.0.0.1' : request()->ip(),
            'user_agent' => app()->runningInConsole() ? 'Console' : request()->userAgent()
        ]);
    }

    public function hasPermission($permission): bool
    {
        return $this->roles()->whereHas('permissions', function($query) use ($permission) {
            $query->where('slug', $permission);
        })->exists();
    }

    // Investment Tier Relationships
    public function currentInvestmentTier()
    {
        return $this->belongsTo(InvestmentTier::class, 'current_investment_tier_id');
    }

    // MyGrowNet Membership Tier (alias for currentInvestmentTier)
    public function membershipTier()
    {
        return $this->currentInvestmentTier();
    }

    // Get current tier for educational content access
    public function currentTier()
    {
        return $this->currentInvestmentTier;
    }

    // Investment Tier Methods
    public function getTierHistory()
    {
        return $this->tier_history ?? [];
    }

    public function addTierHistory($tierId, $reason = '')
    {
        $history = $this->getTierHistory();
        $history[] = [
            'tier_id' => $tierId,
            'date' => now()->toDateTimeString(),
            'reason' => $reason
        ];
        $this->tier_history = $history;
        $this->save();
    }

    public function canUpgradeTier()
    {
        if (!$this->currentInvestmentTier) {
            return true;
        }
        return $this->currentInvestmentTier->canUpgrade($this);
    }

    public function getNextTierRequirements()
    {
        if (!$this->currentInvestmentTier) {
            $firstTier = InvestmentTier::ordered()->first();
            return [
                'next_tier' => $firstTier,
                'remaining_amount' => $firstTier->minimum_investment,
                'can_upgrade' => false
            ];
        }
        return $this->currentInvestmentTier->getUpgradeRequirements($this);
    }

    public function upgradeTier(InvestmentTier $newTier, $reason = '')
    {
        $this->current_investment_tier_id = $newTier->id;
        $this->tier_upgraded_at = now();
        $this->addTierHistory($newTier->id, $reason);
        $this->save();
    }

    public function calculateTotalEarnings()
    {
        return $this->total_referral_earnings + $this->total_profit_earnings;
    }

    public function generateReferralCode(): string
    {
        $code = strtoupper(substr(md5(uniqid()), 0, 8));
        $this->update(['referral_code' => $code]);
        return $code;
    }

    public function incrementReferralCount(): void
    {
        $this->increment('referral_count');
        $this->update(['last_referral_at' => now()]);
    }

    public function getReferralStats(): array
    {
        return [
            'total_referrals' => $this->referral_count,
            'active_referrals' => $this->directReferrals()
                ->whereHas('investments', function($query) {
                    $query->where('status', 'active');
                })->count(),
            'total_commission' => $this->referralCommissions()
                ->where('status', 'paid')
                ->sum('amount'),
            'pending_commission' => $this->referralCommissions()
                ->where('status', 'pending')
                ->sum('amount')
        ];
    }

    public function getReferralTree(int $maxLevel = 3): array
    {
        return $this->buildReferralTree($this, 1, $maxLevel);
    }

    // MyGrowNet team volume methods
    public function getCurrentTeamVolume(): ?TeamVolume
    {
        return $this->teamVolumes()
            ->whereBetween('period_start', [now()->startOfMonth(), now()->endOfMonth()])
            ->first();
    }

    public function getTeamVolumeStats(): array
    {
        $currentVolume = $this->getCurrentTeamVolume();
        
        return [
            'personal_volume' => $currentVolume?->personal_volume ?? 0,
            'team_volume' => $currentVolume?->team_volume ?? 0,
            'team_depth' => $currentVolume?->team_depth ?? 0,
            'active_referrals_count' => $currentVolume?->active_referrals_count ?? 0,
            'performance_bonus' => $currentVolume?->calculatePerformanceBonus() ?? 0,
        ];
    }

    public function getNetworkMembers(int $maxLevel = 5): array
    {
        return UserNetwork::getNetworkMembers($this->id, $maxLevel);
    }

    public function getUplineReferrers(int $maxLevel = 5): array
    {
        return UserNetwork::getUplineReferrers($this->id, $maxLevel);
    }

    public function hasActiveSubscription(): bool
    {
        // Check both user status AND verified payment existence
        // This ensures only users who have actually paid show as active
        if ($this->status !== 'active') {
            return false;
        }
        
        // Double-check: User must have at least one verified payment
        return $this->memberPayments()
            ->where('status', 'verified')
            ->exists();
    }

    /**
     * Get payment transactions
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Get commission payments
     */
    public function commissionPayments(): HasMany
    {
        return $this->paymentTransactions()->where('type', 'commission_payment');
    }

    /**
     * Get user's active subscription (duplicate removed - using relationship at line 112)
     */
    public function getActiveSubscriptionAttribute()
    {
        return $this->activeSubscription;
    }

    /**
     * Get member payments (subscriptions, workshops, products, etc.)
     */
    public function memberPayments(): HasMany
    {
        return $this->hasMany(\App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::class);
    }

    /**
     * Get user's achievement bonuses
     */
    public function achievementBonuses(): HasMany
    {
        return $this->hasMany(AchievementBonus::class);
    }

    /**
     * Get bonus payments
     */
    public function bonusPayments(): HasMany
    {
        return $this->paymentTransactions()->where('type', 'bonus_payment');
    }

    /**
     * Get user's physical rewards (smartphones, vehicles, property, etc.)
     */
    public function physicalRewards(): HasMany
    {
        return $this->hasMany(PhysicalReward::class);
    }

    /**
     * Update current team volume tracking fields
     */
    public function updateCurrentTeamVolume(): void
    {
        $currentVolume = $this->getCurrentTeamVolume();
        
        if ($currentVolume) {
            $this->update([
                'current_team_volume' => $currentVolume->team_volume,
                'current_personal_volume' => $currentVolume->personal_volume,
                'current_team_depth' => $currentVolume->team_depth,
                'active_referrals_count' => $currentVolume->active_referrals_count,
            ]);
        }
    }

    /**
     * Update network path for efficient traversal
     */
    public function updateNetworkPath(): void
    {
        if ($this->referrer_id) {
            $referrerPath = User::where('id', $this->referrer_id)->value('network_path');
            $level = User::where('id', $this->referrer_id)->value('network_level') + 1;
            
            $path = $referrerPath ? $referrerPath . '.' . $this->id : (string) $this->id;
            
            $this->update([
                'network_path' => $path,
                'network_level' => $level
            ]);
        } else {
            $this->update([
                'network_path' => (string) $this->id,
                'network_level' => 0
            ]);
        }
    }

    /**
     * Get all downline members efficiently using network path
     */
    public function getDownlineMembers(int $maxLevel = 5): array
    {
        if (!$this->network_path) {
            return [];
        }

        return User::where('network_path', 'LIKE', $this->network_path . '.%')
                   ->where('network_level', '<=', $this->network_level + $maxLevel)
                   ->where('network_level', '>', $this->network_level)
                   ->select('id', 'name', 'email', 'network_level', 'current_investment_tier_id')
                   ->chunk(100, function($users) {
                       return $users->groupBy(function($user) {
                           return $user->network_level - $this->network_level;
                       });
                   });
    }

    /**
     * Calculate team volume from downline efficiently
     */
    public function calculateTeamVolumeFromDownline(): float
    {
        if (!$this->network_path) {
            return 0;
        }

        return User::where('network_path', 'LIKE', $this->network_path . '.%')
                   ->where('network_level', '<=', $this->network_level + 5)
                   ->sum('current_personal_volume');
    }

    // MyGrowNet tier advancement methods
    public function checkMyGrowNetTierUpgradeEligibility(): array
    {
        $currentTier = $this->membershipTier;
        $currentVolume = $this->getCurrentTeamVolume();
        
        if (!$currentTier) {
            $bronzeTier = InvestmentTier::where('name', 'Bronze')->first();
            return [
                'eligible' => true,
                'current_tier' => null,
                'next_tier' => $bronzeTier,
                'requirements_met' => true,
                'message' => 'Ready to join Bronze tier'
            ];
        }

        $nextTier = $currentTier->getNextTier();
        if (!$nextTier) {
            return [
                'eligible' => false,
                'current_tier' => $currentTier,
                'next_tier' => null,
                'requirements_met' => false,
                'message' => 'Already at highest tier (Elite)'
            ];
        }

        $activeReferrals = $currentVolume?->active_referrals_count ?? 0;
        $teamVolume = $currentVolume?->team_volume ?? 0;

        $requirementsMet = $nextTier->qualifiesForUpgrade($activeReferrals, $teamVolume);

        return [
            'eligible' => $requirementsMet,
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'requirements_met' => $requirementsMet,
            'current_stats' => [
                'active_referrals' => $activeReferrals,
                'team_volume' => $teamVolume
            ],
            'requirements' => $nextTier->getUpgradeRequirements(),
            'achievement_bonus' => $nextTier->achievement_bonus
        ];
    }

    public function upgradeToMyGrowNetTier(InvestmentTier $newTier, string $reason = 'tier_qualification_met'): bool
    {
        $eligibility = $this->checkMyGrowNetTierUpgradeEligibility();
        
        if (!$eligibility['eligible'] || $eligibility['next_tier']?->id !== $newTier->id) {
            return false;
        }

        // Award achievement bonus
        if ($newTier->achievement_bonus > 0) {
            $this->increment('balance', $newTier->achievement_bonus);
            $this->recordActivity(
                'achievement_bonus_received',
                "Received K{$newTier->achievement_bonus} achievement bonus for {$newTier->name} tier upgrade"
            );
        }

        // Upgrade tier
        $this->upgradeTier($newTier, $reason);

        // Record tier upgrade activity
        $this->recordActivity(
            'mygrownet_tier_upgraded',
            "Upgraded to {$newTier->name} tier with K{$newTier->achievement_bonus} achievement bonus"
        );

        return true;
    }

    public function getMyGrowNetTierBenefits(): array
    {
        $tier = $this->membershipTier;
        if (!$tier) {
            return [];
        }

        return $tier->getMyGrowNetBenefits();
    }

    public function calculateMonthlySubscriptionShare(): float
    {
        $tier = $this->membershipTier;
        return $tier?->calculateMonthlyShare() ?? 0;
    }

    public function isEligibleForProfitSharing(string $type = 'quarterly'): bool
    {
        $tier = $this->membershipTier;
        return $tier?->isEligibleForProfitSharing($type) ?? false;
    }

    public function isEligibleForBusinessFacilitation(): bool
    {
        $tier = $this->membershipTier;
        return $tier?->isEligibleForBusinessFacilitation() ?? false;
    }

    public function calculateTotalTeamVolume(): float
    {
        $networkMembers = $this->getNetworkMembers();
        $totalVolume = 0;

        foreach ($networkMembers as $level => $members) {
            foreach ($members as $member) {
                $memberVolume = $member['user']->getCurrentTeamVolume();
                $totalVolume += $memberVolume?->personal_volume ?? 0;
            }
        }

        return $totalVolume;
    }

    protected function buildReferralTree($user, $level, $maxLevel): array
    {
        if ($level > $maxLevel) {
            return [];
        }

        $children = $user->directReferrals()
            ->with(['investments' => function($query) {
                $query->where('status', 'active');
            }])
            ->get()
            ->map(function($referral) use ($level, $maxLevel) {
                return [
                    'id' => $referral->id,
                    'name' => $referral->name,
                    'email' => $referral->email,
                    'activeInvestments' => $referral->investments->count(),
                    'totalInvested' => $referral->investments->sum('amount'),
                    'children' => $this->buildReferralTree($referral, $level + 1, $maxLevel)
                ];
            });

        return $children->toArray();
    }

    // VBIF-specific relationships
    public function matrixPositions(): HasMany
    {
        return $this->hasMany(MatrixPosition::class);
    }

    public function sponsoredPositions(): HasMany
    {
        return $this->hasMany(MatrixPosition::class, 'sponsor_id');
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function processedWithdrawals(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class, 'processed_by');
    }

    public function commissionClawbacks(): HasMany
    {
        return $this->hasMany(CommissionClawback::class);
    }

    public function processedClawbacks(): HasMany
    {
        return $this->hasMany(CommissionClawback::class, 'processed_by');
    }

    public function createdDistributions(): HasMany
    {
        return $this->hasMany(ProfitDistribution::class, 'created_by');
    }

    public function processedDistributions(): HasMany
    {
        return $this->hasMany(ProfitDistribution::class, 'processed_by');
    }

    public function otpTokens(): HasMany
    {
        return $this->hasMany(OtpToken::class);
    }

    // VBIF-specific matrix position methods
    public function getMatrixPosition(): ?MatrixPosition
    {
        return $this->matrixPositions()->where('is_active', true)->first();
    }

    public function buildMatrixStructure(int $maxLevel = 3): array
    {
        $position = $this->getMatrixPosition();
        if (!$position) {
            return [];
        }

        return $this->buildMatrixLevel($position, 1, $maxLevel);
    }

    protected function buildMatrixLevel(MatrixPosition $position, int $currentLevel, int $maxLevel): array
    {
        if ($currentLevel > $maxLevel) {
            return [];
        }

        $structure = [
            'level' => $currentLevel,
            'position' => $position->position,
            'user' => [
                'id' => $position->user->id,
                'name' => $position->user->name,
                'email' => $position->user->email,
                'total_investment' => $position->user->total_investment_amount,
                'tier' => $position->user->currentInvestmentTier?->name
            ],
            'children' => []
        ];

        // Get direct children (3x3 matrix allows 3 children per position)
        $children = MatrixPosition::where('sponsor_id', $position->user_id)
            ->where('level', $currentLevel + 1)
            ->where('is_active', true)
            ->with('user.currentInvestmentTier')
            ->orderBy('position')
            ->get();

        foreach ($children as $child) {
            $structure['children'][] = $this->buildMatrixLevel($child, $currentLevel + 1, $maxLevel);
        }

        return $structure;
    }

    public function findNextAvailableMatrixPosition(User $sponsor): ?array
    {
        // Check if sponsor has available direct positions (max 3 in 3x3 matrix)
        $directChildren = MatrixPosition::where('sponsor_id', $sponsor->id)
            ->where('level', 1)
            ->where('is_active', true)
            ->count();

        if ($directChildren < 3) {
            return [
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $directChildren + 1
            ];
        }

        // Implement spillover logic - find next available position in downline
        return $this->findSpilloverPosition($sponsor, 1, 3);
    }

    protected function findSpilloverPosition(User $sponsor, int $currentLevel, int $maxLevel): ?array
    {
        if ($currentLevel >= $maxLevel) {
            return null;
        }

        // Get all positions at current level under this sponsor
        $positions = MatrixPosition::where('sponsor_id', $sponsor->id)
            ->where('level', $currentLevel)
            ->where('is_active', true)
            ->with('user')
            ->get();

        foreach ($positions as $position) {
            // Check if this position has available slots
            $childrenCount = MatrixPosition::where('sponsor_id', $position->user_id)
                ->where('level', $currentLevel + 1)
                ->where('is_active', true)
                ->count();

            if ($childrenCount < 3) {
                return [
                    'sponsor_id' => $position->user_id,
                    'level' => $currentLevel + 1,
                    'position' => $childrenCount + 1
                ];
            }

            // Recursively check deeper levels
            $spillover = $this->findSpilloverPosition($position->user, $currentLevel + 1, $maxLevel);
            if ($spillover) {
                return $spillover;
            }
        }

        return null;
    }

    public function placeInMatrix(User $sponsor): bool
    {
        $position = $this->findNextAvailableMatrixPosition($sponsor);
        
        if (!$position) {
            return false;
        }

        MatrixPosition::create([
            'user_id' => $this->id,
            'sponsor_id' => $position['sponsor_id'],
            'level' => $position['level'],
            'position' => $position['position'],
            'is_active' => true,
            'placed_at' => now()
        ]);

        return true;
    }

    public function getMatrixCommissionEligibility(int $level): bool
    {
        $tier = $this->currentInvestmentTier;
        if (!$tier) {
            return false;
        }

        // Check tier eligibility for different commission levels
        return match($level) {
            1 => true, // All tiers eligible for level 1
            2 => in_array($tier->name, ['Starter', 'Builder', 'Leader', 'Elite']),
            3 => in_array($tier->name, ['Builder', 'Leader', 'Elite']),
            default => false
        };
    }

    // Enhanced referral code methods
    public function generateUniqueReferralCode(): string
    {
        do {
            $code = 'VBIF' . strtoupper(substr(md5(uniqid($this->id)), 0, 6));
        } while (User::where('referral_code', $code)->exists());

        $this->update(['referral_code' => $code]);
        return $code;
    }

    public function validateReferralCode(string $code): bool
    {
        return User::where('referral_code', $code)
            ->where('id', '!=', $this->id)
            ->exists();
    }

    public function getReferralByCode(string $code): ?User
    {
        return User::where('referral_code', $code)->first();
    }

    // Enhanced earnings calculation methods
    public function calculateTotalEarningsDetailed(): array
    {
        $referralEarnings = $this->referralCommissions()
            ->where('status', 'paid')
            ->sum('amount');

        $profitEarnings = $this->profitShares()
            ->where('status', 'paid')
            ->sum('amount');

        // For matrix commissions, we'll use level > 1 as indicator since there's no type column
        $matrixCommissions = $this->referralCommissions()
            ->where('level', '>', 1)
            ->where('status', 'paid')
            ->sum('amount');

        // Reinvestment bonuses would need a separate table or identifier
        $reinvestmentBonuses = 0; // Will implement when we have the proper structure

        return [
            'referral_commissions' => $referralEarnings,
            'profit_shares' => $profitEarnings,
            'matrix_commissions' => $matrixCommissions,
            'reinvestment_bonuses' => $reinvestmentBonuses,
            'total_earnings' => $referralEarnings + $profitEarnings + $matrixCommissions + $reinvestmentBonuses,
            'pending_earnings' => $this->calculatePendingEarnings()
        ];
    }

    public function calculatePendingEarnings(): float
    {
        return $this->referralCommissions()
            ->where('status', 'pending')
            ->sum('amount') +
            $this->profitShares()
            ->where('status', 'pending')
            ->sum('amount');
    }

    public function updateTotalEarnings(): void
    {
        $earnings = $this->calculateTotalEarningsDetailed();
        
        $this->update([
            'total_referral_earnings' => $earnings['referral_commissions'] + $earnings['matrix_commissions'],
            'total_profit_earnings' => $earnings['profit_shares'] + $earnings['reinvestment_bonuses']
        ]);
    }

    // Enhanced tier upgrade methods
    public function checkTierUpgradeEligibility(): array
    {
        $currentTier = $this->currentInvestmentTier;
        $totalInvestment = $this->total_investment_amount;

        if (!$currentTier) {
            $firstTier = InvestmentTier::active()->ordered()->first();
            return [
                'eligible' => $totalInvestment >= $firstTier->minimum_investment,
                'current_tier' => null,
                'next_tier' => $firstTier,
                'required_amount' => $firstTier->minimum_investment,
                'current_amount' => $totalInvestment,
                'remaining_amount' => max(0, $firstTier->minimum_investment - $totalInvestment)
            ];
        }

        $nextTier = $currentTier->getNextTier();
        if (!$nextTier) {
            return [
                'eligible' => false,
                'current_tier' => $currentTier,
                'next_tier' => null,
                'required_amount' => 0,
                'current_amount' => $totalInvestment,
                'remaining_amount' => 0,
                'message' => 'Already at highest tier'
            ];
        }

        $remainingAmount = max(0, $nextTier->minimum_investment - $totalInvestment);

        return [
            'eligible' => $remainingAmount <= 0,
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'required_amount' => $nextTier->minimum_investment,
            'current_amount' => $totalInvestment,
            'remaining_amount' => $remainingAmount
        ];
    }

    public function processAutomaticTierUpgrade(): bool
    {
        $eligibility = $this->checkTierUpgradeEligibility();
        
        if (!$eligibility['eligible'] || !$eligibility['next_tier']) {
            return false;
        }

        $this->upgradeTier(
            $eligibility['next_tier'], 
            'Automatic upgrade based on investment amount'
        );

        // Record activity
        $this->recordActivity(
            'tier_upgraded',
            "Automatically upgraded from {$eligibility['current_tier']?->name} to {$eligibility['next_tier']->name}"
        );

        return true;
    }

    public function getTierProgressPercentage(): float
    {
        $eligibility = $this->checkTierUpgradeEligibility();
        
        if (!$eligibility['next_tier']) {
            return 100; // Already at highest tier
        }

        $currentTierMin = $eligibility['current_tier']?->minimum_investment ?? 0;
        $nextTierMin = $eligibility['next_tier']->minimum_investment;
        $currentAmount = $eligibility['current_amount'];

        if ($nextTierMin <= $currentTierMin) {
            return 100;
        }

        $progress = ($currentAmount - $currentTierMin) / ($nextTierMin - $currentTierMin);
        return min(100, max(0, $progress * 100));
    }

    public function canReceiveMatrixCommission(int $level): bool
    {
        $tier = $this->currentInvestmentTier;
        if (!$tier) {
            return false;
        }

        // Matrix commission eligibility based on tier
        return match($level) {
            1 => true, // All tiers can receive level 1 commissions
            2 => in_array($tier->name, ['Starter', 'Builder', 'Leader', 'Elite']),
            3 => in_array($tier->name, ['Builder', 'Leader', 'Elite']),
            default => false
        };
    }

    public function getMatrixDownlineCount(int $maxLevel = 3): array
    {
        $counts = [];
        
        for ($level = 1; $level <= $maxLevel; $level++) {
            $counts["level_{$level}"] = MatrixPosition::whereHas('sponsor', function($query) use ($level) {
                $query->where('id', $this->id);
            })
            ->where('level', $level)
            ->where('is_active', true)
            ->count();
        }

        $counts['total'] = array_sum($counts);
        return $counts;
    }

    public function getCurrentInvestmentTier(): ?InvestmentTier
    {
        if ($this->current_investment_tier_id) {
            return InvestmentTier::find($this->current_investment_tier_id);
        }

        // Determine tier based on total investment amount
        return InvestmentTier::where('minimum_investment', '<=', $this->total_investment_amount ?? 0)
            ->orderBy('minimum_investment', 'desc')
            ->first();
    }

    // Security-related relationships
    public function deviceFingerprints(): HasMany
    {
        return $this->hasMany(DeviceFingerprint::class);
    }

    public function suspiciousActivities(): HasMany
    {
        return $this->hasMany(SuspiciousActivity::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function idVerifications(): HasMany
    {
        return $this->hasMany(IdVerification::class);
    }

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function blockedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'blocked_by');
    }

    // Security-related methods
    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }

    public function hasValidIdVerification(): bool
    {
        return $this->is_id_verified && 
               $this->idVerifications()
                   ->approved()
                   ->where('expires_at', '>', now())
                   ->exists();
    }

    public function getLatestIdVerification(): ?IdVerification
    {
        return $this->idVerifications()->latest()->first();
    }

    public function incrementFailedLoginAttempts(): void
    {
        $this->increment('failed_login_attempts');
        $this->update(['last_failed_login_at' => now()]);
    }

    public function resetFailedLoginAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'last_failed_login_at' => null,
        ]);
    }

    public function addSecurityFlag(string $flag): void
    {
        $flags = $this->security_flags ?? [];
        if (!in_array($flag, $flags)) {
            $flags[] = $flag;
            $this->update(['security_flags' => $flags]);
        }
    }

    public function removeSecurityFlag(string $flag): void
    {
        $flags = $this->security_flags ?? [];
        $flags = array_filter($flags, fn($f) => $f !== $flag);
        $this->update(['security_flags' => array_values($flags)]);
    }

    public function hasSecurityFlag(string $flag): bool
    {
        return in_array($flag, $this->security_flags ?? []);
    }

    public function getRiskLevel(): string
    {
        $score = $this->risk_score ?? 0;
        
        if ($score >= 80) return 'critical';
        if ($score >= 60) return 'high';
        if ($score >= 40) return 'medium';
        return 'low';
    }

    public function getRiskColor(): string
    {
        return match ($this->getRiskLevel()) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray',
        };
    }

    public function getUnresolvedSuspiciousActivities()
    {
        return $this->suspiciousActivities()
            ->unresolved()
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function hasCriticalSuspiciousActivity(): bool
    {
        return $this->suspiciousActivities()
            ->unresolved()
            ->where('severity', SuspiciousActivity::SEVERITY_CRITICAL)
            ->exists();
    }

    /**
     * Check if user is an administrator
     * This accessor allows checking $user->is_admin
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('Administrator') || $this->hasRole('admin');
    }

    /**
     * Check if user meets monthly qualification requirements
     * Users must meet their MAP requirement to receive commissions
     */
    public function meetsMonthlyQualification(): bool
    {
        if (!$this->points) {
            return false;
        }

        // Get required MAP based on professional level
        $requiredMap = match($this->professional_level) {
            1 => 100,  // Associate
            2 => 200,  // Professional
            3 => 300,  // Senior
            4 => 400,  // Manager
            5 => 500,  // Director
            6 => 600,  // Executive
            7 => 800,  // Ambassador
            default => 100
        };

        return $this->points->monthly_points >= $requiredMap;
    }

    /**
     * Award Life Points (LP) to user
     */
    public function awardLifePoints(int $amount, string $activityType, string $description = null): void
    {
        if ($amount <= 0) {
            return;
        }

        // Add to user's total LP
        $this->increment('life_points', $amount);

        // Record transaction
        \App\Models\PointTransaction::create([
            'user_id' => $this->id,
            'point_type' => 'lifetime',
            'lp_amount' => $amount,
            'map_amount' => 0,
            'source' => $activityType,
            'description' => $description ?? "Earned {$amount} LP from {$activityType}",
        ]);
    }

    /**
     * Award Bonus Points (BP) to user
     */
    public function awardBonusPoints(int $amount, string $activityType, string $description = null): void
    {
        if ($amount <= 0) {
            return;
        }

        // Add to user's monthly BP
        $this->increment('monthly_activity_points', $amount);

        // Record transaction
        \App\Models\PointTransaction::create([
            'user_id' => $this->id,
            'point_type' => 'monthly',
            'lp_amount' => 0,
            'map_amount' => $amount,
            'source' => $activityType,
            'description' => $description ?? "Earned {$amount} MAP from {$activityType}",
        ]);
    }

    /**
     * Award both LP and BP based on activity settings
     */
    public function awardPointsForActivity(string $activityType, string $description = null): void
    {
        // Get LP value from settings
        $lpValue = \App\Models\LifePointSetting::getLPValue($activityType);
        if ($lpValue > 0) {
            $this->awardLifePoints($lpValue, $activityType, $description);
        }

        // Get BP value from settings
        $bpValue = \App\Models\BonusPointSetting::getBPValue($activityType);
        if ($bpValue > 0) {
            $this->awardBonusPoints($bpValue, $activityType, $description);
        }
    }
}

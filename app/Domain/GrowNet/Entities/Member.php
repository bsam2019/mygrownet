<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Entities;

use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\MembershipTier;
use App\Domain\GrowNet\ValueObjects\Money;
use App\Domain\GrowNet\ValueObjects\ReferralCode;
use App\Domain\GrowNet\ValueObjects\SubscriptionStatus;
use App\Domain\GrowNet\ValueObjects\VerificationLevel;
use DateTimeImmutable;

class Member
{
    public function __construct(
        private MemberId $id,
        private int $userId,
        private ?MemberId $referrerId = null,
        private ?ReferralCode $referralCode = null,
        private int $referralCount = 0,
        private int $directReferrals = 0,
        private ?string $rank = null,
        private ?MembershipTier $tier = null,
        private ?string $professionalLevel = null,
        private Money $balance = new Money(0),
        private Money $totalEarnings = new Money(0),
        private Money $totalReferralEarnings = new Money(0),
        private Money $totalProfitEarnings = new Money(0),
        private Money $bonusBalance = new Money(0),
        private float $currentTeamVolume = 0,
        private float $currentPersonalVolume = 0,
        private int $currentTeamDepth = 0,
        private int $activeReferralsCount = 0,
        private SubscriptionStatus $subscriptionStatus = SubscriptionStatus::Inactive,
        private ?DateTimeImmutable $subscriptionStartDate = null,
        private ?DateTimeImmutable $subscriptionEndDate = null,
        private ?float $monthlySubscriptionFee = null,
        private float $loyaltyPoints = 0,
        private float $loyaltyPointsAwardedTotal = 0,
        private float $loyaltyPointsWithdrawnTotal = 0,
        private float $lifePoints = 0,
        private float $bonusPoints = 0,
        private bool $hasStarterKit = false,
        private ?string $starterKitTier = null,
        private ?DateTimeImmutable $starterKitPurchasedAt = null,
        private Money $loanBalance = new Money(0),
        private Money $loanLimit = new Money(0),
        private Money $totalLoanIssued = new Money(0),
        private Money $totalLoanRepaid = new Money(0),
        private VerificationLevel $verificationLevel = VerificationLevel::Basic,
        private ?DateTimeImmutable $verificationCompletedAt = null,
        private float $dailyWithdrawalUsed = 0,
        private ?DateTimeImmutable $dailyWithdrawalResetDate = null,
        private bool $isCurrentlyActive = false,
        private ?DateTimeImmutable $tierUpgradedAt = null,
        private array $tierHistory = [],
        private ?DateTimeImmutable $levelAchievedAt = null,
        private int $coursesCompletedCount = 0,
        private int $daysActiveCount = 0,
        private int $currentStreakMonths = 0,
        private int $longestStreakMonths = 0,
        private ?string $performanceTier = null,
        private ?DateTimeImmutable $libraryAccessUntil = null,
    ) {}

    public static function create(
        int $userId,
        ?int $referrerId = null,
        ?string $referralCode = null,
    ): self {
        return new self(
            id: new MemberId(0),
            userId: $userId,
            referrerId: $referrerId ? new MemberId($referrerId) : null,
            referralCode: $referralCode ? new ReferralCode($referralCode) : null,
        );
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: new MemberId($data['id']),
            userId: $data['user_id'],
            referrerId: isset($data['referrer_id']) ? new MemberId($data['referrer_id']) : null,
            referralCode: isset($data['referral_code']) ? new ReferralCode($data['referral_code']) : null,
            referralCount: $data['referral_count'] ?? 0,
            directReferrals: $data['direct_referrals'] ?? 0,
            rank: $data['rank'] ?? null,
            tier: isset($data['current_professional_level']) ? MembershipTier::tryFrom($data['current_professional_level']) : null,
            professionalLevel: $data['current_professional_level'] ?? null,
            balance: new Money($data['balance'] ?? 0),
            totalEarnings: new Money($data['total_earnings'] ?? 0),
            totalReferralEarnings: new Money($data['total_referral_earnings'] ?? 0),
            totalProfitEarnings: new Money($data['total_profit_earnings'] ?? 0),
            bonusBalance: new Money($data['bonus_balance'] ?? 0),
            currentTeamVolume: $data['current_team_volume'] ?? 0,
            currentPersonalVolume: $data['current_personal_volume'] ?? 0,
            currentTeamDepth: $data['current_team_depth'] ?? 0,
            activeReferralsCount: $data['active_referrals_count'] ?? 0,
            subscriptionStatus: SubscriptionStatus::tryFrom($data['subscription_status'] ?? 'inactive') ?? SubscriptionStatus::Inactive,
            subscriptionStartDate: isset($data['subscription_start_date']) ? new DateTimeImmutable($data['subscription_start_date']) : null,
            subscriptionEndDate: isset($data['subscription_end_date']) ? new DateTimeImmutable($data['subscription_end_date']) : null,
            monthlySubscriptionFee: $data['monthly_subscription_fee'] ?? null,
            loyaltyPoints: $data['loyalty_points'] ?? 0,
            loyaltyPointsAwardedTotal: $data['loyalty_points_awarded_total'] ?? 0,
            loyaltyPointsWithdrawnTotal: $data['loyalty_points_withdrawn_total'] ?? 0,
            lifePoints: $data['life_points'] ?? 0,
            bonusPoints: $data['bonus_points'] ?? 0,
            hasStarterKit: $data['has_starter_kit'] ?? false,
            starterKitTier: $data['starter_kit_tier'] ?? null,
            starterKitPurchasedAt: isset($data['starter_kit_purchased_at']) ? new DateTimeImmutable($data['starter_kit_purchased_at']) : null,
            loanBalance: new Money($data['loan_balance'] ?? 0),
            loanLimit: new Money($data['loan_limit'] ?? 0),
            totalLoanIssued: new Money($data['total_loan_issued'] ?? 0),
            totalLoanRepaid: new Money($data['total_loan_repaid'] ?? 0),
            verificationLevel: VerificationLevel::tryFrom($data['verification_level'] ?? 'basic') ?? VerificationLevel::Basic,
            verificationCompletedAt: isset($data['verification_completed_at']) ? new DateTimeImmutable($data['verification_completed_at']) : null,
            dailyWithdrawalUsed: $data['daily_withdrawal_used'] ?? 0,
            dailyWithdrawalResetDate: isset($data['daily_withdrawal_reset_date']) ? new DateTimeImmutable($data['daily_withdrawal_reset_date']) : null,
            isCurrentlyActive: $data['is_currently_active'] ?? false,
            tierUpgradedAt: isset($data['tier_upgraded_at']) ? new DateTimeImmutable($data['tier_upgraded_at']) : null,
            tierHistory: $data['tier_history'] ?? [],
            levelAchievedAt: isset($data['level_achieved_at']) ? new DateTimeImmutable($data['level_achieved_at']) : null,
            coursesCompletedCount: $data['courses_completed_count'] ?? 0,
            daysActiveCount: $data['days_active_count'] ?? 0,
            currentStreakMonths: $data['current_streak_months'] ?? 0,
            longestStreakMonths: $data['longest_streak_months'] ?? 0,
            performanceTier: $data['performance_tier'] ?? null,
            libraryAccessUntil: isset($data['library_access_until']) ? new DateTimeImmutable($data['library_access_until']) : null,
        );
    }

    public function id(): MemberId { return $this->id; }
    public function userId(): int { return $this->userId; }
    public function referrerId(): ?MemberId { return $this->referrerId; }
    public function referralCode(): ?ReferralCode { return $this->referralCode; }
    public function referralCount(): int { return $this->referralCount; }
    public function directReferrals(): int { return $this->directReferrals; }
    public function rank(): ?string { return $this->rank; }
    public function tier(): ?MembershipTier { return $this->tier; }
    public function professionalLevel(): ?string { return $this->professionalLevel; }
    public function balance(): Money { return $this->balance; }
    public function totalEarnings(): Money { return $this->totalEarnings; }
    public function totalReferralEarnings(): Money { return $this->totalReferralEarnings; }
    public function totalProfitEarnings(): Money { return $this->totalProfitEarnings; }
    public function bonusBalance(): Money { return $this->bonusBalance; }
    public function currentTeamVolume(): float { return $this->currentTeamVolume; }
    public function currentPersonalVolume(): float { return $this->currentPersonalVolume; }
    public function currentTeamDepth(): int { return $this->currentTeamDepth; }
    public function activeReferralsCount(): int { return $this->activeReferralsCount; }
    public function subscriptionStatus(): SubscriptionStatus { return $this->subscriptionStatus; }
    public function hasActiveSubscription(): bool { return $this->subscriptionStatus->isActive(); }
    public function loyaltyPoints(): float { return $this->loyaltyPoints; }
    public function hasStarterKit(): bool { return $this->hasStarterKit; }
    public function starterKitTier(): ?string { return $this->starterKitTier; }
    public function loanBalance(): Money { return $this->loanBalance; }
    public function loanLimit(): Money { return $this->loanLimit; }
    public function verificationLevel(): VerificationLevel { return $this->verificationLevel; }
    public function dailyWithdrawalUsed(): float { return $this->dailyWithdrawalUsed; }
    public function isActive(): bool { return $this->isCurrentlyActive; }

    public function remainingDailyWithdrawalLimit(): float
    {
        return max(0, $this->verificationLevel->dailyWithdrawalLimit() - $this->dailyWithdrawalUsed);
    }

    public function availableCredit(): Money
    {
        $available = max(0, $this->loanLimit->amount() - $this->loanBalance->amount());
        return new Money($available);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'user_id' => $this->userId,
            'referrer_id' => $this->referrerId?->value(),
            'referral_code' => $this->referralCode?->value(),
            'referral_count' => $this->referralCount,
            'direct_referrals' => $this->directReferrals,
            'rank' => $this->rank,
            'current_professional_level' => $this->professionalLevel,
            'balance' => $this->balance->amount(),
            'total_earnings' => $this->totalEarnings->amount(),
            'total_referral_earnings' => $this->totalReferralEarnings->amount(),
            'total_profit_earnings' => $this->totalProfitEarnings->amount(),
            'bonus_balance' => $this->bonusBalance->amount(),
            'current_team_volume' => $this->currentTeamVolume,
            'current_personal_volume' => $this->currentPersonalVolume,
            'active_referrals_count' => $this->activeReferralsCount,
            'subscription_status' => $this->subscriptionStatus->value,
            'loyalty_points' => $this->loyaltyPoints,
            'has_starter_kit' => $this->hasStarterKit,
            'starter_kit_tier' => $this->starterKitTier,
            'loan_balance' => $this->loanBalance->amount(),
            'loan_limit' => $this->loanLimit->amount(),
            'verification_level' => $this->verificationLevel->value,
            'daily_withdrawal_used' => $this->dailyWithdrawalUsed,
            'is_currently_active' => $this->isCurrentlyActive,
            'courses_completed_count' => $this->coursesCompletedCount,
            'current_streak_months' => $this->currentStreakMonths,
        ];
    }
}

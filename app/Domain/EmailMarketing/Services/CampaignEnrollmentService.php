<?php

namespace App\Domain\EmailMarketing\Services;

use App\Domain\EmailMarketing\Entities\EmailCampaign;
use App\Models\User;
use DateTimeImmutable;

class CampaignEnrollmentService
{
    public function canEnroll(EmailCampaign $campaign, User $user): bool
    {
        // Check if campaign is active
        if (!$campaign->isActive()) {
            return false;
        }

        // Check if user is already enrolled
        if ($this->isAlreadyEnrolled($campaign, $user)) {
            return false;
        }

        // Check campaign-specific eligibility
        return $this->checkCampaignEligibility($campaign, $user);
    }

    public function enroll(EmailCampaign $campaign, User $user): void
    {
        if (!$this->canEnroll($campaign, $user)) {
            throw new \DomainException('User cannot be enrolled in this campaign');
        }

        // Enrollment logic will be handled by Application layer
        // This service only validates business rules
    }

    private function isAlreadyEnrolled(EmailCampaign $campaign, User $user): bool
    {
        // Check via repository (will be injected in Application layer)
        return false; // Placeholder
    }

    private function checkCampaignEligibility(EmailCampaign $campaign, User $user): bool
    {
        $type = $campaign->type();

        return match(true) {
            $type->isOnboarding() => $this->checkOnboardingEligibility($user),
            $type->isReactivation() => $this->checkReactivationEligibility($user),
            $type->isUpgrade() => $this->checkUpgradeEligibility($user),
            default => true
        };
    }

    private function checkOnboardingEligibility(User $user): bool
    {
        // Only new users (registered within last 24 hours)
        return $user->created_at->diffInHours(now()) <= 24;
    }

    private function checkReactivationEligibility(User $user): bool
    {
        // Only inactive users (no login in 30+ days)
        if (!$user->last_login_at) {
            return false;
        }
        return $user->last_login_at->diffInDays(now()) >= 30;
    }

    private function checkUpgradeEligibility(User $user): bool
    {
        // Only users with basic tier
        return $user->starter_kit_tier === 'basic';
    }
}

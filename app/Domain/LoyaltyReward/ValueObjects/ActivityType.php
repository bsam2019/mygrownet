<?php

namespace App\Domain\LoyaltyReward\ValueObjects;

enum ActivityType: string
{
    case LEARNING_MODULE = 'learning_module';
    case MARKETPLACE_PURCHASE = 'marketplace_purchase';
    case MARKETPLACE_LISTING = 'marketplace_listing';
    case PLATFORM_EVENT = 'platform_event';
    case COMMUNITY_DISCUSSION = 'community_discussion';
    case BUSINESS_PLAN = 'business_plan';
    case QUIZ_COMPLETION = 'quiz_completion';
    case WEBINAR_ATTENDANCE = 'webinar_attendance';

    public function getDisplayName(): string
    {
        return match($this) {
            self::LEARNING_MODULE => 'Learning Module Completed',
            self::MARKETPLACE_PURCHASE => 'Marketplace Purchase',
            self::MARKETPLACE_LISTING => 'Product Listed',
            self::PLATFORM_EVENT => 'Platform Event Attended',
            self::COMMUNITY_DISCUSSION => 'Community Discussion',
            self::BUSINESS_PLAN => 'Business Plan Completed',
            self::QUIZ_COMPLETION => 'Quiz Completed',
            self::WEBINAR_ATTENDANCE => 'Webinar Attended',
        };
    }

    public function isLearningActivity(): bool
    {
        return in_array($this, [
            self::LEARNING_MODULE,
            self::QUIZ_COMPLETION,
            self::WEBINAR_ATTENDANCE,
        ]);
    }

    public function isBusinessActivity(): bool
    {
        return in_array($this, [
            self::MARKETPLACE_PURCHASE,
            self::MARKETPLACE_LISTING,
            self::COMMUNITY_DISCUSSION,
            self::BUSINESS_PLAN,
        ]);
    }
}

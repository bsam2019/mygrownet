<?php

namespace App\Services;

use App\Application\Services\LoyaltyReward\LgrCycleService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LgrActivityTrackingService
{
    public function __construct(
        private LgrCycleService $cycleService
    ) {}

    /**
     * Record learning module completion activity
     */
    public function recordLearningActivity(int $userId, string $moduleName): void
    {
        try {
            $this->cycleService->recordActivity(
                $userId,
                'learning_module',
                "Completed learning module: {$moduleName}",
                ['module_name' => $moduleName]
            );
            
            Log::info('LGR learning activity recorded', [
                'user_id' => $userId,
                'module' => $moduleName,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record LGR learning activity: ' . $e->getMessage(), [
                'user_id' => $userId,
                'module' => $moduleName,
            ]);
        }
    }

    /**
     * Record marketplace purchase activity
     */
    public function recordMarketplacePurchase(int $userId, int $orderId, float $amount): void
    {
        try {
            $this->cycleService->recordActivity(
                $userId,
                'marketplace_purchase',
                "Made marketplace purchase (Order #{$orderId})",
                [
                    'order_id' => $orderId,
                    'amount' => $amount,
                ]
            );
            
            Log::info('LGR marketplace purchase activity recorded', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'amount' => $amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record LGR marketplace activity: ' . $e->getMessage(), [
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
    }

    /**
     * Record marketplace sale activity
     */
    public function recordMarketplaceSale(int $userId, int $orderId, float $amount): void
    {
        try {
            $this->cycleService->recordActivity(
                $userId,
                'marketplace_sale',
                "Made marketplace sale (Order #{$orderId})",
                [
                    'order_id' => $orderId,
                    'amount' => $amount,
                ]
            );
            
            Log::info('LGR marketplace sale activity recorded', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'amount' => $amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record LGR marketplace sale activity: ' . $e->getMessage(), [
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
        }
    }

    /**
     * Record event attendance activity
     */
    public function recordEventAttendance(int $userId, string $eventName, int $eventId): void
    {
        try {
            $this->cycleService->recordActivity(
                $userId,
                'event_attendance',
                "Attended event: {$eventName}",
                [
                    'event_id' => $eventId,
                    'event_name' => $eventName,
                ]
            );
            
            Log::info('LGR event attendance activity recorded', [
                'user_id' => $userId,
                'event_id' => $eventId,
                'event_name' => $eventName,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record LGR event activity: ' . $e->getMessage(), [
                'user_id' => $userId,
                'event_id' => $eventId,
            ]);
        }
    }

    /**
     * Record community engagement activity
     */
    public function recordCommunityEngagement(int $userId, string $activityType, string $description): void
    {
        try {
            $this->cycleService->recordActivity(
                $userId,
                'community_engagement',
                $description,
                ['activity_type' => $activityType]
            );
            
            Log::info('LGR community engagement activity recorded', [
                'user_id' => $userId,
                'activity_type' => $activityType,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record LGR community activity: ' . $e->getMessage(), [
                'user_id' => $userId,
                'activity_type' => $activityType,
            ]);
        }
    }

    /**
     * Record quiz completion activity
     */
    public function recordQuizCompletion(int $userId, string $quizName, int $score): void
    {
        try {
            $this->cycleService->recordActivity(
                $userId,
                'quiz_completion',
                "Completed quiz: {$quizName} (Score: {$score}%)",
                [
                    'quiz_name' => $quizName,
                    'score' => $score,
                ]
            );
            
            Log::info('LGR quiz completion activity recorded', [
                'user_id' => $userId,
                'quiz_name' => $quizName,
                'score' => $score,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record LGR quiz activity: ' . $e->getMessage(), [
                'user_id' => $userId,
                'quiz_name' => $quizName,
            ]);
        }
    }

    /**
     * Record business plan completion activity
     */
    public function recordBusinessPlanCompletion(int $userId): void
    {
        try {
            $this->cycleService->recordActivity(
                $userId,
                'business_plan',
                "Completed business plan template",
                []
            );
            
            Log::info('LGR business plan activity recorded', [
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record LGR business plan activity: ' . $e->getMessage(), [
                'user_id' => $userId,
            ]);
        }
    }

    /**
     * Record platform task completion activity
     */
    public function recordPlatformTask(int $userId, string $taskName): void
    {
        try {
            $this->cycleService->recordActivity(
                $userId,
                'platform_task',
                "Completed platform task: {$taskName}",
                ['task_name' => $taskName]
            );
            
            Log::info('LGR platform task activity recorded', [
                'user_id' => $userId,
                'task_name' => $taskName,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record LGR platform task activity: ' . $e->getMessage(), [
                'user_id' => $userId,
                'task_name' => $taskName,
            ]);
        }
    }

    /**
     * Record referral registration activity
     */
    public function recordReferralRegistration(int $userId, int $referredUserId, string $referredUserName): void
    {
        try {
            $this->cycleService->recordActivity(
                $userId,
                'referral_registration',
                "Referred new member: {$referredUserName}",
                [
                    'referred_user_id' => $referredUserId,
                    'referred_user_name' => $referredUserName,
                ]
            );
            
            Log::info('LGR referral registration activity recorded', [
                'user_id' => $userId,
                'referred_user_id' => $referredUserId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record LGR referral activity: ' . $e->getMessage(), [
                'user_id' => $userId,
                'referred_user_id' => $referredUserId,
            ]);
        }
    }

    /**
     * Record social sharing activity (minimum 5 shares per day)
     */
    public function recordSocialSharing(int $userId, int $shareCount): void
    {
        try {
            // Only record if user has shared at least 5 links today
            if ($shareCount < 5) {
                return;
            }

            $this->cycleService->recordActivity(
                $userId,
                'social_sharing',
                "Shared {$shareCount} links on social media",
                ['share_count' => $shareCount]
            );
            
            Log::info('LGR social sharing activity recorded', [
                'user_id' => $userId,
                'share_count' => $shareCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record LGR social sharing activity: ' . $e->getMessage(), [
                'user_id' => $userId,
                'share_count' => $shareCount,
            ]);
        }
    }
}


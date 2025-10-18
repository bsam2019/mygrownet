<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\MyGrowNetCommissionNotification;
use App\Notifications\MyGrowNetAchievementNotification;
use App\Notifications\MyGrowNetAssetNotification;
use App\Notifications\MyGrowNetCommunityNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class MyGrowNetNotificationService
{
    /**
     * Send commission notification
     */
    public function sendCommissionNotification(User $user, array $data): void
    {
        try {
            $user->notify(new MyGrowNetCommissionNotification($data));
            
            Log::info('Commission notification sent', [
                'user_id' => $user->id,
                'type' => $data['type'],
                'amount' => $data['amount'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send commission notification', [
                'user_id' => $user->id,
                'type' => $data['type'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send achievement notification
     */
    public function sendAchievementNotification(User $user, array $data): void
    {
        try {
            $user->notify(new MyGrowNetAchievementNotification($data));
            
            Log::info('Achievement notification sent', [
                'user_id' => $user->id,
                'type' => $data['type'],
                'achievement' => $data['achievement_name'] ?? $data['new_tier'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send achievement notification', [
                'user_id' => $user->id,
                'type' => $data['type'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send asset notification
     */
    public function sendAssetNotification(User $user, array $data): void
    {
        try {
            $user->notify(new MyGrowNetAssetNotification($data));
            
            Log::info('Asset notification sent', [
                'user_id' => $user->id,
                'type' => $data['type'],
                'asset' => $data['asset_type'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send asset notification', [
                'user_id' => $user->id,
                'type' => $data['type'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send community notification
     */
    public function sendCommunityNotification(User $user, array $data): void
    {
        try {
            $user->notify(new MyGrowNetCommunityNotification($data));
            
            Log::info('Community notification sent', [
                'user_id' => $user->id,
                'type' => $data['type'],
                'project' => $data['project_name'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send community notification', [
                'user_id' => $user->id,
                'type' => $data['type'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send five-level commission notification
     */
    public function sendFiveLevelCommissionNotification(
        User $user,
        float $amount,
        int $level,
        string $referralName,
        string $commissionType = 'Referral'
    ): void {
        $this->sendCommissionNotification($user, [
            'type' => 'five_level_commission_earned',
            'amount' => $amount,
            'level' => $level,
            'referral_name' => $referralName,
            'commission_type' => $commissionType
        ]);
    }

    /**
     * Send team volume bonus notification
     */
    public function sendTeamVolumeBonusNotification(
        User $user,
        float $amount,
        float $teamVolume,
        float $bonusPercentage,
        string $tier
    ): void {
        $this->sendCommissionNotification($user, [
            'type' => 'team_volume_bonus_earned',
            'amount' => $amount,
            'team_volume' => $teamVolume,
            'bonus_percentage' => $bonusPercentage,
            'tier' => $tier
        ]);
    }

    /**
     * Send performance bonus notification
     */
    public function sendPerformanceBonusNotification(
        User $user,
        float $amount,
        string $bonusType,
        string $achievement
    ): void {
        $this->sendCommissionNotification($user, [
            'type' => 'performance_bonus_earned',
            'amount' => $amount,
            'bonus_type' => $bonusType,
            'achievement' => $achievement
        ]);
    }

    /**
     * Send leadership bonus notification
     */
    public function sendLeadershipBonusNotification(
        User $user,
        float $amount,
        string $leadershipLevel,
        int $teamSize
    ): void {
        $this->sendCommissionNotification($user, [
            'type' => 'leadership_bonus_earned',
            'amount' => $amount,
            'leadership_level' => $leadershipLevel,
            'team_size' => $teamSize
        ]);
    }

    /**
     * Send commission payment processed notification
     */
    public function sendCommissionPaymentProcessedNotification(
        User $user,
        float $amount,
        string $paymentMethod,
        string $transactionId
    ): void {
        $this->sendCommissionNotification($user, [
            'type' => 'commission_payment_processed',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId
        ]);
    }

    /**
     * Send commission payment failed notification
     */
    public function sendCommissionPaymentFailedNotification(
        User $user,
        float $amount,
        string $failureReason,
        string $retryDate = 'within 24 hours'
    ): void {
        $this->sendCommissionNotification($user, [
            'type' => 'commission_payment_failed',
            'amount' => $amount,
            'failure_reason' => $failureReason,
            'retry_date' => $retryDate
        ]);
    }

    /**
     * Send monthly commission summary notification
     */
    public function sendMonthlyCommissionSummaryNotification(
        User $user,
        float $totalEarnings,
        int $commissionCount,
        string $month,
        array $breakdown = []
    ): void {
        $this->sendCommissionNotification($user, [
            'type' => 'monthly_commission_summary',
            'total_earnings' => $totalEarnings,
            'commission_count' => $commissionCount,
            'month' => $month,
            'breakdown' => $breakdown
        ]);
    }

    /**
     * Send tier advancement notification
     */
    public function sendTierAdvancementNotification(
        User $user,
        string $newTier,
        string $previousTier,
        float $achievementBonus,
        array $newBenefits = []
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'tier_advancement',
            'new_tier' => $newTier,
            'previous_tier' => $previousTier,
            'achievement_bonus' => $achievementBonus,
            'new_benefits' => $newBenefits
        ]);
    }

    /**
     * Send achievement unlocked notification
     */
    public function sendAchievementUnlockedNotification(
        User $user,
        string $achievementName,
        string $achievementDescription = '',
        string $reward = '',
        string $category = 'General'
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'achievement_unlocked',
            'achievement_name' => $achievementName,
            'achievement_description' => $achievementDescription,
            'reward' => $reward,
            'category' => $category
        ]);
    }

    /**
     * Send milestone reached notification
     */
    public function sendMilestoneReachedNotification(
        User $user,
        string $milestoneName,
        string $milestoneValue = '',
        string $reward = '',
        string $nextMilestone = ''
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'milestone_reached',
            'milestone_name' => $milestoneName,
            'milestone_value' => $milestoneValue,
            'reward' => $reward,
            'next_milestone' => $nextMilestone
        ]);
    }

    /**
     * Send badge earned notification
     */
    public function sendBadgeEarnedNotification(
        User $user,
        string $badgeName,
        string $badgeDescription = '',
        string $badgeCategory = 'Achievement',
        string $rarity = 'Common'
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'badge_earned',
            'badge_name' => $badgeName,
            'badge_description' => $badgeDescription,
            'badge_category' => $badgeCategory,
            'rarity' => $rarity
        ]);
    }

    /**
     * Send leaderboard position notification
     */
    public function sendLeaderboardPositionNotification(
        User $user,
        int $position,
        string $leaderboardType = 'General',
        string $period = 'this month',
        string $reward = '',
        int $totalParticipants = 0
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'leaderboard_position',
            'position' => $position,
            'leaderboard_type' => $leaderboardType,
            'period' => $period,
            'reward' => $reward,
            'total_participants' => $totalParticipants
        ]);
    }

    /**
     * Send physical reward earned notification
     */
    public function sendPhysicalRewardEarnedNotification(
        User $user,
        string $rewardType,
        float $rewardValue,
        string $qualifyingTier,
        int $maintenancePeriod = 12,
        string $estimatedDelivery = '2-4 weeks'
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'physical_reward_earned',
            'reward_type' => $rewardType,
            'reward_value' => $rewardValue,
            'qualifying_tier' => $qualifyingTier,
            'maintenance_period' => $maintenancePeriod,
            'estimated_delivery' => $estimatedDelivery
        ]);
    }

    /**
     * Send physical reward ready notification
     */
    public function sendPhysicalRewardReadyNotification(
        User $user,
        string $rewardType,
        string $collectionLocation = 'MyGrowNet Office',
        string $contactNumber = '',
        string $deliveryOption = 'collection'
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'physical_reward_ready',
            'reward_type' => $rewardType,
            'collection_location' => $collectionLocation,
            'contact_number' => $contactNumber ?: config('mygrownet.contact.phone'),
            'delivery_option' => $deliveryOption
        ]);
    }

    /**
     * Send asset ownership transferred notification
     */
    public function sendAssetOwnershipTransferredNotification(
        User $user,
        string $assetType,
        float $assetValue,
        int $maintenancePeriodCompleted = 12,
        array $incomeOpportunities = []
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'asset_ownership_transferred',
            'asset_type' => $assetType,
            'asset_value' => $assetValue,
            'maintenance_period_completed' => $maintenancePeriodCompleted,
            'income_opportunities' => $incomeOpportunities
        ]);
    }

    /**
     * Send raffle entry earned notification
     */
    public function sendRaffleEntryEarnedNotification(
        User $user,
        string $raffleName,
        int $entriesEarned = 1,
        int $totalEntries = 1,
        string $drawDate = 'Soon',
        array $prizes = []
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'raffle_entry_earned',
            'raffle_name' => $raffleName,
            'entries_earned' => $entriesEarned,
            'total_entries' => $totalEntries,
            'draw_date' => $drawDate,
            'prizes' => $prizes
        ]);
    }

    /**
     * Send raffle winner notification
     */
    public function sendRaffleWinnerNotification(
        User $user,
        string $raffleName,
        string $prizeWon,
        string $prizeValue = '',
        string $claimInstructions = 'Contact support for claim details'
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'raffle_winner',
            'raffle_name' => $raffleName,
            'prize_won' => $prizeWon,
            'prize_value' => $prizeValue,
            'claim_instructions' => $claimInstructions
        ]);
    }

    /**
     * Send recognition event notification
     */
    public function sendRecognitionEventNotification(
        User $user,
        string $eventName,
        string $recognitionType = 'Achievement',
        string $eventDate = 'Soon',
        string $eventLocation = 'MyGrowNet Center',
        array $specialGuests = []
    ): void {
        $this->sendAchievementNotification($user, [
            'type' => 'recognition_event',
            'event_name' => $eventName,
            'recognition_type' => $recognitionType,
            'event_date' => $eventDate,
            'event_location' => $eventLocation,
            'special_guests' => $specialGuests
        ]);
    }

    /**
     * Send bulk notifications to multiple users
     */
    public function sendBulkCommissionNotifications(array $users, array $data): void
    {
        try {
            Notification::send($users, new MyGrowNetCommissionNotification($data));
            
            Log::info('Bulk commission notifications sent', [
                'user_count' => count($users),
                'type' => $data['type']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk commission notifications', [
                'user_count' => count($users),
                'type' => $data['type'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send bulk achievement notifications to multiple users
     */
    public function sendBulkAchievementNotifications(array $users, array $data): void
    {
        try {
            Notification::send($users, new MyGrowNetAchievementNotification($data));
            
            Log::info('Bulk achievement notifications sent', [
                'user_count' => count($users),
                'type' => $data['type']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk achievement notifications', [
                'user_count' => count($users),
                'type' => $data['type'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get notification preferences for user
     */
    public function getNotificationPreferences(User $user): array
    {
        return [
            'email_enabled' => $user->notification_preferences['email'] ?? true,
            'sms_enabled' => $user->notification_preferences['sms'] ?? true,
            'push_enabled' => $user->notification_preferences['push'] ?? true,
            'commission_notifications' => $user->notification_preferences['commission'] ?? true,
            'achievement_notifications' => $user->notification_preferences['achievement'] ?? true,
            'marketing_notifications' => $user->notification_preferences['marketing'] ?? false
        ];
    }

    /**
     * Update notification preferences for user
     */
    public function updateNotificationPreferences(User $user, array $preferences): void
    {
        $user->update([
            'notification_preferences' => array_merge(
                $user->notification_preferences ?? [],
                $preferences
            )
        ]);
    }

    // Asset Notification Methods

    /**
     * Send asset allocation approved notification
     */
    public function sendAssetAllocationApprovedNotification(
        User $user,
        string $assetType,
        float $assetValue,
        string $qualifyingTier,
        string $assetModel = '',
        int $maintenancePeriod = 12,
        string $estimatedDelivery = '2-4 weeks'
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_allocation_approved',
            'asset_type' => $assetType,
            'asset_model' => $assetModel,
            'asset_value' => $assetValue,
            'qualifying_tier' => $qualifyingTier,
            'maintenance_period' => $maintenancePeriod,
            'estimated_delivery' => $estimatedDelivery
        ]);
    }

    /**
     * Send asset allocation pending notification
     */
    public function sendAssetAllocationPendingNotification(
        User $user,
        string $assetType,
        string $currentTier,
        string $requiredTier,
        float $currentVolume,
        float $requiredVolume,
        string $timeRemaining = ''
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_allocation_pending',
            'asset_type' => $assetType,
            'current_tier' => $currentTier,
            'required_tier' => $requiredTier,
            'current_volume' => $currentVolume,
            'required_volume' => $requiredVolume,
            'time_remaining' => $timeRemaining
        ]);
    }

    /**
     * Send asset delivery scheduled notification
     */
    public function sendAssetDeliveryScheduledNotification(
        User $user,
        string $assetType,
        string $deliveryDate,
        string $assetModel = '',
        string $deliveryTime = '',
        string $deliveryAddress = '',
        array $deliveryInstructions = []
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_delivery_scheduled',
            'asset_type' => $assetType,
            'asset_model' => $assetModel,
            'delivery_date' => $deliveryDate,
            'delivery_time' => $deliveryTime,
            'delivery_address' => $deliveryAddress,
            'delivery_instructions' => $deliveryInstructions,
            'contact_number' => config('mygrownet.contact.phone')
        ]);
    }

    /**
     * Send asset delivered notification
     */
    public function sendAssetDeliveredNotification(
        User $user,
        string $assetType,
        string $deliveryDate,
        string $assetModel = '',
        int $maintenancePeriod = 12,
        string $maintenanceStartDate = ''
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_delivered',
            'asset_type' => $assetType,
            'asset_model' => $assetModel,
            'delivery_date' => $deliveryDate,
            'maintenance_period' => $maintenancePeriod,
            'maintenance_start_date' => $maintenanceStartDate ?: date('Y-m-d'),
            'asset_management_contact' => config('mygrownet.contact.phone')
        ]);
    }

    /**
     * Send asset maintenance reminder notification
     */
    public function sendAssetMaintenanceReminderNotification(
        User $user,
        string $assetType,
        int $maintenanceMonth,
        int $totalMonths,
        string $currentTier,
        string $requiredTier,
        float $currentVolume,
        float $requiredVolume
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_maintenance_reminder',
            'asset_type' => $assetType,
            'maintenance_month' => $maintenanceMonth,
            'total_months' => $totalMonths,
            'current_tier' => $currentTier,
            'required_tier' => $requiredTier,
            'current_volume' => $currentVolume,
            'required_volume' => $requiredVolume
        ]);
    }

    /**
     * Send asset maintenance warning notification
     */
    public function sendAssetMaintenanceWarningNotification(
        User $user,
        string $assetType,
        string $warningType,
        string $currentTier,
        string $requiredTier,
        float $currentVolume,
        float $requiredVolume,
        string $gracePeriod = '30 days'
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_maintenance_warning',
            'asset_type' => $assetType,
            'warning_type' => $warningType,
            'current_tier' => $currentTier,
            'required_tier' => $requiredTier,
            'current_volume' => $currentVolume,
            'required_volume' => $requiredVolume,
            'grace_period' => $gracePeriod,
            'support_contact' => config('mygrownet.contact.phone')
        ]);
    }

    /**
     * Send asset maintenance violation notification
     */
    public function sendAssetMaintenanceViolationNotification(
        User $user,
        string $assetType,
        string $violationType,
        string $violationDate,
        bool $paymentPlanAvailable = true,
        float $paymentPlanAmount = 0,
        string $assetRecoveryDate = ''
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_maintenance_violation',
            'asset_type' => $assetType,
            'violation_type' => $violationType,
            'violation_date' => $violationDate,
            'payment_plan_available' => $paymentPlanAvailable,
            'payment_plan_amount' => $paymentPlanAmount,
            'asset_recovery_date' => $assetRecoveryDate,
            'support_contact' => config('mygrownet.contact.phone')
        ]);
    }

    /**
     * Send asset ownership pending notification
     */
    public function sendAssetOwnershipPendingNotification(
        User $user,
        string $assetType,
        int $maintenancePeriod,
        string $completionDate = '',
        string $ownershipTransferDate = '',
        array $finalRequirements = []
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_ownership_pending',
            'asset_type' => $assetType,
            'maintenance_period' => $maintenancePeriod,
            'completion_date' => $completionDate,
            'ownership_transfer_date' => $ownershipTransferDate,
            'final_requirements' => $finalRequirements
        ]);
    }

    /**
     * Send asset ownership completed notification
     */
    public function sendAssetOwnershipCompletedNotification(
        User $user,
        string $assetType,
        float $assetValue,
        string $transferDate = '',
        array $incomeOpportunities = []
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_ownership_completed',
            'asset_type' => $assetType,
            'asset_value' => $assetValue,
            'transfer_date' => $transferDate ?: date('Y-m-d'),
            'income_opportunities' => $incomeOpportunities,
            'asset_management_contact' => config('mygrownet.contact.phone')
        ]);
    }

    /**
     * Send asset income report notification
     */
    public function sendAssetIncomeReportNotification(
        User $user,
        string $assetType,
        float $incomeGenerated,
        float $totalIncome,
        string $reportPeriod = 'Monthly',
        array $incomeBreakdown = [],
        string $nextPaymentDate = ''
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_income_report',
            'asset_type' => $assetType,
            'report_period' => $reportPeriod,
            'income_generated' => $incomeGenerated,
            'total_income' => $totalIncome,
            'income_breakdown' => $incomeBreakdown,
            'next_payment_date' => $nextPaymentDate
        ]);
    }

    /**
     * Send asset valuation update notification
     */
    public function sendAssetValuationUpdateNotification(
        User $user,
        string $assetType,
        float $previousValue,
        float $currentValue,
        string $valuationDate = '',
        string $nextValuation = 'Next year'
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_valuation_update',
            'asset_type' => $assetType,
            'previous_value' => $previousValue,
            'current_value' => $currentValue,
            'valuation_date' => $valuationDate ?: date('Y-m-d'),
            'next_valuation' => $nextValuation
        ]);
    }

    /**
     * Send asset buyback offer notification
     */
    public function sendAssetBuybackOfferNotification(
        User $user,
        string $assetType,
        float $currentValue,
        float $buybackOffer,
        string $reasonForOffer = 'Market opportunity',
        string $offerExpiry = '30 days'
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_buyback_offer',
            'asset_type' => $assetType,
            'current_value' => $currentValue,
            'buyback_offer' => $buybackOffer,
            'reason_for_offer' => $reasonForOffer,
            'offer_expiry' => $offerExpiry
        ]);
    }

    /**
     * Send asset management enrollment notification
     */
    public function sendAssetManagementEnrollmentNotification(
        User $user,
        string $assetType,
        float $expectedIncome,
        array $managementServices = [],
        string $managementFee = '10%',
        string $enrollmentDeadline = '30 days',
        string $contactPerson = 'Asset Management Team'
    ): void {
        $this->sendAssetNotification($user, [
            'type' => 'asset_management_enrollment',
            'asset_type' => $assetType,
            'management_services' => $managementServices,
            'expected_income' => $expectedIncome,
            'management_fee' => $managementFee,
            'enrollment_deadline' => $enrollmentDeadline,
            'contact_person' => $contactPerson,
            'contact_number' => config('mygrownet.contact.phone')
        ]);
    }

    /**
     * Send bulk asset notifications to multiple users
     */
    public function sendBulkAssetNotifications(array $users, array $data): void
    {
        try {
            Notification::send($users, new MyGrowNetAssetNotification($data));
            
            Log::info('Bulk asset notifications sent', [
                'user_count' => count($users),
                'type' => $data['type']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk asset notifications', [
                'user_count' => count($users),
                'type' => $data['type'],
                'error' => $e->getMessage()
            ]);
        }
    }

    // Community Notification Methods

    /**
     * Send project launched notification
     */
    public function sendProjectLaunchedNotification(
        User $user,
        string $projectName,
        float $targetAmount,
        float $minimumContribution,
        string $projectType = 'Community Project',
        string $projectDescription = '',
        string $expectedReturns = '',
        string $projectDuration = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'project_launched',
            'project_name' => $projectName,
            'project_type' => $projectType,
            'target_amount' => $targetAmount,
            'minimum_contribution' => $minimumContribution,
            'project_description' => $projectDescription,
            'expected_returns' => $expectedReturns,
            'project_duration' => $projectDuration
        ]);
    }

    /**
     * Send project update posted notification
     */
    public function sendProjectUpdatePostedNotification(
        User $user,
        string $projectName,
        string $updateTitle,
        string $updateSummary = '',
        float $progressPercentage = 0,
        float $currentAmount = 0,
        float $targetAmount = 0,
        string $updateDate = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'project_update_posted',
            'project_name' => $projectName,
            'update_title' => $updateTitle,
            'update_summary' => $updateSummary,
            'progress_percentage' => $progressPercentage,
            'current_amount' => $currentAmount,
            'target_amount' => $targetAmount,
            'update_date' => $updateDate ?: date('Y-m-d')
        ]);
    }

    /**
     * Send project milestone reached notification
     */
    public function sendProjectMilestoneReachedNotification(
        User $user,
        string $projectName,
        string $milestoneName,
        string $milestoneDescription = '',
        float $progressPercentage = 0,
        string $nextMilestone = '',
        string $celebrationBonus = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'project_milestone_reached',
            'project_name' => $projectName,
            'milestone_name' => $milestoneName,
            'milestone_description' => $milestoneDescription,
            'progress_percentage' => $progressPercentage,
            'next_milestone' => $nextMilestone,
            'celebration_bonus' => $celebrationBonus
        ]);
    }

    /**
     * Send project funding target reached notification
     */
    public function sendProjectFundingTargetReachedNotification(
        User $user,
        string $projectName,
        float $targetAmount,
        int $totalContributors,
        float $userContribution,
        float $userPercentage,
        string $projectStartDate = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'project_funding_target_reached',
            'project_name' => $projectName,
            'target_amount' => $targetAmount,
            'total_contributors' => $totalContributors,
            'user_contribution' => $userContribution,
            'user_percentage' => $userPercentage,
            'project_start_date' => $projectStartDate
        ]);
    }

    /**
     * Send project completed notification
     */
    public function sendProjectCompletedNotification(
        User $user,
        string $projectName,
        float $totalProfit,
        float $userShare,
        float $returnPercentage,
        string $projectDuration = '',
        string $completionDate = '',
        string $paymentDate = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'project_completed',
            'project_name' => $projectName,
            'project_duration' => $projectDuration,
            'total_profit' => $totalProfit,
            'user_share' => $userShare,
            'return_percentage' => $returnPercentage,
            'completion_date' => $completionDate ?: date('Y-m-d'),
            'payment_date' => $paymentDate
        ]);
    }

    /**
     * Send voting opened notification
     */
    public function sendVotingOpenedNotification(
        User $user,
        string $projectName,
        string $votingTopic,
        string $votingDescription = '',
        string $votingDeadline = '',
        array $votingOptions = [],
        float $userVotingPower = 0
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'voting_opened',
            'project_name' => $projectName,
            'voting_topic' => $votingTopic,
            'voting_description' => $votingDescription,
            'voting_deadline' => $votingDeadline,
            'voting_options' => $votingOptions,
            'user_voting_power' => $userVotingPower
        ]);
    }

    /**
     * Send voting reminder notification
     */
    public function sendVotingReminderNotification(
        User $user,
        string $projectName,
        string $votingTopic,
        string $votingDeadline = '',
        string $timeRemaining = '',
        float $currentParticipation = 0
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'voting_reminder',
            'project_name' => $projectName,
            'voting_topic' => $votingTopic,
            'voting_deadline' => $votingDeadline,
            'time_remaining' => $timeRemaining,
            'current_participation' => $currentParticipation
        ]);
    }

    /**
     * Send voting closing soon notification
     */
    public function sendVotingClosingSoonNotification(
        User $user,
        string $projectName,
        string $votingTopic,
        int $hoursRemaining = 24,
        float $currentParticipation = 0,
        string $leadingOption = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'voting_closing_soon',
            'project_name' => $projectName,
            'voting_topic' => $votingTopic,
            'hours_remaining' => $hoursRemaining,
            'current_participation' => $currentParticipation,
            'leading_option' => $leadingOption
        ]);
    }

    /**
     * Send voting results notification
     */
    public function sendVotingResultsNotification(
        User $user,
        string $projectName,
        string $votingTopic,
        string $winningOption,
        float $winningPercentage,
        float $totalParticipation,
        bool $userVoted = false,
        string $userChoice = '',
        string $implementationDate = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'voting_results',
            'project_name' => $projectName,
            'voting_topic' => $votingTopic,
            'winning_option' => $winningOption,
            'winning_percentage' => $winningPercentage,
            'total_participation' => $totalParticipation,
            'user_voted' => $userVoted,
            'user_choice' => $userChoice,
            'implementation_date' => $implementationDate
        ]);
    }

    /**
     * Send profit distribution calculated notification
     */
    public function sendProfitDistributionCalculatedNotification(
        User $user,
        string $projectName,
        float $totalProfit,
        float $userShare,
        float $userPercentage,
        string $distributionPeriod = 'Quarterly',
        string $paymentDate = '',
        string $projectPerformance = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'profit_distribution_calculated',
            'project_name' => $projectName,
            'distribution_period' => $distributionPeriod,
            'total_profit' => $totalProfit,
            'user_share' => $userShare,
            'user_percentage' => $userPercentage,
            'payment_date' => $paymentDate,
            'project_performance' => $projectPerformance
        ]);
    }

    /**
     * Send profit distribution paid notification
     */
    public function sendProfitDistributionPaidNotification(
        User $user,
        string $projectName,
        float $distributionAmount,
        string $paymentMethod = 'Mobile Money',
        string $transactionId = '',
        string $distributionPeriod = 'Quarterly',
        float $totalEarningsToDate = 0
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'profit_distribution_paid',
            'project_name' => $projectName,
            'distribution_amount' => $distributionAmount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'distribution_period' => $distributionPeriod,
            'total_earnings_to_date' => $totalEarningsToDate
        ]);
    }

    /**
     * Send quarterly community report notification
     */
    public function sendQuarterlyCommunityReportNotification(
        User $user,
        string $reportPeriod,
        int $totalProjects,
        int $completedProjects,
        float $totalCommunityProfit,
        float $userTotalEarnings,
        float $activeContributions,
        string $topPerformingProject = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'quarterly_community_report',
            'report_period' => $reportPeriod,
            'total_projects' => $totalProjects,
            'completed_projects' => $completedProjects,
            'total_community_profit' => $totalCommunityProfit,
            'user_total_earnings' => $userTotalEarnings,
            'active_contributions' => $activeContributions,
            'top_performing_project' => $topPerformingProject
        ]);
    }

    /**
     * Send new project opportunity notification
     */
    public function sendNewProjectOpportunityNotification(
        User $user,
        string $projectName,
        float $targetAmount,
        float $minimumContribution,
        string $projectType = 'Investment Opportunity',
        string $expectedReturns = '',
        string $projectDuration = '',
        string $earlyBirdBonus = '',
        string $launchDate = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'new_project_opportunity',
            'project_name' => $projectName,
            'project_type' => $projectType,
            'target_amount' => $targetAmount,
            'minimum_contribution' => $minimumContribution,
            'expected_returns' => $expectedReturns,
            'project_duration' => $projectDuration,
            'early_bird_bonus' => $earlyBirdBonus,
            'launch_date' => $launchDate
        ]);
    }

    /**
     * Send contribution acknowledged notification
     */
    public function sendContributionAcknowledgedNotification(
        User $user,
        string $projectName,
        float $contributionAmount,
        float $ownershipPercentage,
        float $totalRaised,
        float $targetAmount,
        float $progressPercentage,
        string $contributorRank = ''
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'contribution_acknowledged',
            'project_name' => $projectName,
            'contribution_amount' => $contributionAmount,
            'ownership_percentage' => $ownershipPercentage,
            'total_raised' => $totalRaised,
            'target_amount' => $targetAmount,
            'progress_percentage' => $progressPercentage,
            'contributor_rank' => $contributorRank
        ]);
    }

    /**
     * Send project risk alert notification
     */
    public function sendProjectRiskAlertNotification(
        User $user,
        string $projectName,
        string $riskType,
        string $riskDescription = '',
        string $mitigationPlan = '',
        string $impactLevel = 'Medium',
        bool $actionRequired = false,
        bool $votingScheduled = false
    ): void {
        $this->sendCommunityNotification($user, [
            'type' => 'project_risk_alert',
            'project_name' => $projectName,
            'risk_type' => $riskType,
            'risk_description' => $riskDescription,
            'mitigation_plan' => $mitigationPlan,
            'impact_level' => $impactLevel,
            'action_required' => $actionRequired,
            'voting_scheduled' => $votingScheduled
        ]);
    }

    /**
     * Send bulk community notifications to multiple users
     */
    public function sendBulkCommunityNotifications(array $users, array $data): void
    {
        try {
            Notification::send($users, new MyGrowNetCommunityNotification($data));
            
            Log::info('Bulk community notifications sent', [
                'user_count' => count($users),
                'type' => $data['type']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk community notifications', [
                'user_count' => count($users),
                'type' => $data['type'],
                'error' => $e->getMessage()
            ]);
        }
    }
}
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MyGrowNetAchievementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $data
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        if (config('mygrownet.notifications.email_enabled', true)) {
            $channels[] = 'mail';
        }
        
        if (config('mygrownet.notifications.sms_enabled', true) && $notifiable->phone) {
            // SMS channel will be added when SMS service is implemented
            // $channels[] = 'sms';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return match ($this->data['type']) {
            'tier_advancement' => $this->tierAdvancementMail($notifiable),
            'achievement_unlocked' => $this->achievementUnlockedMail($notifiable),
            'milestone_reached' => $this->milestoneReachedMail($notifiable),
            'badge_earned' => $this->badgeEarnedMail($notifiable),
            'leaderboard_position' => $this->leaderboardPositionMail($notifiable),
            'physical_reward_earned' => $this->physicalRewardEarnedMail($notifiable),
            'physical_reward_ready' => $this->physicalRewardReadyMail($notifiable),
            'asset_ownership_transferred' => $this->assetOwnershipTransferredMail($notifiable),
            'raffle_entry_earned' => $this->raffleEntryEarnedMail($notifiable),
            'raffle_winner' => $this->raffleWinnerMail($notifiable),
            'recognition_event' => $this->recognitionEventMail($notifiable),
            default => $this->defaultMail($notifiable)
        };
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->data['type'],
            'message' => $this->getDatabaseMessage(),
            'data' => $this->data,
            'priority' => $this->getNotificationPriority()
        ];
    }

    /**
     * Tier advancement notification email
     */
    protected function tierAdvancementMail(object $notifiable): MailMessage
    {
        $newTier = $this->data['new_tier'];
        $previousTier = $this->data['previous_tier'] ?? 'Bronze';
        $achievementBonus = number_format($this->data['achievement_bonus'] ?? 0, 2);
        $newBenefits = $this->data['new_benefits'] ?? [];
        
        return (new MailMessage)
            ->subject("🎉 Congratulations! You've Advanced to {$newTier} Tier!")
            ->greeting("Amazing achievement, {$notifiable->name}!")
            ->line("You've successfully advanced from {$previousTier} to **{$newTier} Tier**! This is a significant milestone in your MyGrowNet journey.")
            ->line("**Your Achievement Rewards:**")
            ->line("• **Achievement Bonus:** K{$achievementBonus}")
            ->line("• **New Tier Status:** {$newTier}")
            ->line("• **Upgraded Benefits:** Access to enhanced features and rewards")
            ->when(!empty($newBenefits), function ($mail) use ($newBenefits) {
                $mail->line("**New Benefits Include:**");
                foreach ($newBenefits as $benefit) {
                    $mail->line("• {$benefit}");
                }
                return $mail;
            })
            ->line("**What This Means for You:**")
            ->line("• Higher commission rates on your network")
            ->line("• Access to exclusive {$newTier} tier benefits")
            ->line("• Eligibility for premium physical rewards")
            ->line("• Enhanced profit-sharing opportunities")
            ->line("**Keep Growing!**")
            ->line("This advancement shows your dedication and success. Continue building your network and supporting your team to unlock even greater rewards!")
            ->action('Explore Your New Benefits', url('/mygrownet/dashboard'))
            ->line('Congratulations on this incredible achievement!')
            ->salutation('Celebrating with you, The MyGrowNet Team');
    }

    /**
     * Achievement unlocked notification email
     */
    protected function achievementUnlockedMail(object $notifiable): MailMessage
    {
        $achievementName = $this->data['achievement_name'];
        $achievementDescription = $this->data['achievement_description'] ?? '';
        $reward = $this->data['reward'] ?? '';
        $category = $this->data['category'] ?? 'General';
        
        return (new MailMessage)
            ->subject("🏆 Achievement Unlocked: {$achievementName}!")
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("You've unlocked a new achievement in MyGrowNet!")
            ->line("**Achievement Details:**")
            ->line("• **Achievement:** {$achievementName}")
            ->line("• **Category:** {$category}")
            ->when($achievementDescription, fn($mail) => $mail->line("• **Description:** {$achievementDescription}"))
            ->when($reward, fn($mail) => $mail->line("• **Reward:** {$reward}"))
            ->line("**Your Progress Matters!**")
            ->line("Every achievement brings you closer to your financial goals. Your consistent effort and dedication are paying off!")
            ->line("**Next Steps:**")
            ->line("• Check your dashboard for more achievements to unlock")
            ->line("• Share your success with your team")
            ->line("• Keep building towards your next milestone")
            ->action('View All Achievements', url('/mygrownet/dashboard'))
            ->line('Keep up the excellent work!')
            ->salutation('Proudly yours, The MyGrowNet Team');
    }

    /**
     * Milestone reached notification email
     */
    protected function milestoneMail(object $notifiable): MailMessage
    {
        $milestoneName = $this->data['milestone_name'];
        $milestoneValue = $this->data['milestone_value'] ?? '';
        $reward = $this->data['reward'] ?? '';
        $nextMilestone = $this->data['next_milestone'] ?? '';
        
        return (new MailMessage)
            ->subject("🎯 Milestone Reached: {$milestoneName}!")
            ->greeting("Outstanding progress, {$notifiable->name}!")
            ->line("You've reached an important milestone in your MyGrowNet journey!")
            ->line("**Milestone Details:**")
            ->line("• **Milestone:** {$milestoneName}")
            ->when($milestoneValue, fn($mail) => $mail->line("• **Achievement:** {$milestoneValue}"))
            ->when($reward, fn($mail) => $mail->line("• **Reward:** {$reward}"))
            ->line("**Milestone Significance:**")
            ->line("This milestone represents your growing success and commitment to building wealth through MyGrowNet. You're making real progress!")
            ->when($nextMilestone, function ($mail) use ($nextMilestone) {
                return $mail->line("**Next Target:** {$nextMilestone}")
                    ->line("Keep pushing forward to reach your next milestone!");
            })
            ->action('Track Your Progress', url('/mygrownet/dashboard'))
            ->line('Every milestone brings you closer to financial freedom!')
            ->salutation('Cheering you on, The MyGrowNet Team');
    }

    /**
     * Badge earned notification email
     */
    protected function badgeEarnedMail(object $notifiable): MailMessage
    {
        $badgeName = $this->data['badge_name'];
        $badgeDescription = $this->data['badge_description'] ?? '';
        $badgeCategory = $this->data['badge_category'] ?? 'Achievement';
        $rarity = $this->data['rarity'] ?? 'Common';
        
        return (new MailMessage)
            ->subject("🏅 New Badge Earned: {$badgeName}!")
            ->greeting("Badge earned, {$notifiable->name}!")
            ->line("You've earned a new badge for your achievements in MyGrowNet!")
            ->line("**Badge Details:**")
            ->line("• **Badge:** {$badgeName}")
            ->line("• **Category:** {$badgeCategory}")
            ->line("• **Rarity:** {$rarity}")
            ->when($badgeDescription, fn($mail) => $mail->line("• **Description:** {$badgeDescription}"))
            ->line("**Badge Collection Growing!**")
            ->line("Badges showcase your various accomplishments and skills within the MyGrowNet community. Collect them all to show your expertise!")
            ->line("**Show Off Your Success:**")
            ->line("• Your badge is now displayed on your profile")
            ->line("• Share your achievement with your team")
            ->line("• Work towards earning more badges")
            ->action('View Your Badge Collection', url('/mygrownet/dashboard'))
            ->line('Keep collecting and achieving!')
            ->salutation('Badge team, The MyGrowNet Team');
    }

    /**
     * Leaderboard position notification email
     */
    protected function leaderboardPositionMail(object $notifiable): MailMessage
    {
        $position = $this->data['position'];
        $leaderboardType = $this->data['leaderboard_type'] ?? 'General';
        $period = $this->data['period'] ?? 'this month';
        $reward = $this->data['reward'] ?? '';
        $totalParticipants = $this->data['total_participants'] ?? 0;
        
        $positionText = match (true) {
            $position == 1 => '🥇 1st Place',
            $position == 2 => '🥈 2nd Place', 
            $position == 3 => '🥉 3rd Place',
            $position <= 10 => "🏆 {$position}th Place (Top 10)",
            default => "#{$position}"
        };
        
        return (new MailMessage)
            ->subject("🏆 Leaderboard Achievement: {$positionText}!")
            ->greeting("Incredible performance, {$notifiable->name}!")
            ->line("You've achieved an outstanding position on the MyGrowNet leaderboard!")
            ->line("**Leaderboard Achievement:**")
            ->line("• **Position:** {$positionText}")
            ->line("• **Leaderboard:** {$leaderboardType}")
            ->line("• **Period:** {$period}")
            ->when($totalParticipants > 0, fn($mail) => $mail->line("• **Out of:** {$totalParticipants} participants"))
            ->when($reward, fn($mail) => $mail->line("• **Reward:** {$reward}"))
            ->line("**Outstanding Performance!**")
            ->line("Your position on the leaderboard demonstrates your exceptional commitment and success. You're setting an example for the entire MyGrowNet community!")
            ->when($position <= 3, function ($mail) {
                return $mail->line("**Top 3 Recognition:**")
                    ->line("Being in the top 3 is an incredible achievement that showcases your leadership and dedication. Congratulations!");
            })
            ->line("**Keep Leading:**")
            ->line("• Maintain your momentum")
            ->line("• Inspire others with your success")
            ->line("• Aim for even higher achievements")
            ->action('View Full Leaderboard', url('/mygrownet/dashboard'))
            ->line('You\'re a true MyGrowNet champion!')
            ->salutation('With admiration, The MyGrowNet Team');
    }

    /**
     * Physical reward earned notification email
     */
    protected function physicalRewardEarnedMail(object $notifiable): MailMessage
    {
        $rewardType = $this->data['reward_type'];
        $rewardValue = number_format($this->data['reward_value'] ?? 0, 2);
        $qualifyingTier = $this->data['qualifying_tier'] ?? '';
        $maintenancePeriod = $this->data['maintenance_period'] ?? 12;
        $estimatedDelivery = $this->data['estimated_delivery'] ?? '2-4 weeks';
        
        return (new MailMessage)
            ->subject("🎁 Physical Reward Earned: {$rewardType}!")
            ->greeting("Incredible achievement, {$notifiable->name}!")
            ->line("You've earned a physical reward for your outstanding performance in MyGrowNet!")
            ->line("**Reward Details:**")
            ->line("• **Reward:** {$rewardType}")
            ->line("• **Estimated Value:** K{$rewardValue}")
            ->line("• **Qualifying Tier:** {$qualifyingTier}")
            ->line("• **Maintenance Period:** {$maintenancePeriod} months")
            ->line("• **Estimated Delivery:** {$estimatedDelivery}")
            ->line("**Important Information:**")
            ->line("• Your reward will be processed and delivered within the estimated timeframe")
            ->line("• Maintain your tier performance for {$maintenancePeriod} months for full ownership transfer")
            ->line("• You'll receive updates on delivery status")
            ->line("**Asset Management Opportunity:**")
            ->line("Once you receive your reward, you'll have opportunities to generate additional income through our asset management program.")
            ->line("**Next Steps:**")
            ->line("• We'll contact you for delivery details")
            ->line("• Continue maintaining your tier performance")
            ->line("• Explore income generation opportunities")
            ->action('View Reward Status', url('/mygrownet/dashboard'))
            ->line('Congratulations on this amazing achievement!')
            ->salutation('Celebrating with you, The MyGrowNet Team');
    }

    /**
     * Physical reward ready for collection/delivery
     */
    protected function physicalRewardReadyMail(object $notifiable): MailMessage
    {
        $rewardType = $this->data['reward_type'];
        $collectionLocation = $this->data['collection_location'] ?? 'MyGrowNet Office';
        $contactNumber = $this->data['contact_number'] ?? config('mygrownet.contact.phone');
        $deliveryOption = $this->data['delivery_option'] ?? 'collection';
        
        return (new MailMessage)
            ->subject("📦 Your {$rewardType} is Ready!")
            ->greeting("Great news, {$notifiable->name}!")
            ->line("Your MyGrowNet physical reward is ready for " . ($deliveryOption === 'delivery' ? 'delivery' : 'collection') . "!")
            ->line("**Reward Ready:**")
            ->line("• **Reward:** {$rewardType}")
            ->line("• **Status:** Ready for " . ($deliveryOption === 'delivery' ? 'delivery' : 'collection'))
            ->when($deliveryOption === 'collection', function ($mail) use ($collectionLocation, $contactNumber) {
                return $mail->line("• **Collection Location:** {$collectionLocation}")
                    ->line("• **Contact:** {$contactNumber}");
            })
            ->line("**Next Steps:**")
            ->when($deliveryOption === 'delivery', function ($mail) {
                return $mail->line("• Our delivery team will contact you within 24 hours")
                    ->line("• Ensure someone is available to receive the delivery")
                    ->line("• Have your ID ready for verification");
            })
            ->when($deliveryOption === 'collection', function ($mail) use ($contactNumber) {
                return $mail->line("• Call {$contactNumber} to schedule collection")
                    ->line("• Bring your ID for verification")
                    ->line("• Collection hours: Monday-Friday, 9 AM - 5 PM");
            })
            ->line("**Important Reminders:**")
            ->line("• Continue maintaining your tier performance")
            ->line("• Ask about asset management opportunities")
            ->line("• Share your success with your team!")
            ->action('View Reward Details', url('/mygrownet/dashboard'))
            ->line('Enjoy your well-earned reward!')
            ->salutation('Excited for you, The MyGrowNet Team');
    }

    /**
     * Asset ownership transferred notification
     */
    protected function assetOwnershipTransferredMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $assetValue = number_format($this->data['asset_value'] ?? 0, 2);
        $maintenancePeriodCompleted = $this->data['maintenance_period_completed'] ?? 12;
        $incomeOpportunities = $this->data['income_opportunities'] ?? [];
        
        return (new MailMessage)
            ->subject("🎉 Asset Ownership Transferred: {$assetType}")
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("You have successfully completed the maintenance period and full ownership of your {$assetType} has been transferred to you!")
            ->line("**Ownership Transfer Details:**")
            ->line("• **Asset:** {$assetType}")
            ->line("• **Value:** K{$assetValue}")
            ->line("• **Maintenance Period Completed:** {$maintenancePeriodCompleted} months")
            ->line("• **Status:** Full ownership transferred")
            ->line("**You Now Own This Asset!**")
            ->line("This is a significant milestone - you've demonstrated consistent performance and commitment, and now you have a valuable asset that's completely yours.")
            ->when(!empty($incomeOpportunities), function ($mail) use ($incomeOpportunities) {
                $mail->line("**Income Generation Opportunities:**");
                foreach ($incomeOpportunities as $opportunity) {
                    $mail->line("• {$opportunity}");
                }
                return $mail;
            })
            ->line("**Next Steps:**")
            ->line("• Explore income generation options for your asset")
            ->line("• Consider asset management services")
            ->line("• Continue building your MyGrowNet success")
            ->action('Explore Asset Management', url('/mygrownet/dashboard'))
            ->line('This is just the beginning of your wealth-building journey!')
            ->salutation('Proudly celebrating with you, The MyGrowNet Team');
    }

    /**
     * Raffle entry earned notification
     */
    protected function raffleEntryEarnedMail(object $notifiable): MailMessage
    {
        $raffleName = $this->data['raffle_name'];
        $entriesEarned = $this->data['entries_earned'] ?? 1;
        $totalEntries = $this->data['total_entries'] ?? 1;
        $drawDate = $this->data['draw_date'] ?? 'Soon';
        $prizes = $this->data['prizes'] ?? [];
        
        return (new MailMessage)
            ->subject("🎫 Raffle Entries Earned: {$raffleName}")
            ->greeting("Lucky you, {$notifiable->name}!")
            ->line("You've earned raffle entries for the {$raffleName}!")
            ->line("**Raffle Entry Details:**")
            ->line("• **Raffle:** {$raffleName}")
            ->line("• **New Entries:** {$entriesEarned}")
            ->line("• **Your Total Entries:** {$totalEntries}")
            ->line("• **Draw Date:** {$drawDate}")
            ->when(!empty($prizes), function ($mail) use ($prizes) {
                $mail->line("**Prizes Include:**");
                foreach ($prizes as $prize) {
                    $mail->line("• {$prize}");
                }
                return $mail;
            })
            ->line("**More Entries = Better Chances!**")
            ->line("Keep performing well to earn more entries and increase your chances of winning amazing prizes!")
            ->line("**How to Earn More Entries:**")
            ->line("• Maintain consistent referral activity")
            ->line("• Keep your subscription active")
            ->line("• Participate in community projects")
            ->action('View Raffle Details', url('/mygrownet/dashboard'))
            ->line('Good luck in the draw!')
            ->salutation('Fingers crossed for you, The MyGrowNet Team');
    }

    /**
     * Raffle winner notification
     */
    protected function raffleWinnerMail(object $notifiable): MailMessage
    {
        $raffleName = $this->data['raffle_name'];
        $prizeWon = $this->data['prize_won'];
        $prizeValue = $this->data['prize_value'] ?? '';
        $claimInstructions = $this->data['claim_instructions'] ?? 'Contact support for claim details';
        
        return (new MailMessage)
            ->subject("🎉 WINNER! You Won: {$prizeWon}")
            ->greeting("CONGRATULATIONS, {$notifiable->name}!")
            ->line("🎊 YOU'RE A WINNER! 🎊")
            ->line("You've won a prize in the {$raffleName}!")
            ->line("**Your Winning Details:**")
            ->line("• **Raffle:** {$raffleName}")
            ->line("• **Prize Won:** {$prizeWon}")
            ->when($prizeValue, fn($mail) => $mail->line("• **Prize Value:** {$prizeValue}"))
            ->line("**AMAZING ACHIEVEMENT!**")
            ->line("Out of all the participants, you've been selected as a winner! This is your lucky day and a reward for your consistent participation in MyGrowNet.")
            ->line("**How to Claim Your Prize:**")
            ->line($claimInstructions)
            ->line("**Important:**")
            ->line("• Claim your prize within 30 days")
            ->line("• Bring valid ID for verification")
            ->line("• Share your winning story with your team!")
            ->action('Claim Your Prize', url('/mygrownet/dashboard'))
            ->line('Congratulations again on this fantastic win!')
            ->salutation('Celebrating your victory, The MyGrowNet Team');
    }

    /**
     * Recognition event notification
     */
    protected function recognitionEventMail(object $notifiable): MailMessage
    {
        $eventName = $this->data['event_name'];
        $recognitionType = $this->data['recognition_type'] ?? 'Achievement';
        $eventDate = $this->data['event_date'] ?? 'Soon';
        $eventLocation = $this->data['event_location'] ?? 'MyGrowNet Center';
        $specialGuests = $this->data['special_guests'] ?? [];
        
        return (new MailMessage)
            ->subject("🏆 You're Invited: {$eventName}")
            ->greeting("Special invitation for {$notifiable->name}!")
            ->line("You've been selected to attend our exclusive recognition event!")
            ->line("**Event Details:**")
            ->line("• **Event:** {$eventName}")
            ->line("• **Recognition:** {$recognitionType}")
            ->line("• **Date:** {$eventDate}")
            ->line("• **Location:** {$eventLocation}")
            ->when(!empty($specialGuests), function ($mail) use ($specialGuests) {
                $mail->line("**Special Guests:**");
                foreach ($specialGuests as $guest) {
                    $mail->line("• {$guest}");
                }
                return $mail;
            })
            ->line("**Why You're Invited:**")
            ->line("Your outstanding performance and achievements in MyGrowNet have earned you this special recognition. This event celebrates top performers like you!")
            ->line("**What to Expect:**")
            ->line("• Awards and recognition ceremony")
            ->line("• Networking with top performers")
            ->line("• Inspirational speeches and success stories")
            ->line("• Exclusive gifts and surprises")
            ->line("**RSVP Required:**")
            ->line("Please confirm your attendance as soon as possible. This is a limited-capacity event for our most successful members.")
            ->action('RSVP for Event', url('/mygrownet/dashboard'))
            ->line('We look forward to celebrating your success!')
            ->salutation('With honor and respect, The MyGrowNet Team');
    }

    /**
     * Default email template
     */
    protected function defaultMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('MyGrowNet Achievement Notification')
            ->line('You have received an achievement notification from MyGrowNet.')
            ->action('View Dashboard', url('/mygrownet/dashboard'));
    }

    /**
     * Get database message based on notification type
     */
    protected function getDatabaseMessage(): string
    {
        return match ($this->data['type']) {
            'tier_advancement' => $this->getTierAdvancementMessage(),
            'achievement_unlocked' => $this->getAchievementUnlockedMessage(),
            'milestone_reached' => $this->getMilestoneReachedMessage(),
            'badge_earned' => $this->getBadgeEarnedMessage(),
            'leaderboard_position' => $this->getLeaderboardPositionMessage(),
            'physical_reward_earned' => $this->getPhysicalRewardEarnedMessage(),
            'physical_reward_ready' => $this->getPhysicalRewardReadyMessage(),
            'asset_ownership_transferred' => $this->getAssetOwnershipTransferredMessage(),
            'raffle_entry_earned' => $this->getRaffleEntryEarnedMessage(),
            'raffle_winner' => $this->getRaffleWinnerMessage(),
            'recognition_event' => $this->getRecognitionEventMessage(),
            default => 'MyGrowNet achievement notification'
        };
    }

    /**
     * Get notification priority
     */
    protected function getNotificationPriority(): string
    {
        return match ($this->data['type']) {
            'tier_advancement', 'raffle_winner', 'asset_ownership_transferred' => 'high',
            'physical_reward_earned', 'physical_reward_ready', 'recognition_event' => 'medium',
            'achievement_unlocked', 'milestone_reached', 'badge_earned', 'leaderboard_position', 'raffle_entry_earned' => 'normal',
            default => 'normal'
        };
    }

    /**
     * Get tier advancement message
     */
    protected function getTierAdvancementMessage(): string
    {
        $newTier = $this->data['new_tier'];
        $achievementBonus = number_format($this->data['achievement_bonus'] ?? 0, 2);
        
        return "🎉 Tier Advanced to {$newTier}! Achievement bonus: K{$achievementBonus}";
    }

    /**
     * Get achievement unlocked message
     */
    protected function getAchievementUnlockedMessage(): string
    {
        $achievementName = $this->data['achievement_name'];
        
        return "🏆 Achievement Unlocked: {$achievementName}!";
    }

    /**
     * Get milestone reached message
     */
    protected function getMilestoneReachedMessage(): string
    {
        $milestoneName = $this->data['milestone_name'];
        
        return "🎯 Milestone Reached: {$milestoneName}!";
    }

    /**
     * Get badge earned message
     */
    protected function getBadgeEarnedMessage(): string
    {
        $badgeName = $this->data['badge_name'];
        
        return "🏅 New Badge Earned: {$badgeName}!";
    }

    /**
     * Get leaderboard position message
     */
    protected function getLeaderboardPositionMessage(): string
    {
        $position = $this->data['position'];
        $leaderboardType = $this->data['leaderboard_type'] ?? 'General';
        
        return "🏆 Leaderboard Position: #{$position} in {$leaderboardType}!";
    }

    /**
     * Get physical reward earned message
     */
    protected function getPhysicalRewardEarnedMessage(): string
    {
        $rewardType = $this->data['reward_type'];
        
        return "🎁 Physical Reward Earned: {$rewardType}!";
    }

    /**
     * Get physical reward ready message
     */
    protected function getPhysicalRewardReadyMessage(): string
    {
        $rewardType = $this->data['reward_type'];
        
        return "📦 Your {$rewardType} is ready for collection/delivery!";
    }

    /**
     * Get asset ownership transferred message
     */
    protected function getAssetOwnershipTransferredMessage(): string
    {
        $assetType = $this->data['asset_type'];
        
        return "🎉 Asset Ownership Transferred: {$assetType} is now fully yours!";
    }

    /**
     * Get raffle entry earned message
     */
    protected function getRaffleEntryEarnedMessage(): string
    {
        $raffleName = $this->data['raffle_name'];
        $entriesEarned = $this->data['entries_earned'] ?? 1;
        
        return "🎫 Raffle Entries Earned: {$entriesEarned} entries for {$raffleName}!";
    }

    /**
     * Get raffle winner message
     */
    protected function getRaffleWinnerMessage(): string
    {
        $prizeWon = $this->data['prize_won'];
        
        return "🎉 WINNER! You won: {$prizeWon}!";
    }

    /**
     * Get recognition event message
     */
    protected function getRecognitionEventMessage(): string
    {
        $eventName = $this->data['event_name'];
        
        return "🏆 You're invited to: {$eventName}!";
    }
}
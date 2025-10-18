<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MyGrowNetAssetNotification extends Notification implements ShouldQueue
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
            'asset_allocation_approved' => $this->assetAllocationApprovedMail($notifiable),
            'asset_allocation_pending' => $this->assetAllocationPendingMail($notifiable),
            'asset_delivery_scheduled' => $this->assetDeliveryScheduledMail($notifiable),
            'asset_delivered' => $this->assetDeliveredMail($notifiable),
            'asset_maintenance_reminder' => $this->assetMaintenanceReminderMail($notifiable),
            'asset_maintenance_warning' => $this->assetMaintenanceWarningMail($notifiable),
            'asset_maintenance_violation' => $this->assetMaintenanceViolationMail($notifiable),
            'asset_ownership_pending' => $this->assetOwnershipPendingMail($notifiable),
            'asset_ownership_completed' => $this->assetOwnershipCompletedMail($notifiable),
            'asset_income_report' => $this->assetIncomeReportMail($notifiable),
            'asset_valuation_update' => $this->assetValuationUpdateMail($notifiable),
            'asset_buyback_offer' => $this->assetBuybackOfferMail($notifiable),
            'asset_management_enrollment' => $this->assetManagementEnrollmentMail($notifiable),
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
     * Asset allocation approved notification
     */
    protected function assetAllocationApprovedMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $assetModel = $this->data['asset_model'] ?? '';
        $assetValue = number_format($this->data['asset_value'] ?? 0, 2);
        $qualifyingTier = $this->data['qualifying_tier'] ?? '';
        $maintenancePeriod = $this->data['maintenance_period'] ?? 12;
        $estimatedDelivery = $this->data['estimated_delivery'] ?? '2-4 weeks';
        
        return (new MailMessage)
            ->subject("🎉 Asset Allocation Approved: {$assetType}!")
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("Your asset allocation has been approved! You've qualified for a premium physical reward based on your outstanding performance in MyGrowNet.")
            ->line("**Asset Allocation Details:**")
            ->line("• **Asset Type:** {$assetType}")
            ->when($assetModel, fn($mail) => $mail->line("• **Model:** {$assetModel}"))
            ->line("• **Estimated Value:** K{$assetValue}")
            ->line("• **Qualifying Tier:** {$qualifyingTier}")
            ->line("• **Maintenance Period:** {$maintenancePeriod} months")
            ->line("• **Estimated Delivery:** {$estimatedDelivery}")
            ->line("**What This Means:**")
            ->line("• Your asset is being prepared for delivery")
            ->line("• You'll receive updates on delivery progress")
            ->line("• Maintain your tier performance for full ownership transfer")
            ->line("• Asset management opportunities will be available")
            ->line("**Next Steps:**")
            ->line("• Continue maintaining your tier performance")
            ->line("• We'll contact you for delivery arrangements")
            ->line("• Prepare for asset management enrollment")
            ->action('View Asset Status', url('/mygrownet/dashboard'))
            ->line('This is a significant milestone in your MyGrowNet journey!')
            ->salutation('Celebrating your success, The MyGrowNet Team');
    }

    /**
     * Asset allocation pending notification
     */
    protected function assetAllocationPendingMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $currentTier = $this->data['current_tier'] ?? '';
        $requiredTier = $this->data['required_tier'] ?? '';
        $currentVolume = number_format($this->data['current_volume'] ?? 0, 2);
        $requiredVolume = number_format($this->data['required_volume'] ?? 0, 2);
        $timeRemaining = $this->data['time_remaining'] ?? '';
        
        return (new MailMessage)
            ->subject("⏳ Asset Allocation Pending: {$assetType}")
            ->greeting("Almost there, {$notifiable->name}!")
            ->line("You're very close to qualifying for a {$assetType}! Your performance is impressive, and you're on track for this amazing reward.")
            ->line("**Current Progress:**")
            ->line("• **Target Asset:** {$assetType}")
            ->line("• **Current Tier:** {$currentTier}")
            ->line("• **Required Tier:** {$requiredTier}")
            ->line("• **Current Team Volume:** K{$currentVolume}")
            ->line("• **Required Team Volume:** K{$requiredVolume}")
            ->when($timeRemaining, fn($mail) => $mail->line("• **Time Remaining:** {$timeRemaining}"))
            ->line("**What You Need to Do:**")
            ->line("• Continue building your team and maintaining performance")
            ->line("• Focus on supporting your team members' success")
            ->line("• Keep your subscription active and tier status maintained")
            ->line("**You're So Close!**")
            ->line("Your dedication and hard work are paying off. This asset reward will be a tangible symbol of your success and provide ongoing value.")
            ->action('Track Your Progress', url('/mygrownet/dashboard'))
            ->line('Keep pushing forward - your asset reward awaits!')
            ->salutation('Cheering you on, The MyGrowNet Team');
    }

    /**
     * Asset delivery scheduled notification
     */
    protected function assetDeliveryScheduledMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $assetModel = $this->data['asset_model'] ?? '';
        $deliveryDate = $this->data['delivery_date'] ?? 'Soon';
        $deliveryTime = $this->data['delivery_time'] ?? '';
        $deliveryAddress = $this->data['delivery_address'] ?? '';
        $contactNumber = $this->data['contact_number'] ?? config('mygrownet.contact.phone');
        $deliveryInstructions = $this->data['delivery_instructions'] ?? [];
        
        return (new MailMessage)
            ->subject("📅 Asset Delivery Scheduled: {$assetType}")
            ->greeting("Exciting news, {$notifiable->name}!")
            ->line("Your {$assetType} delivery has been scheduled! Get ready to receive your well-earned reward.")
            ->line("**Delivery Details:**")
            ->line("• **Asset:** {$assetType}")
            ->when($assetModel, fn($mail) => $mail->line("• **Model:** {$assetModel}"))
            ->line("• **Delivery Date:** {$deliveryDate}")
            ->when($deliveryTime, fn($mail) => $mail->line("• **Delivery Time:** {$deliveryTime}"))
            ->when($deliveryAddress, fn($mail) => $mail->line("• **Delivery Address:** {$deliveryAddress}"))
            ->line("• **Contact Number:** {$contactNumber}")
            ->line("**Delivery Preparation:**")
            ->when(!empty($deliveryInstructions), function ($mail) use ($deliveryInstructions) {
                foreach ($deliveryInstructions as $instruction) {
                    $mail->line("• {$instruction}");
                }
                return $mail;
            })
            ->when(empty($deliveryInstructions), function ($mail) {
                return $mail->line("• Ensure someone is available to receive the delivery")
                    ->line("• Have your ID ready for verification")
                    ->line("• Clear space for asset placement/storage")
                    ->line("• Be available at the scheduled time");
            })
            ->line("**Important Notes:**")
            ->line("• Our delivery team will contact you 1 hour before arrival")
            ->line("• Asset maintenance period begins upon delivery")
            ->line("• You'll receive asset management information")
            ->action('View Delivery Details', url('/mygrownet/dashboard'))
            ->line('Your reward is almost in your hands!')
            ->salutation('Excited for you, The MyGrowNet Team');
    }

    /**
     * Asset delivered notification
     */
    protected function assetDeliveredMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $assetModel = $this->data['asset_model'] ?? '';
        $deliveryDate = $this->data['delivery_date'] ?? date('Y-m-d');
        $maintenancePeriod = $this->data['maintenance_period'] ?? 12;
        $maintenanceStartDate = $this->data['maintenance_start_date'] ?? date('Y-m-d');
        $assetManagementContact = $this->data['asset_management_contact'] ?? config('mygrownet.contact.phone');
        
        return (new MailMessage)
            ->subject("✅ Asset Delivered: {$assetType}")
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("Your {$assetType} has been successfully delivered! This is a major milestone in your MyGrowNet journey.")
            ->line("**Delivery Confirmation:**")
            ->line("• **Asset:** {$assetType}")
            ->when($assetModel, fn($mail) => $mail->line("• **Model:** {$assetModel}"))
            ->line("• **Delivered On:** {$deliveryDate}")
            ->line("• **Maintenance Period:** {$maintenancePeriod} months")
            ->line("• **Maintenance Starts:** {$maintenanceStartDate}")
            ->line("**Important: Maintenance Period**")
            ->line("Your asset maintenance period has officially begun. During this time:")
            ->line("• Continue maintaining your tier performance")
            ->line("• Keep your subscription active")
            ->line("• Meet monthly team volume requirements")
            ->line("• After {$maintenancePeriod} months, full ownership transfers to you")
            ->line("**Asset Management Opportunities:**")
            ->line("• Explore income generation options")
            ->line("• Consider asset management program enrollment")
            ->line("• Contact us at {$assetManagementContact} for guidance")
            ->line("**Next Steps:**")
            ->line("• Enjoy your new asset responsibly")
            ->line("• Continue building your MyGrowNet success")
            ->line("• Track your maintenance progress on the dashboard")
            ->action('View Asset Management', url('/mygrownet/dashboard'))
            ->line('Enjoy your well-earned reward!')
            ->salutation('Proudly celebrating with you, The MyGrowNet Team');
    }

    /**
     * Asset maintenance reminder notification
     */
    protected function assetMaintenanceReminderMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $maintenanceMonth = $this->data['maintenance_month'] ?? 1;
        $totalMonths = $this->data['total_months'] ?? 12;
        $currentTier = $this->data['current_tier'] ?? '';
        $requiredTier = $this->data['required_tier'] ?? '';
        $currentVolume = number_format($this->data['current_volume'] ?? 0, 2);
        $requiredVolume = number_format($this->data['required_volume'] ?? 0, 2);
        $monthsRemaining = $totalMonths - $maintenanceMonth;
        
        return (new MailMessage)
            ->subject("📋 Asset Maintenance Reminder: {$assetType}")
            ->greeting("Maintenance check-in, {$notifiable->name}!")
            ->line("This is a friendly reminder about your {$assetType} maintenance requirements. You're doing great - keep it up!")
            ->line("**Maintenance Progress:**")
            ->line("• **Asset:** {$assetType}")
            ->line("• **Month:** {$maintenanceMonth} of {$totalMonths}")
            ->line("• **Months Remaining:** {$monthsRemaining}")
            ->line("• **Progress:** " . round(($maintenanceMonth / $totalMonths) * 100, 1) . "% complete")
            ->line("**Current Performance:**")
            ->line("• **Current Tier:** {$currentTier}")
            ->line("• **Required Tier:** {$requiredTier}")
            ->line("• **Current Team Volume:** K{$currentVolume}")
            ->line("• **Required Team Volume:** K{$requiredVolume}")
            ->line("**Maintenance Requirements:**")
            ->line("• Maintain {$requiredTier} tier status")
            ->line("• Keep monthly team volume above K{$requiredVolume}")
            ->line("• Keep your subscription active")
            ->line("• Continue supporting your team")
            ->line("**You're On Track!**")
            ->line("Your consistent performance shows your commitment to excellence. Keep up the great work and this asset will be fully yours soon!")
            ->action('View Maintenance Status', url('/mygrownet/dashboard'))
            ->line('Stay strong - ownership is getting closer!')
            ->salutation('Supporting your success, The MyGrowNet Team');
    }

    /**
     * Asset maintenance warning notification
     */
    protected function assetMaintenanceWarningMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $warningType = $this->data['warning_type'] ?? 'Performance Below Requirements';
        $currentTier = $this->data['current_tier'] ?? '';
        $requiredTier = $this->data['required_tier'] ?? '';
        $currentVolume = number_format($this->data['current_volume'] ?? 0, 2);
        $requiredVolume = number_format($this->data['required_volume'] ?? 0, 2);
        $gracePeriod = $this->data['grace_period'] ?? '30 days';
        $supportContact = $this->data['support_contact'] ?? config('mygrownet.contact.phone');
        
        return (new MailMessage)
            ->subject("⚠️ Asset Maintenance Warning: {$assetType}")
            ->greeting("Important notice, {$notifiable->name}")
            ->line("We've noticed that your {$assetType} maintenance requirements need attention. Don't worry - we're here to help you get back on track!")
            ->line("**Warning Details:**")
            ->line("• **Asset:** {$assetType}")
            ->line("• **Issue:** {$warningType}")
            ->line("• **Grace Period:** {$gracePeriod}")
            ->line("**Current Status:**")
            ->line("• **Current Tier:** {$currentTier}")
            ->line("• **Required Tier:** {$requiredTier}")
            ->line("• **Current Team Volume:** K{$currentVolume}")
            ->line("• **Required Team Volume:** K{$requiredVolume}")
            ->line("**What You Need to Do:**")
            ->line("• Focus on rebuilding your team volume")
            ->line("• Ensure your subscription is active")
            ->line("• Reach out to your upline for support")
            ->line("• Contact our support team for guidance")
            ->line("**We're Here to Help:**")
            ->line("This is just a temporary setback. Many successful members have faced similar challenges and overcome them. You have {$gracePeriod} to get back on track.")
            ->line("**Support Available:**")
            ->line("• Call our support team at {$supportContact}")
            ->line("• Schedule a mentorship session")
            ->line("• Access our performance improvement resources")
            ->action('Get Support Now', url('/mygrownet/dashboard'))
            ->line('We believe in your ability to overcome this challenge!')
            ->salutation('Here to support you, The MyGrowNet Team');
    }

    /**
     * Asset maintenance violation notification
     */
    protected function assetMaintenanceViolationMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $violationType = $this->data['violation_type'] ?? 'Maintenance Requirements Not Met';
        $violationDate = $this->data['violation_date'] ?? date('Y-m-d');
        $paymentPlanAvailable = $this->data['payment_plan_available'] ?? true;
        $assetRecoveryDate = $this->data['asset_recovery_date'] ?? '';
        $paymentPlanAmount = number_format($this->data['payment_plan_amount'] ?? 0, 2);
        $supportContact = $this->data['support_contact'] ?? config('mygrownet.contact.phone');
        
        return (new MailMessage)
            ->subject("🚨 Asset Maintenance Violation: {$assetType}")
            ->greeting("Urgent notice, {$notifiable->name}")
            ->line("We regret to inform you that your {$assetType} maintenance requirements have not been met within the grace period.")
            ->line("**Violation Details:**")
            ->line("• **Asset:** {$assetType}")
            ->line("• **Violation:** {$violationType}")
            ->line("• **Date:** {$violationDate}")
            ->when($assetRecoveryDate, fn($mail) => $mail->line("• **Recovery Date:** {$assetRecoveryDate}"))
            ->line("**Available Options:**")
            ->when($paymentPlanAvailable, function ($mail) use ($paymentPlanAmount) {
                return $mail->line("**Option 1: Payment Plan**")
                    ->line("• Monthly payment: K{$paymentPlanAmount}")
                    ->line("• Retain asset ownership")
                    ->line("• Flexible payment terms available");
            })
            ->line("**Option 2: Asset Recovery**")
            ->line("• Asset will be recovered by MyGrowNet")
            ->line("• You'll receive credit for payments made")
            ->line("• Opportunity to re-qualify in the future")
            ->line("**Important:**")
            ->line("This situation doesn't affect your MyGrowNet membership or other benefits. You can continue building your network and working towards future asset rewards.")
            ->line("**Next Steps:**")
            ->line("• Contact our support team immediately at {$supportContact}")
            ->line("• Discuss your preferred option")
            ->line("• Set up payment plan if desired")
            ->line("**We're Still Here for You:**")
            ->line("This setback doesn't define your MyGrowNet journey. Many successful members have faced challenges and come back stronger.")
            ->action('Contact Support Now', url('/support'))
            ->line('Let\'s work together to find the best solution.')
            ->salutation('Committed to your success, The MyGrowNet Team');
    }

    /**
     * Asset ownership pending notification
     */
    protected function assetOwnershipPendingMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $maintenancePeriod = $this->data['maintenance_period'] ?? 12;
        $completionDate = $this->data['completion_date'] ?? '';
        $ownershipTransferDate = $this->data['ownership_transfer_date'] ?? '';
        $finalRequirements = $this->data['final_requirements'] ?? [];
        
        return (new MailMessage)
            ->subject("🎯 Asset Ownership Transfer Pending: {$assetType}")
            ->greeting("Almost there, {$notifiable->name}!")
            ->line("Congratulations! You've successfully completed your {$maintenancePeriod}-month maintenance period for your {$assetType}. Ownership transfer is now being processed!")
            ->line("**Ownership Transfer Details:**")
            ->line("• **Asset:** {$assetType}")
            ->line("• **Maintenance Period:** {$maintenancePeriod} months completed")
            ->when($completionDate, fn($mail) => $mail->line("• **Completion Date:** {$completionDate}"))
            ->when($ownershipTransferDate, fn($mail) => $mail->line("• **Transfer Date:** {$ownershipTransferDate}"))
            ->line("**What This Means:**")
            ->line("• You've met all maintenance requirements")
            ->line("• Full ownership will be transferred to you")
            ->line("• The asset becomes completely yours")
            ->line("• Income generation opportunities become available")
            ->when(!empty($finalRequirements), function ($mail) use ($finalRequirements) {
                $mail->line("**Final Requirements:**");
                foreach ($finalRequirements as $requirement) {
                    $mail->line("• {$requirement}");
                }
                return $mail;
            })
            ->line("**Incredible Achievement!**")
            ->line("This is a major milestone that demonstrates your commitment, consistency, and success in MyGrowNet. You should be very proud!")
            ->line("**Next Steps:**")
            ->line("• Complete any final documentation")
            ->line("• Prepare for asset management enrollment")
            ->line("• Explore income generation opportunities")
            ->action('Complete Ownership Transfer', url('/mygrownet/dashboard'))
            ->line('You\'ve earned this incredible achievement!')
            ->salutation('Celebrating your success, The MyGrowNet Team');
    }

    /**
     * Asset ownership completed notification
     */
    protected function assetOwnershipCompletedMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $assetValue = number_format($this->data['asset_value'] ?? 0, 2);
        $transferDate = $this->data['transfer_date'] ?? date('Y-m-d');
        $incomeOpportunities = $this->data['income_opportunities'] ?? [];
        $assetManagementContact = $this->data['asset_management_contact'] ?? config('mygrownet.contact.phone');
        
        return (new MailMessage)
            ->subject("🎉 Asset Ownership Completed: {$assetType}")
            ->greeting("CONGRATULATIONS, {$notifiable->name}!")
            ->line("🎊 IT'S OFFICIAL! 🎊")
            ->line("Full ownership of your {$assetType} has been transferred to you! This asset, worth K{$assetValue}, is now completely yours.")
            ->line("**Ownership Details:**")
            ->line("• **Asset:** {$assetType}")
            ->line("• **Value:** K{$assetValue}")
            ->line("• **Transfer Date:** {$transferDate}")
            ->line("• **Status:** Full ownership transferred")
            ->line("**What This Achievement Means:**")
            ->line("• You've demonstrated exceptional commitment and performance")
            ->line("• You now own a valuable asset that can generate income")
            ->line("• This is tangible proof of your MyGrowNet success")
            ->line("• You're building real wealth, not just earning commissions")
            ->when(!empty($incomeOpportunities), function ($mail) use ($incomeOpportunities) {
                $mail->line("**Income Generation Opportunities:**");
                foreach ($incomeOpportunities as $opportunity) {
                    $mail->line("• {$opportunity}");
                }
                return $mail;
            })
            ->line("**Asset Management Services:**")
            ->line("• Professional asset management available")
            ->line("• Income optimization strategies")
            ->line("• Asset appreciation tracking")
            ->line("• Contact: {$assetManagementContact}")
            ->line("**This Is Just the Beginning!**")
            ->line("This asset ownership is proof that MyGrowNet creates real wealth. Continue building your network and you could earn even more valuable assets!")
            ->action('Explore Asset Management', url('/mygrownet/dashboard'))
            ->line('You\'ve achieved something truly remarkable!')
            ->salutation('Immensely proud of you, The MyGrowNet Team');
    }

    /**
     * Asset income report notification
     */
    protected function assetIncomeReportMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $reportPeriod = $this->data['report_period'] ?? 'Monthly';
        $incomeGenerated = number_format($this->data['income_generated'] ?? 0, 2);
        $totalIncome = number_format($this->data['total_income'] ?? 0, 2);
        $incomeBreakdown = $this->data['income_breakdown'] ?? [];
        $nextPaymentDate = $this->data['next_payment_date'] ?? '';
        
        return (new MailMessage)
            ->subject("💰 Asset Income Report: {$assetType}")
            ->greeting("Income update, {$notifiable->name}!")
            ->line("Here's your {$reportPeriod} income report for your {$assetType}. Your asset is working for you!")
            ->line("**Income Summary:**")
            ->line("• **Asset:** {$assetType}")
            ->line("• **Period:** {$reportPeriod}")
            ->line("• **Income Generated:** K{$incomeGenerated}")
            ->line("• **Total Income to Date:** K{$totalIncome}")
            ->when($nextPaymentDate, fn($mail) => $mail->line("• **Next Payment:** {$nextPaymentDate}"))
            ->when(!empty($incomeBreakdown), function ($mail) use ($incomeBreakdown) {
                $mail->line("**Income Breakdown:**");
                foreach ($incomeBreakdown as $source => $amount) {
                    $mail->line("• {$source}: K" . number_format($amount, 2));
                }
                return $mail;
            })
            ->line("**Asset Performance:**")
            ->line("Your asset is generating consistent income, proving the value of your MyGrowNet achievement. This passive income stream is a testament to your hard work and dedication.")
            ->line("**Optimization Opportunities:**")
            ->line("• Consider asset management program enrollment")
            ->line("• Explore additional income generation strategies")
            ->line("• Reinvest income for compound growth")
            ->line("**Keep Building:**")
            ->line("While your asset generates income, continue building your MyGrowNet network to qualify for additional assets and increase your wealth portfolio.")
            ->action('View Detailed Report', url('/mygrownet/dashboard'))
            ->line('Your wealth is growing every month!')
            ->salutation('Celebrating your success, The MyGrowNet Team');
    }

    /**
     * Asset valuation update notification
     */
    protected function assetValuationUpdateMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $previousValue = number_format($this->data['previous_value'] ?? 0, 2);
        $currentValue = number_format($this->data['current_value'] ?? 0, 2);
        $changeAmount = $this->data['current_value'] - $this->data['previous_value'];
        $changePercentage = $this->data['previous_value'] > 0 ? 
            round(($changeAmount / $this->data['previous_value']) * 100, 2) : 0;
        $valuationDate = $this->data['valuation_date'] ?? date('Y-m-d');
        $nextValuation = $this->data['next_valuation'] ?? 'Next year';
        
        $isAppreciation = $changeAmount > 0;
        $changeText = $isAppreciation ? 'increased' : 'decreased';
        $changeIcon = $isAppreciation ? '📈' : '📉';
        
        return (new MailMessage)
            ->subject("{$changeIcon} Asset Valuation Update: {$assetType}")
            ->greeting("Valuation update, {$notifiable->name}!")
            ->line("We've completed the annual valuation of your {$assetType}. Here are the results:")
            ->line("**Valuation Details:**")
            ->line("• **Asset:** {$assetType}")
            ->line("• **Previous Value:** K{$previousValue}")
            ->line("• **Current Value:** K{$currentValue}")
            ->line("• **Change:** K" . number_format(abs($changeAmount), 2) . " ({$changePercentage}%)")
            ->line("• **Valuation Date:** {$valuationDate}")
            ->line("• **Next Valuation:** {$nextValuation}")
            ->when($isAppreciation, function ($mail) use ($changeAmount) {
                return $mail->line("**Great News! 📈**")
                    ->line("Your asset has appreciated in value by K" . number_format($changeAmount, 2) . "! This appreciation adds to your overall wealth and demonstrates the value of MyGrowNet's asset reward program.");
            })
            ->when(!$isAppreciation, function ($mail) use ($changeAmount) {
                return $mail->line("**Market Update 📉**")
                    ->line("Your asset value has decreased by K" . number_format(abs($changeAmount), 2) . " due to market conditions. This is normal market fluctuation and doesn't affect your ownership or income generation potential.");
            })
            ->line("**What This Means:**")
            ->line("• Your asset ownership remains unchanged")
            ->line("• Income generation opportunities continue")
            ->line("• Market fluctuations are normal and expected")
            ->line("• Long-term asset ownership builds wealth")
            ->when($isAppreciation, function ($mail) {
                return $mail->line("**Consider Your Options:**")
                    ->line("• Continue holding for potential further appreciation")
                    ->line("• Explore our buyback program if interested")
                    ->line("• Maximize income generation opportunities");
            })
            ->action('View Asset Portfolio', url('/mygrownet/dashboard'))
            ->line('Your asset portfolio is part of your wealth-building journey!')
            ->salutation('Tracking your wealth, The MyGrowNet Team');
    }

    /**
     * Asset buyback offer notification
     */
    protected function assetBuybackOfferMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $currentValue = number_format($this->data['current_value'] ?? 0, 2);
        $buybackOffer = number_format($this->data['buyback_offer'] ?? 0, 2);
        $offerPercentage = $this->data['current_value'] > 0 ? 
            round(($this->data['buyback_offer'] / $this->data['current_value']) * 100, 1) : 0;
        $offerExpiry = $this->data['offer_expiry'] ?? '30 days';
        $reasonForOffer = $this->data['reason_for_offer'] ?? 'Market opportunity';
        
        return (new MailMessage)
            ->subject("💼 Asset Buyback Offer: {$assetType}")
            ->greeting("Special offer, {$notifiable->name}!")
            ->line("We have a buyback offer for your {$assetType}. This is an optional opportunity - the choice is entirely yours.")
            ->line("**Buyback Offer Details:**")
            ->line("• **Asset:** {$assetType}")
            ->line("• **Current Market Value:** K{$currentValue}")
            ->line("• **Our Offer:** K{$buybackOffer} ({$offerPercentage}% of market value)")
            ->line("• **Reason:** {$reasonForOffer}")
            ->line("• **Offer Valid Until:** {$offerExpiry}")
            ->line("**Why This Offer?**")
            ->line("We occasionally make buyback offers to provide members with liquidity options or when we have specific market opportunities.")
            ->line("**Your Options:**")
            ->line("**Option 1: Accept the Buyback**")
            ->line("• Receive K{$buybackOffer} immediately")
            ->line("• Maintain eligibility for future asset rewards")
            ->line("• Continue building your MyGrowNet network")
            ->line("**Option 2: Keep Your Asset**")
            ->line("• Continue generating income from your asset")
            ->line("• Benefit from potential future appreciation")
            ->line("• Maintain full ownership and control")
            ->line("**No Pressure Decision:**")
            ->line("This is purely optional. Many members choose to keep their assets for long-term wealth building. The decision should align with your personal financial goals.")
            ->line("**Need Advice?**")
            ->line("Our asset management team can help you evaluate this offer based on your specific situation and goals.")
            ->action('Review Offer Details', url('/mygrownet/dashboard'))
            ->line('Whatever you decide, we support your choice!')
            ->salutation('Respecting your decision, The MyGrowNet Team');
    }

    /**
     * Asset management enrollment notification
     */
    protected function assetManagementEnrollmentMail(object $notifiable): MailMessage
    {
        $assetType = $this->data['asset_type'];
        $managementServices = $this->data['management_services'] ?? [];
        $expectedIncome = number_format($this->data['expected_income'] ?? 0, 2);
        $managementFee = $this->data['management_fee'] ?? '10%';
        $enrollmentDeadline = $this->data['enrollment_deadline'] ?? '30 days';
        $contactPerson = $this->data['contact_person'] ?? 'Asset Management Team';
        $contactNumber = $this->data['contact_number'] ?? config('mygrownet.contact.phone');
        
        return (new MailMessage)
            ->subject("🏢 Asset Management Program: {$assetType}")
            ->greeting("Opportunity awaits, {$notifiable->name}!")
            ->line("Congratulations on your {$assetType} ownership! We're excited to offer you enrollment in our professional Asset Management Program.")
            ->line("**Program Overview:**")
            ->line("Our Asset Management Program helps you maximize income from your asset while minimizing the time and effort required on your part.")
            ->line("**Available Services:**")
            ->when(!empty($managementServices), function ($mail) use ($managementServices) {
                foreach ($managementServices as $service) {
                    $mail->line("• {$service}");
                }
                return $mail;
            })
            ->when(empty($managementServices), function ($mail) {
                return $mail->line("• Professional asset maintenance and care")
                    ->line("• Income optimization strategies")
                    ->line("• Tenant/client management (if applicable)")
                    ->line("• Regular performance reporting")
                    ->line("• Market analysis and recommendations");
            })
            ->line("**Financial Projections:**")
            ->line("• **Expected Monthly Income:** K{$expectedIncome}")
            ->line("• **Management Fee:** {$managementFee} of gross income")
            ->line("• **Your Net Income:** Approximately K" . number_format($this->data['expected_income'] * 0.9, 2) . "/month")
            ->line("**Program Benefits:**")
            ->line("• Passive income with minimal effort")
            ->line("• Professional asset management")
            ->line("• Regular income reporting")
            ->line("• Asset appreciation monitoring")
            ->line("• Full transparency and control")
            ->line("**Enrollment Details:**")
            ->line("• **Enrollment Deadline:** {$enrollmentDeadline}")
            ->line("• **Contact Person:** {$contactPerson}")
            ->line("• **Phone:** {$contactNumber}")
            ->line("**Next Steps:**")
            ->line("If you're interested, contact our team to discuss the program in detail and complete enrollment.")
            ->action('Learn More', url('/mygrownet/dashboard'))
            ->line('Maximize your asset\'s potential!')
            ->salutation('Ready to serve you, The MyGrowNet Team');
    }

    /**
     * Default email template
     */
    protected function defaultMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('MyGrowNet Asset Notification')
            ->line('You have received an asset notification from MyGrowNet.')
            ->action('View Dashboard', url('/mygrownet/dashboard'));
    }

    /**
     * Get database message based on notification type
     */
    protected function getDatabaseMessage(): string
    {
        return match ($this->data['type']) {
            'asset_allocation_approved' => $this->getAssetAllocationApprovedMessage(),
            'asset_allocation_pending' => $this->getAssetAllocationPendingMessage(),
            'asset_delivery_scheduled' => $this->getAssetDeliveryScheduledMessage(),
            'asset_delivered' => $this->getAssetDeliveredMessage(),
            'asset_maintenance_reminder' => $this->getAssetMaintenanceReminderMessage(),
            'asset_maintenance_warning' => $this->getAssetMaintenanceWarningMessage(),
            'asset_maintenance_violation' => $this->getAssetMaintenanceViolationMessage(),
            'asset_ownership_pending' => $this->getAssetOwnershipPendingMessage(),
            'asset_ownership_completed' => $this->getAssetOwnershipCompletedMessage(),
            'asset_income_report' => $this->getAssetIncomeReportMessage(),
            'asset_valuation_update' => $this->getAssetValuationUpdateMessage(),
            'asset_buyback_offer' => $this->getAssetBuybackOfferMessage(),
            'asset_management_enrollment' => $this->getAssetManagementEnrollmentMessage(),
            default => 'MyGrowNet asset notification'
        };
    }

    /**
     * Get notification priority
     */
    protected function getNotificationPriority(): string
    {
        return match ($this->data['type']) {
            'asset_maintenance_violation', 'asset_ownership_completed' => 'high',
            'asset_allocation_approved', 'asset_delivery_scheduled', 'asset_delivered', 'asset_maintenance_warning', 'asset_ownership_pending', 'asset_buyback_offer' => 'medium',
            'asset_allocation_pending', 'asset_maintenance_reminder', 'asset_income_report', 'asset_valuation_update', 'asset_management_enrollment' => 'normal',
            default => 'normal'
        };
    }

    // Database message methods
    protected function getAssetAllocationApprovedMessage(): string
    {
        $assetType = $this->data['asset_type'];
        return "🎉 Asset allocation approved: {$assetType}! Delivery being arranged.";
    }

    protected function getAssetAllocationPendingMessage(): string
    {
        $assetType = $this->data['asset_type'];
        return "⏳ Almost qualified for {$assetType}! Keep building your performance.";
    }

    protected function getAssetDeliveryScheduledMessage(): string
    {
        $assetType = $this->data['asset_type'];
        $deliveryDate = $this->data['delivery_date'] ?? 'Soon';
        return "📅 {$assetType} delivery scheduled for {$deliveryDate}.";
    }

    protected function getAssetDeliveredMessage(): string
    {
        $assetType = $this->data['asset_type'];
        return "✅ {$assetType} delivered! Maintenance period has begun.";
    }

    protected function getAssetMaintenanceReminderMessage(): string
    {
        $assetType = $this->data['asset_type'];
        $maintenanceMonth = $this->data['maintenance_month'] ?? 1;
        return "📋 {$assetType} maintenance reminder - Month {$maintenanceMonth}. Keep up the great work!";
    }

    protected function getAssetMaintenanceWarningMessage(): string
    {
        $assetType = $this->data['asset_type'];
        return "⚠️ {$assetType} maintenance warning. Performance needs attention.";
    }

    protected function getAssetMaintenanceViolationMessage(): string
    {
        $assetType = $this->data['asset_type'];
        return "🚨 {$assetType} maintenance violation. Contact support for options.";
    }

    protected function getAssetOwnershipPendingMessage(): string
    {
        $assetType = $this->data['asset_type'];
        return "🎯 {$assetType} ownership transfer pending! Maintenance period completed.";
    }

    protected function getAssetOwnershipCompletedMessage(): string
    {
        $assetType = $this->data['asset_type'];
        return "🎉 {$assetType} ownership completed! The asset is now fully yours.";
    }

    protected function getAssetIncomeReportMessage(): string
    {
        $assetType = $this->data['asset_type'];
        $incomeGenerated = number_format($this->data['income_generated'] ?? 0, 2);
        return "💰 {$assetType} income report: K{$incomeGenerated} generated this period.";
    }

    protected function getAssetValuationUpdateMessage(): string
    {
        $assetType = $this->data['asset_type'];
        $currentValue = number_format($this->data['current_value'] ?? 0, 2);
        return "📈 {$assetType} valuation updated: Current value K{$currentValue}.";
    }

    protected function getAssetBuybackOfferMessage(): string
    {
        $assetType = $this->data['asset_type'];
        $buybackOffer = number_format($this->data['buyback_offer'] ?? 0, 2);
        return "💼 {$assetType} buyback offer: K{$buybackOffer}. Review and decide.";
    }

    protected function getAssetManagementEnrollmentMessage(): string
    {
        $assetType = $this->data['asset_type'];
        return "🏢 {$assetType} asset management program available. Maximize your income!";
    }
}
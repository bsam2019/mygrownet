<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TierUpgradeNotification extends Notification implements ShouldQueue
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return match ($this->data['type']) {
            'tier_upgraded' => $this->tierUpgradedMail($notifiable),
            'batch_upgrade_complete' => $this->batchUpgradeCompleteMail($notifiable),
            'benefit_recalculation_complete' => $this->benefitRecalculationCompleteMail($notifiable),
            'processing_failure' => $this->processingFailureMail($notifiable),
            'critical_failure' => $this->criticalFailureMail($notifiable),
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
            'data' => $this->data
        ];
    }

    /**
     * Tier upgraded email
     */
    protected function tierUpgradedMail(object $notifiable): MailMessage
    {
        $fromTier = $this->data['from_tier'];
        $toTier = $this->data['to_tier'];
        $totalInvestment = number_format($this->data['total_investment'], 2);
        
        return (new MailMessage)
            ->subject('🎉 MyGrowNet Tier Upgrade - Congratulations!')
            ->greeting("Congratulations {$notifiable->name}!")
            ->line("Excellent news! Your investment tier has been automatically upgraded.")
            ->line("**Upgrade Details:**")
            ->line("• Previous Tier: {$fromTier}")
            ->line("• New Tier: **{$toTier}**")
            ->line("• Total Investment: K{$totalInvestment}")
            ->line("**Your New Benefits Include:**")
            ->line("• Higher profit share percentage")
            ->line("• Increased referral commission rates")
            ->line("• Enhanced matrix position benefits")
            ->line("• Access to exclusive tier features")
            ->line("Your new tier benefits are now active and will apply to all future earnings calculations.")
            ->action('View Your Dashboard', url('/dashboard'))
            ->line('Thank you for your continued investment in MyGrowNet!')
            ->salutation('Best regards, The MyGrowNet Team');
    }

    /**
     * Batch upgrade complete email (for admins)
     */
    protected function batchUpgradeCompleteMail(object $notifiable): MailMessage
    {
        $processedCount = $this->data['processed_count'];
        $upgradedCount = $this->data['upgraded_count'];
        
        return (new MailMessage)
            ->subject('MyGrowNet Batch Tier Upgrade Processing Complete')
            ->greeting("Hello {$notifiable->name}!")
            ->line("The batch tier upgrade processing has been completed successfully.")
            ->line("**Processing Summary:**")
            ->line("• Users Processed: {$processedCount}")
            ->line("• Tier Upgrades Completed: {$upgradedCount}")
            ->line("• Success Rate: " . ($processedCount > 0 ? round(($upgradedCount / $processedCount) * 100, 1) : 0) . "%")
            ->line("All upgraded users have been notified about their new tier status and benefits.")
            ->action('View Admin Panel', url('/admin/tier-upgrades'))
            ->salutation('VBIF System');
    }

    /**
     * Benefit recalculation complete email (for admins)
     */
    protected function benefitRecalculationCompleteMail(object $notifiable): MailMessage
    {
        $recalculatedCount = $this->data['recalculated_count'];
        
        return (new MailMessage)
            ->subject('VBIF Tier Benefit Recalculation Complete')
            ->greeting("Hello {$notifiable->name}!")
            ->line("The tier benefit recalculation process has been completed successfully.")
            ->line("**Recalculation Summary:**")
            ->line("• Users Processed: {$recalculatedCount}")
            ->line("All affected users now have updated benefit calculations based on their current tier status.")
            ->action('View Admin Panel', url('/admin/tier-benefits'))
            ->salutation('VBIF System');
    }

    /**
     * Processing failure email (for admins)
     */
    protected function processingFailureMail(object $notifiable): MailMessage
    {
        $jobType = ucwords(str_replace('_', ' ', $this->data['job_type']));
        $error = $this->data['error_message'];
        
        return (new MailMessage)
            ->subject("URGENT: VBIF {$jobType} Processing Failed")
            ->greeting("Hello {$notifiable->name}!")
            ->error()
            ->line("The {$jobType} processing has failed and requires attention.")
            ->line("**Error Details:**")
            ->line($error)
            ->line("**Job Information:**")
            ->line("• Job Type: {$jobType}")
            ->line("• User ID: " . ($this->data['user_id'] ?? 'N/A'))
            ->line("Please review the error and retry the processing if necessary.")
            ->action('View Admin Panel', url('/admin/jobs'))
            ->salutation('VBIF System Alert');
    }

    /**
     * Critical failure email (for admins)
     */
    protected function criticalFailureMail(object $notifiable): MailMessage
    {
        $jobType = ucwords(str_replace('_', ' ', $this->data['job_type']));
        $error = $this->data['error_message'];
        $attempts = $this->data['attempts'] ?? 'Unknown';
        
        return (new MailMessage)
            ->subject("CRITICAL: VBIF {$jobType} Processing Failed Permanently")
            ->greeting("Hello {$notifiable->name}!")
            ->error()
            ->line("CRITICAL ALERT: The {$jobType} processing has failed permanently after {$attempts} attempts.")
            ->line("**Error Details:**")
            ->line($error)
            ->line("**Job Information:**")
            ->line("• Job Type: {$jobType}")
            ->line("• User ID: " . ($this->data['user_id'] ?? 'N/A'))
            ->line("• Failed Attempts: {$attempts}")
            ->line("**IMMEDIATE ACTION REQUIRED:**")
            ->line("1. Review the error logs")
            ->line("2. Fix the underlying issue")
            ->line("3. Manually process the tier upgrades")
            ->line("4. Notify affected users if necessary")
            ->action('Emergency Admin Panel', url('/admin/jobs/emergency'))
            ->line('This is a critical system failure that requires immediate intervention.')
            ->salutation('VBIF Critical Alert System');
    }

    /**
     * Default email template
     */
    protected function defaultMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('VBIF Tier Upgrade Notification')
            ->line('You have received a tier upgrade notification.')
            ->action('View Dashboard', url('/dashboard'));
    }

    /**
     * Get database message based on notification type
     */
    protected function getDatabaseMessage(): string
    {
        return match ($this->data['type']) {
            'tier_upgraded' => $this->getTierUpgradedMessage(),
            'batch_upgrade_complete' => $this->getBatchUpgradeCompleteMessage(),
            'benefit_recalculation_complete' => $this->getBenefitRecalculationCompleteMessage(),
            'processing_failure' => $this->getProcessingFailureMessage(),
            'critical_failure' => $this->getCriticalFailureMessage(),
            default => 'Tier upgrade notification'
        };
    }

    /**
     * Get tier upgraded message
     */
    protected function getTierUpgradedMessage(): string
    {
        $fromTier = $this->data['from_tier'];
        $toTier = $this->data['to_tier'];
        $totalInvestment = number_format($this->data['total_investment'], 2);
        
        return "Congratulations! Your tier has been upgraded from {$fromTier} to {$toTier} (Total Investment: K{$totalInvestment}).";
    }

    /**
     * Get batch upgrade complete message
     */
    protected function getBatchUpgradeCompleteMessage(): string
    {
        $processedCount = $this->data['processed_count'];
        $upgradedCount = $this->data['upgraded_count'];
        
        return "Batch tier upgrade processing completed: {$upgradedCount} of {$processedCount} users upgraded.";
    }

    /**
     * Get benefit recalculation complete message
     */
    protected function getBenefitRecalculationCompleteMessage(): string
    {
        $recalculatedCount = $this->data['recalculated_count'];
        
        return "Tier benefit recalculation completed for {$recalculatedCount} users.";
    }

    /**
     * Get processing failure message
     */
    protected function getProcessingFailureMessage(): string
    {
        $jobType = ucwords(str_replace('_', ' ', $this->data['job_type']));
        
        return "{$jobType} processing failed: {$this->data['error_message']}";
    }

    /**
     * Get critical failure message
     */
    protected function getCriticalFailureMessage(): string
    {
        $jobType = ucwords(str_replace('_', ' ', $this->data['job_type']));
        $attempts = $this->data['attempts'] ?? 'multiple';
        
        return "CRITICAL: {$jobType} processing failed permanently after {$attempts} attempts. Immediate action required.";
    }
}
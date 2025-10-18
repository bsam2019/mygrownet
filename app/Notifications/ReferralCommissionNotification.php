<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralCommissionNotification extends Notification implements ShouldQueue
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
            'commission_earned' => $this->commissionEarnedMail($notifiable),
            'commission_clawback' => $this->commissionClawbackMail($notifiable),
            'batch_process_complete' => $this->batchProcessCompleteMail($notifiable),
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
     * Commission earned email
     */
    protected function commissionEarnedMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->data['amount'], 2);
        $level = $this->data['level'];
        
        return (new MailMessage)
            ->subject('VBIF Referral Commission Earned')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Great news! You've earned a referral commission.")
            ->line("**Commission Details:**")
            ->line("• Amount: K{$amount}")
            ->line("• Level: {$level}")
            ->line("• Status: Pending Payment")
            ->line("This commission will be processed and credited to your account within 1-2 business days.")
            ->action('View Dashboard', url('/dashboard'))
            ->line('Thank you for growing the VBIF community!')
            ->salutation('Best regards, The VBIF Team');
    }

    /**
     * Commission clawback email
     */
    protected function commissionClawbackMail(object $notifiable): MailMessage
    {
        $clawbackAmount = number_format($this->data['clawback_amount'], 2);
        $clawbackCount = $this->data['clawback_count'];
        
        return (new MailMessage)
            ->subject('VBIF Commission Adjustment Notice')
            ->greeting("Hello {$notifiable->name}!")
            ->line("We're writing to inform you about a commission adjustment on your account.")
            ->line("**Adjustment Details:**")
            ->line("• Adjustment Amount: K{$clawbackAmount}")
            ->line("• Number of Commissions Affected: {$clawbackCount}")
            ->line("• Reason: Early withdrawal by referred member")
            ->line("This adjustment is part of our policy to maintain system sustainability when members withdraw early.")
            ->line("If you have any questions about this adjustment, please contact our support team.")
            ->action('View Account Details', url('/dashboard'))
            ->salutation('Best regards, The VBIF Team');
    }

    /**
     * Batch process complete email (for admins)
     */
    protected function batchProcessCompleteMail(object $notifiable): MailMessage
    {
        $processedCount = $this->data['processed_count'];
        $totalAmount = number_format($this->data['total_amount'], 2);
        
        return (new MailMessage)
            ->subject('VBIF Commission Batch Processing Complete')
            ->greeting("Hello {$notifiable->name}!")
            ->line("The referral commission batch processing has been completed successfully.")
            ->line("**Processing Summary:**")
            ->line("• Commissions Processed: {$processedCount}")
            ->line("• Total Amount: K{$totalAmount}")
            ->line("All affected users have been notified about their commission payments.")
            ->action('View Admin Panel', url('/admin/commissions'))
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
            ->line("• Investment ID: " . ($this->data['investment_id'] ?? 'N/A'))
            ->line("• Withdrawal Request ID: " . ($this->data['withdrawal_request_id'] ?? 'N/A'))
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
            ->line("• Investment ID: " . ($this->data['investment_id'] ?? 'N/A'))
            ->line("• Withdrawal Request ID: " . ($this->data['withdrawal_request_id'] ?? 'N/A'))
            ->line("• Failed Attempts: {$attempts}")
            ->line("**IMMEDIATE ACTION REQUIRED:**")
            ->line("1. Review the error logs")
            ->line("2. Fix the underlying issue")
            ->line("3. Manually process the commissions")
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
            ->subject('VBIF Referral Commission Notification')
            ->line('You have received a referral commission notification.')
            ->action('View Dashboard', url('/dashboard'));
    }

    /**
     * Get database message based on notification type
     */
    protected function getDatabaseMessage(): string
    {
        return match ($this->data['type']) {
            'commission_earned' => $this->getCommissionEarnedMessage(),
            'commission_clawback' => $this->getCommissionClawbackMessage(),
            'batch_process_complete' => $this->getBatchProcessCompleteMessage(),
            'processing_failure' => $this->getProcessingFailureMessage(),
            'critical_failure' => $this->getCriticalFailureMessage(),
            default => 'Referral commission notification'
        };
    }

    /**
     * Get commission earned message
     */
    protected function getCommissionEarnedMessage(): string
    {
        $amount = number_format($this->data['amount'], 2);
        $level = $this->data['level'];
        
        return "You earned a Level {$level} referral commission of K{$amount}. Payment is pending.";
    }

    /**
     * Get commission clawback message
     */
    protected function getCommissionClawbackMessage(): string
    {
        $clawbackAmount = number_format($this->data['clawback_amount'], 2);
        
        return "Commission adjustment of K{$clawbackAmount} applied due to early withdrawal by referred member.";
    }

    /**
     * Get batch process complete message
     */
    protected function getBatchProcessCompleteMessage(): string
    {
        $processedCount = $this->data['processed_count'];
        $totalAmount = number_format($this->data['total_amount'], 2);
        
        return "Batch processing completed: {$processedCount} commissions totaling K{$totalAmount} processed.";
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
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ProfitDistributionNotification extends Notification implements ShouldQueue
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
            'user_distribution' => $this->userDistributionMail($notifiable),
            'admin_success' => $this->adminSuccessMail($notifiable),
            'admin_failure' => $this->adminFailureMail($notifiable),
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
            'distribution_type' => $this->data['distribution_type'],
            'message' => $this->getDatabaseMessage(),
            'data' => $this->data
        ];
    }

    /**
     * User distribution email
     */
    protected function userDistributionMail(object $notifiable): MailMessage
    {
        $distributionType = ucfirst($this->data['distribution_type']);
        $amount = number_format($this->data['amount'], 2);
        $date = $this->data['distribution_date']->format('F j, Y');

        return (new MailMessage)
            ->subject("VBIF {$distributionType} Profit Distribution")
            ->greeting("Hello {$notifiable->name}!")
            ->line("We're pleased to inform you that your {$distributionType} profit distribution has been processed.")
            ->line("**Distribution Details:**")
            ->line("• Amount: K{$amount}")
            ->line("• Distribution Date: {$date}")
            ->line("• Type: {$distributionType} Distribution")
            ->line("This amount will be credited to your account within 1-2 business days.")
            ->action('View Dashboard', url('/dashboard'))
            ->line('Thank you for being a valued member of VBIF!')
            ->salutation('Best regards, The VBIF Team');
    }

    /**
     * Admin success email
     */
    protected function adminSuccessMail(object $notifiable): MailMessage
    {
        $distributionType = ucfirst($this->data['distribution_type']);
        $totalDistributed = number_format($this->data['total_distributed'], 2);
        $userCount = $this->data['user_count'];
        $date = $this->data['distribution_date']->format('F j, Y');

        return (new MailMessage)
            ->subject("VBIF {$distributionType} Distribution Completed Successfully")
            ->greeting("Hello {$notifiable->name}!")
            ->line("The {$distributionType} profit distribution has been completed successfully.")
            ->line("**Distribution Summary:**")
            ->line("• Total Distributed: K{$totalDistributed}")
            ->line("• Users Benefited: {$userCount}")
            ->line("• Distribution Date: {$date}")
            ->line("• Distribution ID: {$this->data['distribution_id']}")
            ->action('View Distribution Details', url("/admin/distributions/{$this->data['distribution_id']}"))
            ->line('All users have been notified about their distributions.')
            ->salutation('VBIF System');
    }

    /**
     * Admin failure email
     */
    protected function adminFailureMail(object $notifiable): MailMessage
    {
        $distributionType = ucfirst($this->data['distribution_type']);
        $date = $this->data['distribution_date']->format('F j, Y');
        $error = $this->data['error_message'];

        return (new MailMessage)
            ->subject("URGENT: VBIF {$distributionType} Distribution Failed")
            ->greeting("Hello {$notifiable->name}!")
            ->error()
            ->line("The {$distributionType} profit distribution scheduled for {$date} has failed.")
            ->line("**Error Details:**")
            ->line($error)
            ->line("**Distribution Details:**")
            ->line("• Distribution Type: {$distributionType}")
            ->line("• Scheduled Date: {$date}")
            ->line("• Total Profit: K" . number_format($this->data['total_profit'], 2))
            ->line("Please review the error and retry the distribution process.")
            ->action('View Admin Panel', url('/admin/distributions'))
            ->line('This requires immediate attention to ensure users receive their distributions.')
            ->salutation('VBIF System Alert');
    }

    /**
     * Critical failure email
     */
    protected function criticalFailureMail(object $notifiable): MailMessage
    {
        $distributionType = ucfirst($this->data['distribution_type']);
        $date = $this->data['distribution_date']->format('F j, Y');
        $error = $this->data['error_message'];
        $attempts = $this->data['attempts'] ?? 'Unknown';

        return (new MailMessage)
            ->subject("CRITICAL: VBIF {$distributionType} Distribution Failed Permanently")
            ->greeting("Hello {$notifiable->name}!")
            ->error()
            ->line("CRITICAL ALERT: The {$distributionType} profit distribution has failed permanently after {$attempts} attempts.")
            ->line("**Error Details:**")
            ->line($error)
            ->line("**Distribution Details:**")
            ->line("• Distribution Type: {$distributionType}")
            ->line("• Scheduled Date: {$date}")
            ->line("• Total Profit: K" . number_format($this->data['total_profit'], 2))
            ->line("• Failed Attempts: {$attempts}")
            ->line("**IMMEDIATE ACTION REQUIRED:**")
            ->line("1. Review the error logs")
            ->line("2. Fix the underlying issue")
            ->line("3. Manually process the distribution")
            ->line("4. Notify affected users")
            ->action('Emergency Admin Panel', url('/admin/distributions/emergency'))
            ->line('This is a critical system failure that requires immediate intervention.')
            ->salutation('VBIF Critical Alert System');
    }

    /**
     * Default email template
     */
    protected function defaultMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('VBIF Profit Distribution Notification')
            ->line('You have received a profit distribution notification.')
            ->action('View Dashboard', url('/dashboard'));
    }

    /**
     * Get database message based on notification type
     */
    protected function getDatabaseMessage(): string
    {
        return match ($this->data['type']) {
            'user_distribution' => $this->getUserDistributionMessage(),
            'admin_success' => $this->getAdminSuccessMessage(),
            'admin_failure' => $this->getAdminFailureMessage(),
            'critical_failure' => $this->getCriticalFailureMessage(),
            default => 'Profit distribution notification'
        };
    }

    /**
     * Get user distribution message
     */
    protected function getUserDistributionMessage(): string
    {
        $distributionType = ucfirst($this->data['distribution_type']);
        $amount = number_format($this->data['amount'], 2);
        
        return "Your {$distributionType} profit distribution of K{$amount} has been processed and will be credited to your account.";
    }

    /**
     * Get admin success message
     */
    protected function getAdminSuccessMessage(): string
    {
        $distributionType = ucfirst($this->data['distribution_type']);
        $totalDistributed = number_format($this->data['total_distributed'], 2);
        $userCount = $this->data['user_count'];
        
        return "{$distributionType} distribution completed successfully. K{$totalDistributed} distributed to {$userCount} users.";
    }

    /**
     * Get admin failure message
     */
    protected function getAdminFailureMessage(): string
    {
        $distributionType = ucfirst($this->data['distribution_type']);
        
        return "{$distributionType} distribution failed: {$this->data['error_message']}";
    }

    /**
     * Get critical failure message
     */
    protected function getCriticalFailureMessage(): string
    {
        $distributionType = ucfirst($this->data['distribution_type']);
        $attempts = $this->data['attempts'] ?? 'multiple';
        
        return "CRITICAL: {$distributionType} distribution failed permanently after {$attempts} attempts. Immediate action required.";
    }
}
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MyGrowNetCommissionNotification extends Notification implements ShouldQueue
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
            'five_level_commission_earned' => $this->fiveLevelCommissionEarnedMail($notifiable),
            'team_volume_bonus_earned' => $this->teamVolumeBonusEarnedMail($notifiable),
            'performance_bonus_earned' => $this->performanceBonusEarnedMail($notifiable),
            'leadership_bonus_earned' => $this->leadershipBonusEarnedMail($notifiable),
            'commission_payment_processed' => $this->commissionPaymentProcessedMail($notifiable),
            'commission_payment_failed' => $this->commissionPaymentFailedMail($notifiable),
            'monthly_commission_summary' => $this->monthlyCommissionSummaryMail($notifiable),
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
     * Five-level commission earned email
     */
    protected function fiveLevelCommissionEarnedMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->data['amount'], 2);
        $level = $this->data['level'];
        $referralName = $this->data['referral_name'] ?? 'Team Member';
        $commissionType = $this->data['commission_type'] ?? 'Referral';
        
        return (new MailMessage)
            ->subject("🎉 MyGrowNet Level {$level} Commission Earned!")
            ->greeting("Great news, {$notifiable->name}!")
            ->line("You've earned a Level {$level} {$commissionType} commission from your growing network!")
            ->line("**Commission Details:**")
            ->line("• **Amount:** K{$amount}")
            ->line("• **Level:** {$level} of 5")
            ->line("• **From:** {$referralName}")
            ->line("• **Type:** {$commissionType}")
            ->line("• **Status:** Pending Payment (within 24 hours)")
            ->line("**Your MyGrowNet Network is Growing!**")
            ->line("This commission shows the power of building a strong team. Keep mentoring and supporting your network to unlock even more earning opportunities.")
            ->action('View Your Dashboard', url('/mygrownet/dashboard'))
            ->line('Continue building your wealth with MyGrowNet!')
            ->salutation('Best regards, The MyGrowNet Team');
    }

    /**
     * Team volume bonus earned email
     */
    protected function teamVolumeBonusEarnedMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->data['amount'], 2);
        $teamVolume = number_format($this->data['team_volume'], 2);
        $bonusPercentage = $this->data['bonus_percentage'];
        $tier = $this->data['tier'] ?? 'Member';
        
        return (new MailMessage)
            ->subject("💰 MyGrowNet Team Volume Bonus - K{$amount}!")
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("Your team's outstanding performance has earned you a Team Volume Bonus!")
            ->line("**Bonus Details:**")
            ->line("• **Bonus Amount:** K{$amount}")
            ->line("• **Team Volume:** K{$teamVolume}")
            ->line("• **Bonus Rate:** {$bonusPercentage}%")
            ->line("• **Your Tier:** {$tier}")
            ->line("**Leadership Excellence Rewarded!**")
            ->line("This bonus reflects your success in building and mentoring a productive team. Your leadership is creating wealth for everyone in your network.")
            ->line("**Next Steps:**")
            ->line("• Continue supporting your team members")
            ->line("• Help them reach their goals")
            ->line("• Watch your bonuses grow even more!")
            ->action('View Team Performance', url('/mygrownet/dashboard'))
            ->line('Keep leading the way to financial freedom!')
            ->salutation('Proudly yours, The MyGrowNet Team');
    }

    /**
     * Performance bonus earned email
     */
    protected function performanceBonusEarnedMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->data['amount'], 2);
        $bonusType = $this->data['bonus_type'] ?? 'Performance';
        $achievement = $this->data['achievement'] ?? 'Outstanding Performance';
        
        return (new MailMessage)
            ->subject("🏆 MyGrowNet Performance Bonus - K{$amount}!")
            ->greeting("Outstanding work, {$notifiable->name}!")
            ->line("Your exceptional performance has earned you a special bonus!")
            ->line("**Performance Bonus Details:**")
            ->line("• **Bonus Amount:** K{$amount}")
            ->line("• **Bonus Type:** {$bonusType}")
            ->line("• **Achievement:** {$achievement}")
            ->line("**Excellence Recognized!**")
            ->line("This bonus is our way of recognizing your dedication and success in the MyGrowNet community. You're setting an example for others to follow.")
            ->action('View Your Achievements', url('/mygrownet/dashboard'))
            ->line('Keep up the excellent work!')
            ->salutation('With appreciation, The MyGrowNet Team');
    }

    /**
     * Leadership bonus earned email
     */
    protected function leadershipBonusEarnedMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->data['amount'], 2);
        $leadershipLevel = $this->data['leadership_level'] ?? 'Leader';
        $teamSize = $this->data['team_size'] ?? 0;
        
        return (new MailMessage)
            ->subject("👑 MyGrowNet Leadership Bonus - K{$amount}!")
            ->greeting("Exceptional leadership, {$notifiable->name}!")
            ->line("Your outstanding leadership has earned you a Leadership Bonus!")
            ->line("**Leadership Bonus Details:**")
            ->line("• **Bonus Amount:** K{$amount}")
            ->line("• **Leadership Level:** {$leadershipLevel}")
            ->line("• **Team Size:** {$teamSize} members")
            ->line("**True Leadership Rewarded!**")
            ->line("This bonus recognizes your commitment to developing others and building a strong, successful team. Your mentorship is changing lives!")
            ->line("**Leadership Impact:**")
            ->line("• You're helping others achieve financial freedom")
            ->line("• Your team is growing stronger every day")
            ->line("• You're building a lasting legacy")
            ->action('View Leadership Dashboard', url('/mygrownet/dashboard'))
            ->line('Thank you for being an inspiring leader!')
            ->salutation('With deep respect, The MyGrowNet Team');
    }

    /**
     * Commission payment processed email
     */
    protected function commissionPaymentProcessedMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->data['amount'], 2);
        $paymentMethod = $this->data['payment_method'] ?? 'Mobile Money';
        $transactionId = $this->data['transaction_id'] ?? 'N/A';
        
        return (new MailMessage)
            ->subject("✅ MyGrowNet Payment Processed - K{$amount}")
            ->greeting("Payment confirmed, {$notifiable->name}!")
            ->line("Your MyGrowNet commission payment has been successfully processed!")
            ->line("**Payment Details:**")
            ->line("• **Amount:** K{$amount}")
            ->line("• **Method:** {$paymentMethod}")
            ->line("• **Transaction ID:** {$transactionId}")
            ->line("• **Status:** Completed")
            ->line("The payment should reflect in your account within a few minutes.")
            ->action('View Payment History', url('/mygrownet/dashboard'))
            ->line('Thank you for being part of MyGrowNet!')
            ->salutation('Best regards, The MyGrowNet Team');
    }

    /**
     * Commission payment failed email
     */
    protected function commissionPaymentFailedMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->data['amount'], 2);
        $reason = $this->data['failure_reason'] ?? 'Technical issue';
        $retryDate = $this->data['retry_date'] ?? 'within 24 hours';
        
        return (new MailMessage)
            ->subject("⚠️ MyGrowNet Payment Issue - K{$amount}")
            ->greeting("Hello {$notifiable->name},")
            ->line("We encountered an issue processing your commission payment, but don't worry - we're resolving it!")
            ->line("**Payment Details:**")
            ->line("• **Amount:** K{$amount}")
            ->line("• **Issue:** {$reason}")
            ->line("• **Retry:** {$retryDate}")
            ->line("**What's Next:**")
            ->line("• We'll automatically retry the payment")
            ->line("• You'll be notified once it's successful")
            ->line("• No action needed from you")
            ->line("If you continue to experience issues, please contact our support team.")
            ->action('Contact Support', url('/support'))
            ->line('We apologize for any inconvenience.')
            ->salutation('The MyGrowNet Team');
    }

    /**
     * Monthly commission summary email
     */
    protected function monthlyCommissionSummaryMail(object $notifiable): MailMessage
    {
        $totalEarnings = number_format($this->data['total_earnings'], 2);
        $commissionCount = $this->data['commission_count'] ?? 0;
        $month = $this->data['month'] ?? date('F Y');
        $breakdown = $this->data['breakdown'] ?? [];
        
        return (new MailMessage)
            ->subject("📊 MyGrowNet Monthly Summary - {$month}")
            ->greeting("Monthly update for {$notifiable->name}")
            ->line("Here's your MyGrowNet performance summary for {$month}:")
            ->line("**Monthly Earnings Summary:**")
            ->line("• **Total Earnings:** K{$totalEarnings}")
            ->line("• **Commission Payments:** {$commissionCount}")
            ->line("**Earnings Breakdown:**")
            ->when(!empty($breakdown), function ($mail) use ($breakdown) {
                foreach ($breakdown as $type => $amount) {
                    $mail->line("• {$type}: K" . number_format($amount, 2));
                }
                return $mail;
            })
            ->line("**Keep Growing Your Network!**")
            ->line("Every month brings new opportunities to increase your earnings. Continue building relationships and supporting your team.")
            ->action('View Detailed Report', url('/mygrownet/dashboard'))
            ->line('Here\'s to an even better month ahead!')
            ->salutation('Cheering you on, The MyGrowNet Team');
    }

    /**
     * Default email template
     */
    protected function defaultMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('MyGrowNet Commission Notification')
            ->line('You have received a commission notification from MyGrowNet.')
            ->action('View Dashboard', url('/mygrownet/dashboard'));
    }

    /**
     * Get database message based on notification type
     */
    protected function getDatabaseMessage(): string
    {
        return match ($this->data['type']) {
            'five_level_commission_earned' => $this->getFiveLevelCommissionMessage(),
            'team_volume_bonus_earned' => $this->getTeamVolumeBonusMessage(),
            'performance_bonus_earned' => $this->getPerformanceBonusMessage(),
            'leadership_bonus_earned' => $this->getLeadershipBonusMessage(),
            'commission_payment_processed' => $this->getPaymentProcessedMessage(),
            'commission_payment_failed' => $this->getPaymentFailedMessage(),
            'monthly_commission_summary' => $this->getMonthlySummaryMessage(),
            default => 'MyGrowNet commission notification'
        };
    }

    /**
     * Get notification priority
     */
    protected function getNotificationPriority(): string
    {
        return match ($this->data['type']) {
            'commission_payment_failed' => 'high',
            'five_level_commission_earned', 'team_volume_bonus_earned', 'performance_bonus_earned', 'leadership_bonus_earned' => 'medium',
            'commission_payment_processed', 'monthly_commission_summary' => 'normal',
            default => 'normal'
        };
    }

    /**
     * Get five-level commission message
     */
    protected function getFiveLevelCommissionMessage(): string
    {
        $amount = number_format($this->data['amount'], 2);
        $level = $this->data['level'];
        
        return "🎉 Level {$level} commission earned: K{$amount}. Payment processing within 24 hours.";
    }

    /**
     * Get team volume bonus message
     */
    protected function getTeamVolumeBonusMessage(): string
    {
        $amount = number_format($this->data['amount'], 2);
        $teamVolume = number_format($this->data['team_volume'], 2);
        
        return "💰 Team Volume Bonus: K{$amount} earned from K{$teamVolume} team performance!";
    }

    /**
     * Get performance bonus message
     */
    protected function getPerformanceBonusMessage(): string
    {
        $amount = number_format($this->data['amount'], 2);
        $bonusType = $this->data['bonus_type'] ?? 'Performance';
        
        return "🏆 {$bonusType} Bonus earned: K{$amount} for outstanding performance!";
    }

    /**
     * Get leadership bonus message
     */
    protected function getLeadershipBonusMessage(): string
    {
        $amount = number_format($this->data['amount'], 2);
        $leadershipLevel = $this->data['leadership_level'] ?? 'Leader';
        
        return "👑 Leadership Bonus: K{$amount} earned as {$leadershipLevel} for exceptional team development!";
    }

    /**
     * Get payment processed message
     */
    protected function getPaymentProcessedMessage(): string
    {
        $amount = number_format($this->data['amount'], 2);
        
        return "✅ Payment processed: K{$amount} commission payment completed successfully.";
    }

    /**
     * Get payment failed message
     */
    protected function getPaymentFailedMessage(): string
    {
        $amount = number_format($this->data['amount'], 2);
        
        return "⚠️ Payment issue: K{$amount} commission payment failed. Retrying automatically.";
    }

    /**
     * Get monthly summary message
     */
    protected function getMonthlySummaryMessage(): string
    {
        $totalEarnings = number_format($this->data['total_earnings'], 2);
        $month = $this->data['month'] ?? date('F Y');
        
        return "📊 Monthly Summary: K{$totalEarnings} total earnings for {$month}.";
    }
}
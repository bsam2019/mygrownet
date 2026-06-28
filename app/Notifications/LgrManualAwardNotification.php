<?php

namespace App\Notifications;

use App\Models\LGR\LGRManualAward;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LgrManualAwardNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LgrManualAward $award
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $awardType = ucwords(str_replace('_', ' ', $this->award->award_type));
        $amount = number_format($this->award->amount, 2);
        
        return (new MailMessage)
            ->subject('🎉 You\'ve Received a Loyalty Growth Reward!')
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("You have been awarded **K{$amount}** in Loyalty Growth Credits (LGC)!")
            ->line("**Award Details:**")
            ->line("• **Amount:** K{$amount}")
            ->line("• **Type:** {$awardType}")
            ->line("• **Reason:** {$this->award->reason}")
            ->line("**Your LGC Balance Has Been Updated!**")
            ->line("This bonus has been credited to your wallet and is available for immediate use. You can use it for:")
            ->line("• Purchasing products from the MyGrowNet shop")
            ->line("• Upgrading your starter kit")
            ->line("• Participating in special offers")
            ->action('View My Wallet', url('/mygrownet/wallet'))
            ->line('Thank you for being a valued premium member of MyGrowNet!')
            ->salutation('Best regards, The MyGrowNet Team');
    }

    public function toDatabase($notifiable): array
    {
        $awardType = ucwords(str_replace('_', ' ', $this->award->award_type));
        $amount = number_format($this->award->amount, 2);
        
        return [
            'type' => 'lgr.award.received',
            'category' => 'reward',
            'title' => 'LGR Award Received',
            'message' => "🎉 You've been awarded K{$amount} in Loyalty Growth Credits! {$awardType}: {$this->award->reason}",
            'action_url' => '/mygrownet/wallet',
            'action_text' => 'View Wallet',
            'data' => [
                'amount' => $this->award->amount,
                'award_type' => $this->award->award_type,
                'reason' => $this->award->reason,
                'award_id' => $this->award->id,
            ],
            'priority' => 'high',
        ];
    }
}

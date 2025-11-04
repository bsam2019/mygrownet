<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public float $amount,
        public User $issuedBy,
        public ?string $notes = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $amount = number_format($this->amount, 2);
        
        return (new MailMessage)
            ->subject('💰 Loan Approved - Funds Available')
            ->greeting("Hello, {$notifiable->name}!")
            ->line("Great news! Your loan request has been approved and processed.")
            ->line("**Loan Details:**")
            ->line("• **Amount:** K{$amount}")
            ->line("• **Approved By:** {$this->issuedBy->name}")
            ->when($this->notes, function ($mail) {
                return $mail->line("• **Purpose:** {$this->notes}");
            })
            ->line("**Important Information:**")
            ->line("✓ Funds have been credited to your wallet immediately")
            ->line("✓ You can use these funds for purchases or services")
            ->line("✓ 100% of your future earnings will go towards loan repayment")
            ->line("✓ Withdrawals are restricted until the loan is fully repaid")
            ->line("**Repayment Terms:**")
            ->line("Your loan will be automatically repaid from your future earnings including commissions, bonuses, and profit shares. You can track your repayment progress in your wallet.")
            ->action('View My Wallet', url('/mygrownet/wallet'))
            ->line('Thank you for being a valued member of MyGrowNet!')
            ->salutation('Best regards, The MyGrowNet Team');
    }

    public function toDatabase($notifiable): array
    {
        $amount = number_format($this->amount, 2);
        
        return [
            'type' => 'loan.issued',
            'category' => 'financial',
            'title' => 'Loan Approved',
            'message' => "💰 Your loan of K{$amount} has been approved and credited to your wallet. Automatic repayment will begin from future earnings.",
            'action_url' => '/mygrownet/wallet',
            'action_text' => 'View Wallet',
            'data' => [
                'amount' => $this->amount,
                'issued_by' => $this->issuedBy->name,
                'notes' => $this->notes,
            ],
            'priority' => 'high',
        ];
    }
}

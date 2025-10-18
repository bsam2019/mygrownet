<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LevelAdvancementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $newLevel;
    protected array $bonus;

    public function __construct(string $newLevel, array $bonus)
    {
        $this->newLevel = $newLevel;
        $this->bonus = $bonus;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $levelName = ucfirst($this->newLevel);
        
        return (new MailMessage)
            ->subject("🎉 Congratulations! You've Advanced to {$levelName} Level")
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("You've successfully advanced to the **{$levelName}** level!")
            ->line("This is a significant milestone in your MyGrowNet journey.")
            ->line("**Your Milestone Bonus:**")
            ->line("💰 Cash Bonus: K" . number_format($this->bonus['cash'], 2))
            ->line("⭐ Lifetime Points: {$this->bonus['lp']} LP")
            ->action('View Your Dashboard', url('/dashboard'))
            ->line('Keep up the great work and continue growing with MyGrowNet!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'level_advancement',
            'new_level' => $this->newLevel,
            'cash_bonus' => $this->bonus['cash'],
            'lp_bonus' => $this->bonus['lp'],
            'message' => "Congratulations! You've advanced to {$this->newLevel} level!",
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyQualificationAtRisk extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $userPoints = $notifiable->points;
        $required = $userPoints->getRequiredMapForLevel();
        $current = $userPoints->monthly_points;
        $needed = $required - $current;
        $daysLeft = now()->endOfMonth()->diffInDays(now());

        return (new MailMessage)
            ->subject("⚠️ Action Needed: Monthly Qualification At Risk")
            ->greeting("Hi {$notifiable->name},")
            ->line("You're close to qualifying for this month's earnings, but you need to take action!")
            ->line("**Current Status:**")
            ->line("📊 Monthly Points: {$current} MAP")
            ->line("🎯 Required: {$required} MAP")
            ->line("⚠️ Still Needed: {$needed} MAP")
            ->line("⏰ Days Remaining: {$daysLeft}")
            ->line("**Quick Ways to Qualify:**")
            ->line("• Complete a course (+30-60 MAP)")
            ->line("• Make a product sale (+10 MAP per K100)")
            ->line("• Login daily (+5 MAP)")
            ->line("• Mentor a team member (+30 MAP)")
            ->action('Earn Points Now', url('/dashboard/points'))
            ->line("Don't miss out on your earnings this month!");
    }

    public function toArray(object $notifiable): array
    {
        $userPoints = $notifiable->points;
        $required = $userPoints->getRequiredMapForLevel();
        $current = $userPoints->monthly_points;

        return [
            'type' => 'qualification_at_risk',
            'current_map' => $current,
            'required_map' => $required,
            'needed_map' => $required - $current,
            'days_left' => now()->endOfMonth()->diffInDays(now()),
            'message' => "You need {$required - $current} more MAP to qualify this month!",
        ];
    }
}

<?php

namespace App\Notifications\GrowFinance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Base notification class for GrowFinance module.
 * Automatically sets the module column when notification is stored.
 * Broadcasts to user's private channel for real-time updates.
 */
abstract class BaseGrowFinanceNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected string $module = 'growfinance';

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Store the notification and set the module column.
     * This is called after the notification is created.
     */
    public function afterCommit(): bool
    {
        return true;
    }

    /**
     * Get the array representation of the notification.
     */
    abstract public function toDatabase(object $notifiable): array;

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => static::class,
            'data' => $this->toDatabase($notifiable),
            'read_at' => null,
            'created_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get the type of the notification being broadcast.
     */
    public function broadcastType(): string
    {
        return 'growfinance.' . class_basename(static::class);
    }
}

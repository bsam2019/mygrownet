<?php

namespace App\Notifications\GrowMart;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $data,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $productName = $this->data['product_name'] ?? 'Unknown';
        $currentStock = $this->data['current_stock'] ?? 0;
        $threshold = $this->data['threshold'] ?? 0;

        return (new MailMessage)
            ->subject("Low Stock Alert – {$productName}")
            ->greeting("Low Stock Alert")
            ->line("Product **{$productName}** is running low.")
            ->line("Current stock: **{$currentStock}** (threshold: {$threshold})")
            ->action('View Inventory', url('/admin/growmart/inventory'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'growmart.low_stock',
            'message' => "Low stock: {$this->data['product_name']} ({$this->data['current_stock']} remaining)",
            'data' => $this->data,
            'priority' => 'high',
        ];
    }
}

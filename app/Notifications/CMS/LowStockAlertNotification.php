<?php

namespace App\Notifications\CMS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LowStockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $item
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Stock Alert - ' . $this->item['name'])
            ->greeting('Hello')
            ->line('An inventory item is running low on stock.')
            ->line('Item: ' . $this->item['name'])
            ->line('Item Code: ' . $this->item['item_code'])
            ->line('Current Stock: ' . $this->item['current_stock'] . ' ' . $this->item['unit'])
            ->line('Minimum Stock: ' . $this->item['minimum_stock'] . ' ' . $this->item['unit'])
            ->action('View Inventory', url('/cms/inventory/' . $this->item['id']))
            ->line('Please restock this item soon to avoid shortages.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'low_stock_alert',
            'title' => 'Low Stock Alert',
            'message' => $this->item['name'] . ' is low on stock (' . $this->item['current_stock'] . ' ' . $this->item['unit'] . ' remaining)',
            'inventory_item_id' => $this->item['id'],
            'item_code' => $this->item['item_code'],
            'item_name' => $this->item['name'],
            'current_stock' => $this->item['current_stock'],
            'minimum_stock' => $this->item['minimum_stock'],
            'unit' => $this->item['unit'],
            'url' => '/cms/inventory/' . $this->item['id'],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

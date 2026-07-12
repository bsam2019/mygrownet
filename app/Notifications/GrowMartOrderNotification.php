<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GrowMartOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $type,
        public array $data,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $orderNumber = $this->data['order_number'] ?? '';
        $url = url('/growmart/orders/' . ($this->data['order_id'] ?? ''));

        return match ($this->type) {
            'order_placed' => (new MailMessage)
                ->subject("Order Confirmed – {$orderNumber}")
                ->greeting("Thank you for your order!")
                ->line("Your order **{$orderNumber}** has been placed successfully.")
                ->line("---")
                ->line("**Order Summary:**")
                ->line($this->formatItemsTable())
                ->line("---")
                ->line("**Total: K" . number_format(($this->data['total'] ?? 0) / 100, 2) . "**")
                ->line("We'll notify you when your order status changes.")
                ->action('View Order', $url),

            'admin_new_order' => (new MailMessage)
                ->subject("New GrowMart Order – {$orderNumber}")
                ->greeting("New order received!")
                ->line("A new order **{$orderNumber}** has been placed.")
                ->line("Customer: **{$this->data['customer_name']}**")
                ->line("Total: **K" . number_format(($this->data['total'] ?? 0) / 100, 2) . "**")
                ->action('View Order', url('/admin/growmart/orders')),

            'order_status' => (new MailMessage)
                ->subject("Order Update – {$orderNumber}")
                ->greeting("Order status updated!")
                ->line("Your order **{$orderNumber}** is now **" . str_replace('_', ' ', $this->data['status'] ?? '') . "**.")
                ->action('View Order', $url),

            'order_cancelled' => (new MailMessage)
                ->subject("Order Cancelled – {$orderNumber}")
                ->greeting("Order cancelled")
                ->line("Your order **{$orderNumber}** has been cancelled.")
                ->line("If you did not request this cancellation, please contact us.")
                ->action('View Order', $url),

            'order_paid' => (new MailMessage)
                ->subject("Payment Received – {$orderNumber}")
                ->greeting("Payment confirmed!")
                ->line("Your payment for order **{$orderNumber}** has been received.")
                ->action('View Order', $url),

            default => (new MailMessage)
                ->subject("GrowMart Update – {$orderNumber}")
                ->line("There's an update regarding your order {$orderNumber}.")
                ->action('View Order', $url),
        };
    }

    private function formatItemsTable(): string
    {
        $items = $this->data['items'] ?? [];
        if (empty($items)) {
            return '(No items)';
        }
        $lines = [];
        foreach ($items as $item) {
            $name = $item['name'] ?? 'Unknown';
            $qty = $item['quantity'] ?? 0;
            $price = number_format(($item['unit_price'] ?? 0) / 100, 2);
            $subtotal = number_format(($item['total'] ?? 0) / 100, 2);
            $lines[] = "• {$name} x{$qty} @ K{$price} = K{$subtotal}";
        }
        return implode("\n", $lines);
    }

    public function toDatabase(object $notifiable): array
    {
        $message = match ($this->type) {
            'order_placed' => "Order {$this->data['order_number']} placed successfully!",
            'order_status' => "Order {$this->data['order_number']} is now " . str_replace('_', ' ', $this->data['status']),
            'order_cancelled' => "Order {$this->data['order_number']} has been cancelled.",
            'order_paid' => "Payment received for order {$this->data['order_number']}.",
            default => "GrowMart update: {$this->data['order_number']}",
        };

        return [
            'type' => "growmart.{$this->type}",
            'message' => $message,
            'data' => $this->data,
            'priority' => 'normal',
        ];
    }
}

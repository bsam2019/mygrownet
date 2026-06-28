<?php

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Services\SocialMedia\WhatsAppService;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostOrderModel;
use Illuminate\Support\Facades\Log;

class OrderNotificationService
{
    public function sendOrderConfirmation(BizBoostOrderModel $order): void
    {
        $business = $order->business;
        $integration = BizBoostIntegrationModel::where('business_id', $business->id)
            ->where('provider', 'whatsapp')
            ->where('status', 'active')
            ->first();

        if (!$integration) {
            return;
        }

        $service = new WhatsAppService($integration);

        $customerPhone = $order->customer_phone;
        $itemsText = $order->items->map(fn($item) => "{$item->product_name} x{$item->quantity} - ZMW " . number_format($item->subtotal, 2))->implode("\n");

        $message = "✅ *Order Confirmed!*\n\n"
            . "Hi {$order->customer_name},\n\n"
            . "Your order #{$order->order_number} has been received.\n\n"
            . "*Items:*\n{$itemsText}\n\n"
            . "*Total:* ZMW " . number_format($order->total, 2) . "\n"
            . "*Payment:* " . str_replace('_', ' ', ucwords($order->payment_method, '_')) . "\n"
            . "*Status:* " . ucfirst($order->order_status) . "\n\n";

        if ($order->delivery_address) {
            $message .= "*Delivery:* {$order->delivery_address}\n\n";
        }

        $message .= "We'll notify you when your order is updated. Thank you for shopping with {$business->name}! 🎉";

        try {
            $service->sendMessage($customerPhone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation via WhatsApp', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendOrderStatusUpdate(BizBoostOrderModel $order): void
    {
        $business = $order->business;
        $integration = BizBoostIntegrationModel::where('business_id', $business->id)
            ->where('provider', 'whatsapp')
            ->where('status', 'active')
            ->first();

        if (!$integration) {
            return;
        }

        $service = new WhatsAppService($integration);

        $statusEmoji = match ($order->order_status) {
            'confirmed' => '📦',
            'processing' => '🔧',
            'delivered' => '✅',
            'cancelled' => '❌',
            default => '🔄',
        };

        $message = "{$statusEmoji} *Order Update #{$order->order_number}*\n\n"
            . "Hi {$order->customer_name},\n\n"
            . "Your order status has been updated to: *" . ucfirst($order->order_status) . "*\n";

        if ($order->payment_status === 'paid') {
            $message .= "💳 Payment received: ZMW " . number_format($order->total, 2) . "\n";
        }

        $message .= "\nThank you for choosing {$business->name}!";

        try {
            $service->sendMessage($customerPhone = $order->customer_phone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send order status update via WhatsApp', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

<?php

namespace App\Domain\GrowMart\Services;

use App\Models\GrowMart\GrowMartOrder;
use App\Notifications\GrowMartOrderNotification;
use App\Domain\GrowMart\Services\NotificationService;

enum PaymentProvider: string
{
    case MobileMoney = 'mobile_money';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case Crypto = 'crypto';
}

class PaymentService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function processPayment(GrowMartOrder $order, PaymentProvider $provider, array $metadata = []): array
    {
        return match ($provider) {
            PaymentProvider::MobileMoney => $this->processMobileMoneyPayment($order, $metadata),
            PaymentProvider::Card => $this->processCardPayment($order, $metadata),
            PaymentProvider::BankTransfer => $this->processBankTransfer($order, $metadata),
            PaymentProvider::Crypto => $this->processCryptoPayment($order, $metadata),
        };
    }

    public function markAsPaid(GrowMartOrder $order, PaymentProvider $provider, string $reference = null): void
    {
        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->notificationService->notify(
            $order->user,
            'growmart.order_paid',
            'Payment Received',
            "Payment received for order {$order->order_number}.",
            route('growmart.orders.show', $order->id),
            'View Order',
            'payments',
            'high',
            ['order_number' => $order->order_number, 'order_id' => $order->id],
        );
        $order->user->notify(new GrowMartOrderNotification('order_paid', [
            'order_number' => $order->order_number,
            'order_id' => $order->id,
            'payment_method' => $provider->value,
            'reference' => $reference,
        ]));
    }

    private function processMobileMoneyPayment(GrowMartOrder $order, array $metadata): array
    {
        // Stub: Integrate with Airtel Money / MTN Mobile Money APIs
        return [
            'success' => true,
            'provider' => PaymentProvider::MobileMoney->value,
            'message' => 'Mobile money payment initiated.',
            'reference' => null,
            'requires_action' => true,
        ];
    }

    private function processCardPayment(GrowMartOrder $order, array $metadata): array
    {
        // Stub: Integrate with Stripe/Paystack/Flutterwave
        return [
            'success' => false,
            'provider' => PaymentProvider::Card->value,
            'message' => 'Card payments coming soon.',
            'requires_action' => false,
        ];
    }

    private function processBankTransfer(GrowMartOrder $order, array $metadata): array
    {
        return [
            'success' => true,
            'provider' => PaymentProvider::BankTransfer->value,
            'message' => 'Bank transfer payment. Awaiting confirmation.',
            'requires_action' => true,
        ];
    }

    private function processCryptoPayment(GrowMartOrder $order, array $metadata): array
    {
        return [
            'success' => true,
            'provider' => PaymentProvider::Crypto->value,
            'message' => 'Crypto payment initiated. Awaiting blockchain confirmation.',
            'requires_action' => true,
        ];
    }
}

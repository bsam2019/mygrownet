<?php

namespace App\Domain\GrowMart\Services;

use App\Domain\GrowMart\Repositories\OrderRepositoryInterface;
use App\Models\User;
use App\Notifications\GrowMartOrderNotification;

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
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly NotificationService $notificationService,
    ) {}

    public function processPayment(array $order, PaymentProvider $provider, array $metadata = []): array
    {
        return match ($provider) {
            PaymentProvider::MobileMoney => $this->processMobileMoneyPayment($order, $metadata),
            PaymentProvider::Card => $this->processCardPayment($order, $metadata),
            PaymentProvider::BankTransfer => $this->processBankTransfer($order, $metadata),
            PaymentProvider::Crypto => $this->processCryptoPayment($order, $metadata),
        };
    }

    public function markAsPaid(array $order, PaymentProvider $provider, string $reference = null): void
    {
        $this->orderRepository->update($order['id'], [
            'payment_status' => 'paid',
            'paid_at' => now()->toDateTimeString(),
        ]);

        $user = User::find($order['user_id']);
        if ($user) {
            $this->notificationService->notify(
                $user,
                'growmart.order_paid',
                'Payment Received',
                "Payment received for order {$order['order_number']}.",
                route('growmart.orders.show', $order['id']),
                'View Order',
                'payments',
                'high',
                ['order_number' => $order['order_number'], 'order_id' => $order['id']],
            );
            $user->notify(new GrowMartOrderNotification('order_paid', [
                'order_number' => $order['order_number'],
                'order_id' => $order['id'],
                'payment_method' => $provider->value,
                'reference' => $reference,
            ]));
        }
    }

    private function processMobileMoneyPayment(array $order, array $metadata): array
    {
        return [
            'success' => true,
            'provider' => PaymentProvider::MobileMoney->value,
            'message' => 'Mobile money payment initiated.',
            'reference' => null,
            'requires_action' => true,
        ];
    }

    private function processCardPayment(array $order, array $metadata): array
    {
        return [
            'success' => false,
            'provider' => PaymentProvider::Card->value,
            'message' => 'Card payments coming soon.',
            'requires_action' => false,
        ];
    }

    private function processBankTransfer(array $order, array $metadata): array
    {
        return [
            'success' => true,
            'provider' => PaymentProvider::BankTransfer->value,
            'message' => 'Bank transfer payment. Awaiting confirmation.',
            'requires_action' => true,
        ];
    }

    private function processCryptoPayment(array $order, array $metadata): array
    {
        return [
            'success' => true,
            'provider' => PaymentProvider::Crypto->value,
            'message' => 'Crypto payment initiated. Awaiting blockchain confirmation.',
            'requires_action' => true,
        ];
    }
}

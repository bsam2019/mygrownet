<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowMart\Services;

use App\Domain\GrowMart\Repositories\OrderRepositoryInterface;
use App\Domain\GrowMart\Services\NotificationService;
use App\Domain\GrowMart\Services\PaymentProvider;
use App\Domain\GrowMart\Services\PaymentService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    private OrderRepositoryInterface $orderRepo;
    private NotificationService $notificationService;
    private PaymentService $service;

    protected function setUp(): void
    {
        $this->orderRepo = $this->createStub(OrderRepositoryInterface::class);
        $this->notificationService = $this->createStub(NotificationService::class);
        $this->service = new PaymentService($this->orderRepo, $this->notificationService);
    }

    private function sampleOrder(): array
    {
        return [
            'id' => 1,
            'order_number' => 'GM-TEST-123',
            'user_id' => 42,
            'total' => 5000,
        ];
    }

    #[Test]
    public function process_mobile_money_payment(): void
    {
        $result = $this->service->processPayment(
            $this->sampleOrder(),
            PaymentProvider::MobileMoney,
            ['phone' => '0977123456'],
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('mobile_money', $result['provider']);
        $this->assertStringContainsString('initiated', $result['message']);
        $this->assertTrue($result['requires_action']);
    }

    #[Test]
    public function process_card_payment_returns_not_available(): void
    {
        $result = $this->service->processPayment(
            $this->sampleOrder(),
            PaymentProvider::Card,
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('card', $result['provider']);
        $this->assertStringContainsString('coming soon', $result['message']);
        $this->assertFalse($result['requires_action']);
    }

    #[Test]
    public function process_bank_transfer_payment(): void
    {
        $result = $this->service->processPayment(
            $this->sampleOrder(),
            PaymentProvider::BankTransfer,
            ['reference' => 'REF-001'],
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('bank_transfer', $result['provider']);
        $this->assertStringContainsString('Awaiting confirmation', $result['message']);
        $this->assertTrue($result['requires_action']);
    }

    #[Test]
    public function process_crypto_payment(): void
    {
        $result = $this->service->processPayment(
            $this->sampleOrder(),
            PaymentProvider::Crypto,
            ['wallet' => '0xabc'],
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('crypto', $result['provider']);
        $this->assertStringContainsString('blockchain', $result['message']);
        $this->assertTrue($result['requires_action']);
    }

    #[Test]
    public function process_payment_passes_metadata(): void
    {
        $metadata = ['phone' => '0977111111', 'network' => 'MTN'];
        $result = $this->service->processPayment(
            $this->sampleOrder(),
            PaymentProvider::MobileMoney,
            $metadata,
        );

        $this->assertTrue($result['success']);
    }

    #[Test]
    public function process_payment_with_different_enum_values(): void
    {
        $this->assertEquals('mobile_money', PaymentProvider::MobileMoney->value);
        $this->assertEquals('card', PaymentProvider::Card->value);
        $this->assertEquals('bank_transfer', PaymentProvider::BankTransfer->value);
        $this->assertEquals('crypto', PaymentProvider::Crypto->value);
    }

    #[Test]
    public function payment_provider_enum_has_four_cases(): void
    {
        $cases = PaymentProvider::cases();
        $this->assertCount(4, $cases);
        $this->assertContains(PaymentProvider::MobileMoney, $cases);
        $this->assertContains(PaymentProvider::Card, $cases);
        $this->assertContains(PaymentProvider::BankTransfer, $cases);
        $this->assertContains(PaymentProvider::Crypto, $cases);
    }

    #[Test]
    public function payment_provider_from_string(): void
    {
        $this->assertEquals(PaymentProvider::MobileMoney, PaymentProvider::from('mobile_money'));
        $this->assertEquals(PaymentProvider::Card, PaymentProvider::from('card'));
        $this->assertEquals(PaymentProvider::BankTransfer, PaymentProvider::from('bank_transfer'));
        $this->assertEquals(PaymentProvider::Crypto, PaymentProvider::from('crypto'));
    }

    #[Test]
    public function payment_provider_try_from(): void
    {
        $this->assertEquals(PaymentProvider::MobileMoney, PaymentProvider::tryFrom('mobile_money'));
        $this->assertNull(PaymentProvider::tryFrom('invalid'));
    }
}

<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\PlatformPayments\DTOs\PaymentResponse;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentResponseTest extends TestCase
{
    #[Test]
    public function it_creates_success_response(): void
    {
        $dto = new PaymentResponse(
            success: true,
            status: PaymentStatus::COMPLETED,
            transactionReference: 'REF-001',
            externalReference: 'EXT-001',
            message: 'Payment successful',
            rawResponse: ['id' => '123'],
            checkoutUrl: null,
        );

        $this->assertTrue($dto->success);
        $this->assertEquals(PaymentStatus::COMPLETED, $dto->status);
        $this->assertEquals('REF-001', $dto->transactionReference);
        $this->assertEquals('EXT-001', $dto->externalReference);
        $this->assertEquals('Payment successful', $dto->message);
        $this->assertEquals(['id' => '123'], $dto->rawResponse);
        $this->assertNull($dto->checkoutUrl);
    }

    #[Test]
    public function it_creates_failed_response(): void
    {
        $dto = new PaymentResponse(
            success: false,
            status: PaymentStatus::FAILED,
            transactionReference: 'REF-002',
        );

        $this->assertFalse($dto->success);
        $this->assertEquals(PaymentStatus::FAILED, $dto->status);
        $this->assertEquals('REF-002', $dto->transactionReference);
        $this->assertNull($dto->externalReference);
        $this->assertNull($dto->message);
        $this->assertNull($dto->rawResponse);
        $this->assertNull($dto->checkoutUrl);
    }

    #[Test]
    public function it_creates_with_checkout_url(): void
    {
        $dto = new PaymentResponse(
            success: true,
            status: PaymentStatus::PENDING,
            transactionReference: 'REF-003',
            checkoutUrl: 'https://pay.example.com/checkout',
        );

        $this->assertTrue($dto->success);
        $this->assertEquals('https://pay.example.com/checkout', $dto->checkoutUrl);
    }

    #[Test]
    public function to_array_serializes_correctly(): void
    {
        $dto = new PaymentResponse(
            success: true,
            status: PaymentStatus::PENDING,
            transactionReference: 'REF-004',
            externalReference: 'EXT-004',
            message: 'Pending',
            rawResponse: ['status' => 'pending'],
            checkoutUrl: 'https://pay.test',
        );

        $result = $dto->toArray();

        $this->assertTrue($result['success']);
        $this->assertEquals('pending', $result['status']);
        $this->assertEquals('REF-004', $result['transaction_reference']);
        $this->assertEquals('EXT-004', $result['external_reference']);
        $this->assertEquals('Pending', $result['message']);
        $this->assertEquals(['status' => 'pending'], $result['raw_response']);
        $this->assertEquals('https://pay.test', $result['checkout_url']);
    }
}

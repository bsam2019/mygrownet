<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentRequestTest extends TestCase
{
    #[Test]
    public function it_creates_with_required_fields(): void
    {
        $dto = new PaymentRequest(
            amount: '100.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-001',
            description: 'Test payment',
        );

        $this->assertEquals('100.00', $dto->amount);
        $this->assertEquals('ZMW', $dto->currency);
        $this->assertEquals('260977123456', $dto->phoneNumber);
        $this->assertEquals('REF-001', $dto->reference);
        $this->assertEquals('Test payment', $dto->description);
        $this->assertNull($dto->customerName);
        $this->assertNull($dto->customerEmail);
        $this->assertNull($dto->metadata);
        $this->assertNull($dto->callbackUrl);
        $this->assertNull($dto->returnUrl);
    }

    #[Test]
    public function it_creates_with_all_fields(): void
    {
        $dto = new PaymentRequest(
            amount: '50.00',
            currency: 'USD',
            phoneNumber: '260965123456',
            reference: 'REF-002',
            description: 'Full payment',
            customerName: 'John Doe',
            customerEmail: 'john@example.com',
            metadata: ['order_id' => 123],
            callbackUrl: 'https://example.com/callback',
            returnUrl: 'https://example.com/return',
        );

        $this->assertEquals('50.00', $dto->amount);
        $this->assertEquals('John Doe', $dto->customerName);
        $this->assertEquals('john@example.com', $dto->customerEmail);
        $this->assertEquals(['order_id' => 123], $dto->metadata);
        $this->assertEquals('https://example.com/callback', $dto->callbackUrl);
        $this->assertEquals('https://example.com/return', $dto->returnUrl);
    }

    #[Test]
    public function to_array_returns_snake_case_keys(): void
    {
        $dto = new PaymentRequest(
            amount: '100.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-003',
            description: 'Test',
            customerName: 'Jane',
            customerEmail: 'jane@test.com',
            metadata: ['key' => 'val'],
            callbackUrl: 'https://cb.test',
            returnUrl: 'https://ret.test',
        );

        $result = $dto->toArray();

        $this->assertEquals('100.00', $result['amount']);
        $this->assertEquals('ZMW', $result['currency']);
        $this->assertEquals('260977123456', $result['phone_number']);
        $this->assertEquals('REF-003', $result['reference']);
        $this->assertEquals('Test', $result['description']);
        $this->assertEquals('Jane', $result['customer_name']);
        $this->assertEquals('jane@test.com', $result['customer_email']);
        $this->assertEquals(['key' => 'val'], $result['metadata']);
        $this->assertEquals('https://cb.test', $result['callback_url']);
        $this->assertEquals('https://ret.test', $result['return_url']);
    }
}

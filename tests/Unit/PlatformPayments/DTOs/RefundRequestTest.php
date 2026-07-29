<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\PlatformPayments\DTOs\RefundRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RefundRequestTest extends TestCase
{
    #[Test]
    public function it_creates_with_required_fields(): void
    {
        $dto = new RefundRequest(
            transactionReference: 'TXN-001',
            amount: '50.00',
            reason: 'Customer request',
        );

        $this->assertEquals('TXN-001', $dto->transactionReference);
        $this->assertEquals('50.00', $dto->amount);
        $this->assertEquals('Customer request', $dto->reason);
        $this->assertNull($dto->metadata);
    }

    #[Test]
    public function it_creates_with_all_fields(): void
    {
        $dto = new RefundRequest(
            transactionReference: 'TXN-002',
            amount: '100.00',
            reason: 'Duplicate payment',
            metadata: ['original_txn' => 'TXN-001'],
        );

        $this->assertEquals('TXN-002', $dto->transactionReference);
        $this->assertEquals('100.00', $dto->amount);
        $this->assertEquals('Duplicate payment', $dto->reason);
        $this->assertEquals(['original_txn' => 'TXN-001'], $dto->metadata);
    }

    #[Test]
    public function to_array_returns_correct_format(): void
    {
        $dto = new RefundRequest(
            transactionReference: 'TXN-003',
            amount: '75.00',
            reason: 'Refund',
            metadata: ['key' => 'val'],
        );

        $result = $dto->toArray();

        $this->assertEquals('TXN-003', $result['transaction_reference']);
        $this->assertEquals('75.00', $result['amount']);
        $this->assertEquals('Refund', $result['reason']);
        $this->assertEquals(['key' => 'val'], $result['metadata']);
    }
}

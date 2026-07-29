<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\PlatformPayments\DTOs\RefundResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RefundResponseTest extends TestCase
{
    #[Test]
    public function it_creates_success_refund_response(): void
    {
        $dto = new RefundResponse(
            success: true,
            refundReference: 'RFND-001',
            message: 'Refund processed',
            rawResponse: ['id' => '123'],
        );

        $this->assertTrue($dto->success);
        $this->assertEquals('RFND-001', $dto->refundReference);
        $this->assertEquals('Refund processed', $dto->message);
        $this->assertEquals(['id' => '123'], $dto->rawResponse);
    }

    #[Test]
    public function it_creates_failed_refund_response(): void
    {
        $dto = new RefundResponse(
            success: false,
            refundReference: '',
            message: 'Refund failed',
        );

        $this->assertFalse($dto->success);
        $this->assertEquals('', $dto->refundReference);
        $this->assertEquals('Refund failed', $dto->message);
        $this->assertNull($dto->rawResponse);
    }

    #[Test]
    public function to_array_returns_correct_format(): void
    {
        $dto = new RefundResponse(
            success: true,
            refundReference: 'RFND-002',
            message: 'Done',
            rawResponse: ['status' => 'ok'],
        );

        $result = $dto->toArray();

        $this->assertTrue($result['success']);
        $this->assertEquals('RFND-002', $result['refund_reference']);
        $this->assertEquals('Done', $result['message']);
        $this->assertEquals(['status' => 'ok'], $result['raw_response']);
    }
}

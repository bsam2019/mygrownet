<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\Entities\Investment;
use App\Domain\VentureBuilder\ValueObjects\InvestmentStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvestmentTest extends TestCase
{
    #[Test]
    public function can_be_created_with_minimal_data(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 42,
            amount: 5000.0,
            status: InvestmentStatus::pending(),
        );

        $this->assertSame(1, $investment->ventureId);
        $this->assertSame(42, $investment->userId);
        $this->assertSame(5000.0, $investment->amount);
        $this->assertTrue($investment->status->isPending());
        $this->assertNull($investment->id);
    }

    #[Test]
    public function is_confirmed_delegates_to_status(): void
    {
        $confirmed = new Investment(ventureId: 1, userId: 1, amount: 100.0, status: InvestmentStatus::confirmed());
        $pending = new Investment(ventureId: 1, userId: 1, amount: 100.0, status: InvestmentStatus::pending());

        $this->assertTrue($confirmed->isConfirmed());
        $this->assertFalse($pending->isConfirmed());
    }

    #[Test]
    public function is_pending_delegates_to_status(): void
    {
        $pending = new Investment(ventureId: 1, userId: 1, amount: 100.0, status: InvestmentStatus::pending());
        $this->assertTrue($pending->isPending());
    }

    #[Test]
    public function can_be_cancelled_delegates_to_status(): void
    {
        $pending = new Investment(ventureId: 1, userId: 1, amount: 100.0, status: InvestmentStatus::pending());
        $refunded = new Investment(ventureId: 1, userId: 1, amount: 100.0, status: InvestmentStatus::refunded());

        $this->assertTrue($pending->canBeCancelled());
        $this->assertFalse($refunded->canBeCancelled());
    }

    #[Test]
    public function can_be_reconstituted_from_array(): void
    {
        $data = [
            'id' => 10,
            'venture_id' => 1,
            'user_id' => 42,
            'amount' => 5000.0,
            'shares_allocated' => 500.0,
            'status' => 'confirmed',
            'payment_method' => 'wallet',
            'payment_reference' => 'WALLET_ABC123',
            'payment_confirmed_at' => '2026-02-01 12:00:00',
            'is_shareholder' => true,
        ];

        $investment = Investment::reconstitute($data);

        $this->assertSame(10, $investment->id);
        $this->assertSame(1, $investment->ventureId);
        $this->assertSame(42, $investment->userId);
        $this->assertSame(5000.0, $investment->amount);
        $this->assertSame(500.0, $investment->sharesAllocated);
        $this->assertTrue($investment->status->isConfirmed());
        $this->assertSame('wallet', $investment->paymentMethod);
        $this->assertTrue($investment->isShareholder);
    }

    #[Test]
    public function reconstitute_defaults_status_to_pending(): void
    {
        $investment = Investment::reconstitute([
            'venture_id' => 1,
            'user_id' => 1,
            'amount' => 100.0,
        ]);

        $this->assertTrue($investment->status->isPending());
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 42,
            amount: 5000.0,
            status: InvestmentStatus::confirmed(),
            id: 5,
            sharesAllocated: 500.0,
            paymentMethod: 'bank',
            createdAt: new DateTimeImmutable('2026-01-01 08:00:00'),
        );

        $arr = $investment->toArray();

        $this->assertSame(5, $arr['id']);
        $this->assertSame(1, $arr['venture_id']);
        $this->assertSame(42, $arr['user_id']);
        $this->assertSame(5000.0, $arr['amount']);
        $this->assertSame('confirmed', $arr['status']);
        $this->assertSame('2026-01-01 08:00:00', $arr['created_at']);
    }
}

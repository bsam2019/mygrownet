<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\Entities\ShareTransfer;
use App\Domain\VentureBuilder\ValueObjects\TransferStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ShareTransferTest extends TestCase
{
    #[Test]
    public function can_be_created_with_minimal_data(): void
    {
        $transfer = new ShareTransfer(
            ventureId: 1,
            fromUserId: 42,
            toUserId: 99,
            shares: 100.0,
            status: TransferStatus::pending(),
        );

        $this->assertSame(1, $transfer->ventureId);
        $this->assertSame(42, $transfer->fromUserId);
        $this->assertSame(99, $transfer->toUserId);
        $this->assertSame(100.0, $transfer->shares);
        $this->assertTrue($transfer->status->isPending());
    }

    #[Test]
    public function is_pending_delegates_to_status(): void
    {
        $pending = new ShareTransfer(ventureId: 1, fromUserId: 1, toUserId: 2, shares: 10.0, status: TransferStatus::pending());
        $approved = new ShareTransfer(ventureId: 1, fromUserId: 1, toUserId: 2, shares: 10.0, status: TransferStatus::approved());

        $this->assertTrue($pending->isPending());
        $this->assertFalse($approved->isPending());
    }

    #[Test]
    public function is_completed_returns_true_when_approved(): void
    {
        $approved = new ShareTransfer(ventureId: 1, fromUserId: 1, toUserId: 2, shares: 10.0, status: TransferStatus::approved());
        $this->assertTrue($approved->isCompleted());
    }

    #[Test]
    public function is_completed_returns_false_when_not_approved(): void
    {
        $pending = new ShareTransfer(ventureId: 1, fromUserId: 1, toUserId: 2, shares: 10.0, status: TransferStatus::pending());
        $this->assertFalse($pending->isCompleted());
    }

    #[Test]
    public function can_be_reconstituted_from_array(): void
    {
        $data = [
            'id' => 4,
            'venture_id' => 1,
            'from_user_id' => 42,
            'to_user_id' => 99,
            'shares' => 100.0,
            'status' => 'pending',
            'price_per_share' => 10.0,
            'total_value' => 1000.0,
            'reason' => 'Sale of shares',
        ];

        $transfer = ShareTransfer::reconstitute($data);

        $this->assertSame(4, $transfer->id);
        $this->assertSame(100.0, $transfer->shares);
        $this->assertSame(10.0, $transfer->pricePerShare);
        $this->assertSame(1000.0, $transfer->totalValue);
        $this->assertSame('Sale of shares', $transfer->reason);
    }

    #[Test]
    public function reconstitute_defaults_status_to_pending(): void
    {
        $transfer = ShareTransfer::reconstitute([
            'venture_id' => 1,
            'from_user_id' => 1,
            'to_user_id' => 2,
            'shares' => 10.0,
        ]);

        $this->assertTrue($transfer->status->isPending());
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $transfer = new ShareTransfer(
            ventureId: 1,
            fromUserId: 42,
            toUserId: 99,
            shares: 100.0,
            status: TransferStatus::approved(),
            id: 4,
            pricePerShare: 10.0,
            approvedAt: new DateTimeImmutable('2026-05-01 12:00:00'),
        );

        $arr = $transfer->toArray();

        $this->assertSame(4, $arr['id']);
        $this->assertSame(100.0, $arr['shares']);
        $this->assertSame(10.0, $arr['price_per_share']);
        $this->assertSame('approved', $arr['status']);
        $this->assertSame('2026-05-01 12:00:00', $arr['approved_at']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Entities\ShareTransfer;
use App\Domain\VentureBuilder\Entities\Shareholder;
use App\Domain\VentureBuilder\Repositories\ShareTransferRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Services\VentureShareTransferService;
use App\Domain\VentureBuilder\ValueObjects\ShareholderStatus;
use App\Domain\VentureBuilder\ValueObjects\TransferStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VentureShareTransferServiceTest extends TestCase
{
    private ShareTransferRepositoryInterface $transferRepo;
    private ShareholderRepositoryInterface $shareholderRepo;
    private VentureRepositoryInterface $ventureRepo;
    private VentureShareTransferService $service;

    protected function setUp(): void
    {
        $this->transferRepo = $this->createStub(ShareTransferRepositoryInterface::class);
        $this->shareholderRepo = $this->createStub(ShareholderRepositoryInterface::class);
        $this->ventureRepo = $this->createStub(VentureRepositoryInterface::class);
        $this->service = new VentureShareTransferService(
            $this->transferRepo,
            $this->shareholderRepo,
            $this->ventureRepo,
        );
    }

    #[Test]
    public function request_transfer_throws_when_not_an_active_shareholder(): void
    {
        $this->shareholderRepo->method('findActiveByUserAndVenture')->with(42, 1)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not an active shareholder');

        $this->service->requestTransfer(42, 1, 99, 100.0);
    }

    #[Test]
    public function request_transfer_throws_when_insufficient_shares(): void
    {
        $shareholder = new Shareholder(
            ventureId: 1, userId: 42, status: ShareholderStatus::active(), investmentId: 1,
            id: 5, sharesOwned: 50.0,
        );

        $this->shareholderRepo->method('findActiveByUserAndVenture')->with(42, 1)->willReturn($shareholder);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient shares');

        $this->service->requestTransfer(42, 1, 99, 100.0);
    }

    #[Test]
    public function reject_transfer_throws_when_not_pending(): void
    {
        $transfer = new ShareTransfer(ventureId: 1, fromUserId: 1, toUserId: 2, shares: 10.0, status: TransferStatus::approved(), id: 5);
        $this->transferRepo->method('findById')->with(5)->willReturn($transfer);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only pending transfers');

        $this->service->rejectTransfer(5, 'Not allowed');
    }

    #[Test]
    public function reject_transfer_throws_when_not_found(): void
    {
        $this->transferRepo->method('findById')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->rejectTransfer(999);
    }

    #[Test]
    public function approve_transfer_throws_when_not_pending(): void
    {
        $transfer = new ShareTransfer(ventureId: 1, fromUserId: 1, toUserId: 2, shares: 10.0, status: TransferStatus::rejected(), id: 5);
        $this->transferRepo->method('findById')->with(5)->willReturn($transfer);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only pending transfers');

        $this->service->approveTransfer(5);
    }
}

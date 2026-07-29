<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Entities\Investment;
use App\Domain\VentureBuilder\Entities\Shareholder;
use App\Domain\VentureBuilder\Entities\Venture;
use App\Domain\VentureBuilder\Repositories\InvestmentRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Services\VentureInvestmentService;
use App\Domain\VentureBuilder\Services\VentureLockInService;
use App\Domain\VentureBuilder\Services\VentureService;
use App\Domain\VentureBuilder\ValueObjects\InvestmentStatus;
use App\Domain\VentureBuilder\ValueObjects\ShareholderStatus;
use App\Domain\VentureBuilder\ValueObjects\VentureStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VentureInvestmentServiceTest extends TestCase
{
    private InvestmentRepositoryInterface $investmentRepo;
    private VentureRepositoryInterface $ventureRepo;
    private ShareholderRepositoryInterface $shareholderRepo;
    private VentureService $ventureService;
    private VentureLockInService $lockInService;
    private VentureInvestmentService $service;

    protected function setUp(): void
    {
        $this->investmentRepo = $this->createStub(InvestmentRepositoryInterface::class);
        $this->ventureRepo = $this->createStub(VentureRepositoryInterface::class);
        $this->shareholderRepo = $this->createStub(ShareholderRepositoryInterface::class);
        $this->ventureService = $this->createStub(VentureService::class);
        $this->lockInService = $this->createStub(VentureLockInService::class);
        $this->service = new VentureInvestmentService(
            $this->investmentRepo,
            $this->ventureRepo,
            $this->shareholderRepo,
            $this->ventureService,
            $this->lockInService,
        );
    }

    #[Test]
    public function register_shareholder_creates_from_confirmed_investment(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 42,
            amount: 5000.0,
            status: InvestmentStatus::confirmed(),
            id: 10,
            sharesAllocated: 250.0,
        );

        $venture = new Venture(
            title: 'Test',
            slug: 'test',
            status: VentureStatus::funded(),
            id: 1,
            totalRaised: 50000.0,
        );

        $shareholderRepo = $this->createMock(ShareholderRepositoryInterface::class);
        $shareholderRepo
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn(Shareholder $s) => new Shareholder(
                ventureId: $s->ventureId,
                userId: $s->userId,
                status: $s->status,
                investmentId: $s->investmentId,
                id: 1,
                totalInvestment: $s->totalInvestment,
                sharesOwned: $s->sharesOwned,
                equityPercentage: $s->equityPercentage,
                certificateNumber: $s->certificateNumber,
            ));

        $service = new VentureInvestmentService(
            $this->investmentRepo,
            $this->ventureRepo,
            $shareholderRepo,
            $this->ventureService,
            $this->lockInService,
        );

        $this->investmentRepo->method('findById')->with(10)->willReturn($investment);
        $this->investmentRepo->method('getTotalInvestedByUser')->with(42, 1)->willReturn(5000.0);
        $this->ventureRepo->method('findById')->with(1)->willReturn($venture);

        $result = $service->registerShareholder(10);

        $this->assertArrayHasKey('id', $result);
        $this->assertSame(42, $result['user_id']);
        $this->assertArrayHasKey('certificate_number', $result);
    }

    #[Test]
    public function register_shareholder_throws_for_non_confirmed_investment(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 42,
            amount: 100.0,
            status: InvestmentStatus::pending(),
            id: 10,
        );

        $this->investmentRepo->method('findById')->with(10)->willReturn($investment);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only confirmed investments');

        $this->service->registerShareholder(10);
    }

    #[Test]
    public function register_shareholder_throws_when_investment_not_found(): void
    {
        $this->investmentRepo->method('findById')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->registerShareholder(999);
    }

    #[Test]
    public function register_shareholder_calculates_equity_percentage(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 42,
            amount: 5000.0,
            status: InvestmentStatus::confirmed(),
            id: 10,
            sharesAllocated: 250.0,
        );

        $venture = new Venture(
            title: 'Test',
            slug: 'test',
            status: VentureStatus::funded(),
            id: 1,
            totalRaised: 50000.0,
        );

        $this->investmentRepo->method('findById')->with(10)->willReturn($investment);
        $this->investmentRepo->method('getTotalInvestedByUser')->with(42, 1)->willReturn(5000.0);
        $this->ventureRepo->method('findById')->with(1)->willReturn($venture);

        $this->shareholderRepo
            ->method('save')
            ->willReturnCallback(fn(Shareholder $s) => $s);

        $result = $this->service->registerShareholder(10);

        $this->assertSame(10.0, $result['equity_percentage']);
    }

    #[Test]
    public function register_shareholder_generates_certificate_number_when_not_provided(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 42,
            amount: 1000.0,
            status: InvestmentStatus::confirmed(),
            id: 10,
            sharesAllocated: 50.0,
        );

        $venture = new Venture(
            title: 'Test',
            slug: 'test',
            status: VentureStatus::funded(),
            id: 1,
            totalRaised: 10000.0,
        );

        $this->investmentRepo->method('findById')->with(10)->willReturn($investment);
        $this->investmentRepo->method('getTotalInvestedByUser')->with(42, 1)->willReturn(1000.0);
        $this->ventureRepo->method('findById')->with(1)->willReturn($venture);

        $this->shareholderRepo
            ->method('save')
            ->willReturnCallback(fn(Shareholder $s) => new Shareholder(
                ventureId: $s->ventureId,
                userId: $s->userId,
                status: $s->status,
                investmentId: $s->investmentId,
                id: 1,
                totalInvestment: $s->totalInvestment,
                sharesOwned: $s->sharesOwned,
                equityPercentage: $s->equityPercentage,
                certificateNumber: $s->certificateNumber,
            ));

        $result = $this->service->registerShareholder(10);

        $this->assertStringStartsWith('SH-', $result['certificate_number']);
    }

    #[Test]
    public function register_shareholder_uses_provided_certificate_number(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 42,
            amount: 1000.0,
            status: InvestmentStatus::confirmed(),
            id: 10,
            sharesAllocated: 50.0,
        );

        $venture = new Venture(
            title: 'Test',
            slug: 'test',
            status: VentureStatus::funded(),
            id: 1,
            totalRaised: 10000.0,
        );

        $this->investmentRepo->method('findById')->with(10)->willReturn($investment);
        $this->investmentRepo->method('getTotalInvestedByUser')->with(42, 1)->willReturn(1000.0);
        $this->ventureRepo->method('findById')->with(1)->willReturn($venture);

        $this->shareholderRepo
            ->method('save')
            ->willReturnCallback(fn(Shareholder $s) => $s);

        $result = $this->service->registerShareholder(10, 'CERT-001');

        $this->assertSame('CERT-001', $result['certificate_number']);
    }
}

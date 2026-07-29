<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Entities\Dividend;
use App\Domain\VentureBuilder\Entities\Shareholder;
use App\Domain\VentureBuilder\Entities\Venture;
use App\Domain\VentureBuilder\Repositories\DividendRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Services\VentureDividendService;
use App\Domain\VentureBuilder\ValueObjects\DividendStatus;
use App\Domain\VentureBuilder\ValueObjects\ShareholderStatus;
use App\Domain\VentureBuilder\ValueObjects\VentureStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VentureDividendServiceTest extends TestCase
{
    private DividendRepositoryInterface $dividendRepo;
    private ShareholderRepositoryInterface $shareholderRepo;
    private VentureRepositoryInterface $ventureRepo;
    private VentureDividendService $service;

    protected function setUp(): void
    {
        $this->dividendRepo = $this->createStub(DividendRepositoryInterface::class);
        $this->shareholderRepo = $this->createStub(ShareholderRepositoryInterface::class);
        $this->ventureRepo = $this->createStub(VentureRepositoryInterface::class);
        $this->service = new VentureDividendService(
            $this->dividendRepo,
            $this->shareholderRepo,
            $this->ventureRepo,
        );
    }

    #[Test]
    public function declare_dividend_throws_when_venture_not_active(): void
    {
        $venture = new Venture(title: 'T', slug: 't', status: VentureStatus::funding(), id: 1);
        $this->ventureRepo->method('findById')->with(1)->willReturn($venture);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only active ventures');

        $this->service->declareDividend(1, 'Q1 2026', 10000.0);
    }

    #[Test]
    public function declare_dividend_throws_when_no_active_shareholders(): void
    {
        $venture = new Venture(title: 'T', slug: 't', status: VentureStatus::active(), id: 1);
        $this->ventureRepo->method('findById')->with(1)->willReturn($venture);
        $this->shareholderRepo->method('findActiveByVenture')->with(1)->willReturn([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No active shareholders');

        $this->service->declareDividend(1, 'Q1 2026', 10000.0);
    }

    #[Test]
    public function declare_dividend_throws_when_venture_not_found(): void
    {
        $this->ventureRepo->method('findById')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->declareDividend(999, 'Q1 2026', 10000.0);
    }

    #[Test]
    public function process_dividend_throws_when_not_declared(): void
    {
        $dividend = new Dividend(ventureId: 1, shareholderId: 1, amount: 100.0, status: DividendStatus::paid(), id: 5);
        $this->dividendRepo->method('findById')->with(5)->willReturn($dividend);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only declared dividends');

        $this->service->processDividend(5);
    }

    #[Test]
    public function process_dividend_throws_when_dividend_not_found(): void
    {
        $this->dividendRepo->method('findById')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->processDividend(999);
    }
}

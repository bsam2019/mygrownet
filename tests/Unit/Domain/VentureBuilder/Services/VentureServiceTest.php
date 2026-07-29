<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Entities\Venture;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Services\VentureService;
use App\Domain\VentureBuilder\ValueObjects\VentureStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VentureServiceTest extends TestCase
{
    private VentureRepositoryInterface $ventureRepo;
    private VentureService $service;

    protected function setUp(): void
    {
        $this->ventureRepo = $this->createStub(VentureRepositoryInterface::class);
        $this->service = new VentureService($this->ventureRepo);
    }

    #[Test]
    public function calculate_shares_uses_share_price_when_provided(): void
    {
        $ventureData = ['share_price' => 10.0];
        $shares = $this->service->calculateShares(500.0, $ventureData);

        $this->assertSame(50.0, $shares);
    }

    #[Test]
    public function calculate_shares_returns_floor_value(): void
    {
        $ventureData = ['share_price' => 3.0];
        $shares = $this->service->calculateShares(10.0, $ventureData);

        $this->assertSame(3.0, $shares);
    }

    #[Test]
    public function calculate_shares_uses_default_when_no_share_price(): void
    {
        $ventureData = [];
        $shares = $this->service->calculateShares(250.0, $ventureData);

        $this->assertSame(2.0, $shares);
    }

    #[Test]
    public function calculate_shares_uses_default_when_share_price_is_zero(): void
    {
        $ventureData = ['share_price' => 0];
        $shares = $this->service->calculateShares(250.0, $ventureData);

        $this->assertSame(2.0, $shares);
    }
}

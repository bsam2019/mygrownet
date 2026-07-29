<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Repositories\InvestmentRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Services\VentureCacheService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VentureCacheServiceTest extends TestCase
{
    private VentureRepositoryInterface $ventureRepo;
    private InvestmentRepositoryInterface $investmentRepo;
    private VentureCacheService $service;

    protected function setUp(): void
    {
        $this->ventureRepo = $this->createStub(VentureRepositoryInterface::class);
        $this->investmentRepo = $this->createStub(InvestmentRepositoryInterface::class);
        $this->service = new VentureCacheService($this->ventureRepo, $this->investmentRepo);
    }

    #[Test]
    public function can_be_instantiated(): void
    {
        $this->assertInstanceOf(VentureCacheService::class, $this->service);
    }
}

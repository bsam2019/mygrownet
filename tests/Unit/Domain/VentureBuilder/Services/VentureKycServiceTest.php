<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Repositories\InvestmentRepositoryInterface;
use App\Domain\VentureBuilder\Services\VentureKycService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VentureKycServiceTest extends TestCase
{
    private InvestmentRepositoryInterface $investmentRepo;
    private VentureKycService $service;

    protected function setUp(): void
    {
        $this->investmentRepo = $this->createStub(InvestmentRepositoryInterface::class);
        $this->service = new VentureKycService($this->investmentRepo);
    }

    #[Test]
    public function requires_kyc_returns_true_when_total_invested_exceeds_threshold(): void
    {
        $this->investmentRepo->method('getTotalInvestedByUser')->willReturn(15000.0);

        $this->assertTrue($this->service->requiresKyc(1));
    }

    #[Test]
    public function requires_kyc_returns_true_when_tier_level_high(): void
    {
        $this->investmentRepo->method('getTotalInvestedByUser')->willReturn(5000.0);

        $this->assertTrue($this->service->requiresKyc(1, ['investment_tier' => ['level' => 3]]));
    }

    #[Test]
    public function requires_kyc_returns_false_when_below_thresholds(): void
    {
        $this->investmentRepo->method('getTotalInvestedByUser')->willReturn(5000.0);

        $this->assertFalse($this->service->requiresKyc(1, ['investment_tier' => ['level' => 2]]));
    }

    #[Test]
    public function requires_kyc_returns_false_when_no_user_data(): void
    {
        $this->investmentRepo->method('getTotalInvestedByUser')->willReturn(5000.0);

        $this->assertFalse($this->service->requiresKyc(1));
    }

    #[Test]
    public function is_kyc_verified_returns_true_when_id_verified_at_present(): void
    {
        $this->assertTrue($this->service->isKycVerified(['id_verified_at' => '2026-01-01']));
    }

    #[Test]
    public function is_kyc_verified_returns_false_when_not_verified(): void
    {
        $this->assertFalse($this->service->isKycVerified([]));
        $this->assertFalse($this->service->isKycVerified(null));
    }

    #[Test]
    public function can_invest_returns_empty_when_no_issues(): void
    {
        $this->investmentRepo->method('getTotalInvestedByUser')->willReturn(5000.0);

        $issues = $this->service->canInvest(1, 500.0, ['investment_tier' => ['level' => 1], 'id_verified_at' => '2026-01-01']);

        $this->assertEmpty($issues);
    }

    #[Test]
    public function can_invest_returns_issue_when_amount_above_kyc_threshold_and_not_verified(): void
    {
        $this->investmentRepo->method('getTotalInvestedByUser')->willReturn(5000.0);

        $issues = $this->service->canInvest(1, 15000.0, ['investment_tier' => ['level' => 1]]);

        $this->assertContains('KYC verification required for investments of K10,000 or more.', $issues);
    }

    #[Test]
    public function can_invest_returns_issue_when_profile_requires_kyc_and_not_verified(): void
    {
        $this->investmentRepo->method('getTotalInvestedByUser')->willReturn(15000.0);

        $issues = $this->service->canInvest(1, 500.0, ['investment_tier' => ['level' => 1]]);

        $this->assertContains('KYC verification required based on your investment profile.', $issues);
    }

    #[Test]
    public function can_invest_returns_multiple_issues(): void
    {
        $this->investmentRepo->method('getTotalInvestedByUser')->willReturn(15000.0);

        $issues = $this->service->canInvest(1, 15000.0, null);

        $this->assertCount(2, $issues);
    }
}

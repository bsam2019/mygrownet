<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\Entities\Shareholder;
use App\Domain\VentureBuilder\ValueObjects\ShareholderStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ShareholderTest extends TestCase
{
    #[Test]
    public function can_be_created_with_minimal_data(): void
    {
        $shareholder = new Shareholder(
            ventureId: 1,
            userId: 42,
            status: ShareholderStatus::active(),
            investmentId: 5,
        );

        $this->assertSame(1, $shareholder->ventureId);
        $this->assertSame(42, $shareholder->userId);
        $this->assertTrue($shareholder->status->isActive());
        $this->assertSame(5, $shareholder->investmentId);
        $this->assertNull($shareholder->id);
    }

    #[Test]
    public function is_active_delegates_to_status(): void
    {
        $active = new Shareholder(ventureId: 1, userId: 1, status: ShareholderStatus::active(), investmentId: 1);
        $inactive = new Shareholder(ventureId: 1, userId: 1, status: ShareholderStatus::inactive(), investmentId: 1);

        $this->assertTrue($active->isActive());
        $this->assertFalse($inactive->isActive());
    }

    #[Test]
    public function has_signed_agreement_returns_true_when_signed(): void
    {
        $shareholder = new Shareholder(
            ventureId: 1, userId: 1, status: ShareholderStatus::active(), investmentId: 1,
            agreementSigned: true, agreementSignedAt: new DateTimeImmutable('2026-01-01'),
        );

        $this->assertTrue($shareholder->hasSignedAgreement());
    }

    #[Test]
    public function has_signed_agreement_returns_false_when_not_signed(): void
    {
        $shareholder = new Shareholder(
            ventureId: 1, userId: 1, status: ShareholderStatus::active(), investmentId: 1,
        );

        $this->assertFalse($shareholder->hasSignedAgreement());
    }

    #[Test]
    public function can_be_reconstituted_from_array(): void
    {
        $data = [
            'id' => 3,
            'venture_id' => 1,
            'user_id' => 42,
            'investment_id' => 5,
            'status' => 'active',
            'total_investment' => 10000.0,
            'shares_owned' => 500.0,
            'equity_percentage' => 10.0,
            'certificate_number' => 'SH-ABC123',
        ];

        $shareholder = Shareholder::reconstitute($data);

        $this->assertSame(3, $shareholder->id);
        $this->assertSame(10000.0, $shareholder->totalInvestment);
        $this->assertSame(500.0, $shareholder->sharesOwned);
        $this->assertSame(10.0, $shareholder->equityPercentage);
        $this->assertSame('SH-ABC123', $shareholder->certificateNumber);
    }

    #[Test]
    public function reconstitute_defaults_status_to_active(): void
    {
        $shareholder = Shareholder::reconstitute([
            'venture_id' => 1,
            'user_id' => 1,
            'investment_id' => 1,
        ]);

        $this->assertTrue($shareholder->status->isActive());
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $shareholder = new Shareholder(
            ventureId: 1,
            userId: 42,
            status: ShareholderStatus::active(),
            investmentId: 5,
            id: 3,
            totalInvestment: 10000.0,
            sharesOwned: 500.0,
            createdAt: new DateTimeImmutable('2026-02-01 10:00:00'),
        );

        $arr = $shareholder->toArray();

        $this->assertSame(3, $arr['id']);
        $this->assertSame(1, $arr['venture_id']);
        $this->assertSame(10000.0, $arr['total_investment']);
        $this->assertSame('active', $arr['status']);
        $this->assertSame('2026-02-01 10:00:00', $arr['created_at']);
    }
}

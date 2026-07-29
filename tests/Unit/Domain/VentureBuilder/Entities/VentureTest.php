<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\Entities\Venture;
use App\Domain\VentureBuilder\ValueObjects\VentureStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VentureTest extends TestCase
{
    #[Test]
    public function can_be_created_with_minimal_data(): void
    {
        $venture = new Venture(
            title: 'Test Venture',
            slug: 'test-venture',
            status: VentureStatus::draft(),
        );

        $this->assertSame('Test Venture', $venture->title);
        $this->assertSame('test-venture', $venture->slug);
        $this->assertTrue($venture->status->isDraft());
        $this->assertNull($venture->id);
    }

    #[Test]
    public function can_be_reconstituted_from_array(): void
    {
        $data = [
            'id' => 5,
            'title' => 'Green Energy',
            'slug' => 'green-energy',
            'status' => 'funding',
            'category_id' => 3,
            'description' => 'A green energy venture',
            'funding_target' => 100000.0,
            'minimum_investment' => 100.0,
            'maximum_investment' => 50000.0,
            'share_price' => 10.0,
            'total_raised' => 25000.0,
            'investor_count' => 12,
            'funding_start_date' => '2026-01-01 00:00:00',
            'funding_end_date' => '2026-06-30 23:59:59',
            'is_featured' => true,
            'views_count' => 150,
            'created_by' => 42,
        ];

        $venture = Venture::reconstitute($data);

        $this->assertSame(5, $venture->id);
        $this->assertSame('Green Energy', $venture->title);
        $this->assertTrue($venture->status->isFunding());
        $this->assertSame(3, $venture->categoryId);
        $this->assertSame(100000.0, $venture->fundingTarget);
        $this->assertSame(100.0, $venture->minimumInvestment);
        $this->assertSame(50000.0, $venture->maximumInvestment);
        $this->assertSame(10.0, $venture->sharePrice);
        $this->assertSame(25000.0, $venture->totalRaised);
        $this->assertSame(12, $venture->investorCount);
        $this->assertTrue($venture->isFeatured);
        $this->assertSame(150, $venture->viewsCount);
        $this->assertSame(42, $venture->createdBy);
        $this->assertNull($venture->businessModel);
    }

    #[Test]
    public function reconstitute_defaults_status_to_draft(): void
    {
        $venture = Venture::reconstitute([
            'title' => 'Test',
            'slug' => 'test',
        ]);

        $this->assertTrue($venture->status->isDraft());
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $venture = new Venture(
            title: 'Test Venture',
            slug: 'test-venture',
            status: VentureStatus::funding(),
            id: 1,
            categoryId: 2,
            description: 'A test',
            fundingTarget: 50000.0,
            totalRaised: 10000.0,
            createdAt: new DateTimeImmutable('2026-01-15 10:00:00'),
        );

        $arr = $venture->toArray();

        $this->assertSame(1, $arr['id']);
        $this->assertSame('Test Venture', $arr['title']);
        $this->assertSame('funding', $arr['status']);
        $this->assertSame(50000.0, $arr['funding_target']);
        $this->assertSame(10000.0, $arr['total_raised']);
        $this->assertSame('2026-01-15 10:00:00', $arr['created_at']);
        $this->assertNull($arr['business_model']);
    }

    #[Test]
    public function get_funding_progress_returns_zero_when_no_target(): void
    {
        $venture = new Venture(title: 'T', slug: 't', status: VentureStatus::funding());
        $this->assertSame(0.0, $venture->getFundingProgressPercentage());
    }

    #[Test]
    public function get_funding_progress_returns_percentage(): void
    {
        $venture = new Venture(
            title: 'T',
            slug: 't',
            status: VentureStatus::funding(),
            fundingTarget: 100000.0,
            totalRaised: 25000.0,
        );

        $this->assertSame(25.0, $venture->getFundingProgressPercentage());
    }

    #[Test]
    public function get_funding_progress_caps_at_100(): void
    {
        $venture = new Venture(
            title: 'T',
            slug: 't',
            status: VentureStatus::funding(),
            fundingTarget: 100000.0,
            totalRaised: 200000.0,
        );

        $this->assertSame(100.0, $venture->getFundingProgressPercentage());
    }

    #[Test]
    public function is_funding_open_returns_false_when_not_funding(): void
    {
        $venture = new Venture(title: 'T', slug: 't', status: VentureStatus::draft());
        $this->assertFalse($venture->isFundingOpen());
    }

    #[Test]
    public function is_funding_open_returns_false_when_end_date_passed(): void
    {
        $venture = new Venture(
            title: 'T',
            slug: 't',
            status: VentureStatus::funding(),
            fundingEndDate: new DateTimeImmutable('-1 day'),
        );

        $this->assertFalse($venture->isFundingOpen());
    }

    #[Test]
    public function is_funding_open_returns_true_when_funding_and_no_end_date(): void
    {
        $venture = new Venture(title: 'T', slug: 't', status: VentureStatus::funding());
        $this->assertTrue($venture->isFundingOpen());
    }

    #[Test]
    public function can_accept_investments_when_open_and_target_not_reached(): void
    {
        $venture = new Venture(
            title: 'T',
            slug: 't',
            status: VentureStatus::funding(),
            fundingTarget: 100000.0,
            totalRaised: 50000.0,
        );

        $this->assertTrue($venture->canAcceptInvestments());
    }

    #[Test]
    public function cannot_accept_investments_when_target_reached(): void
    {
        $venture = new Venture(
            title: 'T',
            slug: 't',
            status: VentureStatus::funding(),
            fundingTarget: 100000.0,
            totalRaised: 100000.0,
        );

        $this->assertFalse($venture->canAcceptInvestments());
    }
}

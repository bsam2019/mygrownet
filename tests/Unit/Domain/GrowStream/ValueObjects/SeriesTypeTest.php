<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\SeriesType;
use PHPUnit\Framework\TestCase;

class SeriesTypeTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('course', SeriesType::Course->value);
        $this->assertEquals('show', SeriesType::Show->value);
        $this->assertEquals('documentary', SeriesType::Documentary->value);
        $this->assertEquals('workshop_series', SeriesType::WorkshopSeries->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('Course', SeriesType::Course->label());
        $this->assertEquals('Show', SeriesType::Show->label());
        $this->assertEquals('Documentary', SeriesType::Documentary->label());
        $this->assertEquals('Workshop Series', SeriesType::WorkshopSeries->label());
    }

    public function test_color_returns_expected(): void
    {
        $this->assertEquals('#22c55e', SeriesType::Course->color());
        $this->assertEquals('#3b82f6', SeriesType::Show->color());
        $this->assertEquals('#f59e0b', SeriesType::Documentary->color());
        $this->assertEquals('#8b5cf6', SeriesType::WorkshopSeries->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(SeriesType::WorkshopSeries, SeriesType::fromString('workshop_series'));
        $this->assertSame(SeriesType::WorkshopSeries, SeriesType::fromString('WORKSHOP_SERIES'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SeriesType::fromString('miniseries');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(SeriesType::Show, SeriesType::tryFrom('show'));
        $this->assertNull(SeriesType::tryFrom('podcast'));
    }

    public function test_all_returns_all_cases(): void
    {
        $all = SeriesType::all();
        $this->assertCount(4, $all);
    }
}

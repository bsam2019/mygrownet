<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\ContentRating;
use PHPUnit\Framework\TestCase;

class ContentRatingTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('G', ContentRating::G->value);
        $this->assertEquals('PG', ContentRating::PG->value);
        $this->assertEquals('PG-13', ContentRating::PG13->value);
        $this->assertEquals('R', ContentRating::R->value);
        $this->assertEquals('NR', ContentRating::NR->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('General Audiences', ContentRating::G->label());
        $this->assertEquals('Parental Guidance', ContentRating::PG->label());
        $this->assertEquals('Parents Strongly Cautioned', ContentRating::PG13->label());
        $this->assertEquals('Restricted', ContentRating::R->label());
        $this->assertEquals('Not Rated', ContentRating::NR->label());
    }

    public function test_color_returns_expected(): void
    {
        $this->assertEquals('#22c55e', ContentRating::G->color());
        $this->assertEquals('#3b82f6', ContentRating::PG->color());
        $this->assertEquals('#f59e0b', ContentRating::PG13->color());
        $this->assertEquals('#ef4444', ContentRating::R->color());
        $this->assertEquals('#6b7280', ContentRating::NR->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(ContentRating::PG13, ContentRating::fromString('PG-13'));
        $this->assertSame(ContentRating::PG13, ContentRating::fromString('pg-13'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ContentRating::fromString('XXX');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(ContentRating::R, ContentRating::tryFrom('R'));
        $this->assertNull(ContentRating::tryFrom('X'));
    }

    public function test_all_returns_all_cases(): void
    {
        $all = ContentRating::all();
        $this->assertCount(5, $all);
        $this->assertEquals('General Audiences', $all[0]['label']);
    }
}

<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\ContentType;
use PHPUnit\Framework\TestCase;

class ContentTypeTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('movie', ContentType::Movie->value);
        $this->assertEquals('series', ContentType::Series->value);
        $this->assertEquals('episode', ContentType::Episode->value);
        $this->assertEquals('lesson', ContentType::Lesson->value);
        $this->assertEquals('short', ContentType::Short->value);
        $this->assertEquals('workshop', ContentType::Workshop->value);
        $this->assertEquals('webinar', ContentType::Webinar->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('Movie', ContentType::Movie->label());
        $this->assertEquals('Series', ContentType::Series->label());
        $this->assertEquals('Episode', ContentType::Episode->label());
        $this->assertEquals('Lesson', ContentType::Lesson->label());
        $this->assertEquals('Short', ContentType::Short->label());
        $this->assertEquals('Workshop', ContentType::Workshop->label());
        $this->assertEquals('Webinar', ContentType::Webinar->label());
    }

    public function test_color_returns_expected(): void
    {
        $this->assertEquals('#ef4444', ContentType::Movie->color());
        $this->assertEquals('#3b82f6', ContentType::Series->color());
        $this->assertEquals('#14b8a6', ContentType::Webinar->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(ContentType::Lesson, ContentType::fromString('lesson'));
        $this->assertSame(ContentType::Lesson, ContentType::fromString('LESSON'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ContentType::fromString('clip');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(ContentType::Short, ContentType::tryFrom('short'));
        $this->assertNull(ContentType::tryFrom('feature'));
    }

    public function test_all_returns_all_cases(): void
    {
        $all = ContentType::all();
        $this->assertCount(7, $all);
    }
}

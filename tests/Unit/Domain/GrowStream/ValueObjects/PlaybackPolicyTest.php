<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\PlaybackPolicy;
use PHPUnit\Framework\TestCase;

class PlaybackPolicyTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('public', PlaybackPolicy::Public->value);
        $this->assertEquals('signed', PlaybackPolicy::Signed->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('Public', PlaybackPolicy::Public->label());
        $this->assertEquals('Signed URL', PlaybackPolicy::Signed->label());
    }

    public function test_color_returns_expected(): void
    {
        $this->assertEquals('#22c55e', PlaybackPolicy::Public->color());
        $this->assertEquals('#3b82f6', PlaybackPolicy::Signed->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(PlaybackPolicy::Signed, PlaybackPolicy::fromString('signed'));
        $this->assertSame(PlaybackPolicy::Signed, PlaybackPolicy::fromString('SIGNED'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PlaybackPolicy::fromString('private');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(PlaybackPolicy::Public, PlaybackPolicy::tryFrom('public'));
        $this->assertNull(PlaybackPolicy::tryFrom('restricted'));
    }

    public function test_all_returns_all_cases(): void
    {
        $all = PlaybackPolicy::all();
        $this->assertCount(2, $all);
    }
}

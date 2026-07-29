<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\VideoProvider;
use PHPUnit\Framework\TestCase;

class VideoProviderTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('digitalocean', VideoProvider::DigitalOcean->value);
        $this->assertEquals('cloudflare', VideoProvider::Cloudflare->value);
        $this->assertEquals('local', VideoProvider::Local->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('DigitalOcean Spaces', VideoProvider::DigitalOcean->label());
        $this->assertEquals('Cloudflare Stream', VideoProvider::Cloudflare->label());
        $this->assertEquals('Local Storage', VideoProvider::Local->label());
    }

    public function test_color_returns_expected(): void
    {
        $this->assertEquals('#0080ff', VideoProvider::DigitalOcean->color());
        $this->assertEquals('#f38020', VideoProvider::Cloudflare->color());
        $this->assertEquals('#6b7280', VideoProvider::Local->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(VideoProvider::Cloudflare, VideoProvider::fromString('cloudflare'));
        $this->assertSame(VideoProvider::Cloudflare, VideoProvider::fromString('CLOUDFLARE'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        VideoProvider::fromString('aws');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(VideoProvider::Local, VideoProvider::tryFrom('local'));
        $this->assertNull(VideoProvider::tryFrom('vimeo'));
    }

    public function test_all_returns_all_cases(): void
    {
        $all = VideoProvider::all();
        $this->assertCount(3, $all);
    }
}

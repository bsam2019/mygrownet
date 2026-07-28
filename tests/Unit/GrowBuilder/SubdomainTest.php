<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SubdomainTest extends TestCase
{
    public function test_valid_subdomain(): void
    {
        $sub = Subdomain::fromString('my-cool-site');
        $this->assertEquals('my-cool-site', $sub->value());
        $this->assertTrue($sub->equals(Subdomain::fromString('my-cool-site')));
    }

    public function test_trim_and_lowercase(): void
    {
        $sub = Subdomain::fromString('  MySite  ');
        $this->assertEquals('mysite', $sub->value());
    }

    public function test_get_full_domain(): void
    {
        $sub = Subdomain::fromString('mysite');
        $this->assertEquals('mysite.mygrownet.com', $sub->getFullDomain());
    }

    public function test_too_short(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Subdomain::fromString('ab');
    }

    public function test_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Subdomain::fromString(str_repeat('a', 64));
    }

    public function test_invalid_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Subdomain::fromString('my site!');
    }

    public function test_starts_with_hyphen(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Subdomain::fromString('-mysite');
    }

    public function test_ends_with_hyphen(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Subdomain::fromString('mysite-');
    }

    public function test_reserved_word_www(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Subdomain::fromString('www');
    }

    public function test_reserved_word_api(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Subdomain::fromString('api');
    }

    public function test_reserved_word_admin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Subdomain::fromString('admin');
    }

    public function test_single_character_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Subdomain::fromString('a');
    }

    public function test_two_character_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Subdomain::fromString('ab');
    }
}

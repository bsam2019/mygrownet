<?php

namespace Tests\Unit\Domain\Ubumi\ValueObjects;

use App\Domain\Ubumi\ValueObjects\Slug;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SlugTest extends TestCase
{
    #[Test]
    public function fromString_creates_slug_from_simple_text()
    {
        $slug = Slug::fromString('hello');
        $this->assertInstanceOf(Slug::class, $slug);
        $this->assertEquals('hello', $slug->value());
    }

    #[Test]
    public function fromString_converts_to_lowercase()
    {
        $slug = Slug::fromString('Hello World');
        $this->assertEquals('hello-world', $slug->value());
    }

    #[Test]
    public function fromString_handles_special_characters()
    {
        $slug = Slug::fromString('Hello! World 2024');
        $this->assertEquals('hello-world-2024', $slug->value());
    }

    #[Test]
    public function fromString_throws_for_empty_text()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create slug from empty text');
        Slug::fromString('');
    }

    #[Test]
    public function fromString_throws_for_whitespace_only()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create slug from empty text');
        Slug::fromString('   ');
    }

    #[Test]
    public function fromStringWithSuffix_creates_slug_with_numeric_suffix()
    {
        $slug = Slug::fromStringWithSuffix('Hello World', 2);
        $this->assertEquals('hello-world-2', $slug->value());
    }

    #[Test]
    public function fromStringWithSuffix_throws_for_empty_text()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create slug from empty text');
        Slug::fromStringWithSuffix('', 1);
    }

    #[Test]
    public function value_returns_slug_string()
    {
        $slug = Slug::fromString('Test Slug');
        $this->assertEquals('test-slug', $slug->value());
    }

    #[Test]
    public function toString_returns_slug_string()
    {
        $slug = Slug::fromString('Test Slug');
        $this->assertEquals('test-slug', (string) $slug);
    }
}

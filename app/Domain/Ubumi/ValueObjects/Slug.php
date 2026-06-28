<?php

namespace App\Domain\Ubumi\ValueObjects;

use Illuminate\Support\Str;

/**
 * Slug Value Object
 * 
 * Represents a URL-friendly slug
 */
class Slug
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $text): self
    {
        $slug = Str::slug($text);
        
        if (empty($slug)) {
            throw new \InvalidArgumentException('Cannot create slug from empty text');
        }
        
        return new self($slug);
    }

    public static function fromStringWithSuffix(string $text, int $suffix): self
    {
        $baseSlug = Str::slug($text);
        
        if (empty($baseSlug)) {
            throw new \InvalidArgumentException('Cannot create slug from empty text');
        }
        
        return new self($baseSlug . '-' . $suffix);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

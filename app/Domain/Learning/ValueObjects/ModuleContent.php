<?php

namespace App\Domain\Learning\ValueObjects;

class ModuleContent
{
    private function __construct(private string $value)
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('Module content cannot be empty');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function wordCount(): int
    {
        return str_word_count(strip_tags($this->value));
    }

    public function estimatedReadingMinutes(): int
    {
        // Average reading speed: 200 words per minute
        return max(1, (int) ceil($this->wordCount() / 200));
    }
}

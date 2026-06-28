<?php

namespace App\Domain\Learning\ValueObjects;

class ContentType
{
    private const VALID_TYPES = ['text', 'video', 'pdf', 'audio'];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_TYPES)) {
            throw new \InvalidArgumentException(
                "Invalid content type. Must be one of: " . implode(', ', self::VALID_TYPES)
            );
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function text(): self
    {
        return new self('text');
    }

    public static function video(): self
    {
        return new self('video');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isText(): bool
    {
        return $this->value === 'text';
    }

    public function isVideo(): bool
    {
        return $this->value === 'video';
    }
}

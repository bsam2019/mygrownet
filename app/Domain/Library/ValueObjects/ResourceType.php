<?php

namespace App\Domain\Library\ValueObjects;

use InvalidArgumentException;

final class ResourceType
{
    private const VALID_TYPES = ['pdf', 'video', 'article', 'course', 'tool', 'template'];

    private function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, self::VALID_TYPES)) {
            throw new InvalidArgumentException("Invalid resource type: {$value}");
        }
    }

    public static function fromString(string $value): self
    {
        return new self(strtolower($value));
    }

    public static function pdf(): self
    {
        return new self('pdf');
    }

    public static function video(): self
    {
        return new self('video');
    }

    public static function article(): self
    {
        return new self('article');
    }

    public static function course(): self
    {
        return new self('course');
    }

    public static function tool(): self
    {
        return new self('tool');
    }

    public static function template(): self
    {
        return new self('template');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match($this->value) {
            'pdf' => 'PDF Document',
            'video' => 'Video',
            'article' => 'Article',
            'course' => 'Online Course',
            'tool' => 'Tool/Template',
            'template' => 'Template',
        };
    }

    public function equals(ResourceType $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

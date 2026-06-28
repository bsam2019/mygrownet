<?php

namespace App\Domain\Storage\ValueObjects;

class MimeType
{
    private function __construct(private string $value)
    {
        $this->validate();
    }

    public static function fromString(string $mimeType): self
    {
        return new self($mimeType);
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('MIME type cannot be empty');
        }

        // Basic MIME type format validation
        if (!preg_match('/^[a-z]+\/[a-z0-9\-\+\.]+$/i', $this->value)) {
            throw new \InvalidArgumentException("Invalid MIME type format: {$this->value}");
        }
    }

    public function matches(string $pattern): bool
    {
        return fnmatch($pattern, $this->value);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->value, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->value, 'video/');
    }

    public function isAudio(): bool
    {
        return str_starts_with($this->value, 'audio/');
    }

    public function isDocument(): bool
    {
        return in_array($this->value, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

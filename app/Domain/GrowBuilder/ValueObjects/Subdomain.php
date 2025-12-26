<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\ValueObjects;

use InvalidArgumentException;

final class Subdomain
{
    private const MIN_LENGTH = 3;
    private const MAX_LENGTH = 63;
    private const PATTERN = '/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/';
    private const RESERVED = ['www', 'api', 'admin', 'app', 'mail', 'ftp', 'cdn', 'static', 'assets', 'images'];

    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));

        if (strlen($value) < self::MIN_LENGTH) {
            throw new InvalidArgumentException('Subdomain must be at least ' . self::MIN_LENGTH . ' characters');
        }

        if (strlen($value) > self::MAX_LENGTH) {
            throw new InvalidArgumentException('Subdomain must not exceed ' . self::MAX_LENGTH . ' characters');
        }

        if (!preg_match(self::PATTERN, $value)) {
            throw new InvalidArgumentException('Subdomain can only contain lowercase letters, numbers, and hyphens');
        }

        if (in_array($value, self::RESERVED, true)) {
            throw new InvalidArgumentException('This subdomain is reserved');
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getFullDomain(): string
    {
        return $this->value . '.mygrownet.com';
    }
}

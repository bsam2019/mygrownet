<?php

namespace App\Domain\Storage\ValueObjects;

class FileSize
{
    private function __construct(private int $bytes)
    {
        if ($bytes < 0) {
            throw new \InvalidArgumentException('File size cannot be negative');
        }
    }

    public static function fromBytes(int $bytes): self
    {
        return new self($bytes);
    }

    public static function fromMegabytes(float $mb): self
    {
        return new self((int)($mb * 1024 * 1024));
    }

    public static function fromGigabytes(float $gb): self
    {
        return new self((int)($gb * 1024 * 1024 * 1024));
    }

    public function toBytes(): int
    {
        return $this->bytes;
    }

    public function toMegabytes(): float
    {
        return $this->bytes / (1024 * 1024);
    }

    public function toGigabytes(): float
    {
        return $this->bytes / (1024 * 1024 * 1024);
    }

    public function add(FileSize $other): self
    {
        return new self($this->bytes + $other->bytes);
    }

    public function subtract(FileSize $other): self
    {
        return new self(max(0, $this->bytes - $other->bytes));
    }

    public function isGreaterThan(FileSize $other): bool
    {
        return $this->bytes > $other->bytes;
    }

    public function isLessThan(FileSize $other): bool
    {
        return $this->bytes < $other->bytes;
    }

    public function equals(FileSize $other): bool
    {
        return $this->bytes === $other->bytes;
    }

    public function format(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->bytes;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

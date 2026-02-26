<?php

namespace App\Domain\Storage\ValueObjects;

class StorageQuota
{
    private function __construct(
        private FileSize $limit,
        private FileSize $used
    ) {}

    public static function create(FileSize $limit, FileSize $used): self
    {
        return new self($limit, $used);
    }

    public function canAccommodate(FileSize $fileSize): bool
    {
        $newTotal = $this->used->add($fileSize);
        return !$newTotal->isGreaterThan($this->limit);
    }

    public function getRemaining(): FileSize
    {
        $remaining = $this->limit->toBytes() - $this->used->toBytes();
        return FileSize::fromBytes(max(0, $remaining));
    }

    public function getPercentUsed(): float
    {
        if ($this->limit->toBytes() === 0) {
            return 0;
        }

        return ($this->used->toBytes() / $this->limit->toBytes()) * 100;
    }

    public function isNearLimit(float $threshold = 80.0): bool
    {
        return $this->getPercentUsed() >= $threshold;
    }

    public function isExceeded(): bool
    {
        return $this->used->isGreaterThan($this->limit);
    }

    public function getLimit(): FileSize
    {
        return $this->limit;
    }

    public function getUsed(): FileSize
    {
        return $this->used;
    }
}

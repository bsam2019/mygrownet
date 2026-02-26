<?php

namespace App\Application\Storage\DTOs;

class StorageUsageDTO
{
    public function __construct(
        public readonly int $usedBytes,
        public readonly int $quotaBytes,
        public readonly int $remainingBytes,
        public readonly float $percentUsed,
        public readonly int $filesCount,
        public readonly string $planName,
        public readonly bool $nearLimit
    ) {}

    public function toArray(): array
    {
        return [
            'used_bytes' => $this->usedBytes,
            'quota_bytes' => $this->quotaBytes,
            'remaining_bytes' => $this->remainingBytes,
            'percent_used' => $this->percentUsed,
            'files_count' => $this->filesCount,
            'plan_name' => $this->planName,
            'near_limit' => $this->nearLimit,
        ];
    }
}

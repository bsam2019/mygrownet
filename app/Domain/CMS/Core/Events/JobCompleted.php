<?php

namespace App\Domain\CMS\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $jobId,
        public readonly int $companyId,
        public readonly int $customerId,
        public readonly string $jobNumber,
        public readonly float $actualValue,
        public readonly float $totalCost,
        public readonly float $profitAmount,
        public readonly int $completedBy
    ) {}
}

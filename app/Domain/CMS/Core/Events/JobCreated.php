<?php

namespace App\Domain\CMS\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $jobId,
        public readonly int $companyId,
        public readonly int $customerId,
        public readonly string $jobNumber,
        public readonly string $jobType,
        public readonly ?float $quotedValue,
        public readonly int $createdBy
    ) {}
}

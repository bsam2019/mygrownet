<?php

namespace App\Domain\CMS\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $jobId,
        public readonly int $companyId,
        public readonly int $assignedTo,
        public readonly int $assignedBy
    ) {}
}

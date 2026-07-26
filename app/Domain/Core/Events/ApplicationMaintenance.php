<?php

namespace App\Domain\Core\Events;

use App\Domain\Core\Models\Organization;
use Illuminate\Foundation\Events\Dispatchable;

class ApplicationMaintenance
{
    use Dispatchable;

    public function __construct(
        public Organization $organization,
        public string $applicationId,
        public string $applicationName,
        public bool $enteringMaintenance,
    ) {}
}

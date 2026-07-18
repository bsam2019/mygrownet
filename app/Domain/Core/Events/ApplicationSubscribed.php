<?php

namespace App\Domain\Core\Events;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Organization;
use Illuminate\Foundation\Events\Dispatchable;

class ApplicationSubscribed
{
    use Dispatchable;

    public function __construct(
        public Organization $organization,
        public Application $application,
    ) {}
}

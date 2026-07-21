<?php

namespace App\Domain\Workspace\ValueObjects;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Organization;

class DomainResolution
{
    public function __construct(
        public readonly string $type,
        public readonly ?Application $application = null,
        public readonly ?Organization $organization = null,
        public readonly string $route = '/',
        public readonly bool $shouldAutoLaunch = false,
        public readonly string $source = 'platform',
    ) {}
}

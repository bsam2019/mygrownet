<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\CustomDomain;
use App\Domain\Core\Models\Organization;

class ResolvedWorkspace
{
    public function __construct(
        public readonly string $type,
        public readonly ?Application $application = null,
        public readonly ?Organization $organization = null,
        public readonly ?CustomDomain $domain = null,
        public readonly bool $shouldAutoLaunch = false,
        public readonly string $source = 'platform',
    ) {}
}

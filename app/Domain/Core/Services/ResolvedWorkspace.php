<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\CustomDomain;
use App\Domain\Core\Models\Organization;

class ResolvedWorkspace
{
    public function __construct(
        public readonly string $type, // 'application', 'organization', 'custom'
        public readonly ?Application $application = null,
        public readonly ?Organization $organization = null,
        public readonly ?CustomDomain $domain = null,
    ) {}
}

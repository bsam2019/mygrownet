<?php

namespace App\Application\Queries;

class GetAvailableModulesQuery
{
    public function __construct(
        public readonly ?string $category = null,
        public readonly ?string $accountType = null,
        public readonly bool $activeOnly = true
    ) {}
}

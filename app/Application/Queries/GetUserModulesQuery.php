<?php

namespace App\Application\Queries;

class GetUserModulesQuery
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $category = null,
        public readonly ?string $status = 'active'
    ) {}
}

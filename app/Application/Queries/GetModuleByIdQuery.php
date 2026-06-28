<?php

namespace App\Application\Queries;

class GetModuleByIdQuery
{
    public function __construct(
        public readonly string $moduleId
    ) {}
}

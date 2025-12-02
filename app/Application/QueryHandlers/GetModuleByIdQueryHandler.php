<?php

namespace App\Application\QueryHandlers;

use App\Application\DTOs\ModuleDTO;
use App\Application\Queries\GetModuleByIdQuery;
use App\Application\UseCases\Module\GetModuleByIdUseCase;

class GetModuleByIdQueryHandler
{
    public function __construct(
        private GetModuleByIdUseCase $useCase
    ) {}

    public function handle(GetModuleByIdQuery $query): ?ModuleDTO
    {
        return $this->useCase->execute($query->moduleId);
    }
}

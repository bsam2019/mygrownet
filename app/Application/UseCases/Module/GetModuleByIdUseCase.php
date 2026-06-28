<?php

namespace App\Application\UseCases\Module;

use App\Application\DTOs\ModuleDTO;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;

class GetModuleByIdUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository
    ) {}

    /**
     * Get module by ID
     *
     * @param string $moduleId
     * @return ModuleDTO|null
     */
    public function execute(string $moduleId): ?ModuleDTO
    {
        $module = $this->moduleRepository->findById($moduleId);

        return $module ? ModuleDTO::fromEntity($module) : null;
    }
}

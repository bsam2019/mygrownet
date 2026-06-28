<?php

namespace App\Application\QueryHandlers;

use App\Application\Queries\GetUserModulesQuery;
use App\Application\UseCases\Module\GetUserModulesUseCase;
use App\Models\User;

class GetUserModulesQueryHandler
{
    public function __construct(
        private GetUserModulesUseCase $useCase
    ) {}

    /**
     * @return array<\App\Application\DTOs\ModuleCardDTO>
     */
    public function handle(GetUserModulesQuery $query): array
    {
        $user = User::findOrFail($query->userId);
        
        return $this->useCase->execute($user, $query->category);
    }
}

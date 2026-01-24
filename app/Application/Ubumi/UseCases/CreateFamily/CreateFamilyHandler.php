<?php

namespace App\Application\Ubumi\UseCases\CreateFamily;

use App\Domain\Ubumi\Entities\Family;
use App\Domain\Ubumi\Repositories\FamilyRepositoryInterface;
use App\Domain\Ubumi\Services\SlugGeneratorService;
use App\Domain\Ubumi\ValueObjects\FamilyName;

/**
 * Create Family Use Case Handler
 */
class CreateFamilyHandler
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
        private SlugGeneratorService $slugGenerator
    ) {}

    public function handle(CreateFamilyCommand $command): Family
    {
        $slug = $this->slugGenerator->generateFamilySlug($command->name);
        
        $family = Family::create(
            FamilyName::fromString($command->name),
            $slug,
            $command->adminUserId
        );

        $this->familyRepository->save($family);

        return $family;
    }
}

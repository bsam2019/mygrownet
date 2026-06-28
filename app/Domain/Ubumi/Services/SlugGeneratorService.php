<?php

namespace App\Domain\Ubumi\Services;

use App\Domain\Ubumi\Repositories\FamilyRepositoryInterface;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\FamilyId;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\Slug;

class SlugGeneratorService
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Generate a unique slug for a family
     */
    public function generateFamilySlug(string $name, ?FamilyId $excludeId = null): Slug
    {
        $baseSlug = Slug::fromString($name);
        $slug = $baseSlug->value();
        $counter = 1;

        while ($this->familyRepository->slugExists($slug, $excludeId)) {
            $slug = Slug::fromStringWithSuffix($name, $counter)->value();
            $counter++;
        }

        return Slug::fromString($slug);
    }

    /**
     * Generate a unique slug for a person within a family
     */
    public function generatePersonSlug(string $familyId, string $name, ?PersonId $excludeId = null): Slug
    {
        $baseSlug = Slug::fromString($name);
        $slug = $baseSlug->value();
        $counter = 1;

        while ($this->personRepository->slugExistsInFamily($familyId, $slug, $excludeId)) {
            $slug = Slug::fromStringWithSuffix($name, $counter)->value();
            $counter++;
        }

        return Slug::fromString($slug);
    }
}

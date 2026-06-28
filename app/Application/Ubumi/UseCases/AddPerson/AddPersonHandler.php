<?php

namespace App\Application\Ubumi\UseCases\AddPerson;

use App\Domain\Ubumi\Entities\Person;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\Services\SlugGeneratorService;
use App\Domain\Ubumi\ValueObjects\PersonName;
use App\Domain\Ubumi\ValueObjects\ApproximateAge;

/**
 * Add Person Use Case Handler
 */
class AddPersonHandler
{
    public function __construct(
        private PersonRepositoryInterface $personRepository,
        private SlugGeneratorService $slugGenerator
    ) {}

    public function handle(AddPersonCommand $command): Person
    {
        $slug = $this->slugGenerator->generatePersonSlug($command->familyId, $command->name);
        
        $person = Person::create(
            $command->familyId,
            PersonName::fromString($command->name),
            $slug,
            $command->createdBy,
            $command->photoUrl,
            $command->dateOfBirth,
            $command->approximateAge ? ApproximateAge::fromInt($command->approximateAge) : null,
            $command->gender
        );

        $this->personRepository->save($person);

        return $person;
    }
}

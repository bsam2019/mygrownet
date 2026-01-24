<?php

namespace App\Application\Ubumi\UseCases\AddPerson;

use DateTimeImmutable;

/**
 * Add Person Command
 */
class AddPersonCommand
{
    public function __construct(
        public readonly string $familyId,
        public readonly string $name,
        public readonly int $createdBy,
        public readonly ?string $photoUrl = null,
        public readonly ?DateTimeImmutable $dateOfBirth = null,
        public readonly ?int $approximateAge = null,
        public readonly ?string $gender = null
    ) {}
}

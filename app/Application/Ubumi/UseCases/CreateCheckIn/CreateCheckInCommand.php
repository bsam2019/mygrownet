<?php

namespace App\Application\Ubumi\UseCases\CreateCheckIn;

final class CreateCheckInCommand
{
    public function __construct(
        public readonly string $personId,
        public readonly string $status,
        public readonly ?string $note = null,
        public readonly ?string $location = null,
        public readonly ?string $photoUrl = null,
        public readonly ?string $checkedInAt = null
    ) {}
}

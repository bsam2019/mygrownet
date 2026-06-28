<?php

namespace App\Application\Ubumi\UseCases\CreateFamily;

/**
 * Create Family Command
 * 
 * Data Transfer Object for creating a family
 */
class CreateFamilyCommand
{
    public function __construct(
        public readonly string $name,
        public readonly int $adminUserId
    ) {}
}

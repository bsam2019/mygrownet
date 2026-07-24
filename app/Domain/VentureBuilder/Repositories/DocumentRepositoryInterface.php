<?php

declare(strict_types=1);

namespace App\Domain\VentureBuilder\Repositories;

use App\Domain\VentureBuilder\Entities\Document;

interface DocumentRepositoryInterface
{
    public function findById(int $id): ?Document;
    public function findByVenture(int $ventureId, ?string $visibility = null): array;
    public function save(Document $document): Document;
    public function delete(int $id): void;
}

<?php

declare(strict_types=1);

namespace App\Domain\VentureBuilder\Repositories;

use App\Domain\VentureBuilder\Entities\Update;

interface UpdateRepositoryInterface
{
    public function findById(int $id): ?Update;
    public function findByVenture(int $ventureId, ?bool $publishedOnly = true): array;
    public function save(Update $update): Update;
    public function delete(int $id): void;
}

<?php

declare(strict_types=1);

namespace App\Domain\VentureBuilder\Repositories;

use App\Domain\VentureBuilder\Entities\Resolution;

interface ResolutionRepositoryInterface
{
    public function findById(int $id): ?Resolution;
    public function findByVenture(int $ventureId): array;
    public function save(Resolution $resolution): Resolution;
    public function updateStatus(int $id, string $status, ?array $extra = null): void;
    public function incrementVote(int $id, string $column, float $equity): void;
}

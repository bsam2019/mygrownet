<?php

declare(strict_types=1);

namespace App\Domain\VentureBuilder\Repositories;

use App\Domain\VentureBuilder\Entities\Dividend;

interface DividendRepositoryInterface
{
    public function findById(int $id): ?Dividend;
    public function findByVenture(int $ventureId): array;
    public function findByShareholder(int $shareholderId): array;
    public function create(array $data): Dividend;
    public function updateStatus(int $id, string $status, array $data = []): void;
}

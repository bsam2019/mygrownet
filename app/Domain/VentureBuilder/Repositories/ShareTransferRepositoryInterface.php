<?php

declare(strict_types=1);

namespace App\Domain\VentureBuilder\Repositories;

use App\Domain\VentureBuilder\Entities\ShareTransfer;

interface ShareTransferRepositoryInterface
{
    public function findById(int $id): ?ShareTransfer;
    public function findByVenture(int $ventureId): array;
    public function findPending(): array;
    public function save(ShareTransfer $transfer): ShareTransfer;
    public function updateStatus(int $id, string $status, array $data = []): void;
}

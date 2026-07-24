<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\QrCode;

interface QrCodeRepositoryInterface
{
    public function findById(int $id): ?QrCode;

    public function findByShortCode(string $code): ?QrCode;

    public function findByBusiness(int $businessId): array;

    public function save(QrCode $entity): QrCode;

    public function delete(int $id): void;
}
<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Repositories;

use App\Domain\GrowBuilder\Entities\Order;
use App\Domain\GrowBuilder\ValueObjects\OrderId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;

interface OrderRepositoryInterface
{
    public function findById(OrderId $id): ?Order;
    public function findBySiteId(SiteId $siteId): array;
    public function findBySiteIdPaginated(SiteId $siteId, int $perPage = 20, ?string $status = null, ?int $siteUserId = null);
    public function findByIdForSite(OrderId $id, SiteId $siteId): ?Order;
    public function countBySiteId(SiteId $siteId): int;
    public function countByStatus(SiteId $siteId, string $status): int;
    public function sumTotalBySiteId(SiteId $siteId): int;
    public function sumTotalPaidBySiteId(SiteId $siteId): int;
    public function save(Order $order): Order;
    public function delete(OrderId $id): void;
}

<?php

declare(strict_types=1);

namespace App\Extensions\Manufacturing\Repositories;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Extensions\Manufacturing\Entities\WorkOrder;
use App\Extensions\Manufacturing\ValueObjects\WorkOrderId;

interface WorkOrderRepositoryInterface
{
    public function findById(WorkOrderId $id): ?WorkOrder;
    public function findByCompany(CompanyId $companyId): array;
    public function save(WorkOrder $workOrder): WorkOrder;
    public function delete(WorkOrderId $id): void;
}

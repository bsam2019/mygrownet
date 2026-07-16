<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Warehouse;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\WarehouseRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\WarehouseId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Throwable;

class WarehouseService
{
    public function __construct(private WarehouseRepositoryInterface $warehouseRepository) {}

    public function createWarehouse(int $companyId, array $data): Warehouse
    {
        try {
            $warehouse = Warehouse::create(
                companyId: CompanyId::fromInt($companyId),
                name: $data['name'],
                code: $data['code'] ?? null,
                address: $data['address'] ?? null,
                city: $data['city'] ?? null,
                country: $data['country'] ?? null,
                contactPerson: $data['contact_person'] ?? null,
                phone: $data['phone'] ?? null,
                isDefault: (bool) ($data['is_default'] ?? false),
            );
            return $this->warehouseRepository->save($warehouse);
        } catch (Throwable $e) {
            throw new OperationFailedException('create warehouse', $e->getMessage());
        }
    }

    public function updateWarehouse(int $id, int $companyId, array $data): Warehouse
    {
        $warehouse = $this->warehouseRepository->findById(WarehouseId::fromInt($id));
        if (!$warehouse || $warehouse->getCompanyId()->toInt() !== $companyId) {
            throw new OperationFailedException('update warehouse', 'Warehouse not found');
        }
        $warehouse->update(
            name: $data['name'] ?? $warehouse->getName(),
            code: $data['code'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            country: $data['country'] ?? null,
            contactPerson: $data['contact_person'] ?? null,
            phone: $data['phone'] ?? null,
            isDefault: (bool) ($data['is_default'] ?? $warehouse->isDefault()),
        );
        return $this->warehouseRepository->save($warehouse);
    }

    public function deleteWarehouse(int $id, int $companyId): void
    {
        $warehouse = $this->warehouseRepository->findById(WarehouseId::fromInt($id));
        if (!$warehouse || $warehouse->getCompanyId()->toInt() !== $companyId) {
            throw new OperationFailedException('delete warehouse', 'Warehouse not found');
        }
        $this->warehouseRepository->delete(WarehouseId::fromInt($id));
    }

    public function getWarehouses(int $companyId): array
    {
        return $this->warehouseRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function getDefaultWarehouse(int $companyId): ?Warehouse
    {
        return $this->warehouseRepository->findDefault(CompanyId::fromInt($companyId));
    }
}

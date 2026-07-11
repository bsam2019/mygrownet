<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Department;
use App\Domain\StockFlow\Entities\Bin;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\DepartmentRepositoryInterface;
use App\Domain\StockFlow\Repositories\BinRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Throwable;

class DepartmentBinService
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private BinRepositoryInterface $binRepository,
    ) {}

    public function createDepartment(int $companyId, array $data): Department
    {
        try {
            $dept = Department::create(
                companyId: CompanyId::fromInt($companyId),
                name: $data['name'],
                description: $data['description'] ?? null,
                sortOrder: (int) ($data['sort_order'] ?? 0),
            );
            return $this->departmentRepository->save($dept);
        } catch (Throwable $e) {
            throw new OperationFailedException('create department', $e->getMessage());
        }
    }

    public function updateDepartment(int $departmentId, array $data): Department
    {
        $dept = $this->departmentRepository->findById(DepartmentId::fromInt($departmentId));
        throw_unless($dept, new OperationFailedException('update department', 'Department not found'));

        try {
            $dept = Department::reconstitute(
                id: $dept->getId(),
                companyId: $dept->getCompanyId(),
                name: $data['name'] ?? $dept->getName(),
                slug: null,
                description: $data['description'] ?? $dept->getDescription(),
                sortOrder: (int) ($data['sort_order'] ?? $dept->getSortOrder()),
                createdAt: $dept->getCreatedAt(),
                updatedAt: new \DateTimeImmutable(),
            );
            return $this->departmentRepository->save($dept);
        } catch (Throwable $e) {
            throw new OperationFailedException('update department', $e->getMessage());
        }
    }

    public function deleteDepartment(int $departmentId): void
    {
        $this->departmentRepository->delete(DepartmentId::fromInt($departmentId));
    }

    public function getDepartments(int $companyId): array
    {
        return $this->departmentRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function createBin(int $companyId, array $data): Bin
    {
        try {
            $bin = Bin::create(
                companyId: CompanyId::fromInt($companyId),
                departmentId: DepartmentId::fromInt($data['sa_department_id']),
                name: $data['name'],
                label: $data['label'] ?? null,
                description: $data['description'] ?? null,
                sortOrder: (int) ($data['sort_order'] ?? 0),
            );
            return $this->binRepository->save($bin);
        } catch (Throwable $e) {
            throw new OperationFailedException('create bin', $e->getMessage());
        }
    }

    public function updateBin(int $binId, array $data): Bin
    {
        $bin = $this->binRepository->findById(BinId::fromInt($binId));
        throw_unless($bin, new OperationFailedException('update bin', 'Bin not found'));

        try {
            $bin = Bin::reconstitute(
                id: $bin->getId(),
                companyId: $bin->getCompanyId(),
                departmentId: isset($data['sa_department_id']) ? DepartmentId::fromInt($data['sa_department_id']) : $bin->getDepartmentId(),
                name: $data['name'] ?? $bin->getName(),
                label: $data['label'] ?? $bin->getLabel(),
                description: $data['description'] ?? $bin->getDescription(),
                sortOrder: (int) ($data['sort_order'] ?? $bin->getSortOrder()),
                createdAt: $bin->getCreatedAt(),
                updatedAt: new \DateTimeImmutable(),
            );
            return $this->binRepository->save($bin);
        } catch (Throwable $e) {
            throw new OperationFailedException('update bin', $e->getMessage());
        }
    }

    public function deleteBin(int $binId): void
    {
        $this->binRepository->delete(BinId::fromInt($binId));
    }

    public function getBins(int $companyId): array
    {
        return $this->binRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }
}

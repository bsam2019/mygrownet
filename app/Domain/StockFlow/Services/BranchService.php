<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Branch;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\BranchRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\BranchId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Throwable;

class BranchService
{
    public function __construct(private BranchRepositoryInterface $branchRepository) {}

    public function createBranch(int $companyId, array $data): Branch
    {
        try {
            $branch = Branch::create(
                companyId: CompanyId::fromInt($companyId), name: $data['name'],
                code: $data['code'] ?? null, phone: $data['phone'] ?? null,
                email: $data['email'] ?? null, address: $data['address'] ?? null,
                city: $data['city'] ?? null, country: $data['country'] ?? null,
                isHeadOffice: (bool) ($data['is_head_office'] ?? false),
                isActive: (bool) ($data['is_active'] ?? true),
            );
            return $this->branchRepository->save($branch);
        } catch (Throwable $e) {
            throw new OperationFailedException('create branch', $e->getMessage());
        }
    }

    public function updateBranch(int $id, int $companyId, array $data): Branch
    {
        $branch = $this->branchRepository->findById(BranchId::fromInt($id));
        if (!$branch || $branch->getCompanyId()->toInt() !== $companyId) throw new OperationFailedException('update branch', 'Not found');
        $branch->update(
            name: $data['name'] ?? $branch->getName(), code: $data['code'] ?? $branch->toArray()['code'],
            phone: $data['phone'] ?? $branch->toArray()['phone'], email: $data['email'] ?? $branch->toArray()['email'],
            address: $data['address'] ?? $branch->toArray()['address'], city: $data['city'] ?? $branch->toArray()['city'],
            country: $data['country'] ?? $branch->toArray()['country'],
            isHeadOffice: (bool) ($data['is_head_office'] ?? $branch->isHeadOffice()),
            isActive: (bool) ($data['is_active'] ?? $branch->isActive()),
        );
        return $this->branchRepository->save($branch);
    }

    public function deleteBranch(int $id, int $companyId): void
    {
        $branch = $this->branchRepository->findById(BranchId::fromInt($id));
        if (!$branch || $branch->getCompanyId()->toInt() !== $companyId) throw new OperationFailedException('delete branch', 'Not found');
        $this->branchRepository->delete(BranchId::fromInt($id));
    }

    public function getBranches(int $companyId): array { return $this->branchRepository->findByCompanyId(CompanyId::fromInt($companyId)); }
    public function getActiveBranches(int $companyId): array { return $this->branchRepository->findActive(CompanyId::fromInt($companyId)); }
}

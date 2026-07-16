<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\PurchaseRequisition;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\PurchaseRequisitionRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\PurchaseRequisitionId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\UserId;
use DateTimeImmutable;
use Throwable;

class RequisitionService
{
    public function __construct(private PurchaseRequisitionRepositoryInterface $requisitionRepository) {}

    public function createRequisition(int $companyId, int $requestedBy, array $data): PurchaseRequisition
    {
        try {
            $requisition = PurchaseRequisition::create(
                companyId: CompanyId::fromInt($companyId),
                requisitionNumber: $data['requisition_number'] ?? ('REQ-' . time()),
                requestedBy: UserId::fromInt($requestedBy),
                dateRequired: isset($data['date_required']) ? new DateTimeImmutable($data['date_required']) : null,
                notes: $data['notes'] ?? null,
            );
            return $this->requisitionRepository->save($requisition);
        } catch (Throwable $e) {
            throw new OperationFailedException('create requisition', $e->getMessage());
        }
    }

    public function approveRequisition(int $id, int $companyId, int $approvedBy): PurchaseRequisition
    {
        $requisition = $this->requisitionRepository->findById(PurchaseRequisitionId::fromInt($id));
        if (!$requisition || $requisition->getCompanyId()->toInt() !== $companyId) {
            throw new OperationFailedException('approve requisition', 'Requisition not found');
        }
        $requisition->approve(UserId::fromInt($approvedBy));
        return $this->requisitionRepository->save($requisition);
    }

    public function rejectRequisition(int $id, int $companyId): PurchaseRequisition
    {
        $requisition = $this->requisitionRepository->findById(PurchaseRequisitionId::fromInt($id));
        if (!$requisition || $requisition->getCompanyId()->toInt() !== $companyId) {
            throw new OperationFailedException('reject requisition', 'Requisition not found');
        }
        $requisition->reject();
        return $this->requisitionRepository->save($requisition);
    }

    public function deleteRequisition(int $id, int $companyId): void
    {
        $this->requisitionRepository->delete(PurchaseRequisitionId::fromInt($id));
    }

    public function getRequisitions(int $companyId, ?string $status = null): array
    {
        if ($status) {
            return $this->requisitionRepository->findByStatus(CompanyId::fromInt($companyId), $status);
        }
        return $this->requisitionRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }
}

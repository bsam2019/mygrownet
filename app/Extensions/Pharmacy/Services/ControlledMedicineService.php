<?php

declare(strict_types=1);

namespace App\Extensions\Pharmacy\Services;

use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\LotRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\LotId;
use App\Domain\StockFlow\ValueObjects\UserId;
use App\Extensions\Pharmacy\Entities\ControlledMedicine;
use App\Extensions\Pharmacy\Repositories\ControlledMedicineRepositoryInterface;
use Throwable;

class ControlledMedicineService
{
    public function __construct(
        private ControlledMedicineRepositoryInterface $repo,
        private LotRepositoryInterface $lotRepo,
    ) {}

    public function recordTransaction(int $companyId, array $data): array
    {
        try {
            $companyCid = CompanyId::fromInt($companyId);
            $itemId = ItemId::fromInt((int) $data['sa_item_id']);
            $quantity = (float) $data['quantity'];

            // Calculate running balance
            $existing = $this->repo->findByItem($companyCid, $itemId);
            $lastBalance = 0;
            foreach ($existing as $e) {
                $lastBalance = $e->toArray()['balance_after'];
            }

            $newBalance = in_array($data['transaction_type'], ['received', 'returned'])
                ? $lastBalance + $quantity
                : $lastBalance - $quantity;

            $entry = ControlledMedicine::create(
                companyId: $companyCid, itemId: $itemId,
                transactionType: $data['transaction_type'], quantity: $quantity,
                balanceAfter: max(0, $newBalance),
                staffUserId: UserId::fromInt((int) ($data['staff_user_id'] ?? $data['user_id'])),
                lotId: isset($data['sa_lot_id']) ? LotId::fromInt((int) $data['sa_lot_id']) : null,
                patientName: $data['patient_name'] ?? null,
                patientIdNumber: $data['patient_id_number'] ?? null,
                prescriptionNumber: $data['prescription_number'] ?? null,
                notes: $data['notes'] ?? null,
            );

            $this->repo->save($entry);
            return $entry->toArray();
        } catch (Throwable $e) {
            throw new OperationFailedException('record controlled medicine', $e->getMessage());
        }
    }

    public function getTransactions(int $companyId, ?int $itemId = null): array
    {
        $companyCid = CompanyId::fromInt($companyId);
        if ($itemId) {
            return array_map(fn($e) => $e->toArray(), $this->repo->findByItem($companyCid, ItemId::fromInt($itemId)));
        }
        return array_map(fn($e) => $e->toArray(), $this->repo->findByCompanyId($companyCid));
    }
}

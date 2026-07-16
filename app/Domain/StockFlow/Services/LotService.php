<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Lot;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\LotRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\LotId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use DateTimeImmutable;
use Throwable;

class LotService
{
    public function __construct(private LotRepositoryInterface $lotRepository) {}

    public function createLot(int $companyId, array $data): Lot
    {
        try {
            $lot = Lot::create(
                companyId: CompanyId::fromInt($companyId),
                itemId: ItemId::fromInt((int) $data['sa_item_id']),
                lotNumber: $data['lot_number'],
                quantity: (float) ($data['quantity'] ?? 0),
                manufacturingDate: isset($data['manufacturing_date']) ? new DateTimeImmutable($data['manufacturing_date']) : null,
                expiryDate: isset($data['expiry_date']) ? new DateTimeImmutable($data['expiry_date']) : null,
                receivedDate: isset($data['received_date']) ? new DateTimeImmutable($data['received_date']) : null,
            );
            return $this->lotRepository->save($lot);
        } catch (Throwable $e) {
            throw new OperationFailedException('create lot', $e->getMessage());
        }
    }

    public function adjustLotQuantity(int $id, int $companyId, float $newQuantity): Lot
    {
        $lot = $this->lotRepository->findById(LotId::fromInt($id));
        if (!$lot || $lot->getCompanyId()->toInt() !== $companyId) {
            throw new OperationFailedException('adjust lot', 'Lot not found');
        }
        $lot->adjustQuantity($newQuantity);
        return $this->lotRepository->save($lot);
    }

    public function deleteLot(int $id, int $companyId): void
    {
        $lot = $this->lotRepository->findById(LotId::fromInt($id));
        if (!$lot || $lot->getCompanyId()->toInt() !== $companyId) {
            throw new OperationFailedException('delete lot', 'Lot not found');
        }
        $this->lotRepository->delete(LotId::fromInt($id));
    }

    public function getLots(int $companyId, ?int $itemId = null): array
    {
        if ($itemId) {
            return $this->lotRepository->findByItemId(CompanyId::fromInt($companyId), ItemId::fromInt($itemId));
        }
        return $this->lotRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }
}

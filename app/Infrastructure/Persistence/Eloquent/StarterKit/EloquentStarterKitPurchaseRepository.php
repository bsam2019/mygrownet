<?php

namespace App\Infrastructure\Persistence\Eloquent\StarterKit;

use App\Domain\StarterKit\Entities\StarterKitPurchase;
use App\Domain\StarterKit\Repositories\StarterKitPurchaseRepositoryInterface;
use App\Domain\StarterKit\ValueObjects\Money;
use App\Domain\StarterKit\ValueObjects\PurchaseStatus;
use DateTimeImmutable;

class EloquentStarterKitPurchaseRepository implements StarterKitPurchaseRepositoryInterface
{
    public function findById(int $id): ?StarterKitPurchase
    {
        $model = StarterKitPurchaseModel::find($id);
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(int $userId): ?StarterKitPurchase
    {
        $model = StarterKitPurchaseModel::where('user_id', $userId)
            ->completed()
            ->first();
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findPendingByUserId(int $userId): ?StarterKitPurchase
    {
        $model = StarterKitPurchaseModel::where('user_id', $userId)
            ->pending()
            ->latest()
            ->first();
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(StarterKitPurchase $purchase): void
    {
        $model = StarterKitPurchaseModel::findOrNew($purchase->id());
        
        $model->fill([
            'user_id' => $purchase->userId(),
            'invoice_number' => $purchase->invoiceNumber(),
            'amount' => $purchase->amount()->toKwacha(),
            'status' => $purchase->status()->value(),
            'payment_method' => $purchase->paymentMethod(),
            'payment_reference' => $purchase->paymentReference(),
            'purchased_at' => $purchase->purchasedAt(),
        ]);
        
        $model->save();
    }

    public function getTotalPurchases(): int
    {
        return StarterKitPurchaseModel::completed()->count();
    }

    public function getTotalRevenue(): int
    {
        return (int) StarterKitPurchaseModel::completed()->sum('amount');
    }

    public function getPendingCount(): int
    {
        return StarterKitPurchaseModel::pending()->count();
    }

    private function toDomainEntity(StarterKitPurchaseModel $model): StarterKitPurchase
    {
        return StarterKitPurchase::fromData(
            $model->id,
            $model->user_id,
            $model->invoice_number,
            Money::fromKwacha((int) $model->amount),
            PurchaseStatus::fromString($model->status),
            $model->payment_method,
            $model->payment_reference,
            DateTimeImmutable::createFromMutable($model->purchased_at ?? $model->created_at),
            $model->purchased_at ? DateTimeImmutable::createFromMutable($model->purchased_at) : null
        );
    }
}

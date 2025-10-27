<?php

namespace App\Domain\StarterKit\Repositories;

use App\Domain\StarterKit\Entities\StarterKitPurchase;

interface StarterKitPurchaseRepositoryInterface
{
    public function findById(int $id): ?StarterKitPurchase;
    
    public function findByUserId(int $userId): ?StarterKitPurchase;
    
    public function findPendingByUserId(int $userId): ?StarterKitPurchase;
    
    public function save(StarterKitPurchase $purchase): void;
    
    public function getTotalPurchases(): int;
    
    public function getTotalRevenue(): int;
    
    public function getPendingCount(): int;
}

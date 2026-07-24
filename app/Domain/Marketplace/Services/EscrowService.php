<?php

namespace App\Domain\Marketplace\Services;

use App\Domain\Marketplace\Repositories\EscrowRepositoryInterface;
use App\Domain\Marketplace\Repositories\SellerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    public function __construct(
        private EscrowRepositoryInterface $escrowRepository,
        private SellerRepositoryInterface $sellerRepository,
    ) {}

    public function holdFunds(int $orderId, int $amount): array
    {
        return $this->escrowRepository->create([
            'order_id' => $orderId,
            'amount' => $amount,
            'status' => 'held',
            'held_at' => now(),
        ]);
    }

    public function releaseFunds(int $orderId, string $reason): void
    {
        $escrow = $this->escrowRepository->findByOrderId($orderId);
        if (!$escrow || $escrow['status'] !== 'held') {
            throw new \Exception('Escrow funds are not in held status.');
        }

        DB::transaction(function () use ($orderId, $reason, $escrow) {
            $this->escrowRepository->updateByOrderId($orderId, [
                'status' => 'released',
                'released_at' => now(),
                'release_reason' => $reason,
            ]);

            // Credit seller's wallet via seller balance
            $this->sellerRepository->incrementBalance(
                $this->getSellerIdForOrder($orderId),
                $escrow['amount']
            );
        });
    }

    public function refundFunds(int $orderId, string $reason): void
    {
        $escrow = $this->escrowRepository->findByOrderId($orderId);
        if (!$escrow || $escrow['status'] !== 'held') {
            return;
        }

        $this->escrowRepository->updateByOrderId($orderId, [
            'status' => 'refunded',
            'released_at' => now(),
            'release_reason' => $reason,
        ]);
    }

    public function markAsDisputed(int $orderId): void
    {
        $this->escrowRepository->updateByOrderId($orderId, ['status' => 'disputed']);
    }

    public function resolveDispute(int $orderId, string $resolution, int $sellerAmount, int $buyerAmount): void
    {
        DB::transaction(function () use ($orderId, $resolution, $sellerAmount, $buyerAmount) {
            if ($sellerAmount > 0) {
                $this->sellerRepository->incrementBalance(
                    $this->getSellerIdForOrder($orderId),
                    $sellerAmount
                );
            }

            if ($buyerAmount > 0) {
                DB::table('marketplace_buyer_refunds')->insert([
                    'buyer_id' => $this->getBuyerIdForOrder($orderId),
                    'amount' => $buyerAmount,
                    'status' => 'pending',
                    'created_at' => now(),
                ]);
            }

            $this->escrowRepository->updateByOrderId($orderId, [
                'status' => 'released',
                'released_at' => now(),
                'release_reason' => "Dispute resolved: {$resolution}",
            ]);

            DB::table('marketplace_orders')
                ->where('id', $orderId)
                ->update([
                    'status' => 'completed',
                    'dispute_resolution' => $resolution,
                ]);
        });
    }

    public function getEscrowBalance(): int
    {
        return $this->escrowRepository->getTotalHeldBalance();
    }

    public function getSellerPendingBalance(int $sellerId): int
    {
        return $this->escrowRepository->getSellerPendingBalance($sellerId);
    }

    private function getSellerIdForOrder(int $orderId): int
    {
        return (int) DB::table('marketplace_orders')
            ->where('id', $orderId)
            ->value('seller_id');
    }

    private function getBuyerIdForOrder(int $orderId): int
    {
        return (int) DB::table('marketplace_orders')
            ->where('id', $orderId)
            ->value('buyer_id');
    }
}

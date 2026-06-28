<?php

namespace App\Domain\Marketplace\Services;

use App\Models\MarketplaceOrder;
use App\Models\MarketplaceEscrow;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    public function holdFunds(MarketplaceOrder $order): MarketplaceEscrow
    {
        return MarketplaceEscrow::create([
            'order_id' => $order->id,
            'amount' => $order->total,
            'status' => 'held',
            'held_at' => now(),
        ]);
    }

    public function releaseFunds(MarketplaceOrder $order, string $reason): void
    {
        $escrow = MarketplaceEscrow::where('order_id', $order->id)->firstOrFail();
        
        if ($escrow->status !== 'held') {
            throw new \Exception('Escrow funds are not in held status.');
        }

        DB::transaction(function () use ($escrow, $reason, $order) {
            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
                'release_reason' => $reason,
            ]);

            // Credit seller's wallet (simplified for MVP)
            // In production, this would integrate with the wallet system
            $this->creditSellerWallet($order->seller_id, $escrow->amount);
        });
    }

    public function refundFunds(MarketplaceOrder $order, string $reason): void
    {
        $escrow = MarketplaceEscrow::where('order_id', $order->id)->first();
        
        if (!$escrow || $escrow->status !== 'held') {
            return; // No escrow to refund
        }

        DB::transaction(function () use ($escrow, $reason, $order) {
            $escrow->update([
                'status' => 'refunded',
                'released_at' => now(),
                'release_reason' => $reason,
            ]);

            // Credit buyer's wallet (simplified for MVP)
            $this->creditBuyerWallet($order->buyer_id, $escrow->amount);
        });
    }

    public function markAsDisputed(MarketplaceOrder $order): void
    {
        MarketplaceEscrow::where('order_id', $order->id)
            ->update(['status' => 'disputed']);
    }

    public function resolveDispute(int $orderId, string $resolution, int $sellerAmount, int $buyerAmount): void
    {
        $escrow = MarketplaceEscrow::where('order_id', $orderId)->firstOrFail();
        $order = MarketplaceOrder::findOrFail($orderId);

        DB::transaction(function () use ($escrow, $order, $resolution, $sellerAmount, $buyerAmount) {
            if ($sellerAmount > 0) {
                $this->creditSellerWallet($order->seller_id, $sellerAmount);
            }

            if ($buyerAmount > 0) {
                $this->creditBuyerWallet($order->buyer_id, $buyerAmount);
            }

            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
                'release_reason' => "Dispute resolved: {$resolution}",
            ]);

            $order->update([
                'status' => 'completed',
                'dispute_resolution' => $resolution,
            ]);
        });
    }

    public function getEscrowBalance(): int
    {
        return MarketplaceEscrow::where('status', 'held')->sum('amount');
    }

    public function getSellerPendingBalance(int $sellerId): int
    {
        return MarketplaceEscrow::whereHas('order', fn($q) => $q->where('seller_id', $sellerId))
            ->where('status', 'held')
            ->sum('amount');
    }

    private function creditSellerWallet(int $sellerId, int $amount): void
    {
        // Simplified wallet credit for MVP
        // In production, integrate with the actual wallet system
        DB::table('marketplace_seller_balances')->updateOrInsert(
            ['seller_id' => $sellerId],
            [
                'available_balance' => DB::raw("COALESCE(available_balance, 0) + {$amount}"),
                'updated_at' => now(),
            ]
        );
    }

    private function creditBuyerWallet(int $buyerId, int $amount): void
    {
        // Simplified wallet credit for MVP
        // In production, integrate with the actual wallet system
        DB::table('marketplace_buyer_refunds')->insert([
            'buyer_id' => $buyerId,
            'amount' => $amount,
            'status' => 'pending',
            'created_at' => now(),
        ]);
    }
}

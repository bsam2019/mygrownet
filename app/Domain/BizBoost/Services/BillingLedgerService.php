<?php

namespace App\Domain\BizBoost\Services;

use App\Infrastructure\Persistence\Eloquent\BizBoostBillingLedgerModel;
use Illuminate\Support\Str;

class BillingLedgerService
{
    public function recordTransaction(
        int $userId,
        string $serviceType,
        float $grossAmountCharged,
        float $netVendorCost,
        ?int $campaignId = null,
        ?string $recipientIdentifier = null,
        string $currency = 'ZMW',
        ?string $vendor = null,
        string $deliveryStatus = 'pending',
        array $meta = [],
    ): BizBoostBillingLedgerModel {
        $profit = $grossAmountCharged - $netVendorCost;

        return BizBoostBillingLedgerModel::create([
            'user_id' => $userId,
            'service_type' => $serviceType,
            'campaign_id' => $campaignId,
            'recipient_identifier' => $recipientIdentifier,
            'gross_amount_charged' => $grossAmountCharged,
            'net_vendor_cost' => $netVendorCost,
            'pure_platform_profit' => max(0, $profit),
            'currency' => $currency,
            'vendor' => $vendor,
            'delivery_status' => $deliveryStatus,
            'reference' => Str::random(20),
            'meta' => $meta,
        ]);
    }

    public function updateDeliveryStatus(int $ledgerId, string $status, array $meta = []): void
    {
        $record = BizBoostBillingLedgerModel::find($ledgerId);
        if ($record) {
            $existingMeta = $record->meta ?? [];
            $record->update([
                'delivery_status' => $status,
                'meta' => array_merge($existingMeta, $meta),
            ]);
        }
    }

    public function getStats(int $userId): array
    {
        return [
            'total_spent' => BizBoostBillingLedgerModel::where('user_id', $userId)->sum('gross_amount_charged'),
            'total_vendor_cost' => BizBoostBillingLedgerModel::where('user_id', $userId)->sum('net_vendor_cost'),
            'total_profit' => BizBoostBillingLedgerModel::where('user_id', $userId)->sum('pure_platform_profit'),
            'by_service' => BizBoostBillingLedgerModel::where('user_id', $userId)
                ->selectRaw('service_type, SUM(gross_amount_charged) as gross, SUM(net_vendor_cost) as net, SUM(pure_platform_profit) as profit')
                ->groupBy('service_type')
                ->get(),
        ];
    }

    public function getGlobalStats(): array
    {
        return [
            'total_gross' => BizBoostBillingLedgerModel::sum('gross_amount_charged'),
            'total_vendor_costs' => BizBoostBillingLedgerModel::sum('net_vendor_cost'),
            'total_platform_profit' => BizBoostBillingLedgerModel::sum('pure_platform_profit'),
            'total_transactions' => BizBoostBillingLedgerModel::count(),
            'by_service' => BizBoostBillingLedgerModel::selectRaw('service_type, SUM(gross_amount_charged) as gross, SUM(net_vendor_cost) as net, SUM(pure_platform_profit) as profit, COUNT(*) as count')
                ->groupBy('service_type')
                ->get(),
            'recent' => BizBoostBillingLedgerModel::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get(),
        ];
    }
}

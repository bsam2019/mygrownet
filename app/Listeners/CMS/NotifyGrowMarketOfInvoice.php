<?php

namespace App\Listeners\CMS;

use App\Events\CMS\InvoiceCreated;
use App\Models\MarketplaceOrder;
use Illuminate\Support\Facades\Log;

class NotifyGrowMarketOfInvoice
{
    /**
     * Handle the event
     */
    public function handle(InvoiceCreated $event): void
    {
        if ($event->source !== 'growmarket') {
            return;
        }

        try {
            $orderNumber = $event->invoice->metadata['order_number'] ?? null;
            
            if (!$orderNumber) {
                return;
            }

            // Update GrowMarket order status using existing model
            MarketplaceOrder::where('order_number', $orderNumber)
                ->update([
                    'status' => 'processing',
                    'payment_reference' => $event->invoice->invoice_number,
                    'paid_at' => now(),
                    'updated_at' => now(),
                ]);

            Log::info('GrowMarket order updated', [
                'invoice_id' => $event->invoice->id,
                'order_number' => $orderNumber,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify GrowMarket of invoice', [
                'invoice_id' => $event->invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

<?php

namespace App\Listeners\CMS;

use App\Events\CMS\InvoiceCreated;
use Illuminate\Support\Facades\Log;

class NotifyGrowBuilderOfInvoice
{
    /**
     * Handle the event
     */
    public function handle(InvoiceCreated $event): void
    {
        if ($event->source !== 'growbuilder') {
            return;
        }

        try {
            $siteId = $event->invoice->metadata['site_id'] ?? null;
            
            if (!$siteId) {
                return;
            }

            // Update GrowBuilder order status (direct database update)
            \DB::table('growbuilder_orders')
                ->where('site_id', $siteId)
                ->where('invoice_id', $event->invoice->id)
                ->update([
                    'status' => 'invoiced',
                    'invoice_number' => $event->invoice->invoice_number,
                    'updated_at' => now(),
                ]);

            Log::info('GrowBuilder order updated', [
                'invoice_id' => $event->invoice->id,
                'site_id' => $siteId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify GrowBuilder of invoice', [
                'invoice_id' => $event->invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

<?php

namespace App\Console\Commands\GrowMart;

use App\Models\GrowMart\GrowMartInventory;
use App\Notifications\GrowMart\LowStockAlertNotification;
use Illuminate\Console\Command;

class SendLowStockAlerts extends Command
{
    protected $signature = 'growmart:low-stock-alerts';
    protected $description = 'Send notifications for products with low stock';

    public function handle(): int
    {
        $lowStockItems = GrowMartInventory::with('product')
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where(function ($q) {
                $q->whereNull('alert_sent_at')
                    ->orWhere('alert_sent_at', '<', now()->subHours(24));
            })
            ->get();

        if ($lowStockItems->isEmpty()) {
            $this->info('No low stock items to alert.');
            return 0;
        }

        $admins = \App\Models\User::role('admin')->get();
        $count = 0;

        foreach ($lowStockItems as $item) {
            foreach ($admins as $admin) {
                $admin->notify(new LowStockAlertNotification([
                    'product_name' => $item->product?->name ?? 'Unknown',
                    'product_id' => $item->product_id,
                    'inventory_id' => $item->id,
                    'current_stock' => $item->quantity,
                    'threshold' => $item->low_stock_threshold,
                    'warehouse' => $item->warehouse?->name ?? 'Unknown',
                ]));
            }
            $item->update(['alert_sent_at' => now()]);
            $count++;
        }

        $this->info("Sent low stock alerts for {$count} products.");
        return 0;
    }
}

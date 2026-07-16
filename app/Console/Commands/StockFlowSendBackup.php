<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaBackupConfigModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StockFlowSendBackup extends Command
{
    protected $signature = 'stockflow:send-backup';
    protected $description = 'Send daily database backups to companies that have enabled backups';

    public function handle(): int
    {
        $configs = SaBackupConfigModel::where('enabled', true)->get();

        if ($configs->isEmpty()) {
            $this->info('No companies with backups enabled.');
            return 0;
        }

        $exportPath = storage_path('app/backup-export.csv');
        $count = 0;

        foreach ($configs as $config) {
            $companyName = $config->company?->name ?? 'Company';
            $fileName = 'backup-' . Str::slug($companyName) . '-' . now()->format('Y-m-d') . '.csv';

            // Export items to CSV
            $items = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel::where('sa_company_id', $config->sa_company_id)->get();

            $handle = fopen($exportPath, 'w');
            fputcsv($handle, ['name', 'sku', 'category', 'unit', 'unit_price', 'system_quantity', 'reorder_level', 'is_expirable', 'expiry_date', 'notes']);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->name, $item->sku, $item->category, $item->unit,
                    $item->unit_price, $item->system_quantity, $item->reorder_level,
                    $item->is_expirable ? 'Yes' : 'No', $item->expiry_date, $item->notes,
                ]);
            }
            fclose($handle);

            // Also export sales summary
            $sales = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaSaleModel::where('sa_company_id', $config->sa_company_id)
                ->whereDate('created_at', today())
                ->get();

            $salesPath = storage_path('app/sales-export.csv');
            $handle = fopen($salesPath, 'w');
            fputcsv($handle, ['receipt_number', 'customer', 'total_amount', 'payment_method', 'created_at']);

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->receipt_number, $sale->customer_name, $sale->total_amount,
                    $sale->payment_method, $sale->created_at,
                ]);
            }
            fclose($handle);

            try {
                Mail::raw(
                    "StockFlow Backup for {$companyName}\n\nDate: " . now()->format('Y-m-d') . "\n\nAttached is your inventory data and today's sales.",
                    function ($message) use ($config, $fileName, $exportPath, $salesPath, $companyName) {
                        $message->to($config->email)
                            ->subject("StockFlow Backup - {$companyName} - " . now()->format('Y-m-d'))
                            ->attach($exportPath, ['as' => $fileName])
                            ->attach($salesPath, ['as' => 'daily-sales.csv']);
                    }
                );

                $config->update(['last_backup_at' => now()]);
                $count++;

                $this->info("Backup sent to {$config->email}");
            } catch (\Exception $e) {
                $this->error("Failed to send backup to {$config->email}: {$e->getMessage()}");
            }

            unlink($exportPath);
            unlink($salesPath);
        }

        $this->info("Backups sent to {$count} company(ies).");
        return 0;
    }
}

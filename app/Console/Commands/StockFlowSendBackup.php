<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaBackupConfigModel;
use Illuminate\Console\Command;
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

        $count = 0;

        foreach ($configs as $config) {
            $companyId = $config->sa_company_id;
            $companyName = $config->company?->name ?? 'Company';
            $date = now()->format('Y-m-d');
            $slug = Str::slug($companyName);

            $files = [];

            // --- Items ---
            $itemsPath = $this->exportItems($companyId, $slug, $date);
            if ($itemsPath) $files[] = $itemsPath;

            // --- Daily Sales ---
            $salesPath = $this->exportDailySales($companyId, $slug, $date);
            if ($salesPath) $files[] = $salesPath;

            // --- Extension tables (guarded by table existence) ---

            // Controlled Medicines (Pharmacy)
            $this->exportTable($companyId, $slug, $date, 'sa_controlled_medicines',
                ['medicine_name', 'dosage', 'schedule', 'quantity', 'expiry_date', 'notes'],
                ['medicine_name', 'dosage', 'schedule', 'quantity', 'expiry_date', 'notes'], $files);

            // Bill of Materials (Manufacturing)
            $this->exportTable($companyId, $slug, $date, 'sa_bill_of_materials',
                ['name', 'output_product', 'output_quantity', 'total_cost', 'status', 'created_at'],
                ['name', 'output_product', 'output_quantity', 'total_cost', 'status', 'created_at'], $files);

            // Work Orders (Manufacturing)
            $this->exportTable($companyId, $slug, $date, 'sa_work_orders',
                ['order_number', 'sa_bom_id', 'quantity', 'status', 'due_date', 'created_at'],
                ['order_number', 'bom_id', 'quantity', 'status', 'due_date', 'created_at'], $files);

            // Recipes (Restaurant)
            $this->exportTable($companyId, $slug, $date, 'sa_recipes',
                ['name', 'category', 'selling_price', 'total_cost', 'profit_margin', 'yield', 'created_at'],
                ['name', 'category', 'selling_price', 'total_cost', 'profit_margin', 'yield', 'created_at'], $files);

            // Wastage Records (Restaurant)
            $this->exportTable($companyId, $slug, $date, 'sa_wastage_records',
                ['item_name', 'quantity', 'unit', 'reason', 'cost', 'recorded_at'],
                ['item_name', 'quantity', 'unit', 'reason', 'cost', 'recorded_at'], $files);

            // --- Send email with all attachments ---
            try {
                $attachmentCount = count($files);
                Mail::raw(
                    "StockFlow Backup for {$companyName}\n\nDate: {$date}\n\nAttached: {$attachmentCount} CSV file(s) with your inventory, sales, and extension data.",
                    function ($message) use ($config, $companyName, $date, $files) {
                        $message->to($config->email)
                            ->subject("StockFlow Backup - {$companyName} - {$date}");
                        foreach ($files as $file) {
                            $message->attach($file['path'], ['as' => $file['name']]);
                        }
                    }
                );

                $config->update(['last_backup_at' => now()]);
                $count++;

                $this->info("Backup sent to {$config->email} ({$attachmentCount} files)");
            } catch (\Exception $e) {
                $this->error("Failed to send backup to {$config->email}: {$e->getMessage()}");
            }

            // Cleanup temp files
            foreach ($files as $file) {
                unlink($file['path']);
            }
        }

        $this->info("Backups sent to {$count} company(ies).");
        return 0;
    }

    private function exportItems(int $companyId, string $slug, string $date): ?array
    {
        $items = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel::where('sa_company_id', $companyId)->get();
        if ($items->isEmpty()) return null;

        $path = storage_path("app/backup-{$slug}-items-{$date}.csv");
        $handle = fopen($path, 'w');
        fputcsv($handle, ['name', 'sku', 'category', 'unit', 'unit_price', 'system_quantity', 'reorder_level', 'is_expirable', 'expiry_date', 'notes']);
        foreach ($items as $item) {
            fputcsv($handle, [
                $item->name, $item->sku, $item->category, $item->unit,
                $item->unit_price, $item->system_quantity, $item->reorder_level,
                $item->is_expirable ? 'Yes' : 'No', $item->expiry_date, $item->notes,
            ]);
        }
        fclose($handle);
        return ['path' => $path, 'name' => "items-{$date}.csv"];
    }

    private function exportDailySales(int $companyId, string $slug, string $date): ?array
    {
        $sales = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaSaleModel::where('sa_company_id', $companyId)
            ->whereDate('created_at', today())->get();
        if ($sales->isEmpty()) return null;

        $path = storage_path("app/backup-{$slug}-sales-{$date}.csv");
        $handle = fopen($path, 'w');
        fputcsv($handle, ['receipt_number', 'customer', 'total_amount', 'payment_method', 'created_at']);
        foreach ($sales as $sale) {
            fputcsv($handle, [
                $sale->receipt_number, $sale->customer_name, $sale->total_amount,
                $sale->payment_method, $sale->created_at,
            ]);
        }
        fclose($handle);
        return ['path' => $path, 'name' => "daily-sales-{$date}.csv"];
    }

    private function exportTable(int $companyId, string $slug, string $date, string $table, array $columns, array $headers, array &$files): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) return;

        try {
            $rows = \Illuminate\Support\Facades\DB::table($table)
                ->where('sa_company_id', $companyId)->get();
            if ($rows->isEmpty()) return;

            $path = storage_path("app/backup-{$slug}-{$table}-{$date}.csv");
            $handle = fopen($path, 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                $vals = [];
                foreach ($columns as $col) {
                    $vals[] = $row->$col ?? '';
                }
                fputcsv($handle, $vals);
            }
            fclose($handle);
            $files[] = ['path' => $path, 'name' => "{$table}-{$date}.csv"];
        } catch (\Exception $e) {
            $this->warn("Skipped table {$table}: {$e->getMessage()}");
        }
    }
}

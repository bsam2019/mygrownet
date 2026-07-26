<?php

namespace App\Services\Integration;

use App\Domain\BMS\Core\Services\BmsDataService;
use App\Domain\BMS\Core\Services\CompanySettingsService;
use App\Domain\Core\Services\OutboxService;
use App\Events\BMS\InvoiceCreated;
use App\Events\BMS\InventoryUpdated;
use App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BMSIntegrationService
{
    public function __construct(
        private CompanySettingsService $settingsService,
        private BmsDataService $bmsData,
        private OutboxService $outbox,
    ) {}

    public function isCMSEnabled(int $userId): bool
    {
        $user = \App\Models\User::find($userId);
        return $user && $user->cmsUser !== null;
    }

    public function getCompanyId(int $userId): ?int
    {
        $user = \App\Models\User::find($userId);
        return $user?->cmsUser?->company_id;
    }

    public function getProducts(int $companyId, bool $activeOnly = true): array
    {
        $products = $this->bmsData->getProducts($companyId, $activeOnly)->map(fn($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->selling_price,
            'cost' => $product->cost_price,
            'sku' => $product->sku,
            'category' => $product->category?->name,
            'stock_quantity' => $product->stock_quantity,
            'low_stock_threshold' => $product->low_stock_threshold ?? 10,
            'image_url' => $product->image_url,
            'is_in_stock' => $product->stock_quantity > 0,
            'is_low_stock' => $product->stock_quantity <= ($product->low_stock_threshold ?? 10),
        ]);

        return $products->toArray();
    }

    public function getProduct(int $companyId, int $productId): ?array
    {
        $product = $this->bmsData->getProduct($companyId, $productId);

        if (!$product) {
            return null;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->selling_price,
            'cost' => $product->cost_price,
            'sku' => $product->sku,
            'category' => $product->category?->name,
            'stock_quantity' => $product->stock_quantity,
            'low_stock_threshold' => $product->low_stock_threshold ?? 10,
            'image_url' => $product->image_url,
            'is_in_stock' => $product->stock_quantity > 0,
        ];
    }

    public function createOrderFromGrowBuilder(int $companyId, array $data): array
    {
        return $this->createOrder($companyId, $data, 'growbuilder');
    }

    public function createOrderFromGrowMarket(int $companyId, array $data): array
    {
        return $this->createOrder($companyId, $data, 'growmarket');
    }

    private function createOrder(int $companyId, array $data, string $source): array
    {
        try {
            DB::beginTransaction();

            $customer = $this->getOrCreateCustomer($companyId, $data['customer'], $source);
            $this->validateInventory($companyId, $data['items']);
            $invoice = $this->createInvoice($companyId, $customer, $data, $source);
            $this->updateInventoryFromOrder($companyId, $data['items'], $source);

            $this->outbox->insert(
                eventName: 'bms.invoice.created.v1',
                payload: ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number, 'source' => $source],
                context: ['company_id' => $companyId],
                publisher: 'bms',
            );

            DB::commit();

            return [
                'success' => true,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total' => $invoice->total_amount,
                'customer_id' => $customer->id,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order creation failed', [
                'company_id' => $companyId,
                'source' => $source,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function getOrCreateCustomer(int $companyId, array $customerData, string $source): CustomerModel
    {
        $customer = $this->bmsData->findOrFailCustomer($companyId, $customerData['email']);

        if (!$customer) {
            $customer = $this->bmsData->createCustomer([
                'company_id' => $companyId,
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'] ?? null,
                'type' => 'individual',
                'source' => $source,
            ]);
        }

        return $customer;
    }

    private function validateInventory(int $companyId, array $items): void
    {
        foreach ($items as $item) {
            $product = $this->bmsData->getProduct($companyId, $item['product_id']);

            if (!$product) {
                throw new \Exception("Product {$item['product_id']} not found");
            }

            if ($product->stock_quantity < $item['quantity']) {
                throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock_quantity}, Requested: {$item['quantity']}");
            }
        }
    }

    private function createInvoice(int $companyId, CustomerModel $customer, array $data, string $source): InvoiceModel
    {
        $settings = $this->settingsService->getSettings($companyId);

        $subtotal = collect($data['items'])->sum(fn($item) => $item['price'] * $item['quantity']);
        $taxRate = $settings['tax']['default_rate'] ?? 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount;

        $invoice = $this->bmsData->createInvoice([
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'invoice_number' => $this->generateInvoiceNumber($companyId, $settings),
            'invoice_date' => now(),
            'due_date' => now()->addDays($settings['invoice']['due_days'] ?? 30),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
            'amount_paid' => 0,
            'status' => 'sent',
            'notes' => "Order from " . ucfirst($source),
            'source' => $source,
            'metadata' => [
                'site_id' => $data['site_id'] ?? null,
                'listing_id' => $data['listing_id'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
            ],
        ]);

        foreach ($data['items'] as $item) {
            $product = $this->bmsData->findProduct($item['product_id']);

            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'description' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total' => $item['price'] * $item['quantity'],
            ]);
        }

        return $invoice;
    }

    private function updateInventoryFromOrder(int $companyId, array $items, string $source): void
    {
        foreach ($items as $item) {
            $this->bmsData->decrementStock($item['product_id'], $item['quantity']);

            $this->bmsData->createInventoryMovement([
                'company_id' => $companyId,
                'product_id' => $item['product_id'],
                'type' => 'sale',
                'quantity' => -$item['quantity'],
                'reference' => ucfirst($source) . ' Order',
                'date' => now(),
            ]);

            $product = $this->bmsData->findProduct($item['product_id']);
            if ($product) {
                event(new InventoryUpdated(
                    $item['product_id'],
                    $companyId,
                    $product->stock_quantity,
                    $source
                ));
            }
        }
    }

    public function getInventory(int $companyId): array
    {
        $inventory = $this->bmsData->getProducts($companyId)
            ->map(fn($product) => [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'stock_quantity' => $product->stock_quantity,
                'low_stock_threshold' => $product->low_stock_threshold ?? 10,
                'is_in_stock' => $product->stock_quantity > 0,
                'is_low_stock' => $product->stock_quantity <= ($product->low_stock_threshold ?? 10),
            ]);

        return $inventory->toArray();
    }

    private function generateInvoiceNumber(int $companyId, array $settings): string
    {
        $prefix = $settings['invoice']['prefix'] ?? 'INV';
        $nextNumber = $settings['invoice']['next_number'] ?? 1;

        $company = $this->bmsData->findCompany($companyId);
        $company->update([
            'settings' => array_merge($settings, [
                'invoice' => array_merge($settings['invoice'], [
                    'next_number' => $nextNumber + 1,
                ]),
            ]),
        ]);

        return $prefix . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}

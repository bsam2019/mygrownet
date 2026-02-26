<?php

namespace App\Services\Integration;

use App\Infrastructure\Persistence\Eloquent\CMS\ProductModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InventoryModel;
use App\Domain\CMS\Core\Services\CompanySettingsService;
use App\Events\CMS\InvoiceCreated;
use App\Events\CMS\InventoryUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CMSIntegrationService
{
    public function __construct(
        private CompanySettingsService $settingsService
    ) {}

    /**
     * Check if user has CMS enabled
     */
    public function isCMSEnabled(int $userId): bool
    {
        $user = \App\Models\User::find($userId);
        return $user && $user->cmsUser !== null;
    }

    /**
     * Get company ID for user
     */
    public function getCompanyId(int $userId): ?int
    {
        $user = \App\Models\User::find($userId);
        return $user?->cmsUser?->company_id;
    }

    /**
     * Get products for integration
     */
    public function getProducts(int $companyId, bool $activeOnly = true): array
    {
        $query = ProductModel::where('company_id', $companyId);
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }

        $products = $query->with('category')->get()->map(fn($product) => [
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

    /**
     * Get single product
     */
    public function getProduct(int $companyId, int $productId): ?array
    {
        $product = ProductModel::where('company_id', $companyId)
            ->where('id', $productId)
            ->with('category')
            ->first();

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

    /**
     * Create order from GrowBuilder
     */
    public function createOrderFromGrowBuilder(int $companyId, array $data): array
    {
        try {
            DB::beginTransaction();

            // 1. Create or get customer
            $customer = $this->getOrCreateCustomer($companyId, $data['customer'], 'growbuilder');

            // 2. Validate inventory
            $this->validateInventory($companyId, $data['items']);

            // 3. Create invoice
            $invoice = $this->createInvoice($companyId, $customer, $data, 'growbuilder');

            // 4. Update inventory
            $this->updateInventoryFromOrder($companyId, $data['items'], 'growbuilder');

            // 5. Fire event
            event(new InvoiceCreated($invoice, 'growbuilder'));

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
            
            Log::error('GrowBuilder order creation failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create order from GrowMarket
     */
    public function createOrderFromGrowMarket(int $companyId, array $data): array
    {
        try {
            DB::beginTransaction();

            // 1. Create or get customer
            $customer = $this->getOrCreateCustomer($companyId, $data['customer'], 'growmarket');

            // 2. Validate inventory
            $this->validateInventory($companyId, $data['items']);

            // 3. Create invoice
            $invoice = $this->createInvoice($companyId, $customer, $data, 'growmarket');

            // 4. Update inventory
            $this->updateInventoryFromOrder($companyId, $data['items'], 'growmarket');

            // 5. Fire event
            event(new InvoiceCreated($invoice, 'growmarket'));

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
            
            Log::error('GrowMarket order creation failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get or create customer
     */
    private function getOrCreateCustomer(int $companyId, array $customerData, string $source): CustomerModel
    {
        $customer = CustomerModel::where('company_id', $companyId)
            ->where('email', $customerData['email'])
            ->first();

        if (!$customer) {
            $customer = CustomerModel::create([
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

    /**
     * Validate inventory availability
     */
    private function validateInventory(int $companyId, array $items): void
    {
        foreach ($items as $item) {
            $product = ProductModel::where('company_id', $companyId)
                ->where('id', $item['product_id'])
                ->first();

            if (!$product) {
                throw new \Exception("Product {$item['product_id']} not found");
            }

            if ($product->stock_quantity < $item['quantity']) {
                throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock_quantity}, Requested: {$item['quantity']}");
            }
        }
    }

    /**
     * Create invoice from order
     */
    private function createInvoice(int $companyId, CustomerModel $customer, array $data, string $source): InvoiceModel
    {
        $settings = $this->settingsService->getSettings($companyId);

        $subtotal = collect($data['items'])->sum(fn($item) => $item['price'] * $item['quantity']);
        $taxRate = $settings['tax']['default_rate'] ?? 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount;

        $invoice = InvoiceModel::create([
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

        // Create invoice items
        foreach ($data['items'] as $item) {
            $product = ProductModel::find($item['product_id']);
            
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

    /**
     * Update inventory from order
     */
    private function updateInventoryFromOrder(int $companyId, array $items, string $source): void
    {
        foreach ($items as $item) {
            $product = ProductModel::find($item['product_id']);
            
            if ($product) {
                $product->decrement('stock_quantity', $item['quantity']);
                
                // Log inventory movement
                InventoryModel::create([
                    'company_id' => $companyId,
                    'product_id' => $item['product_id'],
                    'type' => 'sale',
                    'quantity' => -$item['quantity'],
                    'reference' => ucfirst($source) . ' Order',
                    'date' => now(),
                ]);

                // Fire inventory updated event
                event(new InventoryUpdated(
                    $item['product_id'],
                    $companyId,
                    $product->stock_quantity,
                    $source
                ));
            }
        }
    }

    /**
     * Get inventory for integration
     */
    public function getInventory(int $companyId): array
    {
        $inventory = ProductModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get()
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

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber(int $companyId, array $settings): string
    {
        $prefix = $settings['invoice']['prefix'] ?? 'INV';
        $nextNumber = $settings['invoice']['next_number'] ?? 1;
        
        // Increment next number
        $company = \App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel::find($companyId);
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

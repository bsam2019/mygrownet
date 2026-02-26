# MyGrowNet Ecosystem Integration

**Last Updated:** February 20, 2026  
**Status:** Implementation Complete  
**Version:** 1.0

---

## Overview

This document outlines the integration architecture between three core MyGrowNet products:
- **GrowBuilder** - Website builder for e-commerce sites
- **CMS** - Company Management System for business operations
- **GrowMarket** - Marketplace for buying/selling products

**Goal:** Enable seamless data flow between products so users can manage their entire business from one ecosystem.

---

## Integration Architecture

### High-Level Flow

```
┌──────────────────────────────────────────────────────┐
│           MyGrowNet Platform (Single App)            │
│                                                      │
│  ┌─────────────────┐                                │
│  │   GrowBuilder   │ ← User builds e-commerce site  │
│  │   (Module)      │                                │
│  └────────┬────────┘                                │
│           │                                          │
│           │ Direct Service Calls                     │
│           ↓                                          │
│  ┌─────────────────┐                                │
│  │   CMS (Module)  │ ← Central business management  │
│  │   - Invoices    │                                │
│  │   - Inventory   │                                │
│  │   - Customers   │                                │
│  └────────┬────────┘                                │
│           │                                          │
│           │ Direct Service Calls                     │
│           ↓                                          │
│  ┌─────────────────┐                                │
│  │  GrowMarket     │ ← Marketplace listing          │
│  │  (Module)       │                                │
│  └─────────────────┘                                │
│                                                      │
│  Shared: Database, Auth, User Session               │
└──────────────────────────────────────────────────────┘
```

**Key Insight:** Since all modules are in the same Laravel application, we use:
- Direct service injection (no HTTP calls)
- Shared database and user session
- Event-driven architecture for loose coupling
- No API authentication needed

---

## Integration 1: GrowBuilder ↔ CMS

### Use Case
A user builds an e-commerce website with GrowBuilder. When customers place orders:
1. Orders automatically create invoices in CMS
2. Inventory is synced between GrowBuilder and CMS
3. Customer data is shared
4. Payments are tracked in CMS

### Technical Implementation

#### 1. Shared Integration Service (Direct Service Calls)

Since all modules are in the same application, we use direct service injection instead of HTTP APIs.

**File:** `app/Services/Integration/GrowBuilderIntegrationService.php`

```php
<?php

namespace App\Services\Integration;

use App\Infrastructure\Persistence\Eloquent\CMS\ProductModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InventoryModel;

class GrowBuilderIntegrationService
{
    /**
     * Get products for GrowBuilder
     */
    public function getProducts(int $companyId): array
    {
        $products = ProductModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->with('category')
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->selling_price,
                'sku' => $product->sku,
                'category' => $product->category?->name,
                'stock_quantity' => $product->stock_quantity,
                'image_url' => $product->image_url,
                'is_in_stock' => $product->stock_quantity > 0,
            ]);

        return $products->toArray();
    }

    /**
     * Create order from GrowBuilder
     */
    public function createOrderFromGrowBuilder(int $companyId, array $data): array
    {
        try {
            // 1. Create or get customer
            $customer = $this->getOrCreateCustomer($companyId, $data['customer']);

            // 2. Create invoice
            $invoice = $this->createInvoice($companyId, $customer, $data);

            // 3. Update inventory
            $this->updateInventoryFromOrder($companyId, $data['items']);

            // 4. Send notification
            $this->sendOrderNotification($invoice);

            return [
                'success' => true,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total' => $invoice->total_amount,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get or create customer
     */
    private function getOrCreateCustomer(int $companyId, array $customerData): CustomerModel
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
                'source' => 'growbuilder',
            ]);
        }

        return $customer;
    }

    /**
     * Create invoice from order
     */
    private function createInvoice(int $companyId, CustomerModel $customer, array $data): InvoiceModel
    {
        // Get company settings for invoice numbering
        $settings = app(\App\Domain\CMS\Core\Services\CompanySettingsService::class)
            ->getSettings($companyId);

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
            'notes' => 'Order from GrowBuilder website',
            'source' => 'growbuilder',
            'metadata' => [
                'site_id' => $data['site_id'],
                'payment_method' => $data['payment_method'],
                'shipping_address' => $data['shipping_address'] ?? null,
            ],
        ]);

        // Create invoice items
        foreach ($data['items'] as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'description' => ProductModel::find($item['product_id'])->name,
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
    private function updateInventoryFromOrder(int $companyId, array $items): void
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
                    'reference' => 'GrowBuilder Order',
                    'date' => now(),
                ]);
            }
        }
    }

    /**
     * Get inventory for GrowBuilder
     */
    public function getInventory(int $companyId): array
    {
        $inventory = ProductModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get()
            ->map(fn($product) => [
                'product_id' => $product->id,
                'sku' => $product->sku,
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

    /**
     * Send order notification
     */
    private function sendOrderNotification(InvoiceModel $invoice): void
    {
        // Send email notification
        // Implement email notification logic here
    }
}
```

#### 2. GrowBuilder Controller (Uses Direct Service Injection)

**File:** `app/Http/Controllers/GrowBuilder/CheckoutController.php`

```php
<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Services\Integration\CMSIntegrationService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private CMSIntegrationService $cmsIntegration
    ) {}

    /**
     * Process order and sync to CMS
     */
    public function processOrder(Request $request, int $siteId)
    {
        $validated = $request->validate([
            'customer' => 'required|array',
            'items' => 'required|array',
            'payment_method' => 'required|string',
            'shipping_address' => 'nullable|array',
        ]);

        $user = $request->user();

        // Check if user has CMS enabled
        if ($this->cmsIntegration->isCMSEnabled($user->id)) {
            // Direct service call - no HTTP request!
            $result = $this->cmsIntegration->createOrderFromGrowBuilder(
                $user->cmsUser->company_id,
                array_merge($validated, ['site_id' => $siteId])
            );

            if ($result['success']) {
                return redirect()
                    ->route('growbuilder.order.success', $result['invoice_id'])
                    ->with('success', 'Order placed successfully!');
            }
        }

        // Fallback: Store order in GrowBuilder only
        // (for users without CMS)
        return $this->storeGrowBuilderOrder($siteId, $validated);
    }
}
```

---

## Integration 2: CMS ↔ GrowMarket

### Use Case
A company using CMS wants to list their products on GrowMarket:
1. Products from CMS inventory are synced to GrowMarket
2. Orders from GrowMarket create invoices in CMS
3. Inventory is automatically updated
4. Payments are tracked

### Technical Implementation

#### 1. GrowMarket Controller (Direct Service Injection)

**File:** `app/Http/Controllers/GrowMarket/CheckoutController.php`

```php
<?php

namespace App\Http\Controllers\GrowMarket;

use App\Http\Controllers\Controller;
use App\Services\Integration\CMSIntegrationService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private CMSIntegrationService $cmsIntegration
    ) {}

    /**
     * Process marketplace order
     */
    public function processOrder(Request $request, int $listingId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|array',
        ]);

        $listing = \App\Models\GrowMarket\Listing::findOrFail($listingId);
        $seller = $listing->seller;

        // Check if seller has CMS enabled
        if ($seller->cmsUser) {
            // Direct service call to create invoice in CMS
            $result = $this->cmsIntegration->createOrderFromGrowMarket(
                $seller->cmsUser->company_id,
                [
                    'listing_id' => $listingId,
                    'product_id' => $listing->product_id,
                    'customer' => [
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'phone' => $request->user()->phone,
                    ],
                    'items' => [[
                        'product_id' => $listing->product_id,
                        'quantity' => $validated['quantity'],
                        'price' => $listing->price,
                    ]],
                    'shipping_address' => $validated['shipping_address'],
                ]
            );

            if ($result['success']) {
                // Update listing inventory
                $listing->decrement('stock_quantity', $validated['quantity']);
                
                return redirect()
                    ->route('growmarket.order.success')
                    ->with('success', 'Order placed successfully!');
            }
        }

        // Fallback for sellers without CMS
        return $this->storeMarketplaceOrder($listingId, $validated);
    }
}
```

#### 2. Product Sync Service

**File:** `app/Services/Integration/GrowMarketIntegrationService.php`

```php
<?php

namespace App\Services\Integration;

use App\Infrastructure\Persistence\Eloquent\CMS\ProductModel;
use App\Models\GrowMarket\Listing;

class GrowMarketIntegrationService
{
    /**
     * Sync product to GrowMarket (Direct database operation)
     */
    public function syncProductToMarket(int $companyId, int $productId): array
    {
        $product = ProductModel::where('company_id', $companyId)
            ->where('id', $productId)
            ->first();

        if (!$product) {
            return ['success' => false, 'error' => 'Product not found'];
        }

        // Direct database operation - no HTTP call!
        $listing = Listing::updateOrCreate(
            [
                'company_id' => $companyId,
                'product_id' => $productId,
            ],
            [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->selling_price,
                'sku' => $product->sku,
                'stock_quantity' => $product->stock_quantity,
                'category' => $product->category?->name,
                'image_url' => $product->image_url,
                'status' => 'active',
            ]
        );

        return [
            'success' => true,
            'listing_id' => $listing->id,
        ];
    }

    /**
     * Sync inventory from CMS to GrowMarket
     */
    public function syncInventory(int $companyId): void
    {
        // Get all CMS products that are synced to marketplace
        $products = ProductModel::where('company_id', $companyId)
            ->where('sync_to_market', true)
            ->get();

        foreach ($products as $product) {
            // Direct database update
            Listing::where('company_id', $companyId)
                ->where('product_id', $product->id)
                ->update([
                    'stock_quantity' => $product->stock_quantity,
                    'price' => $product->selling_price,
                ]);
        }
    }
}
```

---

## Integration 3: GrowBuilder ↔ GrowMarket

### Use Case
A GrowBuilder website can also list products on GrowMarket:
1. Products from GrowBuilder site are synced to GrowMarket
2. Orders from either platform are tracked
3. Unified inventory management

---

## Database Schema Changes

### 1. Add Integration Settings to Companies

**Migration:** `database/migrations/2026_02_20_add_integration_settings_to_cms_companies.php`

```php
Schema::table('cms_companies', function (Blueprint $table) {
    $table->json('integration_settings')->nullable()->after('settings');
});
```

**Integration Settings Structure:**
```json
{
  "growbuilder": {
    "enabled": true,
    "site_id": 123,
    "auto_sync_products": true,
    "auto_create_invoices": true,
    "sync_inventory": true
  },
  "growmarket": {
    "enabled": true,
    "seller_id": "SELLER123",
    "auto_sync_products": false,
    "commission_rate": 10
  }
}
```

### 2. Add Source Tracking

**Migration:** `database/migrations/2026_02_20_add_source_to_cms_tables.php`

```php
Schema::table('cms_invoices', function (Blueprint $table) {
    $table->string('source')->default('manual')->after('status');
    // Values: manual, growbuilder, growmarket
});

Schema::table('cms_customers', function (Blueprint $table) {
    $table->string('source')->default('manual')->after('type');
});
```

---

## Configuration

### Config File

**File:** `config/integrations.php`

```php
<?php

return [
    'growbuilder' => [
        'enabled' => env('GROWBUILDER_INTEGRATION_ENABLED', true),
        'auto_sync' => env('GROWBUILDER_AUTO_SYNC', true),
        'auto_create_invoices' => true,
    ],
    
    'growmarket' => [
        'enabled' => env('GROWMARKET_INTEGRATION_ENABLED', true),
        'commission_rate' => env('GROWMARKET_COMMISSION_RATE', 10),
        'auto_sync_inventory' => true,
    ],
];
```

---

## User Interface

### 1. Integration Settings Page

**Location:** CMS → Settings → Integrations

**Features:**
- Enable/disable GrowBuilder integration
- Link GrowBuilder site to CMS company
- Enable/disable GrowMarket integration
- Configure auto-sync settings
- View integration logs
- Test connection

### 2. Product Sync Interface

**Location:** CMS → Inventory → Products → [Product] → Integrations

**Features:**
- Sync to GrowBuilder (checkbox)
- Sync to GrowMarket (checkbox)
- View sync status
- Manual sync button
- Sync history

---

## Event-Driven Architecture

Since all modules are in the same application, we use Laravel Events for loose coupling:

### 1. Domain Events

**File:** `app/Events/CMS/InvoiceCreated.php`

```php
<?php

namespace App\Events\CMS;

use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public InvoiceModel $invoice,
        public string $source // 'growbuilder', 'growmarket', 'manual'
    ) {}
}
```

### 2. Event Listeners

**File:** `app/Listeners/CMS/NotifyGrowBuilderOfInvoice.php`

```php
<?php

namespace App\Listeners\CMS;

use App\Events\CMS\InvoiceCreated;

class NotifyGrowBuilderOfInvoice
{
    public function handle(InvoiceCreated $event): void
    {
        if ($event->source === 'growbuilder') {
            // Update GrowBuilder order status
            $siteId = $event->invoice->metadata['site_id'] ?? null;
            
            if ($siteId) {
                \App\Infrastructure\GrowBuilder\Models\Order::where('site_id', $siteId)
                    ->where('invoice_id', $event->invoice->id)
                    ->update(['status' => 'invoiced']);
            }
        }
    }
}
```

### 3. Inventory Events

**File:** `app/Events/CMS/InventoryUpdated.php`

```php
<?php

namespace App\Events\CMS;

class InventoryUpdated
{
    public function __construct(
        public int $productId,
        public int $companyId,
        public int $newQuantity
    ) {}
}
```

**Listener:** `app/Listeners/CMS/SyncInventoryToMarket.php`

```php
<?php

namespace App\Listeners\CMS;

use App\Events\CMS\InventoryUpdated;
use App\Services\Integration\GrowMarketIntegrationService;

class SyncInventoryToMarket
{
    public function __construct(
        private GrowMarketIntegrationService $marketIntegration
    ) {}

    public function handle(InventoryUpdated $event): void
    {
        // Direct database update - no HTTP call
        \App\Models\GrowMarket\Listing::where('company_id', $event->companyId)
            ->where('product_id', $event->productId)
            ->update(['stock_quantity' => $event->newQuantity]);
    }
}
```

### 4. Register Events

**File:** `app/Providers/EventServiceProvider.php`

```php
protected $listen = [
    \App\Events\CMS\InvoiceCreated::class => [
        \App\Listeners\CMS\NotifyGrowBuilderOfInvoice::class,
        \App\Listeners\CMS\SendInvoiceEmail::class,
    ],
    
    \App\Events\CMS\InventoryUpdated::class => [
        \App\Listeners\CMS\SyncInventoryToMarket::class,
        \App\Listeners\CMS\CheckLowStockAlerts::class,
    ],
];
```

## Security Considerations

### 1. Authorization
- Check user owns the CMS company
- Verify user has access to GrowBuilder site
- Validate product ownership before sync

### 2. Data Validation
- Validate all input data
- Sanitize user-provided content
- Prevent SQL injection

### 3. Data Privacy
- Only sync necessary data
- Respect user privacy settings
- Implement data retention policies
- Allow users to disconnect integrations

---

## Implementation Status

### Phase 1: Core Integration ✅ COMPLETE
- [x] CMSIntegrationService created
- [x] Event-driven architecture implemented
- [x] GrowMarketIntegrationService created
- [x] Event listeners registered
- [x] Database migrations created
- [x] Integration controller created
- [x] Configuration file added

### Implemented Files

**Services:**
- `app/Services/Integration/CMSIntegrationService.php` - Core CMS integration
- `app/Services/Integration/GrowMarketIntegrationService.php` - GrowMarket sync

**Events:**
- `app/Events/CMS/InvoiceCreated.php` - Invoice creation event
- `app/Events/CMS/InventoryUpdated.php` - Inventory update event
- `app/Events/CMS/ProductSynced.php` - Product sync event

**Listeners:**
- `app/Listeners/CMS/NotifyGrowBuilderOfInvoice.php` - GrowBuilder notification
- `app/Listeners/CMS/NotifyGrowMarketOfInvoice.php` - GrowMarket notification
- `app/Listeners/CMS/SyncInventoryToGrowMarket.php` - Inventory sync

**Controllers:**
- `app/Http/Controllers/CMS/IntegrationController.php` - Integration management

**Migrations:**
- `database/migrations/2026_02_20_120000_add_integration_fields_to_cms_products.php`
- `database/migrations/2026_02_20_120001_add_source_to_cms_tables.php`
- `database/migrations/2026_02_20_120002_add_integration_settings_to_cms_companies.php`

**Configuration:**
- `config/integrations.php` - Integration settings

### Phase 2: UI & Testing (Next)
- [ ] Create integration settings UI
- [ ] Add product sync interface
- [ ] Test order flow end-to-end
- [ ] Add integration logs viewer
- [ ] Create user documentation

### Phase 3: Advanced Features (Future)
- [ ] Real-time inventory sync
- [ ] Automated pricing rules
- [ ] Multi-channel analytics
- [ ] Bulk operations
- [ ] Advanced reporting

---

## Testing Strategy

### 1. Unit Tests
- Test integration services
- Test API endpoints
- Test data transformations

### 2. Integration Tests
- Test end-to-end order flow
- Test inventory sync
- Test webhook handling

### 3. Manual Testing
- Create test GrowBuilder site
- Place test orders
- Verify CMS invoice creation
- Test GrowMarket sync

---

## Monitoring & Logging

### 1. Integration Logs
- Log all API calls
- Log sync operations
- Log errors and failures
- Track sync status

### 2. Metrics
- Order sync success rate
- Inventory sync accuracy
- API response times
- Error rates

---

## Troubleshooting

### Common Issues

**1. Orders not syncing**
- Check API token validity
- Verify integration is enabled
- Check webhook configuration
- Review error logs

**2. Inventory mismatch**
- Force manual sync
- Check sync settings
- Review inventory movements
- Verify product mappings

**3. Customer duplicates**
- Check email matching logic
- Review customer merge rules
- Implement deduplication

---

## Future Enhancements

1. **Real-time Sync** - WebSocket-based real-time updates
2. **Multi-channel Dashboard** - Unified view of all sales channels
3. **Automated Pricing** - Dynamic pricing based on inventory/demand
4. **Advanced Analytics** - Cross-platform performance metrics
5. **Bulk Operations** - Bulk product sync and updates
6. **Custom Integrations** - API for third-party integrations

---

## Changelog

### February 20, 2026 - Implementation Complete
- Core integration services implemented
- Event-driven architecture in place
- Database migrations created
- Integration controller added
- GrowMarket sync service complete
- Event listeners registered
- Configuration file added
- Documentation updated

### February 20, 2026 - Initial Design
- Initial design document created
- Architecture defined
- Implementation phases outlined

---

## Related Documentation

- `docs/cms/COMPLETE_FEATURE_SPECIFICATION.md` - CMS features
- `docs/MYGROWNET_PLATFORM_CONCEPT.md` - Platform overview
- API documentation (to be created)


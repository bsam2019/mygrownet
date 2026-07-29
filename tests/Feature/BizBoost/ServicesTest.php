<?php

namespace Tests\Feature\BizBoost;

use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Services\SaleService;
use App\Domain\BizBoost\Services\DashboardService;
use App\Domain\BizBoost\Services\CustomerService;
use App\Domain\BizBoost\Services\ProductService;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTBusinessRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTCustomerRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTProductRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTSaleRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTPostRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTAiUsageLogRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTIntegrationRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTCategoryRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTCustomerTagRepository;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected BizBoostBusinessModel $businessModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'svc@bizboost.test',
            'name' => 'Svc Test User',
        ]);

        $this->businessModel = BizBoostBusinessModel::create([
            'user_id' => $this->user->id,
            'name' => 'Svc Test Biz',
            'slug' => 'svc-test-biz',
            'industry' => 'boutique',
            'description' => 'Test business for service tests',
            'phone' => '+260971234567',
            'whatsapp' => '+260971234567',
            'email' => 'svc@testbusiness.com',
            'city' => 'Lusaka',
            'country' => 'Zambia',
            'timezone' => 'Africa/Lusaka',
            'locale' => 'en',
            'currency' => 'ZMW',
            'is_active' => true,
            'onboarding_completed' => false,
        ]);
    }

    public function test_business_service_get_by_user(): void
    {
        $service = new BusinessService(new EloquentBusinessRepository());

        $business = $service->getBusinessByUser($this->user->id);
        $this->assertNotNull($business);
        $this->assertSame('Svc Test Biz', $business->name);
    }

    public function test_business_service_create_or_update_new(): void
    {
        $service = new BusinessService(new EloquentBusinessRepository());

        $business = $service->createOrUpdate([
            'user_id' => $this->user->id,
            'name' => 'New Business',
        ]);

        $this->assertNotNull($business->id);
        $this->assertSame('New Business', $business->name);
    }

    public function test_business_service_update_existing(): void
    {
        $service = new BusinessService(new EloquentBusinessRepository());

        $updated = $service->updateBusiness($this->businessModel->id, [
            'name' => 'Updated Name',
            'city' => 'Ndola',
            'timezone' => 'Africa/Lusaka',
        ]);

        $this->assertNotNull($updated);
        $this->assertSame('Updated Name', $updated->name);
        $this->assertSame('Ndola', $updated->city);
    }

    public function test_business_service_update_nonexistent_returns_null(): void
    {
        $service = new BusinessService(new EloquentBusinessRepository());

        $result = $service->updateBusiness(99999, ['name' => 'Nope']);
        $this->assertNull($result);
    }

    public function test_business_service_complete_onboarding(): void
    {
        $service = new BusinessService(new EloquentBusinessRepository());

        $service->completeOnboarding($this->businessModel->id);

        $business = $service->getBusinessByUser($this->user->id);
        $this->assertTrue($business->onboardingCompleted);
    }

    public function test_business_service_find_by_slug(): void
    {
        $service = new BusinessService(new EloquentBusinessRepository());

        $found = $service->findBusinessBySlug('svc-test-biz');
        $this->assertNotNull($found);

        $notFound = $service->findBusinessBySlug('non-existent');
        $this->assertNull($notFound);
    }

    public function test_sale_service_create(): void
    {
        $saleRepo = new EloquentSaleRepository();
        $customerRepo = new EloquentCustomerRepository();
        $service = new SaleService($saleRepo, $customerRepo);

        $sale = $service->createSale([
            'business_id' => $this->businessModel->id,
            'sale_date' => '2026-07-29',
            'product_name' => 'Widget',
            'quantity' => 2,
            'unit_price' => 50.0,
            'total_amount' => 100.0,
        ]);

        $this->assertNotNull($sale->id);
        $this->assertSame(100.0, $sale->totalAmount);
    }

    public function test_sale_service_get_stats(): void
    {
        $saleRepo = new EloquentSaleRepository();
        $customerRepo = new EloquentCustomerRepository();
        $service = new SaleService($saleRepo, $customerRepo);

        $service->createSale([
            'business_id' => $this->businessModel->id,
            'sale_date' => now()->format('Y-m-d'),
            'product_name' => 'Widget',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
        ]);

        $today = now()->format('Y-m-d');
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');
        $startOfLastMonth = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $endOfLastMonth = now()->subMonth()->endOfMonth()->format('Y-m-d');

        $stats = $service->getStats(
            $this->businessModel->id,
            $today, $startOfWeek, $startOfMonth, $endOfMonth,
            $startOfLastMonth, $endOfLastMonth
        );

        $this->assertSame(100.0, $stats['today']);
        $this->assertSame(100.0, $stats['this_month']);
        $this->assertArrayHasKey('month_change', $stats);
    }

    public function test_sale_service_delete(): void
    {
        $saleRepo = new EloquentSaleRepository();
        $customerRepo = new EloquentCustomerRepository();
        $service = new SaleService($saleRepo, $customerRepo);

        $sale = $service->createSale([
            'business_id' => $this->businessModel->id,
            'sale_date' => '2026-07-29',
            'product_name' => 'Widget',
            'quantity' => 1,
            'unit_price' => 50,
            'total_amount' => 50,
        ]);

        $service->deleteSale($sale->id);
        $this->assertNull($service->findSale($sale->id));
    }

    public function test_customer_service_create_and_find(): void
    {
        $customerRepo = new EloquentCustomerRepository();
        $tagRepo = new EloquentCustomerTagRepository();
        $service = new CustomerService($customerRepo, $tagRepo);

        $customer = $service->createCustomer([
            'business_id' => $this->businessModel->id,
            'name' => 'Service Customer',
            'phone' => '+260971000001',
        ]);

        $this->assertNotNull($customer->id);
        $this->assertSame('Service Customer', $customer->name);

        $found = $service->findCustomer($customer->id);
        $this->assertNotNull($found);
    }

    public function test_customer_service_update(): void
    {
        $customerRepo = new EloquentCustomerRepository();
        $tagRepo = new EloquentCustomerTagRepository();
        $service = new CustomerService($customerRepo, $tagRepo);

        $customer = $service->createCustomer([
            'business_id' => $this->businessModel->id,
            'name' => 'Old Name',
            'phone' => '+260971000001',
        ]);

        $updated = $service->updateCustomer($customer->id, ['name' => 'New Name']);
        $this->assertNotNull($updated);
        $this->assertSame('New Name', $updated->name);
    }

    public function test_customer_service_delete(): void
    {
        $customerRepo = new EloquentCustomerRepository();
        $tagRepo = new EloquentCustomerTagRepository();
        $service = new CustomerService($customerRepo, $tagRepo);

        $customer = $service->createCustomer([
            'business_id' => $this->businessModel->id,
            'name' => 'To Delete',
            'phone' => '+260971000001',
        ]);

        $service->deleteCustomer($customer->id);
        $this->assertNull($service->findCustomer($customer->id));
    }

    public function test_product_service_create_and_find(): void
    {
        $productRepo = new EloquentProductRepository();
        $categoryRepo = new EloqueNTCategoryRepository();
        $businessRepo = new EloquentBusinessRepository();
        $service = new ProductService($productRepo, $categoryRepo, $businessRepo);

        $product = $service->createProduct([
            'business_id' => $this->businessModel->id,
            'name' => 'Service Product',
            'price' => 75.0,
        ]);

        $this->assertNotNull($product->id);
        $this->assertSame('Service Product', $product->name);

        $found = $service->findProduct($product->id);
        $this->assertNotNull($found);
    }

    public function test_product_service_update(): void
    {
        $productRepo = new EloquentProductRepository();
        $categoryRepo = new EloqueNTCategoryRepository();
        $businessRepo = new EloquentBusinessRepository();
        $service = new ProductService($productRepo, $categoryRepo, $businessRepo);

        $product = $service->createProduct([
            'business_id' => $this->businessModel->id,
            'name' => 'Old Product',
            'price' => 50.0,
        ]);

        $updated = $service->updateProduct($product->id, ['name' => 'New Product', 'price' => 100.0]);
        $this->assertNotNull($updated);
        $this->assertSame('New Product', $updated->name);
        $this->assertSame(100.0, $updated->price);
    }

    public function test_product_service_delete(): void
    {
        $productRepo = new EloquentProductRepository();
        $categoryRepo = new EloqueNTCategoryRepository();
        $businessRepo = new EloquentBusinessRepository();
        $service = new ProductService($productRepo, $categoryRepo, $businessRepo);

        $product = $service->createProduct([
            'business_id' => $this->businessModel->id,
            'name' => 'To Delete',
            'price' => 10.0,
        ]);

        $service->deleteProduct($product->id);
        $this->assertNull($service->findProduct($product->id));
    }

    public function test_dashboard_service_get_stats(): void
    {
        $saleRepo = new EloquentSaleRepository();
        $customerRepo = new EloquentCustomerRepository();
        $postRepo = new EloquentPostRepository();
        $productRepo = new EloquentProductRepository();
        $integrationRepo = new EloquentIntegrationRepository();
        $aiUsageRepo = new EloquentAiUsageLogRepository();
        $businessRepo = new EloquentBusinessRepository();

        $dashboardService = new DashboardService(
            $businessRepo, $postRepo, $saleRepo, $customerRepo,
            $productRepo, $integrationRepo, $aiUsageRepo
        );

        $customerRepo->save(new \App\Domain\BizBoost\Entities\Customer(
            id: null, businessId: $this->businessModel->id, name: 'C1', phone: '+260971000001',
            email: null, whatsapp: null, address: null, notes: null, source: null, birthday: null,
            totalSpent: 0, totalOrders: 0, lastPurchaseAt: null, isActive: true, createdAt: null, updatedAt: null,
        ));

        $productRepo->save(\App\Domain\BizBoost\Entities\Product::create([
            'business_id' => $this->businessModel->id,
            'name' => 'P1',
            'price' => 50,
        ]));

        $stats = $dashboardService->getStats($this->businessModel->id);

        $this->assertSame(1, $stats['customers']);
        $this->assertSame(1, $stats['products']);
        $this->assertArrayHasKey('sales_detail', $stats);
        $this->assertArrayHasKey('posts_detail', $stats);
    }

    public function test_dashboard_service_sparkline(): void
    {
        $saleRepo = new EloquentSaleRepository();
        $customerRepo = new EloquentCustomerRepository();
        $postRepo = new EloquentPostRepository();
        $productRepo = new EloquentProductRepository();
        $integrationRepo = new EloquentIntegrationRepository();
        $aiUsageRepo = new EloquentAiUsageLogRepository();
        $businessRepo = new EloquentBusinessRepository();

        $dashboardService = new DashboardService(
            $businessRepo, $postRepo, $saleRepo, $customerRepo,
            $productRepo, $integrationRepo, $aiUsageRepo
        );

        $data = $dashboardService->getSparklineData($this->businessModel->id);

        $this->assertArrayHasKey('sales', $data);
        $this->assertArrayHasKey('customers', $data);
        $this->assertArrayHasKey('posts', $data);
        $this->assertArrayHasKey('products', $data);
        $this->assertCount(7, $data['sales']);
    }
}

<?php

namespace Tests\Feature\BizBoost;

use App\Domain\BizBoost\Entities\Business;
use App\Domain\BizBoost\Entities\Customer;
use App\Domain\BizBoost\Entities\Product;
use App\Domain\BizBoost\Entities\Sale;
use App\Domain\BizBoost\Entities\Post;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTBusinessRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTCustomerRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTProductRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTSaleRepository;
use App\Infrastructure\Persistence\Repositories\BizBoost\EloqueNTPostRepository;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoriesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected BizBoostBusinessModel $businessModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'repo@bizboost.test',
            'name' => 'Repo Test User',
        ]);

        $this->businessModel = BizBoostBusinessModel::create([
            'user_id' => $this->user->id,
            'name' => 'Repo Test Biz',
            'slug' => 'repo-test-biz',
            'industry' => 'boutique',
            'description' => 'Test business for repo tests',
            'phone' => '+260971234567',
            'whatsapp' => '+260971234567',
            'email' => 'repo@testbusiness.com',
            'city' => 'Lusaka',
            'country' => 'Zambia',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
    }

    public function test_business_repository_save_and_find(): void
    {
        $repo = new EloquentBusinessRepository();

        $entity = Business::create([
            'user_id' => $this->user->id,
            'name' => 'New Biz',
            'slug' => 'new-biz-' . uniqid(),
        ]);

        $saved = $repo->save($entity);
        $this->assertNotNull($saved->id);
        $this->assertSame('New Biz', $saved->name);

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame('New Biz', $found->name);
    }

    public function test_business_repository_update(): void
    {
        $repo = new EloquentBusinessRepository();

        $found = $repo->findById($this->businessModel->id);
        $this->assertNotNull($found);

        $updated = new Business(
            id: $found->id,
            userId: $found->userId,
            organizationId: $found->organizationId,
            name: 'Updated Name',
            slug: $found->slug,
            description: $found->description,
            logoPath: $found->logoPath,
            industry: $found->industry,
            address: $found->address,
            city: 'Ndola',
            province: $found->province,
            country: $found->country,
            phone: $found->phone,
            whatsapp: $found->whatsapp,
            email: $found->email,
            website: $found->website,
            timezone: $found->timezone,
            locale: $found->locale,
            currency: $found->currency,
            socialLinks: $found->socialLinks,
            businessHours: $found->businessHours,
            settings: $found->settings,
            whiteLabelConfig: $found->whiteLabelConfig,
            isActive: $found->isActive,
            onboardingCompleted: $found->onboardingCompleted,
            marketplaceListed: $found->marketplaceListed,
            marketplaceListedAt: $found->marketplaceListedAt,
            marketplaceSellerId: $found->marketplaceSellerId,
            marketplaceSyncEnabled: $found->marketplaceSyncEnabled,
            marketplaceSyncedAt: $found->marketplaceSyncedAt,
            createdAt: $found->createdAt,
            updatedAt: $found->updatedAt,
        );

        $repo->save($updated);

        $refound = $repo->findById($this->businessModel->id);
        $this->assertSame('Updated Name', $refound->name);
        $this->assertSame('Ndola', $refound->city);
    }

    public function test_business_repository_find_by_slug(): void
    {
        $repo = new EloquentBusinessRepository();

        $found = $repo->findBySlug('repo-test-biz');
        $this->assertNotNull($found);
        $this->assertSame('Repo Test Biz', $found->name);

        $notFound = $repo->findBySlug('non-existent');
        $this->assertNull($notFound);
    }

    public function test_business_repository_find_by_user_id(): void
    {
        $repo = new EloquentBusinessRepository();

        $found = $repo->findByUserId($this->user->id);
        $this->assertNotNull($found);
        $this->assertSame('Repo Test Biz', $found->name);
    }

    public function test_business_repository_get_ids_by_user_id(): void
    {
        $repo = new EloquentBusinessRepository();

        $ids = $repo->getBusinessIdsByUserId($this->user->id);
        $this->assertCount(1, $ids);
        $this->assertContains($this->businessModel->id, $ids);
    }

    public function test_business_repository_delete(): void
    {
        $repo = new EloquentBusinessRepository();

        $repo->delete($this->businessModel->id);
        $this->assertNull($repo->findById($this->businessModel->id));
    }

    public function test_customer_repository_save_and_find(): void
    {
        $repo = new EloquentCustomerRepository();

        $entity = Customer::create([
            'business_id' => $this->businessModel->id,
            'name' => 'John Doe',
            'phone' => '+260971111111',
        ]);

        $saved = $repo->save($entity);
        $this->assertNotNull($saved->id);
        $this->assertSame('John Doe', $saved->name);

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame('John Doe', $found->name);
    }

    public function test_customer_repository_update(): void
    {
        $repo = new EloquentCustomerRepository();

        $entity = Customer::create([
            'business_id' => $this->businessModel->id,
            'name' => 'Jane',
            'phone' => '+260972222222',
        ]);
        $saved = $repo->save($entity);

        $updatedEntity = new Customer(
            id: $saved->id,
            businessId: $saved->businessId,
            name: 'Jane Updated',
            phone: '+260972222222',
            email: $saved->email,
            whatsapp: $saved->whatsapp,
            address: $saved->address,
            notes: $saved->notes,
            source: $saved->source,
            birthday: $saved->birthday,
            totalSpent: $saved->totalSpent,
            totalOrders: $saved->totalOrders,
            lastPurchaseAt: $saved->lastPurchaseAt,
            isActive: $saved->isActive,
            createdAt: $saved->createdAt,
            updatedAt: $saved->updatedAt,
        );

        $repo->save($updatedEntity);

        $refound = $repo->findById($saved->id);
        $this->assertSame('Jane Updated', $refound->name);
    }

    public function test_customer_repository_find_by_business(): void
    {
        $repo = new EloquentCustomerRepository();

        for ($i = 0; $i < 3; $i++) {
            $repo->save(Customer::create([
                'business_id' => $this->businessModel->id,
                'name' => "Customer $i",
                'phone' => "+26097100000$i",
            ]));
        }

        $customers = $repo->findByBusiness($this->businessModel->id);
        $this->assertCount(3, $customers);
    }

    public function test_customer_repository_find_by_business_with_search(): void
    {
        $repo = new EloquentCustomerRepository();

        $repo->save(Customer::create([
            'business_id' => $this->businessModel->id,
            'name' => 'Alice',
            'phone' => '+260971000001',
        ]));
        $repo->save(Customer::create([
            'business_id' => $this->businessModel->id,
            'name' => 'Bob',
            'phone' => '+260971000002',
        ]));

        $results = $repo->findByBusiness($this->businessModel->id, ['search' => 'Alice']);
        $this->assertCount(1, $results);
        $this->assertSame('Alice', $results[0]->name);
    }

    public function test_customer_repository_delete(): void
    {
        $repo = new EloquentCustomerRepository();

        $saved = $repo->save(Customer::create([
            'business_id' => $this->businessModel->id,
            'name' => 'To Delete',
            'phone' => '+260971999999',
        ]));

        $repo->delete($saved->id);
        $this->assertNull($repo->findById($saved->id));
    }

    public function test_customer_repository_count_by_business(): void
    {
        $repo = new EloquentCustomerRepository();

        $this->assertSame(0, $repo->countByBusiness($this->businessModel->id));

        $repo->save(Customer::create([
            'business_id' => $this->businessModel->id,
            'name' => 'C1',
            'phone' => '+260971000001',
        ]));

        $this->assertSame(1, $repo->countByBusiness($this->businessModel->id));
    }

    public function test_product_repository_save_and_find(): void
    {
        $repo = new EloquentProductRepository();

        $entity = Product::create([
            'business_id' => $this->businessModel->id,
            'name' => 'Widget',
            'price' => 50.0,
        ]);

        $saved = $repo->save($entity);
        $this->assertNotNull($saved->id);
        $this->assertSame('Widget', $saved->name);

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame('Widget', $found->name);
        $this->assertSame(50.0, $found->price);
    }

    public function test_product_repository_update(): void
    {
        $repo = new EloquentProductRepository();

        $saved = $repo->save(Product::create([
            'business_id' => $this->businessModel->id,
            'name' => 'Old Name',
            'price' => 100.0,
        ]));

        $updatedEntity = new Product(
            id: $saved->id,
            businessId: $saved->businessId,
            name: 'New Name',
            sku: $saved->sku,
            description: $saved->description,
            price: 150.0,
            salePrice: $saved->salePrice,
            currency: $saved->currency,
            category: $saved->category,
            categoryId: $saved->categoryId,
            stockQuantity: $saved->stockQuantity,
            trackInventory: $saved->trackInventory,
            isActive: $saved->isActive,
            isFeatured: $saved->isFeatured,
            sortOrder: $saved->sortOrder,
            attributes: $saved->attributes,
            createdAt: $saved->createdAt,
            updatedAt: $saved->updatedAt,
        );

        $repo->save($updatedEntity);

        $refound = $repo->findById($saved->id);
        $this->assertSame('New Name', $refound->name);
        $this->assertSame(150.0, $refound->price);
    }

    public function test_product_repository_find_by_business(): void
    {
        $repo = new EloquentProductRepository();

        $repo->save(Product::create(['business_id' => $this->businessModel->id, 'name' => 'P1', 'price' => 10]));
        $repo->save(Product::create(['business_id' => $this->businessModel->id, 'name' => 'P2', 'price' => 20]));
        $repo->save(Product::create(['business_id' => $this->businessModel->id, 'name' => 'P3', 'price' => 30]));

        $products = $repo->findByBusiness($this->businessModel->id);
        $this->assertCount(3, $products);
    }

    public function test_product_repository_find_active_by_business(): void
    {
        $repo = new EloquentProductRepository();

        $repo->save(Product::create(['business_id' => $this->businessModel->id, 'name' => 'Active', 'price' => 10]));
        $inactive = Product::create(['business_id' => $this->businessModel->id, 'name' => 'Inactive', 'price' => 20]);

        $repo->save(new Product(
            id: null, businessId: $inactive->businessId, name: $inactive->name,
            sku: $inactive->sku, description: $inactive->description, price: $inactive->price,
            salePrice: $inactive->salePrice, currency: $inactive->currency,
            category: $inactive->category, categoryId: $inactive->categoryId,
            stockQuantity: $inactive->stockQuantity, trackInventory: $inactive->trackInventory,
            isActive: false, isFeatured: $inactive->isFeatured, sortOrder: $inactive->sortOrder,
            attributes: $inactive->attributes, createdAt: null, updatedAt: null,
        ));

        $active = $repo->findActiveByBusiness($this->businessModel->id);
        $this->assertCount(1, $active);
    }

    public function test_product_repository_count_by_business(): void
    {
        $repo = new EloquentProductRepository();
        $this->assertSame(0, $repo->countByBusiness($this->businessModel->id));
    }

    public function test_product_repository_delete(): void
    {
        $repo = new EloquentProductRepository();

        $saved = $repo->save(Product::create(['business_id' => $this->businessModel->id, 'name' => 'Del', 'price' => 10]));
        $repo->delete($saved->id);
        $this->assertNull($repo->findById($saved->id));
    }

    public function test_sale_repository_save_and_find(): void
    {
        $repo = new EloquentSaleRepository();

        $entity = Sale::create([
            'business_id' => $this->businessModel->id,
            'sale_date' => '2026-07-29',
            'quantity' => 2,
            'unit_price' => 50.0,
            'total_amount' => 100.0,
        ]);

        $saved = $repo->save($entity);
        $this->assertNotNull($saved->id);

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame(100.0, $found->totalAmount);
    }

    public function test_sale_repository_update(): void
    {
        $repo = new EloquentSaleRepository();

        $saved = $repo->save(Sale::create([
            'business_id' => $this->businessModel->id,
            'sale_date' => '2026-07-29',
            'quantity' => 1,
            'unit_price' => 100.0,
            'total_amount' => 100.0,
        ]));

        $updatedEntity = new Sale(
            id: $saved->id,
            businessId: $saved->businessId,
            customerId: $saved->customerId,
            productId: $saved->productId,
            productName: $saved->productName,
            quantity: $saved->quantity,
            unitPrice: $saved->unitPrice,
            totalAmount: 200.0,
            currency: $saved->currency,
            saleDate: $saved->saleDate,
            paymentMethod: $saved->paymentMethod,
            source: $saved->source,
            linkedPostId: $saved->linkedPostId,
            notes: $saved->notes,
            createdAt: $saved->createdAt,
            updatedAt: $saved->updatedAt,
        );

        $repo->save($updatedEntity);

        $refound = $repo->findById($saved->id);
        $this->assertSame(200.0, $refound->totalAmount);
    }

    public function test_sale_repository_find_by_business(): void
    {
        $repo = new EloquentSaleRepository();

        $repo->save(Sale::create(['business_id' => $this->businessModel->id, 'sale_date' => '2026-07-29']));
        $repo->save(Sale::create(['business_id' => $this->businessModel->id, 'sale_date' => '2026-07-30', 'total_amount' => 50]));

        $sales = $repo->findByBusiness($this->businessModel->id);
        $this->assertCount(2, $sales);
    }

    public function test_sale_repository_sum_by_business(): void
    {
        $repo = new EloquentSaleRepository();

        $repo->save(Sale::create(['business_id' => $this->businessModel->id, 'sale_date' => '2026-07-01', 'quantity' => 1, 'unit_price' => 100, 'total_amount' => 100]));
        $repo->save(Sale::create(['business_id' => $this->businessModel->id, 'sale_date' => '2026-07-15', 'quantity' => 1, 'unit_price' => 200, 'total_amount' => 200]));
        $repo->save(Sale::create(['business_id' => $this->businessModel->id, 'sale_date' => '2026-08-01', 'quantity' => 1, 'unit_price' => 300, 'total_amount' => 300]));

        $total = $repo->sumByBusiness($this->businessModel->id);
        $this->assertSame(600.0, $total);

        $filtered = $repo->sumByBusiness($this->businessModel->id, [
            'date_from' => '2026-07-01',
            'date_to' => '2026-07-31',
        ]);
        $this->assertSame(300.0, $filtered);
    }

    public function test_sale_repository_delete(): void
    {
        $repo = new EloquentSaleRepository();

        $saved = $repo->save(Sale::create(['business_id' => $this->businessModel->id, 'sale_date' => '2026-07-29']));
        $repo->delete($saved->id);
        $this->assertNull($repo->findById($saved->id));
    }

    public function test_post_repository_save_and_find(): void
    {
        $repo = new EloquentPostRepository();

        $entity = Post::create([
            'business_id' => $this->businessModel->id,
            'caption' => 'Test post',
            'status' => 'draft',
        ]);

        $saved = $repo->save($entity);
        $this->assertNotNull($saved->id);
        $this->assertSame('Test post', $saved->caption);

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame('Test post', $found->caption);
    }

    public function test_post_repository_update(): void
    {
        $repo = new EloquentPostRepository();

        $saved = $repo->save(Post::create([
            'business_id' => $this->businessModel->id,
            'caption' => 'Original',
            'status' => 'draft',
        ]));

        $updatedEntity = new Post(
            id: $saved->id,
            businessId: $saved->businessId,
            title: $saved->title,
            caption: 'Updated caption',
            status: 'published',
            scheduledAt: $saved->scheduledAt,
            publishedAt: now()->toDateTimeString(),
            platformTargets: $saved->platformTargets,
            externalIds: $saved->externalIds,
            analytics: $saved->analytics,
            postType: $saved->postType,
            templateId: $saved->templateId,
            campaignId: $saved->campaignId,
            errorMessage: $saved->errorMessage,
            retryCount: $saved->retryCount,
            createdAt: $saved->createdAt,
            updatedAt: $saved->updatedAt,
        );

        $repo->save($updatedEntity);

        $refound = $repo->findById($saved->id);
        $this->assertSame('Updated caption', $refound->caption);
        $this->assertSame('published', $refound->status);
    }

    public function test_post_repository_find_by_business(): void
    {
        $repo = new EloquentPostRepository();

        $repo->save(Post::create(['business_id' => $this->businessModel->id, 'caption' => 'P1', 'status' => 'draft']));
        $repo->save(Post::create(['business_id' => $this->businessModel->id, 'caption' => 'P2', 'status' => 'published']));

        $posts = $repo->findByBusiness($this->businessModel->id);
        $this->assertCount(2, $posts);
    }

    public function test_post_repository_filter_by_status(): void
    {
        $repo = new EloquentPostRepository();

        $repo->save(Post::create(['business_id' => $this->businessModel->id, 'caption' => 'Draft', 'status' => 'draft']));
        $repo->save(Post::create(['business_id' => $this->businessModel->id, 'caption' => 'Published', 'status' => 'published']));

        $drafts = $repo->findByBusiness($this->businessModel->id, ['status' => 'draft']);
        $this->assertCount(1, $drafts);
        $this->assertSame('draft', $drafts[0]->status);
    }

    public function test_post_repository_count_by_business(): void
    {
        $repo = new EloquentPostRepository();

        $this->assertSame(0, $repo->countByBusiness($this->businessModel->id));

        $repo->save(Post::create(['business_id' => $this->businessModel->id, 'caption' => 'Test', 'status' => 'draft']));
        $this->assertSame(1, $repo->countByBusiness($this->businessModel->id));
    }

    public function test_post_repository_delete(): void
    {
        $repo = new EloquentPostRepository();

        $saved = $repo->save(Post::create(['business_id' => $this->businessModel->id, 'caption' => 'Del', 'status' => 'draft']));
        $repo->delete($saved->id);
        $this->assertNull($repo->findById($saved->id));
    }

    public function test_customer_update_purchase_stats_after_sale(): void
    {
        $customerRepo = new EloquentCustomerRepository();
        $saleRepo = new EloquentSaleRepository();

        $customer = $customerRepo->save(Customer::create([
            'business_id' => $this->businessModel->id,
            'name' => 'Stats Test',
            'phone' => '+260971000000',
        ]));

        $sale = $saleRepo->save(Sale::create([
            'business_id' => $this->businessModel->id,
            'customer_id' => $customer->id,
            'sale_date' => '2026-07-29',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
        ]));

        $customerRepo->updatePurchaseStats($customer->id);
        $updated = $customerRepo->findById($customer->id);

        $this->assertSame(100.0, $updated->totalSpent);
        $this->assertSame(1, $updated->totalOrders);
    }

    public function test_sale_repository_get_sales_report(): void
    {
        $repo = new EloquentSaleRepository();

        $repo->save(Sale::create(['business_id' => $this->businessModel->id, 'sale_date' => '2026-07-01', 'product_name' => 'Widget', 'quantity' => 1, 'unit_price' => 100, 'total_amount' => 100, 'payment_method' => 'cash']));
        $repo->save(Sale::create(['business_id' => $this->businessModel->id, 'sale_date' => '2026-07-02', 'product_name' => 'Gadget', 'quantity' => 1, 'unit_price' => 200, 'total_amount' => 200, 'payment_method' => 'mobile']));

        $report = $repo->getSalesReport($this->businessModel->id, '2026-07-01', '2026-07-31');

        $this->assertArrayHasKey('by_day', $report);
        $this->assertArrayHasKey('top_products', $report);
        $this->assertArrayHasKey('by_payment', $report);
        $this->assertCount(2, $report['by_day']);
    }
}

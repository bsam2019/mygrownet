<?php

namespace Tests\Feature\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Order;
use App\Domain\GrowBuilder\Repositories\OrderRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\OrderId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepositoryInterface $repository;
    private GrowBuilderSite $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(OrderRepositoryInterface::class);
        $user = User::factory()->create();
        $this->site = GrowBuilderSite::create([
            'user_id' => $user->id,
            'name' => 'Shop',
            'subdomain' => 'ordershop',
            'status' => 'published',
            'plan' => 'business',
        ]);
    }

    private function createOrder(int $subtotal = 10000): Order
    {
        return Order::create(
            siteId: $this->site->id,
            customerName: 'John',
            customerPhone: '+260977000000',
            items: [['name' => 'Item', 'quantity' => 1, 'price' => $subtotal]],
            subtotalInNgwee: $subtotal,
            shippingCostInNgwee: 0,
        );
    }

    public function test_save_and_find_by_id(): void
    {
        $order = $this->createOrder();
        $saved = $this->repository->save($order);

        $this->assertNotNull($saved->getId());

        $found = $this->repository->findById($saved->getId());
        $this->assertNotNull($found);
        $this->assertEquals('John', $found->getCustomerName());
        $this->assertEquals(10000, $found->getSubtotal()->getAmountInNgwee());
    }

    public function test_find_by_site_id(): void
    {
        $this->repository->save($this->createOrder(5000));
        $this->repository->save($this->createOrder(8000));

        $orders = $this->repository->findBySiteId(SiteId::fromInt($this->site->id));
        $this->assertCount(2, $orders);
    }

    public function test_find_by_site_id_paginated(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->repository->save($this->createOrder(1000 * $i));
        }

        $paginator = $this->repository->findBySiteIdPaginated(SiteId::fromInt($this->site->id), 2);
        $this->assertEquals(5, $paginator->total());
        $this->assertCount(2, $paginator->items());
    }

    public function test_find_by_id_for_site(): void
    {
        $order = $this->createOrder();
        $saved = $this->repository->save($order);

        $found = $this->repository->findByIdForSite($saved->getId(), SiteId::fromInt($this->site->id));
        $this->assertNotNull($found);

        $notFound = $this->repository->findByIdForSite($saved->getId(), SiteId::fromInt(9999));
        $this->assertNull($notFound);
    }

    public function test_count_by_site_id(): void
    {
        $this->repository->save($this->createOrder());
        $this->repository->save($this->createOrder());

        $this->assertEquals(2, $this->repository->countBySiteId(SiteId::fromInt($this->site->id)));
    }

    public function test_count_by_status(): void
    {
        $o1 = $this->createOrder();
        $this->repository->save($o1);

        $o2 = $this->createOrder();
        $o2->markAsPaid('REF');
        $this->repository->save($o2);

        $pending = $this->repository->countByStatus(SiteId::fromInt($this->site->id), 'pending');
        $paid = $this->repository->countByStatus(SiteId::fromInt($this->site->id), 'paid');

        $this->assertEquals(1, $pending);
        $this->assertEquals(1, $paid);
    }

    public function test_sum_total_by_site_id(): void
    {
        $o1 = $this->createOrder(10000);
        $o1->markAsPaid('REF1');
        $this->repository->save($o1);

        $o2 = $this->createOrder(20000);
        $o2->markAsPaid('REF2');
        $this->repository->save($o2);

        $total = $this->repository->sumTotalBySiteId(SiteId::fromInt($this->site->id));
        $this->assertEquals(30000, $total);
    }

    public function test_sum_total_paid(): void
    {
        $o1 = $this->createOrder(10000);
        $o1->markAsPaid('REF1');
        $this->repository->save($o1);

        $o2 = $this->createOrder(20000);
        $o2->markAsPaid('REF2');
        $this->repository->save($o2);

        $o3 = $this->createOrder(30000); // unpaid
        $this->repository->save($o3);

        $paidTotal = $this->repository->sumTotalPaidBySiteId(SiteId::fromInt($this->site->id));
        $this->assertEquals(30000, $paidTotal);
    }

    public function test_save_updates_status(): void
    {
        $order = $this->createOrder();
        $saved = $this->repository->save($order);

        $saved->markAsPaid('TXN123');
        $this->repository->save($saved);

        $found = $this->repository->findById($saved->getId());
        $this->assertTrue($found->isPaid());
        $this->assertEquals('TXN123', $found->getPaymentReference());
        $this->assertNotNull($found->getPaidAt());
    }

    public function test_save_preserves_discount(): void
    {
        $order = $this->createOrder(10000);
        $saved = $this->repository->save($order);

        $saved->applyDiscount('SAVE500', 500);
        $this->repository->save($saved);

        $found = $this->repository->findById($saved->getId());
        $this->assertEquals('SAVE500', $found->getDiscountCode());
        $this->assertEquals(500, $found->getDiscountAmount()->getAmountInNgwee());
    }

    public function test_delete(): void
    {
        $order = $this->createOrder();
        $saved = $this->repository->save($order);

        $this->repository->delete($saved->getId());

        $this->assertNull($this->repository->findById($saved->getId()));
    }

    public function test_save_generates_order_number(): void
    {
        $order = $this->createOrder();
        $saved = $this->repository->save($order);

        $this->assertStringStartsWith('GB-', $saved->getOrderNumber());
    }

    public function test_sequential_full_lifecycle(): void
    {
        $order = $this->createOrder();
        $saved = $this->repository->save($order);

        $saved->markAsPaid('PAY123');
        $this->repository->save($saved);

        $saved->markAsProcessing();
        $this->repository->save($saved);

        $saved->markAsShipped();
        $this->repository->save($saved);

        $saved->markAsDelivered();
        $this->repository->save($saved);

        $saved->markAsCompleted();
        $this->repository->save($saved);

        $found = $this->repository->findById($saved->getId());
        $this->assertTrue($found->getStatus()->isCompleted());
        $this->assertNotNull($found->getPaidAt());
        $this->assertNotNull($found->getShippedAt());
        $this->assertNotNull($found->getDeliveredAt());
    }

    public function test_save_with_customer_email(): void
    {
        $order = Order::create(
            siteId: $this->site->id,
            customerName: 'Jane',
            customerPhone: '+260977000000',
            items: [['name' => 'A', 'quantity' => 1, 'price' => 5000]],
            subtotalInNgwee: 5000,
            shippingCostInNgwee: 0,
            customerEmail: 'jane@example.com',
            customerAddress: 'Lusaka',
        );
        $saved = $this->repository->save($order);

        $found = $this->repository->findById($saved->getId());
        $this->assertEquals('jane@example.com', $found->getCustomerEmail());
        $this->assertEquals('Lusaka', $found->getCustomerAddress());
    }
}

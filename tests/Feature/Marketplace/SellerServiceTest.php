<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Services\SellerService;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentSellerRepository;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceSeller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerServiceTest extends TestCase
{
    use RefreshDatabase;

    private SellerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = new EloquentSellerRepository();
        $this->service = new SellerService($repo);
    }

    public function test_register_creates_seller(): void
    {
        $user = User::factory()->create();

        $result = $this->service->register($user->id, [
            'business_name' => 'My Shop',
            'province' => 'Lusaka',
            'district' => 'Lusaka',
        ]);

        $this->assertNotNull($result['id']);
        $this->assertEquals('My Shop', $result['business_name']);
        $this->assertEquals('new', $result['trust_level']);
        $this->assertFalse($result['is_active']);
    }

    public function test_getByUserId_returns_seller(): void
    {
        $user = User::factory()->create();
        $this->service->register($user->id, ['business_name' => 'Shop', 'province' => 'Lusaka', 'district' => 'Lusaka']);

        $result = $this->service->getByUserId($user->id);
        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result['user_id']);
    }

    public function test_getByUserId_returns_null_when_none(): void
    {
        $this->assertNull($this->service->getByUserId(999));
    }

    public function test_getById_returns_seller(): void
    {
        $user = User::factory()->create();
        $created = $this->service->register($user->id, ['business_name' => 'Shop', 'province' => 'Lusaka', 'district' => 'Lusaka']);

        $result = $this->service->getById($created['id']);
        $this->assertNotNull($result);
        $this->assertEquals($created['id'], $result['id']);
    }

    public function test_approveKyc_activates_seller(): void
    {
        $user = User::factory()->create();
        $created = $this->service->register($user->id, ['business_name' => 'Shop', 'province' => 'Lusaka', 'district' => 'Lusaka']);

        $this->service->approveKyc($created['id']);

        $seller = MarketplaceSeller::find($created['id']);
        $this->assertEquals('approved', $seller->kyc_status);
        $this->assertEquals('verified', $seller->trust_level);
        $this->assertTrue((bool) $seller->is_active);
    }

    public function test_rejectKyc_updates_status(): void
    {
        $user = User::factory()->create();
        $created = $this->service->register($user->id, ['business_name' => 'Shop', 'province' => 'Lusaka', 'district' => 'Lusaka']);

        $this->service->rejectKyc($created['id'], 'Invalid documents');

        $seller = MarketplaceSeller::find($created['id']);
        $this->assertEquals('rejected', $seller->kyc_status);
    }

    public function test_updateProfile_changes_fields(): void
    {
        $user = User::factory()->create();
        $created = $this->service->register($user->id, ['business_name' => 'Shop', 'province' => 'Lusaka', 'district' => 'Lusaka']);

        $updated = $this->service->updateProfile($created['id'], [
            'business_name' => 'New Name',
            'phone' => '0977111111',
        ]);

        $this->assertEquals('New Name', $updated['business_name']);
        $this->assertEquals('0977111111', $updated['phone']);
    }

    public function test_getProvinces_returns_list(): void
    {
        $provinces = $this->service->getProvinces();
        $this->assertContains('Lusaka', $provinces);
        $this->assertCount(10, $provinces);
    }
}

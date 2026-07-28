<?php

namespace Tests\Feature\GrowNet;

use App\Domain\GrowNet\Services\StarterKitService;
use App\Infrastructure\Persistence\Eloquent\GrowNet\MemberAchievement;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class StarterKitServiceTest extends GrowNetTestCase
{
    private StarterKitService $starterKitService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->starterKitService = app(StarterKitService::class);
    }

    public function test_calculate_shop_credit_is_20_percent_of_price(): void
    {
        $ref = new \ReflectionMethod($this->starterKitService, 'calculateShopCredit');

        $this->assertEquals(60.0, $ref->invoke($this->starterKitService, 300.0));
        $this->assertEquals(100.0, $ref->invoke($this->starterKitService, 500.0));
        $this->assertEquals(200.0, $ref->invoke($this->starterKitService, 1000.0));
        $this->assertEquals(400.0, $ref->invoke($this->starterKitService, 2000.0));
        $this->assertEquals(0.0, $ref->invoke($this->starterKitService, 0.0));
    }

    public function test_starter_kit_tier_constants(): void
    {
        $this->assertEquals('lite', StarterKitService::TIER_LITE);
        $this->assertEquals('basic', StarterKitService::TIER_BASIC);
        $this->assertEquals('growth_plus', StarterKitService::TIER_GROWTH_PLUS);
        $this->assertEquals('pro', StarterKitService::TIER_PRO);
        $this->assertEquals('premium', StarterKitService::TIER_PREMIUM);
    }

    public function test_starter_kit_price_constants(): void
    {
        $this->assertEquals(300.00, StarterKitService::PRICE_LITE);
        $this->assertEquals(500.00, StarterKitService::PRICE_BASIC);
        $this->assertEquals(1000.00, StarterKitService::PRICE_GROWTH_PLUS);
        $this->assertEquals(2000.00, StarterKitService::PRICE_PRO);
        $this->assertEquals(1000.00, StarterKitService::PRICE_PREMIUM);
        $this->assertEquals(500.00, StarterKitService::PRICE); // BC compat
    }

    public function test_shop_credit_constants(): void
    {
        $this->assertEquals(50.00, StarterKitService::SHOP_CREDIT_LITE);
        $this->assertEquals(100.00, StarterKitService::SHOP_CREDIT_BASIC);
        $this->assertEquals(200.00, StarterKitService::SHOP_CREDIT_GROWTH_PLUS);
        $this->assertEquals(400.00, StarterKitService::SHOP_CREDIT_PRO);
        $this->assertEquals(200.00, StarterKitService::SHOP_CREDIT_PREMIUM);
        $this->assertEquals(90, StarterKitService::CREDIT_EXPIRY_DAYS);
    }

    public function test_award_achievement_creates_achievement(): void
    {
        $result = $this->starterKitService->awardAchievement($this->user, 'profile_completed');

        $this->assertNotNull($result);
        $this->assertInstanceOf(MemberAchievement::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('profile_completed', $result->achievement_type);
    }

    public function test_award_achievement_returns_null_for_duplicate(): void
    {
        $this->starterKitService->awardAchievement($this->user, 'profile_completed');
        $result = $this->starterKitService->awardAchievement($this->user, 'profile_completed');

        $this->assertNull($result);
    }

    public function test_award_achievement_returns_null_for_unknown_type(): void
    {
        $result = $this->starterKitService->awardAchievement($this->user, 'non_existent_type');

        $this->assertNull($result);
    }

    public function test_award_achievement_awards_lp_to_users_table(): void
    {
        $achievement = $this->starterKitService->awardAchievement($this->user, 'profile_completed');

        $this->assertNotNull($achievement);
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'life_points' => 25,
        ]);
    }

    public function test_award_achievement_does_not_award_lp_for_type_without_reward(): void
    {
        $this->starterKitService->awardAchievement($this->user, 'first_earner');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'life_points' => 0,
        ]);
    }

    public function test_expire_shop_credits_returns_zero_when_none_expired(): void
    {
        $count = $this->starterKitService->expireShopCredits();
        $this->assertEquals(0, $count);
    }

    public function test_process_unlocks_returns_zero_when_none_pending(): void
    {
        $count = $this->starterKitService->processUnlocks();
        $this->assertEquals(0, $count);
    }

    public function test_get_user_progress_without_kit(): void
    {
        $progress = $this->starterKitService->getUserProgress($this->user);

        $this->assertFalse($progress['has_starter_kit']);
    }
}

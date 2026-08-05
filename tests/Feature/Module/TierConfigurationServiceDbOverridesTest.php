<?php

declare(strict_types=1);

namespace Tests\Feature\Module;

use App\Domain\Module\Services\TierConfigurationService;
use App\Models\ModuleTier;
use App\Models\ModuleTierFeature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Admin-tier editing: DB records in module_tiers must override the
 * config defaults surfaced through TierConfigurationService (the same
 * service the plans page and GrowStream AccessControlService read from).
 */
class TierConfigurationServiceDbOverridesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function db_overrides_win_over_config_when_present(): void
    {
        $service = $this->app->make(TierConfigurationService::class);

        // Seed a DB tier for growstream overriding the config defaults
        $tier = ModuleTier::create([
            'module_id' => 'growstream',
            'tier_key' => 'starter',
            'name' => 'Starter (Admin Edited)',
            'description' => 'Edited from dashboard',
            'price_monthly' => 199,
            'price_annual' => 1990,
            'is_active' => true,
            'is_default' => false,
            'is_popular' => true,
            'sort_order' => 1,
        ]);

        ModuleTierFeature::create([
            'module_tier_id' => $tier->id,
            'feature_key' => 'watch_minutes_per_month',
            'feature_name' => 'Premium Views / Month',
            'feature_type' => 'limit',
            'value_limit' => 500,
            'is_active' => true,
        ]);

        $service->clearCache('growstream');

        $tiers = $service->getTiers('growstream');

        $this->assertArrayHasKey('starter', $tiers);
        $this->assertSame('Starter (Admin Edited)', $tiers['starter']['name']);
        $this->assertSame(199, (int) $tiers['starter']['price_monthly']);
        $this->assertTrue($tiers['starter']['popular']);
        $this->assertSame(500, $tiers['starter']['limits']['watch_minutes_per_month']);
    }

    #[Test]
    public function config_is_used_when_no_db_records_exist(): void
    {
        $service = $this->app->make(TierConfigurationService::class);

        $tiers = $service->getTiers('growstream');

        $this->assertArrayHasKey('starter', $tiers);
        $this->assertSame('Starter', $tiers['starter']['name']);
        $this->assertSame(35, (int) $tiers['starter']['price_monthly']);
        $this->assertSame(500, $tiers['starter']['limits']['watch_minutes_per_month']);
    }

    #[Test]
    public function unlimited_limit_survives_db_round_trip(): void
    {
        $service = $this->app->make(TierConfigurationService::class);

        $tier = ModuleTier::create([
            'module_id' => 'growstream',
            'tier_key' => 'business',
            'name' => 'Business',
            'price_monthly' => 549,
            'price_annual' => 5270,
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 2,
        ]);

        ModuleTierFeature::create([
            'module_tier_id' => $tier->id,
            'feature_key' => 'watch_minutes_per_month',
            'feature_name' => 'Premium Views / Month',
            'feature_type' => 'limit',
            'value_limit' => -1,
            'is_active' => true,
        ]);

        $service->clearCache('growstream');

        $this->assertSame(-1, $service->getTiers('growstream')['business']['limits']['watch_minutes_per_month']);
    }
}

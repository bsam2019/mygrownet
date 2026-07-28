<?php

namespace Tests\Feature\GrowNet;

use App\Models\SystemSetting;
use App\Domain\GrowNet\Services\CommissionSettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CommissionSettingsServiceTest extends GrowNetTestCase
{
    private CommissionSettingsService $commissionSettings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commissionSettings = app(CommissionSettingsService::class);
        Cache::flush();
    }

    public function test_default_base_percentage(): void
    {
        $this->assertEquals(50.0, $this->commissionSettings->getBasePercentage());
    }

    public function test_default_non_kit_multiplier(): void
    {
        $this->assertEquals(0.5, $this->commissionSettings->getNonKitMultiplier());
    }

    public function test_default_level_rates(): void
    {
        $rates = $this->commissionSettings->getLevelRates();

        $this->assertCount(7, $rates);
        $this->assertEquals(15.0, $rates[1]);
        $this->assertEquals(10.0, $rates[2]);
        $this->assertEquals(8.0, $rates[3]);
        $this->assertEquals(6.0, $rates[4]);
        $this->assertEquals(4.0, $rates[5]);
        $this->assertEquals(3.0, $rates[6]);
        $this->assertEquals(2.0, $rates[7]);
    }

    public function test_default_rate_for_level(): void
    {
        $this->assertEquals(15.0, $this->commissionSettings->getRateForLevel(1));
        $this->assertEquals(2.0, $this->commissionSettings->getRateForLevel(7));
    }

    public function test_rate_for_unknown_level_returns_zero(): void
    {
        $this->assertEquals(0.0, $this->commissionSettings->getRateForLevel(99));
    }

    public function test_default_is_enabled(): void
    {
        $this->assertTrue($this->commissionSettings->isEnabled());
    }

    public function test_calculate_base_amount(): void
    {
        $this->assertEquals(500.0, $this->commissionSettings->calculateBaseAmount(1000.0));
    }

    public function test_calculate_commission_with_kit(): void
    {
        $result = $this->commissionSettings->calculateCommission(1000.0, 1, true);

        $this->assertEquals(1000.0, $result['purchase_amount']);
        $this->assertEquals(50.0, $result['base_percentage']);
        $this->assertEquals(500.0, $result['base_amount']);
        $this->assertEquals(1, $result['level']);
        $this->assertEquals(15.0, $result['level_rate']);
        $this->assertTrue($result['referrer_has_kit']);
        $this->assertEquals(1.0, $result['non_kit_multiplier']);
        $this->assertEquals(75.0, $result['commission_amount']);
    }

    public function test_calculate_commission_without_kit(): void
    {
        $result = $this->commissionSettings->calculateCommission(1000.0, 2, false);

        $this->assertEquals(500.0, $result['base_amount']);
        $this->assertEquals(2, $result['level']);
        $this->assertEquals(10.0, $result['level_rate']);
        $this->assertFalse($result['referrer_has_kit']);
        $this->assertEquals(0.5, $result['non_kit_multiplier']);
        $this->assertEquals(25.0, $result['commission_amount']);
    }

    public function test_calculate_commission_with_unknown_level(): void
    {
        $result = $this->commissionSettings->calculateCommission(500.0, 99, true);

        $this->assertEquals(0.0, $result['level_rate']);
        $this->assertEquals(0.0, $result['commission_amount']);
    }

    public function test_get_all_settings_defaults(): void
    {
        $all = $this->commissionSettings->getAllSettings();

        $this->assertEquals(50.0, $all['base_percentage']);
        $this->assertEquals(50, $all['non_kit_multiplier_percentage']);
        $this->assertCount(7, $all['level_rates']);
        $this->assertTrue($all['enabled']);
        $this->assertEquals(48.0, $all['total_payout_percentage']);
    }

    public function test_custom_base_percentage_from_db(): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'commission_base_percentage'],
            ['value' => json_encode(60), 'description' => 'test']
        );

        Cache::flush();

        $this->assertEquals(60.0, $this->commissionSettings->getBasePercentage());
    }

    public function test_custom_non_kit_multiplier_from_db(): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'commission_non_kit_multiplier'],
            ['value' => json_encode(75), 'description' => 'test']
        );

        Cache::flush();

        $this->assertEquals(0.75, $this->commissionSettings->getNonKitMultiplier());
    }

    public function test_custom_level_rates_from_db(): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'commission_level_rates'],
            ['value' => json_encode([1 => 20.0, 2 => 15.0, 3 => 10.0]), 'description' => 'test']
        );

        Cache::flush();

        $rates = $this->commissionSettings->getLevelRates();
        $this->assertCount(3, $rates);
        $this->assertEquals(20.0, $rates[1]);
        $this->assertEquals(15.0, $rates[2]);
        $this->assertEquals(10.0, $rates[3]);
    }

    public function test_setting_disabled_from_db(): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'commission_enabled'],
            ['value' => json_encode(false), 'description' => 'test']
        );

        Cache::flush();

        $this->assertFalse($this->commissionSettings->isEnabled());
    }

    public function test_update_settings_persists_and_clears_cache(): void
    {
        $this->commissionSettings->updateSettings([
            'base_percentage' => 60,
            'non_kit_multiplier_percentage' => 75,
            'level_rates' => [1 => 20.0, 2 => 15.0],
        ]);

        $this->assertEquals(60.0, $this->commissionSettings->getBasePercentage());
        $this->assertEquals(0.75, $this->commissionSettings->getNonKitMultiplier());

        $rates = $this->commissionSettings->getLevelRates();
        $this->assertCount(2, $rates);
        $this->assertEquals(20.0, $rates[1]);
    }

    public function test_update_settings_persists_to_database(): void
    {
        $this->commissionSettings->updateSettings([
            'base_percentage' => 55,
        ]);

        Cache::flush();

        $this->assertEquals(55.0, $this->commissionSettings->getBasePercentage());

        $record = SystemSetting::where('key', 'commission_base_percentage')->first();
        $this->assertNotNull($record);
        $this->assertEquals(55, $record->value);
    }

    public function test_clear_cache(): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'commission_base_percentage'],
            ['value' => json_encode(70), 'description' => 'test']
        );

        $this->commissionSettings->clearCache();
        $this->assertEquals(70.0, $this->commissionSettings->getBasePercentage());
    }

    public function test_get_all_settings_reflects_updates(): void
    {
        $this->commissionSettings->updateSettings([
            'base_percentage' => 40,
        ]);

        $all = $this->commissionSettings->getAllSettings();
        $this->assertEquals(40.0, $all['base_percentage']);
    }
}

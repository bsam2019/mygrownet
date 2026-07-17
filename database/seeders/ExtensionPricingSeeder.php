<?php

namespace Database\Seeders;

use App\Models\StockAudit\Extension;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ExtensionPricingSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasColumn('extensions', 'price_monthly_usd')) return;

        $pricing = [
            'pharmacy'      => ['price_monthly_usd' => 29.00, 'price_yearly_usd' => 299.00, 'price_monthly_zmw' => 725.00, 'price_yearly_zmw' => 7475.00],
            'manufacturing' => ['price_monthly_usd' => 49.00, 'price_yearly_usd' => 499.00, 'price_monthly_zmw' => 1225.00, 'price_yearly_zmw' => 12475.00],
            'restaurant'    => ['price_monthly_usd' => 39.00, 'price_yearly_usd' => 399.00, 'price_monthly_zmw' => 975.00, 'price_yearly_zmw' => 9975.00],
            'supermarket'   => ['price_monthly_usd' => 49.00, 'price_yearly_usd' => 499.00, 'price_monthly_zmw' => 1225.00, 'price_yearly_zmw' => 12475.00],
            'hardware'      => ['price_monthly_usd' => 39.00, 'price_yearly_usd' => 399.00, 'price_monthly_zmw' => 975.00, 'price_yearly_zmw' => 9975.00],
            'electronics'   => ['price_monthly_usd' => 34.00, 'price_yearly_usd' => 349.00, 'price_monthly_zmw' => 850.00, 'price_yearly_zmw' => 8725.00],
        ];

        foreach ($pricing as $code => $prices) {
            Extension::where('code', $code)->update($prices);
        }
    }
}

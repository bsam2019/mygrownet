<?php

namespace Database\Seeders;

use App\Models\StockAudit\Extension;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ExtensionPricingSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasColumn('extensions', 'price_monthly')) return;

        $pricing = [
            'pharmacy'       => ['price_monthly' => 29.00, 'price_yearly' => 299.00],
            'manufacturing'  => ['price_monthly' => 49.00, 'price_yearly' => 499.00],
            'restaurant'     => ['price_monthly' => 39.00, 'price_yearly' => 399.00],
            'supermarket'    => ['price_monthly' => 49.00, 'price_yearly' => 499.00],
            'hardware'       => ['price_monthly' => 39.00, 'price_yearly' => 399.00],
            'electronics'    => ['price_monthly' => 34.00, 'price_yearly' => 349.00],
        ];

        foreach ($pricing as $code => $prices) {
            Extension::where('code', $code)->update($prices);
        }
    }
}

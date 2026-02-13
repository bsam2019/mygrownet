<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrenciesSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            // African Currencies
            ['code' => 'ZMW', 'name' => 'Zambian Kwacha', 'symbol' => 'K', 'decimal_places' => 2, 'format' => 'K{amount}'],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'decimal_places' => 2, 'format' => 'R{amount}'],
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'decimal_places' => 2, 'format' => '₦{amount}'],
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'decimal_places' => 2, 'format' => 'KSh{amount}'],
            ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => 'GH₵', 'decimal_places' => 2, 'format' => 'GH₵{amount}'],
            ['code' => 'TZS', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'decimal_places' => 2, 'format' => 'TSh{amount}'],
            ['code' => 'UGX', 'name' => 'Ugandan Shilling', 'symbol' => 'USh', 'decimal_places' => 0, 'format' => 'USh{amount}'],
            ['code' => 'MWK', 'name' => 'Malawian Kwacha', 'symbol' => 'MK', 'decimal_places' => 2, 'format' => 'MK{amount}'],
            ['code' => 'BWP', 'name' => 'Botswana Pula', 'symbol' => 'P', 'decimal_places' => 2, 'format' => 'P{amount}'],
            ['code' => 'ZWL', 'name' => 'Zimbabwean Dollar', 'symbol' => 'Z$', 'decimal_places' => 2, 'format' => 'Z${amount}'],
            
            // Major International Currencies
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2, 'format' => '${amount}'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2, 'format' => '€{amount}'],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'decimal_places' => 2, 'format' => '£{amount}'],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'decimal_places' => 0, 'format' => '¥{amount}'],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'decimal_places' => 2, 'format' => '¥{amount}'],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'decimal_places' => 2, 'format' => '₹{amount}'],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'decimal_places' => 2, 'format' => 'A${amount}'],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'decimal_places' => 2, 'format' => 'C${amount}'],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'decimal_places' => 2, 'format' => 'CHF{amount}'],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'decimal_places' => 2, 'format' => '{amount} د.إ'],
        ];

        foreach ($currencies as $currency) {
            DB::table('cms_currencies')->updateOrInsert(
                ['code' => $currency['code']],
                array_merge($currency, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

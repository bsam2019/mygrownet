<?php

namespace Database\Seeders\GrowStart;

use Illuminate\Database\Seeder;
use App\Models\GrowStart\Country;

class GrowStartCountriesSeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Zambia',
                'code' => 'ZMB',
                'currency' => 'ZMW',
                'currency_symbol' => 'K',
                'is_active' => true,
                'pack_version' => '1.0.0',
                'config' => [
                    'regulatory_agencies' => ['pacra', 'zra', 'napsa', 'zda'],
                    'provinces' => [
                        'Central', 'Copperbelt', 'Eastern', 'Luapula',
                        'Lusaka', 'Muchinga', 'Northern', 'North-Western',
                        'Southern', 'Western'
                    ],
                    'languages' => ['en'],
                ],
            ],
            [
                'name' => 'Malawi',
                'code' => 'MWI',
                'currency' => 'MWK',
                'currency_symbol' => 'MK',
                'is_active' => false,
                'pack_version' => null,
                'config' => null,
            ],
            [
                'name' => 'Botswana',
                'code' => 'BWA',
                'currency' => 'BWP',
                'currency_symbol' => 'P',
                'is_active' => false,
                'pack_version' => null,
                'config' => null,
            ],
            [
                'name' => 'Kenya',
                'code' => 'KEN',
                'currency' => 'KES',
                'currency_symbol' => 'KSh',
                'is_active' => false,
                'pack_version' => null,
                'config' => null,
            ],
            [
                'name' => 'South Africa',
                'code' => 'ZAF',
                'currency' => 'ZAR',
                'currency_symbol' => 'R',
                'is_active' => false,
                'pack_version' => null,
                'config' => null,
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                $country
            );
        }
    }
}
